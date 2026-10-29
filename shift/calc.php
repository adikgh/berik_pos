<?
	// Кассирдің өз қабылдаған ақшасы:
	//  Жеткізу (1) / Өзімен (2) — тек предоплата (қалғанын курьер алады)
	//  Кафе (4) — барлық сома
	//  Сервис (3) — бөлек есеп (ақшасы сервистен түседі)
	function shift_calc($company, $branch, $user_id, $start, $end) {
		$col = function($c) { return mysqli_num_rows(db::query("SHOW COLUMNS FROM `retail_orders` LIKE '$c'")) > 0; };
		$has = ['pay_prepay' => $col('pay_prepay'), 'prepay_method' => $col('prepay_method'), 'pay_transfer' => $col('pay_transfer'), 'service' => $col('service')];

		// «Отказ» (money=none) есепке кірмейді; «Отказ при получение» / «Отказ вне» (money=kept) — тек бізде қалған ақша (kept_sum)
		$st_off = []; $st_none = []; $st_kept = [];
		$q = db::query("select id, money from retail_orders_status where money <> 'full'");
		while ($r = mysqli_fetch_assoc($q)) { $st_off[] = (int)$r['id']; if ($r['money'] == 'kept') $st_kept[] = (int)$r['id']; else $st_none[] = (int)$r['id']; }
		$st_cond = $st_none ? "and (order_status is null or order_status not in (".implode(',', $st_none)."))" : '';

		$res = ['cash' => 0, 'qr' => 0, 'transfer' => 0, 'red' => 0, 'my' => 0, 'cnt' => 0,
			'pre_cnt' => 0, 'pre_sum' => 0, 'cafe_cnt' => 0, 'cafe_sum' => 0,
			'srv' => [], 'srv_cnt' => 0, 'srv_sum' => 0, 'off' => 0];

		$base = "from retail_orders where company_id = '$company' and branch_id = '$branch' and user_id = '$user_id' and paid = 1 and ins_dt >= '$start' and ins_dt <= '$end'";
		if ($st_off) $res['off'] = (int)mysqli_fetch_assoc(db::query("select count(*) c $base and order_status in (".implode(',', $st_off).")"))['c'];

		$q = db::query("select * $base $st_cond");
		while ($o = mysqli_fetch_assoc($q)) {
			$t = (int)$o['order_type'];
			$kept = in_array((int)$o['order_status'], $st_kept) ? max(0, (int)@$o['kept_sum']) : -1; // -1: отказ емес
			$cash = (int)$o['pay_cash']; $qr = (int)$o['pay_qr'];
			$tr = $has['pay_transfer'] ? (int)$o['pay_transfer'] : 0;
			if ($t == 3) {
				$name = ($has['service'] && trim((string)$o['service']) !== '') ? trim($o['service']) : 'Басқа';
				if (!isset($res['srv'][$name])) $res['srv'][$name] = ['cnt' => 0, 'sum' => 0];
				$srv_sum = $kept >= 0 ? $kept : (int)$o['total'];
				$res['srv'][$name]['cnt']++; $res['srv'][$name]['sum'] += $srv_sum;
				$res['srv_cnt']++; $res['srv_sum'] += $srv_sum;
				continue;
			}
			if ($t == 4) {
				$res['cash'] += $cash; $res['qr'] += $qr; $res['transfer'] += $tr;
				$res['cafe_cnt']++; $res['cafe_sum'] += $cash + $qr + $tr;
				continue;
			}
			// Жеткізу / Өзімен: тек предоплата
			$pre = $has['pay_prepay'] ? (int)$o['pay_prepay'] : $qr;
			if ($kept >= 0) $pre = min($pre, $kept); // отказ: предоплатадан тек бізде қалғаны
			if ($pre <= 0) continue;
			$m = $has['prepay_method'] ? (string)$o['prepay_method'] : '';
			if ($m == 'cash') $res['cash'] += $pre;
			elseif ($m == 'transfer') $res['transfer'] += $pre;
			elseif ($m == 'red') $res['red'] += $pre;
			elseif ($m == 'mix') {
				$res['transfer'] += $tr; $res['qr'] += $qr;
				$res['cash'] += max(0, $pre - $tr - $qr);
			} else $res['qr'] += $pre;
			$res['pre_cnt']++; $res['pre_sum'] += $pre;
		}
		$res['my'] = $res['cash'] + $res['qr'] + $res['transfer'] + $res['red'];
		$res['cnt'] = $res['pre_cnt'] + $res['cafe_cnt'];
		return $res;
	}
