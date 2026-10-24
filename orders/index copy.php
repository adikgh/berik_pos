<? include "../config/core.php";

	// 
	if (!$user_id) header('location: /');



   	$type = @$_GET['type'];

	if (@$_GET['time']) {
		$time_sort = $_GET['time'];
		$start_cdate = date('Y-m-d 06:00:00', strtotime("$date $time_sort day"));
		$end_cdate = date('Y-m-d 06:00:00', strtotime("$start_cdate +1 day"));
	}


	if (@$_GET['status'] && @$_GET['staff']) {
		$status = $_GET['status'];
		$staff = $_GET['staff'];
		if ($staff == 'off') $orders = db::query("select * from retail_orders where ins_dt BETWEEN '$start_cdate' and '$end_cdate' and order_status = '$status' and сourier_id is null and branch_id = '$branch' order by number desc");
		elseif ($staff == 'soboi') $orders = db::query("select * from retail_orders where ins_dt BETWEEN '$start_cdate' and '$end_cdate' and order_status = '$status' and order_type = 2 and branch_id = '$branch' order by number desc");
		else $orders = db::query("select * from retail_orders where ins_dt BETWEEN '$start_cdate' and '$end_cdate' and order_status = '$status' and сourier_id  = '$staff'  and branch_id = '$branch' order by number desc");
	} elseif (@$_GET['status']) {
		$status = $_GET['status'];
		$orders = db::query("select * from retail_orders where ins_dt BETWEEN '$start_cdate' and '$end_cdate' and order_status = '$status'  and branch_id = '$branch' order by number desc");
	} elseif (@$_GET['staff']) {
		$staff = $_GET['staff'];
		if ($staff == 'off') $orders = db::query("select * from retail_orders where ins_dt BETWEEN '$start_cdate' and '$end_cdate' and order_type = 1 and сourier_id is null and branch_id = '$branch' order by number desc");
		elseif ($staff == 'soboi') $orders = db::query("select * from retail_orders where ins_dt BETWEEN '$start_cdate' and '$end_cdate' and order_type = 2 and branch_id = '$branch' order by number desc");
		else $orders = db::query("select * from retail_orders where ins_dt BETWEEN '$start_cdate' and '$end_cdate' and сourier_id  = '$staff'  and branch_id = '$branch' order by number desc");
	} else $orders = db::query("select * from retail_orders where ins_dt BETWEEN '$start_cdate' and '$end_cdate' and paid = 1 and branch_id = '$branch' order by number desc");


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

	<div class="flex_clm_rev">

		<div class="bl_c">

			<div class="uc_u">

				<? if ($orders != ''): ?>
					<? if (mysqli_num_rows($orders) != 0): ?>
						<? while ($buy_d = mysqli_fetch_assoc($orders)): ?>
							<? // if ($buy_d['branch_id']) $branch_d = fun::branch($buy_d['branch_id']); ?>
							<? $order_sts = fun::order_sts($buy_d['order_status']); ?>

							<div class="uc_ui">
								<div class="uc_uil2" >
									<div class="uc_uil2_top">
										<div class="uc_uil2_nmb"><?=$buy_d['number']?></div>
										<div class="uc_uil2_date">
											<div class="uc_uil2_date1">
												<? if ($buy_d['сourier_id']): $сourier_d = fun::user($buy_d['сourier_id']); ?>
													<?=$сourier_d['name']?> <br> <span class="fr_phone"><?=$сourier_d['phone']?></span>
												<? else: ?>
													<select name="" id="" class="on_stype" data-order-id="<?=$buy_d['id']?>" >
														<option value="" <?=($buy_d['order_type']==1?'selected':'')?> data-id="kr">Курьер</option>
														<option value="" <?=($buy_d['order_type']==2?'selected':'')?> data-id="sb">Собой</option>
														
														<? $staff = db::query("select * from user_staff where positions_id = 6 and company_id = '$company'"); ?>
														<? while ($staff_d = mysqli_fetch_assoc($staff)): ?>
															<? $staff_user_d = fun::user($staff_d['user_id']); ?>
															<option value="" data-id="<?=$staff_d['user_id']?>"><?=$staff_user_d['name']?></option>
														<? endwhile ?>

													</select>
												<? endif ?>
											</div>
										</div>
										<div class="or_status" style="background-color:<?=$order_sts['clr']?>;"> <?//=$order_sts['name_kz']?>
											<select name="" id="" class="on_status" data-order-id="<?=$buy_d['id']?>" >
												<? $orders_status = db::query("select * from retail_orders_status"); ?>
												<? while ($orders_status_d = mysqli_fetch_assoc($orders_status)): ?>
													<option data-id="<?=$orders_status_d['id']?>" <?=($order_sts['id'] == $orders_status_d['id']?'selected':'')?> value="" ><?=$orders_status_d['name_kz']?></option>
												<? endwhile ?>
											</select>
										</div>
									</div>
									<br>
									<div class="uc_uil2_date">
										<div class=""><?=date("d-m-Y", strtotime($buy_d['ins_dt']))?> ⌛ <?=date("H:i", strtotime($buy_d['ins_dt']))?> <?=($buy_d['preorder_dt']?'| 🔴':'')?>  <?=($buy_d['preorder_dt']?$buy_d['preorder_dt']:'')?></div>
									</div>
									<div class="uc_uil2_raz">
										<div class="uc_uil2_mi">
											<div class="uc_uil2_mi1">Адрес:</div>
											<div class="uc_uil2_mi2"><?=$buy_d['address']?></div>
										</div>
										<div class="uc_uil2_mi">
											<div class="uc_uil2_mi1">Номер:</div>
											<div class="uc_uil2_mi2 fr_phone"><?=$buy_d['phone']?></div>
										</div>
									</div>
									<div class="uc_uil2_raz">
										<div class="uc_uil2_trt">
											<div class="uc_uil2_trt1">Атауы</div>
											<div class="uc_uil2_trt2">Саны</div>
											<div class="uc_uil2_trt3">Бағасы</div>
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
												<div class="uc_uil2_trt1">Доставка</div>
												<div class="uc_uil2_trt3 fr_price"><?=$buy_d['pay_delivery']?></div>
											</div>
										</div>
										<div class="uc_uil2_trb">
											<div class="uc_uil2_trt1">Жалпы</div>
											<div class="uc_uil2_trt2"></div>
											<div class="uc_uil2_trt3 fr_price"><?=$buy_d['total']?></div>
										</div>
										<div class="uc_uil2_trc">
											<div class="uc_uil2_trt">
												<div class="uc_uil2_trt1">Предоплата</div>
												<div class="uc_uil2_trt2">-</div>
												<div class="uc_uil2_trt3 fr_price"><?=$buy_d['pay_qr']?></div>
											</div>
											<div class="uc_uil2_trt">
												<div class="uc_uil2_trt1">Курьерге (нал)</div>
												<div class="uc_uil2_trt2"></div>
												<div class="uc_uil2_trt3 fr_price"><?=$buy_d['pay_cash']?></div>
											</div>
										</div>
									</div>
									<div class="uc_uil2_raz">
										<div class="uc_uil2_mib">
											<a class="btn btn_cl on_ubd" data-id="<?=$buy_d['id']?>" href="/cashbox/?id=<?=$buy_d['id']?>&type=ubd">Изменить</a>
											<div class="btn  on_print" data-id="<?=$buy_d['id']?>">Печать</div>
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
					<? else: ?> <div class="ds_nr"><i class="fal fa-none"></i><p>демалыс</p></div> <? endif ?>
				<? else: ?> <div class="ds_nr"><i class="fal fa-none"></i><p>демалыс</p></div> <? endif ?>

			</div>

		</div>

		<div class="bl_c">

			<div class="">
				<div class="uc_ui uc_ui69">
					<!-- <div class="btn cashbox_pay" style="margin-right: 15px;">Новый заказ</div> -->
					<div class="uc_uin_other">
						<select name="status" class="on_sort_time" data-order-id="<?=$buy_d['id']?>" >
							<option data-id="" value="" data-val="0" <?=(@$time_sort == 0?'selected':'')?>>Бүгін (<?=date('d', strtotime("$date"))?>)</option>
							<option data-id="" value="" data-val="-1" <?=(@$time_sort == -1?'selected':'')?>>Кеше (<?=date('d', strtotime("$date -1 day"))?>)</option>
							<option data-id="" value="" data-val="-2" <?=(@$time_sort == -2?'selected':'')?>>Алдыңғы күні (<?=date('d', strtotime("$date -2 day"))?>)</option>
						</select>
					</div>
					<div class="uc_uin_other">
						<select name="status" class="on_sort_status" data-order-id="<?=$buy_d['id']?>" >
							<option data-id="" value="">Барлығы</option>
							<? $orders_status = db::query("select * from retail_orders_status"); ?>
							<? while ($orders_status_d = mysqli_fetch_assoc($orders_status)): ?>
								<option data-id="<?=$orders_status_d['id']?>" <?=(@$_GET['status'] == $orders_status_d['id']?'selected':'')?> value="" ><?=$orders_status_d['name_kz']?></option>
							<? endwhile ?>
						</select>
					</div>
					<div class="uc_uin_other">
						<select name="staff" class="on_sort_staff" data-order-id="<?=$buy_d['id']?>" >
							<option data-id="" value="">Барлығы</option>
							<option data-id="soboi" <?=(@$_GET['staff'] == 'soboi'?'selected':'')?> value="">Собой</option>
							<option data-id="off" <?=(@$_GET['staff'] == 'off'?'selected':'')?> value="">Таңдалмаған</option>
							<? $staff = db::query("select * from user_staff where positions_id = 6"); ?>
							<? while ($staff_d = mysqli_fetch_assoc($staff)): ?>
								<? $staff_user_d = fun::user($staff_d['user_id']); ?>
								<option data-id="<?=$staff_d['user_id']?>" <?=(@$_GET['staff'] == $staff_d['user_id']?'selected':'')?> value=""><?=$staff_user_d['name']?></option>
							<? endwhile ?>
						</select>
					</div>
					<div class="uc_uin_other">Жалпы: <?=$allorder['total']?> тг</div>
					<div class="uc_uin_other">QR: <?=$allorder['pay_qr']?> тг</div>
					<div class="uc_uin_other">Нал: <?=$allorder['pay_cash']?> тг</div>
				</div>
			</div>

		</div>


	</div>

