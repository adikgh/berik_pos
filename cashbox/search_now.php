<? include "../config/core.php";
	require_once "../config/loyalty.php";

	// Кассадағы «Тұтынушы жайлы» блогы. Ретімен: кэшбэк → бүгінгі заказ → соңғы 3 заказ → сыйлыққа дейін.
	// Ештеңе болмаса бос қайтарады (блок ашылмайды).
	if (!isset($_GET['search_now']) || !$user_id) exit();

	$cmp = (int)$company;
	$d = preg_replace('/\D+/', '', (string)@$_POST['inp']);
	if (strlen($d) == 11 && ($d[0] == '8' || $d[0] == '7')) $d = substr($d, 1);
	if (strlen($d) != 10) exit();
	$oid = (int)@$_POST['oid']; // қазір ашық тапсырыс (өзі тізімге кірмейді)

	$cl = mysqli_fetch_assoc(db::query("select * from company_client where company_id = '$cmp' and phone = '$d' limit 1"));
	$lc = null;
	if (loyalty::on() && loyalty::any($cmp)) { loyalty::sync($cmp); if ($cl) $cl = mysqli_fetch_assoc(db::query("select * from company_client where id = '".(int)$cl['id']."'")); $lc = loyalty::client_info($cmp, $cl, $oid); }

	// Тапсырыстар: клиентке байланған не нөмірі сәйкес, тек сақталған (paid = 1)
	$who = "(o.phone like '%$d'".($cl ? " or o.client_id = '".(int)$cl['id']."'" : "").")";
	$base = "from retail_orders o where o.company_id = '$cmp' and o.paid = 1 and $who".($oid ? " and o.id <> '$oid'" : "");
	$today = db::query("select o.* $base and o.ins_dt between '$start_cdate' and '$end_cdate' order by o.ins_dt desc limit 5");
	$prev = db::query("select o.* $base and o.ins_dt < '$start_cdate' order by o.ins_dt desc limit 3");

	$types = [1 => t::w('Delivery', 'Жеткізу'), 2 => t::w('Takeaway', 'Өзімен'), 3 => t::w('Service', 'Сервис'), 4 => t::w('Cafe', 'Кафе')];
	$sum = function ($v) { return number_format((int)$v, 0, '', ' ').' тг'; };
	$items = function ($id) {
		$r = []; $n = 0;
		$q = db::query("select p.name_kz, p.name_ru, op.quantity from retail_orders_products op left join product p on p.id = op.product_id where op.order_id = '".(int)$id."' order by op.id asc");
		while ($q && $x = mysqli_fetch_assoc($q)) { $n++; if (count($r) < 3) $r[] = htmlspecialchars(trim((string)($x['name_kz'] ?: $x['name_ru']))).((int)$x['quantity'] > 1 ? ' ×'.(int)$x['quantity'] : ''); }
		return implode(', ', $r).($n > 3 ? ' +'.($n - 3) : '');
	};
	$row = function ($o, $with_date) use ($types, $sum, $items) {
		$st = fun::order_sts($o['order_status']);
		$st_name = t::w('st:'.trim((string)@$st['name_ru']), (string)@$st['name_kz']);
		$clr = (string)@$st['clr'];
		$t = strtotime($o['ins_dt']);
		ob_start(); ?>
		<div class="ui_o">
			<div class="ui_o_nmb"><?=($o['number'] ?: '—')?></div>
			<div class="ui_o_c">
				<div class="ui_o_top">
					<b><?=$sum($o['total'])?></b>
					<span class="ui_o_st" style="<?=($clr ? 'color:'.$clr.';background:'.$clr.'1a' : '')?>"><?=htmlspecialchars($st_name)?></span>
				</div>
				<div class="ui_o_items"><?=$items($o['id'])?></div>
				<div class="ui_o_meta"><?=($with_date ? date('d.m.Y', $t).' · ' : '').date('H:i', $t)?> · <?=@$types[(int)$o['order_type']]?><?=($o['address'] ? ' · '.htmlspecialchars(mb_substr($o['address'], 0, 40)) : '')?></div>
			</div>
		</div>
		<? return ob_get_clean();
	};

	$out = '';

	// 1. Кэшбэк
	if ($lc && $lc['cashback'] && $lc['balance'] > 0) {
		$out .= '<div class="ui_cb"><div><i class="far fa-coins"></i> '.t::w('Cashback balance', 'Жиналған кэшбэк').'</div><b>'.$sum($lc['balance']).'</b></div>';
	}

	// 2. Бүгінгі заказ
	$h = ''; while ($today && $o = mysqli_fetch_assoc($today)) $h .= $row($o, false);
	if ($h) $out .= '<div class="ui_h ui_h_today">'.t::w('Ordered today', 'Бұл нөмерде бүгін заказ болған').'</div>'.$h;

	// 3. Соңғы 3 заказ
	$h = ''; while ($prev && $o = mysqli_fetch_assoc($prev)) $h .= $row($o, true);
	if ($h) $out .= '<div class="ui_h">'.t::w('Last orders', 'Соңғы заказдары').'</div>'.$h;

	// 4. Сыйлық
	if ($lc && $lc['gift'] && $cl) {
		$n = $lc['gift_every']; $c = min($lc['gift_cnt'], $n - 1);
		$out .= '<div class="ui_h">'.t::w('Gift', 'Сыйлық').'</div><div class="ui_gift'.($lc['gift_due'] ? ' ui_gift_due' : '').'">';
		$out .= '<div class="ui_gift_t">'.($lc['gift_due']
			? '<i class="far fa-gift"></i> '.t::w('This order is a gift', 'Бұл тапсырыс — сыйлық').': <b>'.htmlspecialchars($lc['gift_name']).'</b>'
			: $n.t::w('-th order gift', '-шы тапсырысқа сыйлық').': <b>'.htmlspecialchars($lc['gift_name']).'</b>').'</div>';
		$out .= '<div class="ui_stamps">';
		for ($i = 1; $i < $n; $i++) $out .= '<span class="ui_stamp'.($i <= $c ? ' ui_stamp_on' : '').'"></span>';
		$out .= '<span class="ui_stamp ui_stamp_gift'.($lc['gift_due'] ? ' ui_stamp_on' : '').'"><i class="far fa-gift"></i></span></div>';
		if (!$lc['gift_due']) $out .= '<div class="ui_gift_s">'.$c.' / '.$n.' · '.($n - 1 - $c).' '.t::w('orders left', 'тапсырыс қалды').'</div>';
		$out .= '</div>';
	}

	echo $out;
