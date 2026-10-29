<? include "../config/core.php";
   require_once "../config/loyalty.php";
   if ($user_id) loyalty::sync($company); // кэшбэк: аяқталған/отказ тапсырыстарды реттеу (15 секундта бір рет)
   
   // returns
	if(isset($_GET['return_sn'])) {
		$id = strip_tags($_POST['id']);
      $order_d = fun::retailo($id);
      $total = $order_d['total'];
      $quantity_all = $order_d['quantity'];

      $return_id = (mysqli_fetch_assoc(db::query("SELECT * FROM `retail_returns` order by id desc")))['id'] + 1;
      $ins = db::query("INSERT INTO `retail_returns`(`id`, `user_id`, `returns`, `total`, `quantity`) VALUES ('$return_id', '$user_id', 1, '$total', '$quantity_all')");
      $upd = db::query("UPDATE retail_orders SET returned = 1 WHERE id = '$id'");

      $op = db::query("select * from retail_orders_products where order_id = '$id'");
		if (mysqli_num_rows($op)) {
         while ($op_d = mysqli_fetch_array($op)) {
            $product_id = $op_d['product_id'];
            $item_id = $op_d['product_item_id'];
            $quantity = $op_d['quantity'];
            $price = $op_d['price'];
            $ins = db::query("INSERT INTO `retail_returns_products`(`return_id`, `product_id`, `product_item_id`, `quantity`, `price`) VALUES ('$return_id', '$product_id', '$item_id', '$quantity', '$price')");
            
            $pq_id = (mysqli_fetch_array(db::query("select * from product_item_quantity where item_id = '$item_id' limit 1")))['id'];
            $upd = db::query("UPDATE `product_item_quantity` SET quantity = quantity + '$quantity' WHERE id = '$pq_id'");
         
         }

         $arr = array(
            'Номер возврата: ' => $return_id,
            'Сумма: ' => $total.' тг',
            'Количество: '	=> $quantity_all.' шт',
            'Продавец: ' => $user['name'],
         );
         foreach($arr as $key => $value) {$txt .= "<b>".$key."</b> ".$value."%0A";};
         $sendToTelegram = fopen("https://api.telegram.org/bot{$token}/sendMessage?chat_id={$chat_id}&parse_mode=html&text={$txt}","r");
         // if ($sendToTelegram) echo "yes"; else echo "error";

         echo 'yes';
      } else echo 'none';

      exit();
	}
   
   
   
 

   // 
	if(isset($_GET['delete'])) {
      $id = strip_tags($_POST['id']);
      $del = db::query("DELETE FROM `retail_orders` where id = '$id'");
      if ($del) echo 'yes';

      exit();
	}



   include_once __DIR__.'/log.php';

   // Карточкадағы тарих терезесі
   if(isset($_GET['status_log'])) {
      if (session_status() == PHP_SESSION_ACTIVE) session_write_close(); // сессия бұғаттамасын (басқа сұраныстарды күтпейді)
      $oid = (int)@$_POST['id'];
      $o = mysqli_fetch_assoc(db::query("select * from retail_orders where id = '$oid' limit 1"));
      if (!$o) exit();
      if (!isset($st_colors_db)) { $st_colors_db = []; $stc_q = db::query("select name_ru, clr from retail_orders_status"); while ($stc = mysqli_fetch_assoc($stc_q)) $st_colors_db[trim($stc["name_ru"])] = $stc["clr"]; } $st_colors = $st_colors_db; // түстер базадан: retail_orders_status.clr
      $last = [];
      if (order_log_on()) {
         $lg = db::query("select new_value sid, max(ins_dt) dt from retail_orders_log where order_id = '$oid' and field = 'status' group by new_value");
         while ($l = mysqli_fetch_assoc($lg)) $last[(int)$l['sid']] = $l['dt'];
      }
      $sts = db::query("select * from retail_orders_status order by number asc");
      $first = true;
      $cur = (int)$o['order_status'];
      $rows = []; $st_names = [];
      while ($s = mysqli_fetch_assoc($sts)) {
         $sid = (int)$s['id']; $nm = trim($s['name_ru']); $st_names[$sid] = $nm;
         $dt = @$last[$sid];
         if ($first) $dt = $o['ins_dt']; // бірінші статус (Жаңа): әрқашан тапсырыс жасалған уақыт
         $first = false;
         if ($nm == 'Вопрос' && !$dt && $cur != $sid) continue;
         if (!in_array((string)@$o['order_type'], explode(',', (string)@$s['types'])) && !$dt && $cur != $sid) continue; // тапсырыс түріне жатпайтын статустар жоқ
         $clr = isset($st_colors[$nm]) ? $st_colors[$nm] : ($s['clr'] ?: '#9aa0a6');
         $rows[] = ['nm' => t::w('st:'.$nm, $nm), 'raw' => $nm, 'clr' => $clr, 'dt' => $dt, 'on' => ($dt || $cur == $sid), 'cur' => $cur == $sid];
      }
      // Қазіргі этаптан кейінгілер өтпеген болып саналады (кейін қайта ауыстырылса да)
      $ci = -1; foreach ($rows as $k => $r) if ($r['cur']) $ci = $k;
      if ($ci >= 0) foreach ($rows as $k => $r) if ($k > $ci) { $rows[$k]['on'] = false; $rows[$k]['dt'] = null; }
      // Аттап кеткен этаптар сызылады: қазіргіге дейінгі өтпегендер, ал аяқталса/отказ болса — қалғанның бәрі
      $last_on = -1; foreach ($rows as $k => $r) if ($r['on']) $last_on = $k;
      $cur_nm = trim((string)@$st_names[$cur]);
      $final = in_array($cur, [4, 5, 6, 10]); // Аяқталды / барлық отказ
      foreach ($rows as $k => $r) $rows[$k]['skip'] = !$r['on'] && ($k < $last_on || $final);
      if ($o['preorder_dt']) $rows[] = ['nm' => t::w('Preorder', 'Предзаказ'), 'clr' => '#e5484d', 'dt' => $o['preorder_dt'], 'on' => true, 'cur' => false];

      // 2-бөлім: өзгерістер (қысқаша)
      $ch_rows = [];
      if (order_log_on()) {
         $lbl = ['created' => t::w('Created', 'Жасалды'), 'order_type' => t::w('Type', 'Түрі'), 'courier' => t::w('Courier', 'Курьер'), 'branch_id' => t::w('Branch', 'Точка'), 'phone' => t::w('Phone', 'Номер'), 'address' => t::w('Address', 'Адрес'), 'floor_apt' => t::w('Floor / apt', 'Этаж / кв'), 'additional' => t::w('Notes', 'Қосымша'), 'preorder_dt' => t::w('Preorder', 'Предзаказ'), 'total' => t::w('Total', 'Жалпы'), 'pay_delivery' => t::w('Delivery', 'Доставка'), 'pay_qr' => t::w('Prepayment', 'Предоплата'), 'pay_cash' => t::w('Cash', 'Нал'), 'pay_prepay' => t::w('Prepayment', 'Предоплата'), 'prepay_method' => t::w('Prepay type', 'Предоплата түрі'), 'payment_method' => t::w('Payment', 'Төлем'), 'pay_transfer' => t::w('Transfer', 'Аударым'), 'pay_red' => 'RED', 'service' => t::w('Service', 'Сервис'), 'items' => t::w('Items', 'Тағамдар'), 'refuse_reason' => t::w('Refusal reason', 'Отказ себебі'), 'kept_sum' => t::w('Kept money', 'Бізде қалды')];
         $pm = ['transfer' => t::w('Transfer', 'Аударым'), 'qr' => 'Kaspi QR', 'cash' => t::w('Cash', 'Қолма-қол'), 'mixed' => t::w('Mixed', 'Аралас'), 'unpaid' => t::w('Unpaid', 'Төленбеген'), 'red' => 'RED', 'mix' => t::w('Mixed', 'Аралас')];
         $types = []; $tq = db::query("select id, name from retail_orders_type"); while ($t = mysqli_fetch_assoc($tq)) $types[$t['id']] = $t['name'];
         $hv = function ($f, $v) use ($pm, $types) {
            if ($v === null || $v === '') return '—';
            $v = trim(html_entity_decode(strip_tags((string)$v))); // атауларда <b> тегтері болуы мүмкін
            if ($f == 'order_type') return @$types[$v] ?: $v;
            if ($f == 'courier') { $u = fun::user($v); return @$u['name'] ?: $v; }
            if ($f == 'branch_id') { $b = fun::branch($v); return @$b['name'] ?: $v; }
            if ($f == 'payment_method' || $f == 'prepay_method') return @$pm[$v] ?: $v;
            if (in_array($f, ['total','pay_delivery','pay_qr','pay_cash','pay_prepay','pay_transfer','pay_red','kept_sum'])) return number_format((float)$v, 0, '', ' ').' тг';
            if ($f == 'phone' && strlen(preg_replace('/\D/', '', $v)) == 10) { $p = preg_replace('/\D/', '', $v); return '8 ('.substr($p,0,3).') '.substr($p,3,3).'-'.substr($p,6,2).'-'.substr($p,8,2); }
            return $v;
         };
         // Тағамдар: тек айырмасы — «+Месси», «−Кола», «Мия 1→2»
         $parse = function ($v) { $m = []; foreach (array_filter(array_map('trim', explode(';', trim(html_entity_decode(strip_tags((string)$v)))))) as $it) { if (preg_match('/^(.*?)\s*×(\d+)$/u', $it, $x)) $m[trim($x[1])] = (int)$x[2]; else $m[$it] = 1; } return $m; };
         $idiff = function ($old, $new) use ($parse) {
            $a = $parse($old); $b = $parse($new); $out = [];
            foreach ($b as $n => $q) if (!isset($a[$n])) $out[] = ['add', '+'.$n.($q > 1 ? ' ×'.$q : '')]; elseif ($a[$n] != $q) $out[] = ['qn', $n.' '.$a[$n].'→'.$q];
            foreach ($a as $n => $q) if (!isset($b[$n])) $out[] = ['del', '−'.$n];
            return $out;
         };
         $ch = db::query("select * from retail_orders_log where order_id = '$oid' and field <> 'status' order by id desc limit 60");
         $all = []; while ($c = mysqli_fetch_assoc($ch)) $all[] = $c;
         // Тағам өзгергенде сома (Жалпы, нал) өзі қайта есептеледі: оларды бөлек жол етпей, тағамдар жолына қосамыз
         $it_at = []; foreach ($all as $c) if ($c['field'] == 'items') $it_at[$c['ins_dt']] = true;
         $sum_at = []; foreach ($all as $c) if ($c['field'] == 'total' && isset($it_at[$c['ins_dt']])) $sum_at[$c['ins_dt']] = $c;
         foreach ($all as $c) {
            $f = $c['field']; $u = $c['user_id'] ? fun::user($c['user_id']) : null;
            if (($f == 'total' || $f == 'pay_cash') && isset($it_at[$c['ins_dt']])) continue;
            if ($f == 'created') $val = '<span class="oh_new">'.htmlspecialchars((string)$c['new_value']).'</span>';
            elseif ($f == 'items') {
               $val = ''; foreach ($idiff($c['old_value'], $c['new_value']) as $d) $val .= '<span class="oh_it oh_it_'.$d[0].'">'.htmlspecialchars($d[1]).'</span>';
               if (isset($sum_at[$c['ins_dt']])) { $t = $sum_at[$c['ins_dt']]; $val .= '<span class="oh_sum">'.t::w('Total', 'Жалпы').': '.((string)$t['old_value'] !== '' ? '<s>'.htmlspecialchars($hv('total', $t['old_value'])).'</s> → ' : '').'<b>'.htmlspecialchars($hv('total', $t['new_value'])).'</b></span>'; }
               if ($val === '') continue;
            }
            else $val = ((string)$c['old_value'] === '' ? '' : '<s>'.htmlspecialchars($hv($f, $c['old_value'])).'</s> → ').'<b>'.htmlspecialchars($hv($f, $c['new_value'])).'</b>';
            $ch_rows[] = '<div class="oh_cl"><div class="oh_cl_t"><span class="oh_cl_f">'.htmlspecialchars(@$lbl[$f] ?: $f).'</span><small>'.date('H:i', strtotime($c['ins_dt'])).($u ? ' · '.htmlspecialchars((string)@$u['name']) : '').'</small></div><div class="oh_cl_v">'.$val.'</div></div>';
         }
      }

      echo '<div class="oh_tabs"><span class="oh_tab oh_tab_act" data-t="st">'.t::w('Status', 'Статус').'</span><span class="oh_tab" data-t="ch">'.t::w('Changes', 'Өзгерістер').($ch_rows ? ' <i>'.count($ch_rows).'</i>' : '').'</span></div>';
      echo '<div class="oh_pane" data-p="st"><div class="oh_list">';
      foreach ($rows as $i => $r) {
         $nx = @$rows[$i + 1];
         $link = $r['on'] && $nx && $nx['on'] && !$r['cur']; // келесі этап та өткен болса — жолақ градиентпен боялады
         $dash = $nx && (!empty($r['skip']) || !empty($nx['skip']));
         echo '<div class="oh_row'.($r['cur'] ? ' oh_cur' : '').($r['on'] ? ' oh_on' : '').($link ? ' oh_link' : '').(!empty($r['skip']) ? ' oh_skip' : '').($dash ? ' oh_dash' : '').'" style="--c:'.htmlspecialchars($r['clr']).';--n:'.htmlspecialchars($nx ? $nx['clr'] : $r['clr']).';--i:'.$i.'"><em class="oh_dot"></em><span class="oh_nm">'.htmlspecialchars($r['nm']).'</span>'
            .($r['dt'] ? '<span class="oh_dt"><b>'.date('H:i', strtotime($r['dt'])).'</b><small>'.date('d.m.Y', strtotime($r['dt'])).'</small></span>' : '').'</div>';
      }
      echo '</div><div class="oh_foot">';
      if (@$o['upd_dt']) echo '<div class="oh_row"><span class="oh_nm">'.t::w('Last change', 'Соңғы өзгеріс').'</span><span class="oh_dt"><b>'.date('H:i', strtotime($o['upd_dt'])).'</b><small>'.date('d.m.Y', strtotime($o['upd_dt'])).'</small></span></div>';
      $au = $o['user_id'] ? fun::user($o['user_id']) : null;
      echo '<div class="oh_row"><span class="oh_nm">'.t::w('Order author', 'Тапсырыс авторы').'</span><span class="oh_dt"><b>'.htmlspecialchars((string)@$au['name']).'</b></span></div>';
      echo '</div></div>';
      echo '<div class="oh_pane dsp_n" data-p="ch">'.($ch_rows ? implode('', $ch_rows) : '<div class="oh_empty">'.t::w('No changes yet', 'Әзірге өзгеріс жоқ').'</div>').'</div>';
      exit();
   }

   // 
	if(isset($_GET['change_status'])) {
		$id = strip_tags($_POST['id']);
		$order_id = strip_tags($_POST['order_id']);

      $old = order_row($order_id);
      $sid = (int)$id;
      $st_d = mysqli_fetch_assoc(db::query("select id, types from retail_orders_status where id = '$sid' limit 1"));
      if (!$st_d || !$old) { echo 'none'; exit(); }
      if (((int)$old['order_type'] == 1 && ((int)$old['order_status'] == 3 || (int)$old['order_status'] == 6 || ((int)$old['order_status'] == 4 && !empty($old['сourier_id']) && (int)@mysqli_fetch_assoc(db::query("select user_id from retail_orders_log where order_id = '".(int)$old['id']."' and field = 'status' order by id desc limit 1"))['user_id'] == (int)$old['сourier_id'])))) { echo 'by_courier'; exit(); } // статусты курьер қойған: оператор өзгерте алмайды
      if ((int)$old['order_status'] == 4) { echo 'locked'; exit(); } // Аяқталды: тек admin.pf.c өзгертеді
      // id: 3 Жолда, 4 Аяқталды, 6 Отказ при получение, 10 Отказ вне
      if (!in_array((string)@$old['order_type'], explode(',', (string)$st_d['types']))) { echo 'bad_type'; exit(); } // бұл түрге жатпайтын статус
      if ($sid == 6 || ($sid == 3 && @$old['order_type'] == 1)) { echo 'only_courier'; exit(); } // бұл статустарды тек курьер қояды
      if (@$old['order_type'] == 1 && (int)@$old['order_status'] == 4 && in_array($sid, [5, 10])) { echo 'done'; exit(); } // курьер аяқтаған жеткізуге отказ қойылмайды
      if ($sid == 4 && @$old['payment_method'] == 'unpaid') { echo 'unpaid'; exit(); } // төленбеген тапсырыс аяқталмайды
      if (in_array($sid, [11, 3, 4]) && @$old['order_type'] == 1 && empty($old['сourier_id'])) { echo 'no_courier'; exit(); } // жеткізуде курьерсіз Жолда/Аяқталды жоқ
      $reason = mb_substr(trim(strip_tags((string)@$_POST['reason'])), 0, 255);
      if (in_array($sid, [5, 10]) && $reason === '') { echo 'need_reason'; exit(); } // отказ себебі міндетті
      $rs_sql = in_array($sid, [5, 10]) ? "'".mysqli_real_escape_string(db::$connect, $reason)."'" : 'refuse_reason';
      $kept = 0; // Отказ вне: бізде қалған ақша (есепке тек осы кіреді)
      if ($sid == 10) $kept = (isset($_POST['kept']) && $_POST['kept'] !== '') ? max(0, (int)$_POST['kept']) : (int)((int)$old['pay_prepay'] > 0 ? $old['pay_prepay'] : $old['pay_qr']);
      $upd = db::query("UPDATE `retail_orders` SET order_status = '$sid', kept_sum = '$kept', refuse_reason = $rs_sql, `upd_dt` = '$datetime' WHERE id = '".(int)$order_id."'");
      order_log($order_id, 'status', @$old['order_status'], $sid);
      if ($kept != (int)@$old['kept_sum']) order_log($order_id, 'kept_sum', @$old['kept_sum'], $kept);
      if (in_array($sid, [5, 10])) order_log($order_id, 'refuse_reason', @$old['refuse_reason'], $reason);
      if ($sid == 5 && !empty($old['сourier_id']) && in_array((int)$old['order_status'], [1, 8, 2, 11])) { db::query("UPDATE `retail_orders` SET сourier_id = null WHERE id = '".(int)$order_id."'"); order_log($order_id, 'courier', $old['сourier_id'], ''); } // Жолдаға дейін отказ: курьер алынады
      echo 'yes';

      exit();
	}

   // Төленбеген тапсырыс: «Төледі» — төлем түрі жазылып, тапсырыс аяқталады
   if(isset($_GET['mark_paid'])) {
      $order_id = (int)@$_POST['order_id'];
      $m = @trim($_POST['method']);
      if (!in_array($m, ['cash', 'qr', 'transfer', 'mixed', 'red'], true)) { echo 'none'; exit(); }
      $old = order_row($order_id);
      if (!$old) { echo 'none'; exit(); }
      if (@$old['payment_method'] != 'unpaid') { echo 'already'; exit(); }
      if (in_array((int)@$old['order_status'], [5, 6, 10])) { echo 'locked'; exit(); }
      $total = (int)$old['total'];
      $has_tr = (bool)mysqli_num_rows(db::query("SHOW COLUMNS FROM `retail_orders` LIKE 'pay_transfer'"));
      $red = 0;
      if ($m == 'red') { // RED: Сақтаудағыдай — жалпы сомаға company.red пайызы қосылады, ақша Kaspi арқылы (qr болып жазылады)
         $cm = fun::company($company); $red_pct = isset($cm['red']) ? (int)$cm['red'] : 10;
         if ($red_pct <= 0) { echo 'none'; exit(); }
         $red = (int)round($total * $red_pct / 100);
         $total += $red; $m = 'qr';
      }
      if ($m == 'mixed') { // Аралас: сомалар бөлек, қосындысы жалпы сомаға тең болуы керек
         $mx = ['cash' => max(0, (int)@$_POST['cash']), 'qr' => max(0, (int)@$_POST['qr']), 'transfer' => max(0, (int)@$_POST['transfer'])];
         if (array_sum($mx) != $total || count(array_filter($mx)) < 2) { echo 'sum'; exit(); }
         if ($mx['transfer'] && !$has_tr) { echo 'none'; exit(); }
      } else {
         if ($m == 'transfer' && !$has_tr) { echo 'none'; exit(); }
         $mx = ['cash' => ($m == 'cash' ? $total : 0), 'qr' => ($m == 'qr' ? $total : 0), 'transfer' => ($m == 'transfer' ? $total : 0)];
      }
      $cols = ['pay_cash' => $mx['cash'], 'pay_qr' => $mx['qr']];
      if ($has_tr) $cols['pay_transfer'] = $mx['transfer'];
      if ($red) { $cols['total'] = $total; if (mysqli_num_rows(db::query("SHOW COLUMNS FROM `retail_orders` LIKE 'pay_red'"))) $cols['pay_red'] = $red; }
      $set = '';
      foreach ($cols as $c => $v) $set .= "`$c` = '$v', ";
      $upd = db::query("UPDATE `retail_orders` SET $set`payment_method` = '$m', `paid` = 1, `order_status` = 4, `upd_dt` = '$datetime' WHERE id = '$order_id'");
      if (!$upd) { echo 'none'; exit(); }
      order_log($order_id, 'payment_method', 'unpaid', $m);
      foreach ($cols as $c => $v) if ((int)@$old[$c] != $v) order_log($order_id, $c, @$old[$c], $v);
      if ((int)@$old['order_status'] != 4) order_log($order_id, 'status', @$old['order_status'], 4);
      echo 'yes';
      exit();
   }

   // 
	if(isset($_GET['change_type'])) {
		$id = strip_tags($_POST['id']);
		$order_id = strip_tags($_POST['order_id']);
      $old = order_row($order_id);
      if ((int)@$old['order_status'] == 3) { echo 'by_courier'; exit(); } // Жолда: тек курьер өзгертеді
      if (in_array((int)@$old['order_status'], [4, 5, 6, 10])) { echo 'locked'; exit(); } // жабық тапсырыс

      if ($id != 1) $upd = db::query("UPDATE `retail_orders` SET total = total - pay_delivery, pay_delivery = 0, сourier_id = null, `upd_dt` = '$datetime' WHERE id = '$order_id'");
      $upd = db::query("UPDATE `retail_orders` SET order_type = '$id', order_status = 2, `upd_dt` = '$datetime' WHERE id = '$order_id'");
      order_log($order_id, 'order_type', @$old['order_type'], $id);
      order_log($order_id, 'status', @$old['order_status'], 2);
      if ($id != 1) { order_log($order_id, 'pay_delivery', @$old['pay_delivery'], 0); order_log($order_id, 'courier', @$old['сourier_id'], ''); }

      echo 'yes';

      exit();
	}
   
   // 
   if(isset($_GET['change_staff'])) {
      $id = strip_tags($_POST['id']);
      $order_id = strip_tags($_POST['order_id']);
      $old = order_row($order_id);
      if ((int)@$old['order_status'] == 3) { echo 'by_courier'; exit(); } // Жолда: тек курьер өзгертеді
      if (in_array((int)@$old['order_status'], [4, 5, 6, 10])) { echo 'locked'; exit(); } // жабық тапсырыс

      if ($id == 'kr') $upd = db::query("UPDATE `retail_orders` SET сourier_id = null, order_status = 2, `upd_dt` = '$datetime' WHERE id = '$order_id'");
      else $upd = db::query("UPDATE `retail_orders` SET сourier_id = '$id', order_status = 11, `upd_dt` = '$datetime' WHERE id = '$order_id'"); // Курьер тағайындалды, «Жолда»-ны курьер өзі қояды
      order_log($order_id, 'courier', @$old['сourier_id'], $id == 'kr' ? '' : $id);
      order_log($order_id, 'status', @$old['order_status'], $id == 'kr' ? 2 : 11);
      echo 'yes';

      exit();
   }














   // cashbox_pay
	if(isset($_GET['cashbox_pay'])) {
		$cashbox_number = strip_tags($_POST['number']);
		$total = strip_tags($_POST['total']);
		$delivery = strip_tags($_POST['delivery']);
		$qr = strip_tags($_POST['qr']);
		$cash = $total - $qr;

      if ($delivery) $ins = db::query("INSERT INTO `retail_orders`(`user_id`, `number`, `paid`, `total`, `pay_qr`, `pay_cash`, `pay_delivery`, `order_type`, `company_id`, `branch_id`, `ins_dt`) VALUES (1, '$cashbox_number', 1, '$total', '$qr', '$cash', '$delivery', 1, '$company', '$branch', '$datetime')");
      else $ins = db::query("INSERT INTO `retail_orders`(`user_id`, `number`, `paid`, `total`, `pay_qr`, `pay_cash`, `order_type`, `company_id`, `branch_id`, `ins_dt`) VALUES (1, '$cashbox_number', 1, '$total', '$qr', '$cash', 2, '$company', '$branch', '$datetime')");
      
      if ($ins) echo 'yes';
      else echo 'none';

      exit();
	}








   // on_info