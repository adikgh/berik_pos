<? include "../config/core.php";

	// 
	if (!$user_id) header('location: /');

	$cmp = fun::company($company);
	$open = true; $result = 0;
	if ($cmp['ins_dt'] != null && $cmp['end_dt'] != null) {
		$result = intval((strtotime($cmp['end_dt']) - strtotime(date("d.m.Y"))) / (60*60*24));
		if ($result <= 0) header('location: /exit.php');
	}



	// company.show_branch = 0: точка сүзгісі жасырылады, кассир тек өз точкасын көреді (URL арқылы да басқасы ашылмайды)
	$branch_lock = isset($cmp['show_branch']) && $cmp['show_branch'] !== null && (int)$cmp['show_branch'] === 0;
	if ($branch_lock) $_GET['branch'] = (int)$branch;
	// Точка біреу ғана болса, сүзгі шықпайды
	$branch_cnt = mysqli_num_rows(db::query("select id from company_branch where company_id = '$company'"));

   	$type = @$_GET['type'];

	// if (@$_GET['time']) {
	// 	$time_sort = $_GET['time'];
	// 	$start_cdate = date('Y-m-d 06:00:00', strtotime("$date $time_sort day"));
	// 	$end_cdate = date('Y-m-d 06:00:00', strtotime("$start_cdate +1 day"));
	// }
	if (@$_GET['time']) {
		$time_sort = $_GET['time'];
		$start_cdate = date('Y-m-d 06:00:00', strtotime("$time_sort"));
		$end_cdate = date('Y-m-d 06:00:00', strtotime("$start_cdate +1 day"));
	}


	// Предзаказ: ins_dt = предзаказ уақыты, сондықтан өз күнінде шығады. ?pre=1 — алдағы предзаказдар
	$now_dt = date('Y-m-d H:i:s');
	$dt_cond = "ins_dt BETWEEN '$start_cdate' and '$end_cdate'";
	// Бүгінге қабылданған предзаказдар (сол күні орындалатын) негізгі тізімде қабылданған күні де шығады
	if (mysqli_num_rows(db::query("SHOW TABLES LIKE 'retail_orders_log'"))) $dt_cond = "(ins_dt BETWEEN '$start_cdate' and '$end_cdate' or (preorder_dt is not null and preorder_dt != '' and id in (select l.order_id from retail_orders_log l join retail_orders o2 on o2.id = l.order_id where l.field = 'created' and l.ins_dt BETWEEN '$start_cdate' and '$end_cdate' and (date(l.ins_dt) = date(o2.ins_dt) or date(l.ins_dt - interval 6 hour) = date(o2.ins_dt - interval 6 hour)))))";
	if (@$_GET['pre']) $dt_cond = "(ins_dt > '$now_dt' and preorder_dt is not null and preorder_dt != '')";
	// Төленбеген: таңдалған күннің төленбеген тапсырыстары (?unpaid=1), отказдар есепке кірмейді
	$unp_cond = "payment_method = 'unpaid' and (order_status is null or order_status not in (5, 6, 10))";
	$unp_cnt = mysqli_num_rows(db::query("select id from retail_orders where company_id = '$company'".($branch_lock ? " and branch_id = '".(int)$branch."'" : "")." and ins_dt BETWEEN '$start_cdate' and '$end_cdate' and $unp_cond"));
	if (@$_GET['unpaid']) $dt_cond .= " and $unp_cond";
	$pre_cnt = mysqli_num_rows(db::query("select id from retail_orders where company_id = '$company'".($branch_lock ? " and branch_id = '".(int)$branch."'" : "")." and ins_dt > '$now_dt' and preorder_dt is not null and preorder_dt != ''"));

	if (@$_GET['branch']) {
		$sbranch = $_GET['branch'];

		if (@$_GET['status'] && @$_GET['staff']) {
			$status = $_GET['status'];
			$staff = $_GET['staff'];
			if ($staff == 'off') $orders = db::query("select * from retail_orders where $dt_cond and order_status = '$status' and сourier_id is null and company_id = '$company' and branch_id = '$sbranch' order by number desc");
			elseif ($staff == 'soboi') $orders = db::query("select * from retail_orders where $dt_cond and order_status = '$status' and order_type = 2 and company_id = '$company' and branch_id = '$sbranch' order by number desc");
			else $orders = db::query("select * from retail_orders where $dt_cond and order_status = '$status' and сourier_id  = '$staff' and company_id = '$company' and branch_id = '$sbranch' order by number desc");
		} elseif (@$_GET['status']) {
			$status = $_GET['status'];
			$orders = db::query("select * from retail_orders where $dt_cond and order_status = '$status' and company_id = '$company' and branch_id = '$sbranch' order by number desc");
		} elseif (@$_GET['type']) {
			$type = $_GET['type'];
			$orders = db::query("select * from retail_orders where $dt_cond and order_type  = '$type' and company_id = '$company' and branch_id = '$sbranch' order by number desc");
		} elseif (@$_GET['staff']) {
			$staff = $_GET['staff'];
			if ($staff == 'off') $orders = db::query("select * from retail_orders where $dt_cond and order_type = 1 and сourier_id is null and company_id = '$company' and branch_id = '$sbranch' order by number desc");
			else $orders = db::query("select * from retail_orders where $dt_cond and сourier_id  = '$staff' and company_id = '$company' and branch_id = '$sbranch' order by number desc");
		} else $orders = db::query("select * from retail_orders where $dt_cond and paid = 1 and company_id = '$company' and branch_id = '$sbranch' order by number desc");
	} else {
		if (@$_GET['status'] && @$_GET['staff']) {
			$status = $_GET['status'];
			$staff = $_GET['staff'];
			if ($staff == 'off') $orders = db::query("select * from retail_orders where $dt_cond and order_status = '$status' and сourier_id is null and company_id = '$company' order by number desc");
			elseif ($staff == 'soboi') $orders = db::query("select * from retail_orders where $dt_cond and order_status = '$status' and order_type = 2 and company_id = '$company' order by number desc");
			else $orders = db::query("select * from retail_orders where $dt_cond and order_status = '$status' and сourier_id  = '$staff' and company_id = '$company'  order by number desc");
		} elseif (@$_GET['status']) {
			$status = $_GET['status'];
			$orders = db::query("select * from retail_orders where $dt_cond and order_status = '$status' and company_id = '$company'  order by number desc");
		} elseif (@$_GET['type']) {
			$type = $_GET['type'];
			$orders = db::query("select * from retail_orders where $dt_cond and order_type  = '$type' and company_id = '$company'  order by number desc");
		} elseif (@$_GET['staff']) {
			$staff = $_GET['staff'];
			if ($staff == 'off') $orders = db::query("select * from retail_orders where $dt_cond and order_type = 1 and сourier_id is null and company_id = '$company' order by number desc");
			else $orders = db::query("select * from retail_orders where $dt_cond and сourier_id  = '$staff' and company_id = '$company'  order by number desc");
		} else $orders = db::query("select * from retail_orders where $dt_cond and paid = 1 and company_id = '$company' order by number desc");
	}




	$allorder['total'] = 0;
	$allorder['pay_qr'] = 0;
	$allorder['pay_cash'] = 0;
	$allorder['pay_delivery'] = 0;


	// site setting
	$menu_name = 'orders';
	$pod_menu_name = 'main';
	$css = ['orders'];
	$js = ['orders'];
