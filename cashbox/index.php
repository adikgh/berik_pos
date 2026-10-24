<? include "../config/core.php";

	// 
	if (!$user_id) header('location: /');


    $cmp = fun::company($company);
	$open = true; $result = 0;
	if ($cmp['ins_dt'] != null && $cmp['end_dt'] != null) {
		$result = intval((strtotime($cmp['end_dt']) - strtotime(date("d.m.Y"))) / (60*60*24));
		if ($result <= 0) header('location: /exit.php');
	}


	if (@$_GET['id']) {
		$cashbox_id = $_GET['id'];
		$cashbox = db::query("select * from retail_orders where id = '$cashbox_id'");
	} else $cashbox = db::query("select * from retail_orders where user_id = '$user_id' and paid = 0 order by id desc limit 1");
	if (mysqli_num_rows($cashbox)) {
		$cashbox_d = mysqli_fetch_assoc($cashbox);
		$cashbox_id = $cashbox_d['id'];
	} else {
		$cashbox_id = (mysqli_fetch_assoc(db::query("SELECT * FROM `retail_orders` order by id desc")))['id'] + 1;
		$ins = db::query("INSERT INTO `retail_orders`(`id`, `user_id`, `company_id`, `branch_id`) VALUES ('$cashbox_id', '$user_id', '$company', '$branch')");
	}
	$cashboxp = db::query("select * from retail_orders_products where order_id = '$cashbox_id' order by ins_dt asc");
	$number = 0; $total = 0;




	// 
	$catalog_id = mysqli_fetch_assoc(db::query("select * from product_catalog where company_id = '$company' and arh is null order by number asc limit 1"))['id'];
	if (@$_SESSION['catalog_id']) $catalog_id = $_SESSION['catalog_id'];


	// site setting
	$menu_name = 'cashbox';
	$site_set['swiper'] = true;
	$css = ['cashbox'];
	$js = ['cashbox'];
