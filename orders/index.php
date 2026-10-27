<? include "../config/core.php";

	// 
	if (!$user_id) header('location: /');

	$cmp = fun::company($company);
	$open = true; $result = 0;
	if ($cmp['ins_dt'] != null && $cmp['end_dt'] != null) {
		$result = intval((strtotime($cmp['end_dt']) - strtotime(date("d.m.Y"))) / (60*60*24));
		if ($result <= 0) header('location: /exit.php');
	}



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
	if (@$_GET['pre']) $dt_cond = "(ins_dt > '$now_dt' and preorder_dt is not null and preorder_dt != '')";
	$pre_cnt = mysqli_num_rows(db::query("select id from retail_orders where company_id = '$company' and ins_dt > '$now_dt' and preorder_dt is not null and preorder_dt != ''"));

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

		<br><br><br>

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
					<? endif ?>

					<div class="uc_uin_other">
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
							<? $orders_status = db::query("select * from retail_orders_status order by number asc"); ?>
							<? while ($orders_status_d = mysqli_fetch_assoc($orders_status)): ?><? if (trim($orders_status_d['name_ru']) == 'Вопрос' && @$order_sts['id'] != $orders_status_d['id'] && @$_GET['status'] != $orders_status_d['id']) continue; // «Вопрос» тізімде көрсетілмейді, базада қалады ?>
								<option data-id="<?=$orders_status_d['id']?>" <?=(@$_GET['status'] == $orders_status_d['id']?'selected':'')?> value="" ><?=t::w('st:'.$orders_status_d['name_ru'], $orders_status_d['name_ru'])?></option>
							<? endwhile ?>
						</select>
					</div>
					
					<div class="uc_uin_other">
						<select name="staff" class="on_sort_staff" data-order-id="<?=$buy_d['id']?>" >
							<option data-id="" value=""><?=t::w('Courier: all', 'Курьер: барлығы')?></option>
							<option data-id="off" <?=(@$_GET['staff'] == 'off'?'selected':'')?> value=""><?=t::w('Courier: none', 'Курьер: таңдалмаған')?></option>
							<? $staff = db::query("select * from user_staff where positions_id = 6 and company_id = '$company'"); ?>
							<? while ($staff_d = mysqli_fetch_assoc($staff)): ?>
								<? $staff_user_d = fun::user($staff_d['user_id']); ?>
								<option data-id="<?=$staff_d['user_id']?>" <?=(@$_GET['staff'] == $staff_d['user_id']?'selected':'')?> value=""><?=$staff_user_d['name']?></option>
							<? endwhile ?>
						</select>
					</div>
					
					<div class="uc_us">
						<div class="form_im uc_usn">
							<input class="form_txt order_search_in" type="text" placeholder="<?=t::w('Search', 'Іздеу')?>" >
							<i class="fal fa-search form_icon"></i>
						</div>
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
							<? // if ($buy_d['branch_id']) $branch_d = fun::branch($buy_d['branch_id']); ?>
							<? $order_sts = fun::order_sts($buy_d['order_status']); ?>
							<? // Статус түсі (атауы бойынша), тізімде жоқ болса базадағы түс
	$st_colors = ['Жаңа'=>'#1f75a8', 'Дайын'=>'#e9b949', 'Курьерде'=>'#d9480f', 'Аяқталған'=>'#0EA768', 'Отказ'=>'#e5484d', 'Отказ при получение'=>'#e86fa0', 'Вопрос'=>'#9aa0a6'];
	$st_name = trim(@$order_sts['name_ru']);
	$st_clr = isset($st_colors[$st_name]) ? $st_colors[$st_name] : (isset($st_colors[trim(@$order_sts['name_kz'])]) ? $st_colors[trim(@$order_sts['name_kz'])] : $order_sts['clr']);