?>
<? include "../block/header.php"; ?>

	<div class="">

		<div class="bl_c">
			<div class="head_c or_head">
				<h1><?=t::w('Orders', 'Тапсырыстар')?></h1>
			</div>
		</div>

		<!-- <div class="bl_c">
			<div class="pp_cont">
				<div class="uc_uin_other pp_total"></div>
				<div class="uc_uin_other pp_qr"></div>
				<div class="uc_uin_other pp_nal"></div>
			</div>
		</div> -->
	
		<div class="bl_c">

			<div class="">
				<div class="uc_ui uc_ui69">
					<!-- <div class="btn cashbox_pay" style="margin-right: 15px;">Новый заказ</div> -->


					<div class="uc_uin_other">
						<div class="form_im">
							<input class="form_dt on_sort_time" type="date" name="" id="" value="<?=date('Y-m-d', strtotime("$start_cdate"))?>">
						</div>
					</div>

					<? if (true): ?>
						<div class="uc_uin_other">
							<a class="pre_tab <?=(@$_GET['pre'] ? 'pre_tab_act' : '')?>" href="<?=(@$_GET['pre'] ? '/orders/' : '/orders/?pre=1')?>"><i class="fal fa-calendar-alt"></i> <?=t::w('Pre-orders', 'Предзаказдар')?><?=($pre_cnt ? ' <b>'.$pre_cnt.'</b>' : '')?></a>
						</div>
						<? if (!empty($cmp['coffee'])): // Төленбеген тек Кафесі бар компанияда (company.coffee) ?>
						<div class="uc_uin_other">
							<? $unp_q = $_GET; unset($unp_q['pre']); if (@$_GET['unpaid']) unset($unp_q['unpaid']); else $unp_q['unpaid'] = 1; ?>
							<a class="pre_tab unp_tab <?=(@$_GET['unpaid'] ? 'pre_tab_act' : '')?>" href="/orders/<?=($unp_q ? '?'.http_build_query($unp_q) : '')?>"><i class="fal fa-wallet"></i> <?=t::w('Unpaid', 'Төленбеген')?><?=($unp_cnt ? ' <b>'.$unp_cnt.'</b>' : '')?></a>
						</div>
						<? endif ?>
					<? endif ?>

					<div class="uc_uin_other <?=($branch_lock || $branch_cnt < 2 ? 'dsp_n' : '')?>">
						<select name="type" class="on_sort_branch" >
							<option data-id="" value=""><?=t::w('Branch: all', 'Точка: барлығы')?></option>
							<? $уbranch = db::query("select * from company_branch where company_id = '$company' order by number asc"); ?>
							<? while ($branch_d = mysqli_fetch_assoc($уbranch)): ?>
								<option data-id="<?=$branch_d['id']?>" <?=(@$_GET['branch'] == $branch_d['id']?'selected':'')?> value=""><?=$branch_d['name']?></option>
							<? endwhile ?>
						</select>
					</div>

					<div class="uc_uin_other">
						<select name="type" class="on_sort_type" data-order-id="<?=$buy_d['id']?>" >
							<option data-id="" value=""><?=t::w('Type: all', 'Тип: барлығы')?></option>
							<? $staff = db::query("select * from retail_orders_type order by number asc"); ?>
							<? while ($staff_d = mysqli_fetch_assoc($staff)): ?>
								<option data-id="<?=$staff_d['id']?>" <?=(@$_GET['type'] == $staff_d['id']?'selected':'')?> value=""><?=t::w('type:'.$staff_d['name'], $staff_d['name'])?></option>
							<? endwhile ?>
						</select>
					</div>

					<div class="uc_uin_other">
						<select name="status" class="on_sort_status" data-order-id="<?=$buy_d['id']?>" >
							<option data-id="" value=""><?=t::w('Status: all', 'Статус: барлығы')?></option>
							<? // статус түстері — карточкадағы түстермен бірдей ?>
							<? if (!isset($st_colors_db)) { $st_colors_db = []; $stc_q = db::query("select name_ru, clr from retail_orders_status"); while ($stc = mysqli_fetch_assoc($stc_q)) $st_colors_db[trim($stc["name_ru"])] = $stc["clr"]; } $st_colors_f = $st_colors_db; // түстер базадан: retail_orders_status.clr ?>
							<? $orders_status = db::query("select * from retail_orders_status order by number asc"); ?>
							<? while ($orders_status_d = mysqli_fetch_assoc($orders_status)): ?><? if (trim($orders_status_d['name_ru']) == 'Вопрос' && @$order_sts['id'] != $orders_status_d['id'] && @$_GET['status'] != $orders_status_d['id']) continue; // «Вопрос» тізімде көрсетілмейді, базада қалады ?>
								<? $f_nm = trim($orders_status_d['name_ru']); $f_clr = isset($st_colors_f[$f_nm]) ? $st_colors_f[$f_nm] : (isset($st_colors_f[trim($orders_status_d['name_kz'])]) ? $st_colors_f[trim($orders_status_d['name_kz'])] : $orders_status_d['clr']); ?>
								<option data-id="<?=$orders_status_d['id']?>" data-clr="<?=htmlspecialchars((string)$f_clr)?>" <?=(@$_GET['status'] == $orders_status_d['id']?'selected':'')?> value="" ><?=t::w('st:'.$orders_status_d['name_ru'], $orders_status_d['name_ru'])?></option>
							<? endwhile ?>
						</select>
					</div>
					
					<? $cr_cnt = mysqli_num_rows(db::query("select id from company_staff where positions_id = 6 and company_id = '$company'")); ?>
					<? if ($cr_cnt): // Компанияда бірде-бір курьер болмаса, Курьер сүзгісі шықпайды ?>
					<div class="uc_uin_other">
						<select name="staff" class="on_sort_staff" data-order-id="<?=$buy_d['id']?>" >
							<option data-id="" value=""><?=t::w('Courier: all', 'Курьер: барлығы')?></option>
							<option data-id="off" <?=(@$_GET['staff'] == 'off'?'selected':'')?> value=""><?=t::w('Courier: none', 'Курьер: таңдалмаған')?></option>
							<? $staff = db::query("select * from company_staff where positions_id = 6 and company_id = '$company'"); ?>
							<? while ($staff_d = mysqli_fetch_assoc($staff)): ?>
								<? $staff_user_d = fun::user($staff_d['user_id']); ?>
								<option data-id="<?=$staff_d['user_id']?>" <?=(@$_GET['staff'] == $staff_d['user_id']?'selected':'')?> value=""><?=$staff_user_d['name']?></option>
							<? endwhile ?>
						</select>
					</div>
					<? endif ?>
					
					<div class="uc_us">
						<div class="form_im uc_usn">
							<input class="form_txt order_search_in" type="text" placeholder="<?=t::w('Search', 'Іздеу')?>" >
							<i class="fal fa-search form_icon"></i>
						</div>
					</div>

					<div class="uc_uin_other">
						<? /* Карточка / тізім ауыстыру батырмасы: әзірге өшірулі
						<div class="or_view_btn" title="<?=t::w('Cards / list', 'Карточка / тізім')?>"><i class="far fa-list"></i><i class="far fa-th-large"></i></div>
						*/ ?>
					</div>

				</div>
			</div>

		</div>

		<div class="bl_c bl_ors dsp_n">
			<div class="uc_u"></div>
		</div>

		<div class="bl_c bl_orm">

			<div class="uc_u">

				<? if ($orders != ''): ?>
					<? if (mysqli_num_rows($orders) != 0): ?>
						<? while ($buy_d = mysqli_fetch_assoc($orders)): ?>
							<? include __DIR__."/card.php"; ?>
							<?	
								if (in_array((int)$buy_d['order_status'], [6, 10])) $allorder['total'] = $allorder['total'] + (int)@$buy_d['kept_sum']; // отказ: тек бізде қалған ақша
								elseif ($buy_d['order_status'] != 5) {
									$allorder['total'] = $allorder['total'] + $buy_d['total'];
									$allorder['pay_qr'] = $allorder['pay_qr'] + $buy_d['pay_qr'];
									$allorder['pay_cash'] = $allorder['pay_cash'] + $buy_d['pay_cash'];
									$allorder['pay_delivery'] = $allorder['pay_delivery'] + $buy_d['pay_delivery'] + 500;
								}
							?>

						<? endwhile ?>
					<? else: ?> <div class="ds_nr"><i class="fal fa-none"></i><p><?=t::w('Day off', 'демалыс')?></p></div> <? endif ?>
				<? else: ?> <div class="ds_nr"><i class="fal fa-none"></i><p><?=t::w('Day off', 'демалыс')?></p></div> <? endif ?>

			</div>

		</div>

	</div>

	<script>
		// Карточка / тізім көрінісі (таңдау есте қалады)
		(function(){
			var k = 'or_view'; // try { if (localStorage.getItem(k) == 'list') document.body.classList.add('or_list') } catch (e) {} // тізім көрінісі әзірге өшірулі
			$(document).on('click', '.or_view_btn', function(){
				document.body.classList.toggle('or_list')
				try { localStorage.setItem(k, document.body.classList.contains('or_list') ? 'list' : 'card') } catch (e) {}
			})
		})()
		// Карточкадағы таңдаулар (тип, статус, курьер): әдемі тізім. Ішінде бұрынғы select қалады, change сол арқылы жүреді
		$(function(){
			function ph(p){ p = String(p || '').replace(/\D/g, ''); if (p.length == 11) p = p.substr(1); return p.length == 10 ? '8 (' + p.substr(0,3) + ') ' + p.substr(3,3) + '-' + p.substr(6,2) + '-' + p.substr(8,2) : p }
			function esc(t){ return $('<i>').text(t).html() }
			function item(o, staff){
				var n = $(o).attr('data-name'), c = $(o).attr('data-clr')
				if (n) return '<b>' + esc(n) + '</b><small>' + ph($(o).attr('data-phone')) + '</small>'
				var ic = $(o).attr('data-ico'); if (ic) return '<i class="far ' + esc(ic) + ' cr_ico"></i><b>' + esc($.trim($(o).text())) + '</b>'
				if (staff) return '<b class="cr_none">' + esc($(o).text()) + '</b>'
				return (c ? '<em class="cr_dot" style="background:' + esc(c) + '"></em>' : '') + '<b>' + esc($.trim($(o).text())) + '</b>'
			}
			// scope: карточкалар (жаңартылған карточкаға да қайта шақырылады)
			// Жеткізуде курьер таңдалмаса: Курьерде / Аяқталған бұғатталады
			function no_cour(sel){ var card = sel.closest('.or_card'); return card.hasClass('or_t1') && (card.find('select.on_staff option:selected').attr('data-id') || 'kr') == 'kr' }
			window.or_dd = function(scope){ $(scope).find('select.on_staff, select.on_type, select.on_status').each(function(){
				var sel = $(this)
				// Тип: dropdown емес, бірден иконка-батырмалар
				if (sel.hasClass('on_type')) {
					var tb = $('<div class="tp_btns"></div>'), dis = sel.prop('disabled')
					tb.append('<span class="tp_sl"></span>').css('--i', sel.prop('selectedIndex')).toggleClass('tp_off', !!dis)
					sel.find('option').each(function(i){ tb.append($('<div class="tp_b"></div>').attr({ 'data-i': i, title: $.trim($(this).text()) }).toggleClass('tp_act', i == sel.prop('selectedIndex')).toggleClass('tp_dis', !!dis).html('<i class="far ' + esc($(this).attr('data-ico') || 'fa-circle') + '"></i>')) })
					tb.on('click', '.tp_b', function(e){ e.stopPropagation(); if ($(this).hasClass('tp_dis')) { mess('<?=t::w('Closed order cannot be edited', 'Жабық тапсырысты өзгертуге болмайды')?>'); return } var i = +$(this).attr('data-i'); if (i == sel.prop('selectedIndex')) return; tb.find('.tp_b').removeClass('tp_act'); $(this).addClass('tp_act'); tb.css('--i', i); sel.prop('selectedIndex', i).trigger('change') })
					sel.hide().after(tb); return
				}
				var staff = sel.hasClass('on_staff'), box = $('<div class="cr_dd"><div class="cr_btn"></div><div class="cr_list"></div></div>')
				if (sel.hasClass('on_status')) box.addClass('cr_st'); if (sel.hasClass('on_type')) box.addClass('cr_tp')
				if (sel.prop('disabled')) box.addClass('cr_dis')
				sel.find('option').each(function(i){ box.find('.cr_list').append($('<div class="cr_opt"></div>').attr('data-i', i).html(item(this, staff))) })
				function show(){ var o = sel.find('option:selected')[0]; box.find('.cr_btn').html('<span class="cr_v">' + (sel.hasClass('on_status') ? '<b>' + esc($.trim($(o).text())) + '</b>' : item(o, staff)) + '</span><i class="far fa-chevron-down"></i>'); box.find('.cr_opt').removeClass('cr_act').eq(sel.prop('selectedIndex')).addClass('cr_act') }
				show(); sel.hide().after(box)
				box.on('click', '.cr_btn', function(e){ e.stopPropagation(); if (box.hasClass('cr_dis')) return; if (sel.hasClass('on_status')) { var nc = no_cour(sel); sel.find('option').each(function(i){ var lk = $(this).attr('data-lock') == '1' && i != sel.prop('selectedIndex'); var ld = $(this).attr('data-done') == '1'; box.find('.cr_opt').eq(i).toggleClass('cr_lock_d', ld); var lu = $(this).attr('data-unp') == '1' && i != sel.prop('selectedIndex'); box.find('.cr_opt').eq(i).toggleClass('cr_lock_k', lk).toggleClass('cr_lock_u', lu).toggleClass('cr_lock', ld || lk || lu || (nc && $(this).attr('data-cour') == '1')) }) } $('.cr_dd').not(box).removeClass('cr_open'); box.toggleClass('cr_open') })
				box.on('click', '.cr_opt', function(e){ e.stopPropagation(); var i = +$(this).attr('data-i'); if ($(this).hasClass('cr_lock_d')) { mess('<?=t::w('Delivered order cannot be refused', 'Курьер аяқтаған тапсырысқа отказ қоюға болмайды')?>'); return } if ($(this).hasClass('cr_lock_k')) { mess('<?=t::w('Only the courier sets this status', 'Бұл статусты тек курьер қояды')?>'); return } if ($(this).hasClass('cr_lock_u')) { mess('<?=t::w('Unpaid order cannot be completed', 'Төленбеген тапсырысты аяқтауға болмайды')?>'); return } if ($(this).hasClass('cr_lock')) { mess('<?=t::w('Choose a courier first', 'Алдымен курьерді таңдаңыз')?>'); return } box.removeClass('cr_open'); if (i == sel.prop('selectedIndex')) return; sel.prop('selectedIndex', i).trigger('change'); show() })
			}) }
			or_dd($('.or_card'))
			$(document).on('click', function(){ $('.cr_dd').removeClass('cr_open') })
		})
		$(document).ready(function() {
			$('.pp_total').html('<?=t::w('Total', 'Жалпы')?>: <?=$allorder['total']?> тг');
			$('.pp_qr').html('QR: <?=$allorder['pay_qr']?> тг');
			$('.pp_nal').html('<?=t::w('Cash short', 'Нал')?>: <?=$allorder['pay_cash']?> тг');
		})
	</script>


