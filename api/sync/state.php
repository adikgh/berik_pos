<?php
	// Серверден кассаға: осы точканың соңғы заказдары (тауарларымен, тарихымен) және жабылған сменалары.
	// Программа интернет бар кезде минут сайын шақырады: интернет үзілсе, касса жергілікті режимде
	// ашық заказдар мен сменадан жалғастырады (QR/сайт заказдары, курьер статустары да келеді).
	// Клиенттердің деректері жіберілмейді (user 2026-11-24).
	require __DIR__.'/lib.php';

	$dev = device();
	$company = (int)$dev['company_id'];
	$branch = (int)$dev['branch_id'];
	$b = body();

	// Әр жазбаның offline_uuid-і болуы керек: касса жазбаны сол арқылы таниды, қайта жүктегенде қосарланбайды
	function give_uuid($table, $where) {
		if (!has_col($table, 'offline_uuid')) return;
		foreach (rows("SELECT id FROM `$table` WHERE offline_uuid IS NULL AND $where") as $r)
			db::query("UPDATE `$table` SET offline_uuid = UUID() WHERE id = '".(int)$r['id']."' AND offline_uuid IS NULL");
	}

	$closed = '4, 5, 6, 10';
	$from = date('Y-m-d H:i:s', strtotime('-2 day'));
	$old = date('Y-m-d H:i:s', strtotime('-14 day'));
	$w = "company_id = '$company' AND branch_id = '$branch' AND paid = 1
		AND (ins_dt >= '$from' OR (ins_dt >= '$old' AND (order_status IS NULL OR order_status NOT IN ($closed))))";
	give_uuid('retail_orders', $w);

	$orders = [];
	foreach (rows("SELECT * FROM retail_orders WHERE $w ORDER BY id") as $o) {
		$oid = (int)$o['id'];
		$prod = rows("SELECT * FROM retail_orders_products WHERE order_id = '$oid' ORDER BY id");
		foreach ($prod as &$p) unset($p['id'], $p['order_id']); unset($p);
		$log = [];
		if (table_exists('retail_orders_log')) {
			give_uuid('retail_orders_log', "order_id = '$oid'");
			foreach (rows("SELECT * FROM retail_orders_log WHERE order_id = '$oid' ORDER BY id") as $l) {
				$l['uuid'] = $l['offline_uuid']; unset($l['id'], $l['order_id'], $l['offline_uuid']); $log[] = $l;
			}
		}
		$u = $o['offline_uuid'];
		unset($o['offline_uuid'], $o['device_id']);
		$o['server_id'] = $oid; unset($o['id']);
		$orders[] = ['uuid' => $u, 'order' => $o, 'products' => $prod, 'log' => $log];
	}

	// Сменалар: соңғы 10 жабылған (смена есебі «соңғы жабылған сменадан бері» есептеледі)
	$shifts = [];
	if (table_exists('retail_shifts') && has_col('retail_shifts', 'company_id')) {
		$ids = [];
		foreach (rows("SELECT id FROM retail_shifts WHERE company_id = '$company' AND branch_id = '$branch' ORDER BY id DESC LIMIT 10") as $r) $ids[] = (int)$r['id'];
		if ($ids) {
			give_uuid('retail_shifts', "id IN (".implode(',', $ids).")");
			foreach (rows("SELECT * FROM retail_shifts WHERE id IN (".implode(',', $ids).") ORDER BY id") as $s) {
				$u = $s['offline_uuid']; $s['server_id'] = (int)$s['id'];
				unset($s['id'], $s['offline_uuid'], $s['device_id']);
				$shifts[] = ['uuid' => $u, 'shift' => $s];
			}
		}
	}

	// Өзгеріс болмаса, деректі қайта жібермейміз
	$hash = md5(json_encode([$orders, $shifts]));
	if (@$b['hash'] === $hash) out(['ok' => true, 'same' => true, 'hash' => $hash]);
	out(['ok' => true, 'hash' => $hash, 'time' => date('Y-m-d H:i:s'), 'from' => $from, 'orders' => $orders, 'shifts' => $shifts]);