?>
							<? $order_type = fun::order_type($buy_d['order_type']); ?>
							<? $branch_d = fun::branch($buy_d['branch_id']); ?>

							<div class="uc_ui or_card" style="--st:<?=$st_clr?>;">
								<div class="uc_uil2" >
									<div class="uc_uil2_top">
										<div class="uc_uil2_nmb"><?=$branch_d['code']?><?=$buy_d['number']?></div>
										<? if (@$buy_d['payment_method'] == 'unpaid'): ?><div style="align-self:center;margin:0 8px;padding:4px 10px;border-radius:8px;background:#e5484d;color:#fff;font-size:13px;font-weight:700;white-space:nowrap;"><?=t::w('Unpaid', 'Төленбеген')?></div><? endif ?>
										<div class="uc_uil2_topc">
											<div class="uc_uil2_topc1">
												<div class="or_type">
													<select name="" id="" class="on_type" data-order-id="<?=$buy_d['id']?>" <?=($order_sts['id'] != 4 && $order_sts['id'] != 5 && $order_sts['id'] != 6 ? '':'disabled')?>>
														<? $orders_type = db::query("select * from retail_orders_type order by number asc"); ?>
														<? while ($orders_type_d = mysqli_fetch_assoc($orders_type)): ?>
															<? $type_name = $orders_type_d['nms']; ?>
															<? if ($cmp[$type_name] == 1): ?>
																<option data-id="<?=$orders_type_d['id']?>" <?=($order_type['id'] == $orders_type_d['id']?'selected':'')?> value="" ><?=t::w('type:'.$orders_type_d['name'], $orders_type_d['name'])?></option>
															<? endif ?>
														<? endwhile ?>
													</select>
												</div>
												<div class="or_status" style="background-color:<?=$st_clr?>;"> <?//=$order_sts['name_kz']?>
													<select name="" id="" class="on_status" data-order-id="<?=$buy_d['id']?>" >
														<? $orders_status = db::query("select * from retail_orders_status order by number asc"); ?>
														<? while ($orders_status_d = mysqli_fetch_assoc($orders_status)): ?><? if (trim($orders_status_d['name_ru']) == 'Вопрос' && @$order_sts['id'] != $orders_status_d['id'] && @$_GET['status'] != $orders_status_d['id']) continue; // «Вопрос» тізімде көрсетілмейді, базада қалады ?>
															<option data-id="<?=$orders_status_d['id']?>" <?=($order_sts['id'] == $orders_status_d['id']?'selected':'')?> value="" ><?=t::w('st:'.$orders_status_d['name_ru'], $orders_status_d['name_ru'])?></option>
														<? endwhile ?>
													</select>
												</div>
											</div>
											<? if ($order_type['id'] == 1): ?>
												<div class="uc_uil2_date">
													<div class="uc_uil2_date1">
														<select name="" id="" class="on_staff" data-order-id="<?=$buy_d['id']?>" <?=($order_sts['id'] != 4 && $order_sts['id'] != 5 && $order_sts['id'] != 6 ? '':'disabled')?>>
															<option value="" data-id="kr" <?=($buy_d['сourier_id']==null?'selected':'')?>><?=t::w('Courier: none', 'Курьер: таңдалмаған')?></option>
															<? $staff = db::query("select * from user_staff where positions_id = 6 and company_id = '$company'"); ?>
															<? while ($staff_d = mysqli_fetch_assoc($staff)): ?>
																<? $staff_user_d = fun::user($staff_d['user_id']); ?>
																<option value="" data-id="<?=$staff_d['user_id']?>" <?=($buy_d['сourier_id'] == $staff_d['user_id']?'selected':'')?>><?=$staff_user_d['name']?> || <span class="fr_phone"><?=$staff_user_d['phone']?></span></option>
															<? endwhile ?>
														</select>
													</div>
												</div>
											<? endif ?>
										</div>
									</div>
									<div class="uc_uil2_date uc_uil2_raz">
										<div class=""><?=date("d-m-Y", strtotime($buy_d['ins_dt']))?> ⌛ <?=date("H:i", strtotime($buy_d['ins_dt']))?> <?=($buy_d['preorder_dt']?'| 🔴':'')?>  <?=($buy_d['preorder_dt']?$buy_d['preorder_dt']:'')?></div>
									</div>
									<div class="uc_uil2_raz">
										<div class="uc_uil2_mi">
											<div class="uc_uil2_mi1"><?=t::w('Address', 'Адрес:')?></div>
											<div class="uc_uil2_mi2"><?=$buy_d['address']?></div>
										</div>
										<div class="uc_uil2_mi">
											<div class="uc_uil2_mi1"><?=t::w('Number', 'Номер:')?></div>
											<div class="uc_uil2_mi2 fr_phone"><?=$buy_d['phone']?></div>
										</div>
									</div>
									<div class="uc_uil2_raz">
										<div class="uc_uil2_trt">
											<div class="uc_uil2_trt1"><?=t::w('Name', 'Атауы')?></div>
											<div class="uc_uil2_trt2"><?=t::w('Qty', 'Саны')?></div>
											<div class="uc_uil2_trt3"><?=t::w('Price', 'Бағасы')?></div>
										</div>
										<div class="uc_uil2_trc">

											<? 	
												$cashbox_id = $buy_d['id'];
												$cashboxp = db::query("select * from retail_orders_products where order_id = '$cashbox_id' order by ins_dt asc");
												$number = 0; $total = 0;
											?>
											<? if (mysqli_num_rows($cashboxp) != 0): ?>
												<? while ($sel_d = mysqli_fetch_assoc($cashboxp)): ?>
													<? 
														$number++; 
														$sum = $sel_d['quantity'] * $sel_d['price']; 
														$total = $total + $sum;
														$product_d = product::product($sel_d['product_id']);
													?>
													<div class="uc_uil2_trt">
														<div class="uc_uil2_trt1"><?=$number?>. <?=$product_d['name_ru']?></div>
														<div class="uc_uil2_trt2"><?=$sel_d['quantity']?> шт</div>
														<!-- <div class=""><?=$sel_d['price']?></div> -->
														<div class="uc_uil2_trt3 fr_price"><?=$sum?></div>
													</div>
												<? endwhile ?>
											<? endif ?>
											
											<div class="uc_uil2_trt">
												<div class="uc_uil2_trt1"><?=t::w('Delivery fee', 'Доставка')?></div>
												<div class="uc_uil2_trt3 fr_price"><?=$buy_d['pay_delivery']?></div>
											</div>
										</div>
										<div class="uc_uil2_trb">
											<div class="uc_uil2_trt1"><?=t::w('Total', 'Жалпы')?></div>
											<div class="uc_uil2_trt2"></div>
											<div class="uc_uil2_trt3 fr_price"><?=$buy_d['total']?></div>
										</div>
										<div class="uc_uil2_trc">
											<div class="uc_uil2_trt">
												<div class="uc_uil2_trt1"><?=t::w('Prepayment', 'Предоплата')?></div>
												<div class="uc_uil2_trt2">-</div>
												<div class="uc_uil2_trt3 fr_price"><?=$buy_d['pay_qr']?></div>
											</div>
											<div class="uc_uil2_trt">
												<div class="uc_uil2_trt1"><?=t::w('To courier (cash)', 'Курьерге (нал)')?></div>
												<div class="uc_uil2_trt2"></div>
												<div class="uc_uil2_trt3 fr_price"><?=$buy_d['pay_cash']?></div>
											</div>
										</div>
									</div>
									<div class="uc_uil2_raz">
										<div class="uc_uil2_mi">
											<div class="uc_uil2_mi1"><?=t::w('Operator:', 'Оператор:')?></div>
											<div class="uc_uil2_mi2"><?=(fun::user($buy_d['user_id']))['name']?></div>
										</div>
									</div>
									<div class="uc_uil2_raz">
										<div class="uc_uil2_mib">
											<a class="btn btn_cl on_ubd" data-id="<?=$buy_d['id']?>" href="/cashbox/?id=<?=$buy_d['id']?>&type=ubd"><?=t::w('Edit', 'Өзгерту')?></a>
											<div class="btn  on_print" data-id="<?=$buy_d['id']?>"><?=t::w('Print', 'Печать')?></div>
										</div>
									</div>
								</div>
							</div>

							<?	
								if ($buy_d['order_status'] != 5 && $buy_d['order_status'] != 6) {
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