<? include "../block/footer.php"; ?>

	<!--  -->
	<div class="pop_bl pop_bl2 cashbox_pay_block">
		<div class="pop_bl_a cashbox_pay_back"></div>
		<div class="pop_bl_c">
			<div class="head_c">
				<h4>Оплата</h4>
				<div class="btn btn_dd cashbox_pay_back"><i class="fal fa-times"></i></div>
			</div>
			<div class="pop_bl_cl">
				<div class="form_c">

					<div class="">
						<div class="form_im">
							<div class="form_span">Номер заказа:</div>
							<input type="tel" class="form_txt fr_number2 order_number_sel" placeholder="0" value="" data-val="">
							<i class="fal fa-solar-panel form_icon"></i>
						</div>
						<div class="form_im ">
							<div class="form_span">Общий цена:</div>
							<input type="tel" class="form_txt fr_price btype_totol" placeholder="0" data-val="0">
							<i class="fal fa-tenge form_icon"></i>
						</div>
						<div class="form_im ">
							<div class="form_span">Предоплата:</div>
							<input type="tel" class="form_txt fr_price btype_qr" placeholder="0" value="" data-val="">
							<i class="fal fa-tenge form_icon"></i>
						</div>
						<div class="form_im ">
							<div class="form_span">Доставка:</div>
							<input type="tel" class="form_txt fr_price btype_delivery" placeholder="0" data-val="0">
							<i class="fal fa-tenge form_icon"></i>
						</div>
					</div>

					<div class="form_im">
						<div class="btn cashbox_pay2" data-branch="<?=$branch?>">Сақтау</div>
					</div>

				</div>

			</div>
		</div>
	</div>