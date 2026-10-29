<?php
	// Серверден кассаға: базаның құрылымы (барлық кесте) + анықтамалық деректер (меню, қызметкерлер т.б.).
	// Заказдар мен сменалар тартылмайды: олар кассада жасалып, push.php арқылы серверге жүктеледі.
	require __DIR__.'/lib.php';

	$dev = device();
	$company = (int)$dev['company_id'];
	$branch = (int)$dev['branch_id'];

	// Деректері толық тартылатын кестелер (company_id бағаны болса, тек осы компанияныкі)
	$ref = ['company_staff', 'company_staff_positions', 'company_client',
		'product', 'product_item', 'product_item_quantity', 'product_catalog', 'product_warehouses',
		'product_item_color', 'product_item_size', 'product_ch_brand', 'product_ch_style', 'product_ch_country', 'product_ch_collection',
		'retail_orders_status', 'retail_orders_type', 'site', 'site_word', 'site_menu', 'word_block', 'info'];

	// Бұл кестелерде пароль сияқты құпия бағандар кассаға жіберілмейді
	$hide = ['user' => ['password', 'code'], 'company' => ['code', 'smsc_login', 'smsc_password', 'tg_token', 'kaspi_token']];

	$all = []; foreach (rows("SHOW TABLES") as $r) $all[] = array_values($r)[0];

	$schema = [];
	foreach ($all as $t) {
		if ($t == 'company_device') continue; // токендер кассаға керек емес
		$c = row("SHOW CREATE TABLE `".esc($t)."`");
		if ($c && isset($c['Create Table'])) $schema[$t] = $c['Create Table'];
	}

	function clean($t, $list, $hide) {
		if (!isset($hide[$t])) return $list;
		foreach ($list as &$r) foreach ($hide[$t] as $h) if (array_key_exists($h, $r)) $r[$h] = null;
		return $list;
	}

	$data = [];
	$data['company'] = clean('company', rows("SELECT * FROM company WHERE id = '$company'"), $hide);
	$data['company_branch'] = rows("SELECT * FROM company_branch WHERE id = '$branch'");
	foreach ($ref as $t) {
		if (!in_array($t, $all)) continue;
		$w = has_col($t, 'company_id') ? " WHERE company_id = '$company' OR company_id IS NULL" : '';
		$data[$t] = clean($t, rows("SELECT * FROM `$t`$w"), $hide);
	}
	// user: тек осы компанияның қызметкерлері мен клиенттері
	$uids = [];
	foreach ((array)@$data['company_staff'] as $r) if ((int)@$r['user_id']) $uids[(int)$r['user_id']] = 1;
	foreach ((array)@$data['company_client'] as $r) if ((int)@$r['user_id']) $uids[(int)$r['user_id']] = 1;
	$data['user'] = $uids ? clean('user', rows("SELECT * FROM user WHERE id IN (".implode(',', array_keys($uids)).")"), $hide) : [];

	out(['ok' => true, 'api' => SYNC_API_VER, 'time' => date('Y-m-d H:i:s'), 'company_id' => $company, 'branch_id' => $branch,
		'device_id' => (int)$dev['id'], 'schema' => $schema, 'data' => $data]);
