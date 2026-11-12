<? include "../config/core.php";

	// Сайттан (QR меню / жеке кабинет) келген жаңа тапсырыстар: касса әр 10 секунд сайын сұрайды.
	// ?after=<id> — осы id-ден кейінгілер. after=0 болса тек ең соңғы id қайтады (бастапқы нүкте).
	header('Content-Type: application/json; charset=utf-8');
	header('Cache-Control: no-store');
	if (!$user_id) { echo json_encode(['ok' => false]); exit; }

	// retail_orders.source жоқ база (миграция әлі жүрмеген) — ештеңе қайтармаймыз
	if (!mysqli_num_rows(db::query("SHOW COLUMNS FROM retail_orders LIKE 'source'"))) { echo json_encode(['ok' => true, 'last' => 0, 'items' => []]); exit; }

	$cid = (int)$company;
	$cmp = fun::company($company);
	$where = "o.company_id = '$cid' and o.source in ('my', 'qr')";
	// show_branch = 0: кассир тек өз точкасын көреді
	if (isset($cmp['show_branch']) && $cmp['show_branch'] !== null && (int)$cmp['show_branch'] === 0) $where .= " and o.branch_id = '".(int)$branch."'";

	$after = (int)@$_GET['after'];
	$last = (int)@mysqli_fetch_row(db::query("select max(o.id) from retail_orders o where $where"))[0];
	// «Тапсырыстар» батырмасындағы сан: әлі «Жаңа» тұрған сайт тапсырыстары (соңғы 12 сағат)
	$cnt = (int)@mysqli_fetch_row(db::query("select count(*) from retail_orders o where $where and o.order_status = 1 and o.ins_dt > '".date('Y-m-d H:i:s', time() - 12 * 3600)."'"))[0];
	$items = [];
	if ($after > 0) {
		$since = date('Y-m-d H:i:s', time() - 6 * 3600);
		$q = db::query("select o.id, o.number, o.total, o.order_type, o.source, o.ins_dt, o.additional, b.code from retail_orders o left join company_branch b on b.id = o.branch_id
			where o.id > '$after' and $where and o.order_status = 1 and o.ins_dt > '$since' order by o.id asc limit 20");
		$tn = [1 => 'Жеткізу', 2 => 'Өзі алып кетеді', 3 => 'Сервис', 4 => 'Үстел (кафе)'];
		while ($r = mysqli_fetch_assoc($q)) {
			$items[] = [
				'id' => (int)$r['id'],
				'number' => (string)$r['code'].$r['number'],
				'total' => (int)$r['total'],
				'type' => $tn[(int)$r['order_type']] ?? '',
				'src' => $r['source'] === 'qr' ? 'QR меню' : 'Жеке кабинет',
				'time' => date('H:i', strtotime($r['ins_dt'])),
				'note' => mb_substr((string)$r['additional'], 0, 80),
			];
		}
	}
	echo json_encode(['ok' => true, 'last' => $last, 'cnt' => $cnt, 'items' => $items], JSON_UNESCAPED_UNICODE);
