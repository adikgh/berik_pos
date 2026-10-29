<?php
	// Құрылғыны бір рет байлау: телефон + кассир PIN-коды (company_staff.code).
	// Филиал company_branch.offline = 1 болуы керек. Жауапта құрылғы токені қайтады.
	require __DIR__.'/lib.php';

	$b = body();
	$phone = substr(preg_replace('/\D/', '', (string)@$b['phone']), -10);
	$code = preg_replace('/\D/', '', (string)@$b['code']);
	$name = mb_substr(trim(strip_tags((string)@$b['device_name'])), 0, 100);
	if (strlen($phone) != 10 || $code === '') fail('bad_input');

	// Тым көп қате әрекетті шектеу: бір IP-ден 10 минутта 10 рет
	$ip = esc(substr((string)@$_SERVER['REMOTE_ADDR'], 0, 45));
	$tmp = sys_get_temp_dir().'/berik_login_'.md5($ip);
	$tries = is_file($tmp) && filemtime($tmp) > time() - 600 ? (int)file_get_contents($tmp) : 0;
	if ($tries >= 10) fail('too_many', 429);

	$u = row("SELECT u.id, s.company_id, s.branch_id FROM user u JOIN company_staff s ON s.user_id = u.id
		WHERE u.phone = '$phone' AND s.code = '".esc($code)."' AND u.`right` = 1 LIMIT 1");
	if (!$u) { file_put_contents($tmp, $tries + 1); fail('bad_login', 401); }

	// Филиал: қызметкерге бекітілген филиал, әйтпесе компанияның offline филиалы (біреу болса)
	$branch_id = (int)@$b['branch_id'] ?: (int)$u['branch_id'];
	$branches = rows("SELECT id, name FROM company_branch WHERE company_id = '".(int)$u['company_id']."' AND offline = 1");
	if (!$branches) fail('no_offline_branch', 403);
	if (!$branch_id && count($branches) == 1) $branch_id = (int)$branches[0]['id'];
	$ok = false; foreach ($branches as $br) if ((int)$br['id'] == $branch_id) $ok = true;
	if (!$ok) out(['ok' => false, 'error' => 'choose_branch', 'branches' => $branches], 409);

	$token = bin2hex(random_bytes(32));
	$dt = date('Y-m-d H:i:s');
	db::query("INSERT INTO company_device (company_id, branch_id, user_id, name, token, ins_dt, last_dt, last_ip)
		VALUES ('".(int)$u['company_id']."', '$branch_id', '".(int)$u['id']."', ".sqlv($name ?: 'Касса').", '$token', '$dt', '$dt', '$ip')");
	$did = (int)mysqli_insert_id(db::$connect);
	if (!$did) fail('db_error', 500);
	@unlink($tmp);

	out(['ok' => true, 'token' => $token, 'device_id' => $did, 'company_id' => (int)$u['company_id'], 'branch_id' => $branch_id, 'api' => SYNC_API_VER]);
