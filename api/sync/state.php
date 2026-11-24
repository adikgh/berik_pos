<?php
	// Серверден кассаға: осы точканың соңғы заказдары (тауарларымен, тарихымен) және жабылған сменалары.
	// Программа интернет бар кезде минут сайын шақырады: интернет үзілсе, касса жергілікті режимде
	// ашық заказдар мен сменадан жалғастырады (QR/сайт заказдары, курьер статустары да келеді).
	// Клиенттердің деректері жіберілмейді (user 2026-11-24).
	// 2026-11-24: бұрынғы заказдар да (бүкіл тарих) бөліп-бөліп жіберіледі: касса before_id береді, 300-300 ескіге қарай
	// («интернет бар кезде заказдардың бәрі көрінсін» — user).
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

	function order_items($sql) {
		$orders = [];
		foreach (rows($sql) as $o) {
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
		return $orders;
	}
	$orders = order_items("SELECT * FROM retail_orders WHERE $w ORDER BY id");

	// Тарих: before_id-ден ескі заказдар (жоғарыдағы терезеге кірмегендері), 300-ден
	$history = []; $hist_next = 0;
	if (isset($b['before_id'])) {
		$before = (int)$b['before_id'];
		$hw = "company_id = '$company' AND branch_id = '$branch' AND paid = 1 AND NOT ($w)".($before > 0 ? " AND id < '$before'" : '');
		$ids = [];
		foreach (rows("SELECT id FROM retail_orders WHERE $hw ORDER BY id DESC LIMIT 300") as $r) $ids[] = (int)$r['id'];
		if ($ids) {
			give_uuid('retail_orders', "id IN (".implode(',', $ids).")");
			$history = order_items("SELECT * FROM retail_orders WHERE id IN (".implode(',', $ids).") ORDER BY id DESC");
			$hist_next = count($ids) == 300 ? min($ids) : 0;
		}
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
	$hist = isset($b['before_id']) ? ['history' => $history, 'hist_next' => $hist_next] : [];
	if (@$b['hash'] === $hash) out(['ok' => true, 'same' => true, 'hash' => $hash] + $hist);
	out(['ok' => true, 'hash' => $hash, 'time' => date('Y-m-d H:i:s'), 'from' => $from, 'orders' => $orders, 'shifts' => $shifts] + $hist);