?>
<? include "../block/header.php"; ?>

	<div class="bl_c">

		<div class="hup_bl">

			<div class="hup_r">
				<div class="hup_search">
					<i class="fal fa-search hup_search_icon"></i>
					<? // Іздеу өрісінде анимациямен көрсетілетін тауар атаулары (кездейсоқ 30)
						$hints = [];
						$hq = db::query("select p.name_kz, p.name_ru from product p inner join product_catalog c on c.id = p.catalog_id where c.company_id = '$company' and c.arh is null and p.arh = 0 order by rand() limit 30");
						while ($hq && ($hr = mysqli_fetch_assoc($hq))) { $hn = trim(preg_replace('/\s+/u', ' ', strip_tags((string)($lang == 'ru' && $hr['name_ru'] ? $hr['name_ru'] : $hr['name_kz'])))); if ($hn !== '') $hints[] = $hn; }
					?>
					<input type="search" class="hup_search_inp" placeholder="<?=t::w('Search products', 'Тауар іздеу (атауы немесе бағасы бойынша)...')?>" data-hint-pre="<?=htmlspecialchars(t::w('Search hint prefix', 'Іздеу:'))?>" data-hints="<?=htmlspecialchars(json_encode($hints, JSON_UNESCAPED_UNICODE))?>" autocomplete="off" data-cashbox-id="<?=$cashbox_id?>">
					<div class="hup_search_clear dsp_n"><i class="fal fa-times"></i></div>
				</div>
				<div class="">
					<div class="hup_rct">
						<? $catalog = db::query("select * from product_catalog where company_id = '$company' and type = 'main' and arh is null order by number asc"); ?>
						<? while ($catalog_d = mysqli_fetch_assoc($catalog)): ?>
							<div class="hup_rcti catalog_ubd <?=($catalog_d['id']==$catalog_id?'hup_rcti_act':'')?>" data-cashbox-id="<?=$cashbox_id?>" data-id="<?=$catalog_d['id']?>"  style="background:<?=$catalog_d['color']?>"><?=$catalog_d['name_'.$lang]?></div>
						<? endwhile ?>
					</div>
				</div>
				<div class="">
					<div class="hup_rcb">
						<? // $product = db::query("select * from product where catalog_id = '$catalog_id' order by number asc"); ?>
						<? $product = db::query("select * from product where catalog_id = '$catalog_id' and arh = 0 order by name_kz asc"); ?>
						<? while ($product_d = mysqli_fetch_assoc($product)): ?>
							<div class="hup_rcbi cashbox_add" data-oid="<?=$cashbox_id?>" data-id="<?=$product_d['id']?>">
								<div class=""><?=$product_d['name_kz']?></div>
								<div class=""><?=$product_d['price']?> тг</div>
							</div>
						<? endwhile ?>
					</div>
				</div>
			</div>

			<div class="cash_bl1">

				<div class="cash_bl1_l">
					<div class="uc_uh">
						<div class="uc_uh2">
							<div class="uc_uiln"><?=t::w('Name', 'Атауы')?></div>
							<div class="uc_uil">
								<div class="uc_uilf1">
									<div class="uc_uilf1c">
										<div class="uc_uh_other"><?=t::w('Price x qty', 'Бағасы x саны')?></div>
									</div>
								</div>
								<div class="uc_uilf2"></div>
							</div>
						</div>
						<!-- <div class="uc_uh_cn"></div> -->
					</div>
				</div>

				<div class="cash_bl1_r">
					<div class="cash_bl1_rc">
						<div class="uc_u <?=($view_pr?'uc_u2':'')?>">
							<div class="uc_uc lazy_c">
								<? if (mysqli_num_rows($cashboxp) != 0): ?>
									<? while ($sel_d = mysqli_fetch_assoc($cashboxp)): ?>
										<? $number++; $sum = $sel_d['quantity'] * $sel_d['price']; $total = $total + $sum; ?>
										<? $product_d = product::product($sel_d['product_id']); ?>
	
										<div class="uc_ui uc_ui2" data-id="<?=$sel_d['id']?>" data-item-id="<?=$product_d['id']?>" data-pr="<?=$sel_d['price']?>" data-qn="<?=$sel_d['quantity']?>" data-sum="<?=$sum?>">
											<div class="uc_uiln">
												<!-- <div class="uc_ui_img lazy_img" data-src="/assets/uploads/products/<?=$pitem_d['img']?>">
													<?=($pitem_d['img']!=null?'':'<i class="fal fa-box"></i>')?>
												</div> -->
												<div class="uc_uinu">
													<div class="uc_ui_name"><?=$number?>. <?=$product_d['name_ru']?></div>
												</div>
											</div>
											<div class="uc_uil">
												<div class="uc_uilf1">
													<div class="uc_uilf1c" data-qn="<?=$sel_d['quantity']?>">
														<!-- <div class="uc_uin_other">
															<input type="tel" class="uc_uin_calc_q cashbox_pr" value="<?=$sel_d['price']?>" data-lenght="1" />
														</div> -->
														<!-- <div class="uc_uin_other" data-max="<?=$quantity?>">
															<input type="tel" class="uc_uin_calc_q fr_number3 cashbox_qn" value="<?=$sel_d['quantity']?>" data-lenght="1" />
														</div> -->
														<div class="uc_uin_other fr_price cashbox_pr"><?=$sel_d['price']?></div>
														<div class="uc_uin_other cashbox_qn">х <?=$sel_d['quantity']?> шт</div>
													</div>
													<div class="uc_uin_other fr_price cashbox_sum"><?=$sum?></div>
												</div>
												<div class="uc_uilf2">
													<div class="uc_uin_cn cashbox_plus" data-id="<?=$sel_d['id']?>"><i class="far fa-plus"></i></div>
													<? if ($sel_d['quantity'] == 1): ?>
														<div class="uc_uin_cn cashbox_remove" data-id="<?=$sel_d['id']?>"><i class="far fa-trash-alt"></i></div>
														<div class="uc_uin_cn cashbox_minus dsp_n" data-id="<?=$sel_d['id']?>"><i class="far fa-minus"></i></div>
													<? else: ?>
														<div class="uc_uin_cn cashbox_remove dsp_n" data-id="<?=$sel_d['id']?>"><i class="far fa-trash-alt"></i></div>
														<div class="uc_uin_cn cashbox_minus " data-id="<?=$sel_d['id']?>"><i class="far fa-minus"></i></div>
													<? endif ?>

												</div>
											</div>
										</div>
									<? endwhile ?>
								<? else: ?> 
									<div class="ds_nr" Пустой список><p><?=t::w('Empty list', 'Бос тізім')?></p></div>
								<? endif ?>
							</div>
						</div>
					</div>
				</div>

				<div class="cash_bl1_rb <?=($total==0?'dsp_n':'')?>">
					<div class="cash_bl1_rbl">
						<div class="cash_bl1_rblin"><?=t::w('Total:', 'Қорытынды:')?></div>
						<div class="cash_bl1_rblip cashbox_total fr_price" data-total="<?=$total?>"><?=$total?></div>
					</div>
					<div class="cash_bl1_rbr">
						<div class="btn cashbox_pay" data-id="<?=$cashbox_id?>"><i class="far fa-check"></i><span><?=(@$_GET['id'] ? t::w('Edit', 'Өзгерту') : t::w('Save', 'Сақтау'))?></span></div>
					</div>
				</div>

			</div>

		</div>

	</div>

