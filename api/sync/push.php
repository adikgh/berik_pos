<?php
	// Кассадан серверге: заказдар (тауарларымен, статус тарихымен) және сменалар.
	// Әр жазбаның offline_uuid-і бар, сондықтан қайта жіберілсе, қосарланбайды, жаңартылады.
	require __DIR__.'/lib.php';

	$dev = device();
	$company = (int)$dev['company_id'];
	$branch = (int)$dev['branch_id'];
	$did = (int)$dev['id'];
	$b = body();
	$uuid_ok = function ($u) { return is_string($u) && preg_match('/^[a-f0-9-]{36}$/', $u); };

	$res = ['ok' => true, 'orders' => [], 'shifts' => [], 'errors' => []];

	// Клиент: телефон бойынша серверде табамыз, жоқ болса қосамыз
	function client_id($c, $company) {
		$phone = substr(preg_replace('/\D/', '', (string)@$c['phone']), -10);
		if (strlen($phone) != 10 || !table_exists('company_client')) return null;
		$pcol = has_col('company_client', 'phone') ? 'phone' : null;
		if (!$pcol) return null;
		$ex = row("SELECT id FROM company_client WHERE company_id = '$company' AND phone = '$phone' LIMIT 1");
		if ($ex) return (int)$ex['id'];
		$c['company_id'] = $company; $c['phone'] = $phone;
		$u = row("SELECT id FROM user WHERE phone = '$phone' LIMIT 1");
		$c['user_id'] = $u ? (int)$u['id'] : null;
		return save_row('company_client', $c);
	}

	foreach ((array)@$b['orders'] as $o) {
		$u = @$o['uuid'];
		if (!$uuid_ok($u) || !is_array(@$o['order'])) { $res['errors'][] = ['uuid' => $u, 'error' => 'bad_order']; continue; }
		db::query("START TRANSACTION");
		try {
			$ord = $o['order'];
			$ord['offline_uuid'] = $u; $ord['device_id'] = $did;
			$ord['company_id'] = $company; $ord['branch_id'] = $branch;
			unset($ord['synced_at']);
			if (is_array(@$o['client'])) $ord['client_id'] = client_id($o['client'], $company);
			elseif (array_key_exists('client_id', $ord)) $ord['client_id'] = null;

			$ex = row("SELECT id FROM retail_orders WHERE offline_uuid = '".esc($u)."' LIMIT 1");
			$sid = save_row('retail_orders', $ord, $ex ? (int)$ex['id'] : null);
			if (!$sid) throw new Exception('order_insert');

			// Тауарлар: толық ауыстырамыз (касса — заказдың жалғыз иесі)
			db::query("DELETE FROM retail_orders_products WHERE order_id = '$sid'");
			foreach ((array)@$o['products'] as $p) {
				if (!is_array($p)) continue;
				$p['order_id'] = $sid;
				save_row('retail_orders_products', $p);
			}

			// Тарих: тек әлі жоқ жазбалар қосылады
			if (table_exists('retail_orders_log')) foreach ((array)@$o['log'] as $l) {
				$lu = @$l['uuid'];
				if (!$uuid_ok($lu) || row("SELECT id FROM retail_orders_log WHERE offline_uuid = '".esc($lu)."' LIMIT 1")) continue;
				unset($l['uuid']); $l['offline_uuid'] = $lu; $l['order_id'] = $sid;
				save_row('retail_orders_log', $l);
			}
			db::query("COMMIT");
			$res['orders'][$u] = $sid;
		} catch (Throwable $e) {
			db::query("ROLLBACK");
			$res['errors'][] = ['uuid' => $u, 'error' => $e->getMessage()];
		}
	}

	foreach ((array)@$b['shifts'] as $s) {
		$u = @$s['uuid'];
		if (!$uuid_ok($u) || !is_array(@$s['shift'])) { $res['errors'][] = ['uuid' => $u, 'error' => 'bad_shift']; continue; }
		$sh = $s['shift'];
		$sh['offline_uuid'] = $u; $sh['device_id'] = $did;
		$sh['company_id'] = $company; $sh['branch_id'] = $branch; $sh['point_sale_id'] = $branch;
		unset($sh['synced_at']);
		$ex = row("SELECT id FROM retail_shifts WHERE offline_uuid = '".esc($u)."' LIMIT 1");
		if (!$ex) $sh['number'] = (int)@row("SELECT count(*) c FROM retail_shifts WHERE company_id = '$company' AND branch_id = '$branch'")['c'] + 1;
		else unset($sh['number']);
		$sid = save_row('retail_shifts', $sh, $ex ? (int)$ex['id'] : null);
		if ($sid) $res['shifts'][$u] = $sid; else $res['errors'][] = ['uuid' => $u, 'error' => 'shift_insert'];
	}

	db::query("UPDATE company_device SET last_push_dt = '".date('Y-m-d H:i:s')."' WHERE id = '$did'");
	out($res);