<? include "../block/footer.php"; ?>

	<!--  -->
	<div class="pop_bl pop_bl2 cashbox_pay_block">
		<div class="pop_bl_a cashbox_pay_back"></div>
		<div class="pop_bl_c">
			<div class="head_c">
				<h4><?=t::w('Payment', 'Төлем')?></h4>
				<div class="btn btn_dd cashbox_pay_back"><i class="fal fa-times"></i></div>
			</div>
			<div class="pop_bl_cl">
				<div class="form_c">

					<div class="">
						<div class="form_im">
							<div class="form_span"><?=t::w('Order number:', 'Тапсырыс нөмірі:')?></div>
							<input type="tel" class="form_txt fr_number2 order_number_sel" placeholder="0" value="" data-val="">
							<i class="fal fa-solar-panel form_icon"></i>
						</div>
						<div class="form_im ">
							<div class="form_span"><?=t::w('Total price:', 'Жалпы баға:')?></div>
							<input type="tel" class="form_txt fr_price btype_totol" placeholder="0" data-val="0">
							<i class="fal fa-tenge form_icon"></i>
						</div>
						<div class="form_im ">
							<div class="form_span"><?=t::w('Prepayment:', 'Алдын ала төлем (предоплата):')?></div>
							<input type="tel" class="form_txt fr_price btype_qr" placeholder="0" value="" data-val="">
							<i class="fal fa-tenge form_icon"></i>
						</div>
						<div class="form_im ">
							<div class="form_span"><?=t::w('Delivery:', 'Жеткізу:')?></div>
							<input type="tel" class="form_txt fr_price btype_delivery" placeholder="0" data-val="0">
							<i class="fal fa-tenge form_icon"></i>
						</div>
					</div>

					<div class="form_im">
						<div class="btn cashbox_pay2" data-branch="<?=$branch?>"><i class="far fa-check"></i><span><?=t::w('Save', 'Сақтау')?></span></div>
					</div>

				</div>

			</div>
		</div>
	</div>