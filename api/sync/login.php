<?php
	// Құрылғыны бір рет байлау: компанияның коды (company.code, мысалы #n135; # белгісіз де болады).
	// Код арқылы компания табылады, точканы таңдатады. Жауапта құрылғы токені қайтады.
	// Кейін кассирлер офлайнда өз PIN-кодымен кіреді (кассаның кәдімгі кіруі).
	require __DIR__.'/lib.php';

	$b = body();
	$code = strtolower(preg_replace('/[^a-z0-9]/i', '', (string)@$b['company_code']));
	$name = mb_substr(trim(strip_tags((string)@$b['device_name'])), 0, 100);
	if (strlen($code) < 3 || strlen($code) > 10) fail('bad_input');

	// Код қысқа, сондықтан қате әрекетті шектейміз: бір IP-ден 10 минутта 10 рет
	$ip = esc(substr((string)@$_SERVER['REMOTE_ADDR'], 0, 45));
	$tmp = sys_get_temp_dir().'/berik_login_'.md5($ip);
	$tries = is_file($tmp) && filemtime($tmp) > time() - 600 ? (int)file_get_contents($tmp) : 0;
	if ($tries >= 10) fail('too_many', 429);

	$c = row("SELECT id, name FROM company WHERE LOWER(TRIM(LEADING '#' FROM code)) = '$code' LIMIT 1");
	if (!$c) { file_put_contents($tmp, $tries + 1); fail('bad_code', 401); }
	$company = (int)$c['id'];

	// Точка: компанияда біреу болса — өзі, бірнешеу болса — тізім қайтады, программа таңдатады
	$branch_id = (int)@$b['branch_id'];
	$branches = rows("SELECT id, name FROM company_branch WHERE company_id = '$company' ORDER BY id");
	if (!$branches) fail('no_branch', 403);
	if (!$branch_id && count($branches) == 1) $branch_id = (int)$branches[0]['id'];
	$ok = false; foreach ($branches as $br) if ((int)$br['id'] == $branch_id) $ok = true;
	if (!$ok) out(['ok' => false, 'error' => 'choose_branch', 'company' => $c['name'], 'branches' => $branches], 409);

	$token = bin2hex(random_bytes(32));
	$dt = date('Y-m-d H:i:s');
	db::query("INSERT INTO company_device (company_id, branch_id, name, token, ins_dt, last_dt, last_ip)
		VALUES ('$company', '$branch_id', ".sqlv($name ?: 'Касса').", '$token', '$dt', '$dt', '$ip')");
	$did = (int)mysqli_insert_id(db::$connect);
	if (!$did) fail('db_error', 500);
	@unlink($tmp);

	out(['ok' => true, 'token' => $token, 'device_id' => $did, 'company_id' => $company, 'branch_id' => $branch_id, 'api' => SYNC_API_VER]);