<? include "../block/footer.php"; ?>

	<!--  -->
	<div class="pop_bl pop_bl2 cashbox_pay_block">
		<div class="pop_bl_a cashbox_pay_back"></div>

		<div class="pop_usr_info " >
			<div class="head_c">
				<h4><?=t::w('Customer info', 'Тұтынушы жайлы')?></h4>
			</div>
			<div class="pop_usr_info_c"></div>
		</div>

		<div class="pop_bl_c">
			<div class="head_c">
				<h4><?=t::w('Save', 'Сақтау')?></h4>
				<div class="btn btn_dd cashbox_pay_back"><i class="fal fa-times"></i></div>
			</div>
			<div class="pop_bl_cl">
				<div class="form_c">
				<div class="pay_main">

					<? // Точкалар: біреу ғана болса таңдау көрсетілмейді, сол точка автоматты жазылады
						$branch_list = [];
						$уbranch = db::query("select * from company_branch where company_id = '$company' order by number asc");
						while ($branch_d = mysqli_fetch_assoc($уbranch)) $branch_list[] = $branch_d;
						$sel_branch = $branch;
						if (count($branch_list) == 1) $sel_branch = $branch_list[0]['id'];
					?>
					<div class="form_im form_imss sel_branch <?=(count($branch_list) < 2 ? 'dsp_n' : '')?>" data-val="<?=$sel_branch?>">
						<div class="form_span"><?=t::w('Branch:', 'Точка:')?></div>
						<? foreach ($branch_list as $branch_d): ?>
							<div class="form_im_slo " >
								<div class="form_im_slo_i <?=($sel_branch == $branch_d['id']?'form_im_slo_act':'')?>"  data-type="<?=$branch_d['id']?>"><?=$branch_d['name']?></div>
							</div>
						<? endforeach ?>
					</div>

					<? // Заказ түрі: компанияда қосулы түрлер, стандарты company.type_def (1 Жеткізу, 2 Собой, 4 Кафе, 3 Сервис)
						$type_on = [];
						foreach ([1 => 'delivery', 2 => 'yourself', 4 => 'coffee', 3 => 'service'] as $t_id => $t_col) if (@$cmp[$t_col]) $type_on[] = $t_id;
						$type_def = (int)@$cmp['type_def'];
						if (!in_array($type_def, $type_on)) $type_def = $type_on ? $type_on[0] : 1;
					?>
					<div class="form_im form_imss sel_type" data-val="<?=$type_def?>">
						<div class="form_span"><?=t::w('Order type:', 'Заказ түрі:')?></div>
						<? if ($cmp['delivery']): ?>
							<div class="form_im_slo<?=($type_def == 1 ? ' sel_type_def' : '')?>">
								<div class="form_im_slo_i <?=($type_def == 1 ? 'form_im_slo_act' : '')?>"  data-type="1"><?=t::w('Delivery', 'Жеткізу')?></div>
							</div>
						<? endif ?>
						<? if ($cmp['yourself']): ?>
							<div class="form_im_slo<?=($type_def == 2 ? ' sel_type_def' : '')?>">
								<div class="form_im_slo_i <?=($type_def == 2 ? 'form_im_slo_act' : '')?>"  data-type="2"><?=t::w('Takeaway', 'Өзімен')?></div>
							</div>
						<? endif ?>
						<? if ($cmp['coffee']): ?>
							<div class="form_im_slo<?=($type_def == 4 ? ' sel_type_def' : '')?>">
								<div class="form_im_slo_i <?=($type_def == 4 ? 'form_im_slo_act' : '')?>"  data-type="4"><?=t::w('Cafe', 'Кафе')?></div>
							</div>
						<? endif ?>
						<? if ($cmp['service']): ?>
							<div class="form_im_slo<?=($type_def == 3 ? ' sel_type_def' : '')?>">
								<div class="form_im_slo_i <?=($type_def == 3 ? 'form_im_slo_act' : '')?>" data-type="3" btype_ynd><?=t::w('Service', 'Сервис')?></div>
							</div>
						<? endif ?>
					</div>

					<? $srv_list = ['Яндекс', 'Глово', 'Волт', 'Басқа']; $srv_cur = trim((string)@$cashbox_d['service']); if ($srv_cur !== '' && !in_array($srv_cur, $srv_list)) $srv_cur = 'Басқа'; ?>
					<div class="form_im form_imss sel_srv dsp_n" data-val="<?=htmlspecialchars($srv_cur)?>">
						<div class="form_span"><?=t::w('Service type:', 'Сервис түрі:')?></div>
						<? foreach ($srv_list as $srv): ?>
							<div class="form_im_slo ">
								<div class="form_im_slo_i <?=($srv_cur == $srv ? 'form_im_slo_act' : '')?>" data-type="<?=$srv?>"><?=($srv == 'Басқа' ? t::w('Other', 'Басқа') : $srv)?></div>
							</div>
						<? endforeach ?>
					</div>

					<div class="tf_phone">
						<div class="form_im ">
							<div class="form_span"><?=t::w('Phone:', 'Нөмір:')?></div>
							<input type="tel" class="form_txt fr_phone btype_phone" data-lenght="11" placeholder="8 (700) 000-00-00" data-val="8<?=$cashbox_d['phone']?>" value="<?=$cashbox_d['phone']?>">
							<i class="fal fa-phone-alt form_icon"></i>
						</div>
					</div>

					<br>
					
					<div class="tf_address">
						<div class="form_im ">
							<div class="form_span"><?=t::w('Address:', 'Мекен-жай (адрес):')?></div>
							<input type="text" class="form_txt btype_address" placeholder="" data-val="<?=$cashbox_d['address']?>" value="<?=$cashbox_d['address']?>">
							<i class="fal fa-text form_icon"></i>
						</div>
					</div>

					<br>

					<div class="opt_btns">
						<div class="opt_btn" data-target=".tf_add"><i class="fal fa-plus"></i> <?=t::w('Additional info', 'Қосымша ақпарат')?></div>
						<div class="opt_btn" data-target=".tf_preorder"><i class="fal fa-plus"></i> <?=t::w('Preorder', 'Предзаказ')?></div>
					</div>

					<div class="tf_add opt_off">
						<div class="form_im ">
							<div class="form_span"><?=t::w('Notes if needed:', 'Қосымша қажет болса:')?></div>
							<input type="text" class="form_txt btype_add" placeholder="<?=t::w('Notes example', 'Мысалы: Замена пицца или суши')?>" data-val="0">
							<i class="fal fa-text form_icon"></i>
						</div>
					</div>

					<br>

					<div class="tf_preorder opt_off">
						<div class="form_im form_imss sel_pre_day">
							<div class="form_span"><?=t::w('Preorder:', 'Алдын-ала тапсырыс (предзаказ):')?></div>
							<div class="form_im_slo "><div class="form_im_slo_i form_im_slo_act" data-type="<?=date('Y-m-d')?>"><?=t::w('Today', 'Бүгін')?></div></div>
							<div class="form_im_slo "><div class="form_im_slo_i" data-type="<?=date('Y-m-d', strtotime('+1 day'))?>"><?=t::w('Tomorrow', 'Ертең')?></div></div>
							<div class="form_im_slo "><div class="form_im_slo_i sel_pre_other" data-type="other"><i class="fal fa-calendar-alt"></i>&nbsp; <?=t::w('Another day', 'Басқа күн')?></div></div>
						</div>
						<div class="pre_dt_row">
							<div class="form_im pre_date_im dsp_n">
								<input type="date" class="form_txt pre_date" min="<?=date('Y-m-d')?>" max="<?=date('Y-m-d', strtotime('+7 day'))?>" value="<?=date('Y-m-d')?>">
							</div>
							<div class="form_im pre_time_im">
								<input type="time" class="form_txt pre_time" step="300">
								<i class="fal fa-clock form_icon"></i>
							</div>
						</div>
						<div class="pre_note dsp_n"><?=t::w('Preorder note', 'Басқа күнге: тапсырыс сақталады, бірақ бүгінгі тізімге түспейді, «Предзаказдар» бөлімінде тұрады.')?></div>
						<input type="hidden" class="btype_preorder" data-val="0" value="">
					</div>

					<br>

					<? // Жеткізу: дайын сомалар батырмамен, басқа сома керек болса «+» арқылы өріс ашылады
						$dlv_list = [0, 500, 700, 800, 1000, 1200, 1500, 2000];
						$dlv_cur = (int)$cashbox_d['pay_delivery'];
						$dlv_custom = !in_array($dlv_cur, $dlv_list);
					?>
					<div class="tf_dlv">
					<div class="form_im form_imss sel_dlv" data-val="<?=$dlv_cur?>">
						<div class="form_span"><?=t::w('Delivery (courier)', 'Жеткізу (курьер)')?></div>
						<? foreach ($dlv_list as $dlv): ?>
							<div class="form_im_slo ">
								<div class="form_im_slo_i <?=(!$dlv_custom && $dlv_cur == $dlv ? 'form_im_slo_act' : '')?>" data-type="<?=$dlv?>"><?=number_format($dlv, 0, '', ' ')?> тг</div>
							</div>
						<? endforeach ?>
						<div class="form_im_slo ">
							<div class="form_im_slo_i sel_dlv_add <?=($dlv_custom ? 'form_im_slo_act' : '')?>" data-type="add"><i class="fal fa-plus"></i>&nbsp; <?=t::w('Other', 'Басқа')?></div>
						</div>
					</div>
					<div class="form_im dlv_inp <?=($dlv_custom ? '' : 'dsp_n')?>">
						<input type="tel" class="form_txt fr_price btype_delivery" placeholder="<?=t::w('Other amount', 'Басқа сома')?>" data-val="<?=$cashbox_d['pay_delivery']?>" value="<?=$cashbox_d['pay_delivery']?>">
						<i class="fal fa-tenge form_icon"></i>
					</div>
					</div>

					<br>

					<? $red_pct = isset($cmp['red']) ? (int)$cmp['red'] : 10; // company.red: 0 = жасыру, 5 / 10 = пайыз ?>
					<? if ($red_pct > 0): ?>
					<div class="form_im form_imss tf_red">
						<!-- <div class="form_span">Тип оплаты:</div> -->
						<div class="form_im_slo " data-type-name="RED" data-nall="">
							<div class="form_im_slo_i form_im_slo_red btype_red " data-type="<?=$red_pct?>">RED <?=$red_pct?>%</div>
						</div>
					</div>
					<? endif ?>

					<br><br>
					
					<div class="pay_box">
					<div class="pay_box_h"><?=t::w('Payment', 'Төлем')?></div>
					<? // Предоплата: дайын сомалар, «Толық» — жалпы сома, «+» — басқа сома
						$qr_list = [0, 2000, 3000, 5000];
						$qr_cur = (int)$cashbox_d['pay_qr'];
						$qr_custom = !in_array($qr_cur, $qr_list);
					?>
					<div class="cashbox_pay_btype">
						<div class="form_im form_imss sel_qr">
							<div class="form_span"><?=t::w('Prepayment:', 'Алдын ала төлем (предоплата):')?></div>
							<? foreach ($qr_list as $qr): ?>
								<div class="form_im_slo ">
									<div class="form_im_slo_i <?=(!$qr_custom && $qr_cur == $qr ? 'form_im_slo_act' : '')?>" data-type="<?=$qr?>"><?=number_format($qr, 0, '', ' ')?> тг</div>
								</div>
							<? endforeach ?>
							<div class="form_im_slo ">
								<div class="form_im_slo_i sel_qr_full" data-type="full"><?=number_format($total + $cashbox_d['pay_delivery'], 0, '', ' ')?> тг</div>
							</div>
							<div class="form_im_slo ">
								<div class="form_im_slo_i sel_qr_add <?=($qr_custom ? 'form_im_slo_act' : '')?>" data-type="add"><i class="fal fa-plus"></i>&nbsp; <?=t::w('Other', 'Басқа')?></div>
							</div>
						</div>
						<div class="form_im qr_inp <?=($qr_custom ? '' : 'dsp_n')?>">
							<input type="tel" class="form_txt fr_price btype_qr" placeholder="<?=t::w('Other amount', 'Басқа сома')?>" data-val="<?=$cashbox_d['pay_qr']?>" value="<?=$cashbox_d['pay_qr']?>">
							<i class="fal fa-tenge form_icon"></i>
						</div>
						<div class="form_im form_imss sel_qrm <?=($qr_cur > 0 ? '' : 'dsp_n')?>" data-val="qr">
							<div class="form_span"><?=t::w('Prepaid with:', 'Предоплата немен төленді:')?></div>
							<div class="form_im_slo "><div class="form_im_slo_i" data-type="transfer"><?=t::w('Transfer', 'Аударым')?></div></div>
							<div class="form_im_slo "><div class="form_im_slo_i form_im_slo_act" data-type="qr">Kaspi QR</div></div>
							<div class="form_im_slo "><div class="form_im_slo_i" data-type="cash"><?=t::w('Cash', 'Қолма-қол')?></div></div>
							<? if (@$red_pct > 0): // company.red = 0 болса RED шықпайды ?>
							<div class="form_im_slo "><div class="form_im_slo_i" data-type="red">RED <?=$red_pct?>%</div></div>
							<? endif ?>
							<div class="form_im_slo "><div class="form_im_slo_i sel_pay_mix2" data-type="mix"><i class="fal fa-plus"></i>&nbsp; <?=t::w('Mixed', 'Аралас')?></div></div>
						</div>
						<div class="qrm_mix dsp_n">
							<? foreach (['transfer' => t::w('Transfer', 'Аударым'), 'qr' => 'Kaspi QR', 'cash' => t::w('Cash', 'Қолма-қол')] + (@$red_pct > 0 ? ['red' => 'RED'] : []) as $qm_key => $qm): ?>
								<div class="form_im pay_mix_i">
									<div class="form_span"><?=$qm?>:</div>
									<input type="tel" class="form_txt fr_price btype_qrmix" data-name="<?=$qm_key?>" placeholder="0 тг" data-val="0">
									<i class="fal fa-tenge form_icon"></i>
								</div>
							<? endforeach ?>
						</div>
					</div>
					
					<div class="cashbox_pay_bsem">
						<div class="form_im">
							<div class="form_span"><?=t::w('Remaining:', 'Қалған сома:')?></div>
							<div class="cashbox_pay_bsemc btype_cash fr_price" data-val="<?=$total + $cashbox_d['pay_delivery'] - $cashbox_d['pay_qr']?>"><?=$total + $cashbox_d['pay_delivery'] - $cashbox_d['pay_qr']?></div>
						</div>
						<!-- <div class="form_im">
							<div class="form_span"><?=t::w('Total:', 'Қорытынды:')?></div>
							<div class="cashbox_pay_bsemc cashbox_pay_bsemt fr_price"><?=$total?></div>
						</div> -->
					</div>

					<? // Кафе: төлем түрі (бір түр немесе аралас)
						// кілт = retail_orders.payment_method мәні, сома сол бағанға жазылады (transfer → pay_transfer, qr → pay_qr, cash → pay_cash)
						$pay_list = ['transfer' => t::w('Transfer', 'Аударым'), 'qr' => 'Kaspi QR', /* 'halyk' => 'Halyk QR', 'card' => 'Картамен', */ 'cash' => t::w('Cash', 'Қолма-қол')]; // Halyk QR, Картамен уақытша жасырылды
					?>
					<div class="tf_paytype dsp_n">
						<div class="form_im form_imss sel_pay" data-val="">
							<div class="form_span"><?=t::w('Payment type:', 'Төлем түрі:')?></div>
							<? foreach ($pay_list as $pay_key => $pay): ?>
								<div class="form_im_slo ">
									<div class="form_im_slo_i" data-type="<?=$pay_key?>"><?=$pay?></div>
								</div>
							<? endforeach ?>
							<div class="form_im_slo ">
								<div class="form_im_slo_i sel_pay_mix" data-type="mix"><i class="fal fa-plus"></i>&nbsp; <?=t::w('Mixed', 'Аралас')?></div>
							</div>
						</div>
						<div class="pay_cash dsp_n">
							<div class="form_im form_imss sel_given">
								<div class="form_span"><?=t::w('Customer gave:', 'Клиент берді:')?></div>
								<? foreach ([1000, 2000, 5000, 10000, 20000] as $bill): ?>
									<div class="form_im_slo ">
										<div class="form_im_slo_i" data-type="<?=$bill?>"><?=number_format($bill, 0, '', ' ')?> тг</div>
									</div>
								<? endforeach ?>
							</div>
							<div class="form_im">
								<input type="tel" class="form_txt fr_price btype_given" placeholder="<?=t::w('Other amount', 'Басқа сома')?>" data-val="0">
								<i class="fal fa-tenge form_icon"></i>
							</div>
							<div class="pay_change"><span><?=t::w('Change:', 'Сдача (қайтарым):')?></span> <b class="pay_change_c">0 тг</b></div>
						</div>
						<div class="pay_mix dsp_n">
							<? foreach ($pay_list as $pay_key => $pay): ?>
								<div class="form_im pay_mix_i">
									<div class="form_span"><?=$pay?>:</div>
									<input type="tel" class="form_txt fr_price btype_mix" data-name="<?=$pay_key?>" placeholder="0 тг" data-val="0">
									<i class="fal fa-tenge form_icon"></i>
								</div>
							<? endforeach ?>
							<div class="pay_mix_rest"></div>
						</div>
					</div>

					</div>
				</div>
					<div class="pay_foot">
					<div class="cashbox_pay_btotol">
						<div class="form_span"><?=t::w('Grand total:', 'Жалпы ақшасы:')?></div>
						<div class="cashbox_pay_btotol_c fr_price" data-val="<?=$total + $cashbox_d['pay_delivery']?>" data-on-val="<?=$total?>"><?=$total + $cashbox_d['pay_delivery']?></div>
					</div>

					<div class="form_im">
						<? if (@$_GET['id']): ?>
							<div class="btn btn_cl cashbox_pay2" data-id="<?=$cashbox_id?>" data-type="ubd"><i class="far fa-check"></i><span><?=t::w('Edit', 'Өзгерту')?></span></div>
							<? if ($cmp['print'] == 1): ?>
								<div class="btn cashbox_pay2" data-id="<?=$cashbox_id?>" data-type="ubd" data-print="1"><i class="far fa-check-double"></i><span><?=t::w('Sell and print', 'Сату және чек шығару')?></span></div>
							<? endif ?>
						<? else: ?>
							<div class="btn cashbox_pay2" data-id="<?=$cashbox_id?>" data-type="check" data-print="<?=$cmp['print']?>"><i class="far fa-check-double"></i><span><?=t::w('Save and print', 'Сақтау және чек шығару')?></span></div>
						<? endif ?>
					</div>
					</div>
				</div>

			</div>
		</div>
	</div>

