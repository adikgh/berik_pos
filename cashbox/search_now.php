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
		while ($q && $x = mysqli_fetch_assoc($q)) { $n++; if (count($r) < 3) $r[] = htmlspecialchars(trim(preg_replace('/\s+/u', ' ', strip_tags(html_entity_decode((string)($x['name_kz'] ?: $x['name_ru']), ENT_QUOTES, 'UTF-8'))))).((int)$x['quantity'] > 1 ? ' ×'.(int)$x['quantity'] : ''); }
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

	// 4. Сыйлық: осы тапсырыс сыйлық па, келесі сыйлыққа дейін қанша қалды, деңгейлер тізімі
	if ($lc && $lc['gift'] && $cl) {
		$c = (int)$lc['gift_cnt']; $nx = $lc['next'];
		$out .= '<div class="ui_h">'.t::w('Gift', 'Сыйлық').' · '.$c.' '.t::w('orders done', 'тапсырыс жасалған').'</div><div class="ui_gift'.($lc['gift_due'] ? ' ui_gift_due' : '').'">';
		if ($lc['gift_due']) $out .= '<div class="ui_gift_t"><i class="far fa-gift"></i> '.$lc['gift_k'].t::w('-th order is a gift', '-шы тапсырыс — сыйлық').': <b>'.htmlspecialchars($lc['gift_name']).'</b></div>';
		$tg_k = $lc['gift_due'] ? $lc['gift_k'] : ($nx ? $nx['k'] : 0);
		if ($tg_k) {
			// белгілер: толық цикл 1..ең үлкен деңгей (мысалы 1–20); жасалғаны жасыл ✓, осы тапсырыс сары, сыйлық орындары белгімен
			$mx = 0; foreach ($lc['tiers'] as $t) $mx = max($mx, (int)$t['every']);
			$from = $mx ? (int)floor($c / $mx) * $mx : 0;
			$got = []; // берілген сыйлықтар: нешінші тапсырыста
			if (loyalty::tiers_on()) { $gq = db::query("select order_no from company_client_gift where client_id = '".(int)$cl['id']."' and order_no is not null"); while ($gq && $gr = mysqli_fetch_assoc($gq)) $got[(int)$gr['order_no']] = true; }
			$out .= '<div class="ui_stamps">';
			for ($i = $from + 1; $i <= $from + $mx; $i++) {
				$ti = loyalty::tier_for($cmp, $i);
				$out .= '<span class="ui_stamp'.($i <= $c ? ' ui_stamp_on' : '').($ti ? ' ui_stamp_gift' : '').($i == $lc['gift_k'] ? ' ui_stamp_now' : '').(isset($got[$i]) ? ' ui_stamp_got' : '').'" title="'.$i.($ti ? ': '.htmlspecialchars($ti['name']).(isset($got[$i]) ? ' ('.t::w('received', 'алды').')' : '') : '').'">'
					.($ti ? '<i class="far fa-gift"></i><small>'.$i.'</small>' : ($i <= $c ? '✓' : $i)).'</span>';
			}
			$out .= '</div>';
			if (!$lc['gift_due'] && $nx) $out .= '<div class="ui_gift_s">'.$nx['k'].t::w('-th order', '-шы тапсырыс').': <b>'.htmlspecialchars($nx['tier']['name']).'</b> · '.($nx['left'] ? $nx['left'].' '.t::w('orders left after this one', 'тапсырыс қалды (осыдан кейін)') : t::w('The very next order', 'Келесі тапсырыста')).'</div>';
			elseif ($nx) $out .= '<div class="ui_gift_s">'.t::w('Next gift', 'Келесі сыйлық').' — '.$nx['k'].t::w('-th order', '-шы тапсырыс').': '.htmlspecialchars($nx['tier']['name']).'</div>';
		}
		if (count($lc['tiers']) > 1) {
			$tl = array_reverse($lc['tiers']);
			$out .= '<div class="ui_tiers">';
			foreach ($tl as $t) $out .= '<div><span>'.t::w('Every', 'Әр').' '.(int)$t['every'].t::w('-th', '-шы').'</span>'.htmlspecialchars($t['name']).'</div>';
			$out .= '</div>';
		}
		$out .= '</div>';
	}

	echo $out;
