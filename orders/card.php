<? // Тапсырыс карточкасы: тізім (index.php) мен іздеу (search.php) екеуі де осыны қолданады. Керек: $buy_d, $cmp, $company ?>
<? if (!isset($buy_d)) exit(); // бөлек ашылмайды ?>
							<? // if ($buy_d['branch_id']) $branch_d = fun::branch($buy_d['branch_id']); ?>
							<? $order_sts = fun::order_sts($buy_d['order_status']); ?>
							<? $by_cour = ((int)$buy_d['order_type'] == 1 && ((int)$buy_d['order_status'] == 3 || (int)$buy_d['order_status'] == 6 || ((int)$buy_d['order_status'] == 4 && !empty($buy_d['сourier_id']) && (int)@mysqli_fetch_assoc(db::query("select user_id from retail_orders_log where order_id = '".(int)$buy_d['id']."' and field = 'status' order by id desc limit 1"))['user_id'] == (int)$buy_d['сourier_id']))); // статусты курьер қойса (Аяқталды / Отказ при получение), оператор өзгерте алмайды ?>
							<? // Статус түсі (атауы бойынша), тізімде жоқ болса базадағы түс
	if (!isset($st_colors_db)) { $st_colors_db = []; $stc_q = db::query("select name_ru, clr from retail_orders_status"); while ($stc = mysqli_fetch_assoc($stc_q)) $st_colors_db[trim($stc["name_ru"])] = $stc["clr"]; } $st_colors = $st_colors_db; // түстер базадан: retail_orders_status.clr
	$st_name = trim(@$order_sts['name_ru']);
	$st_clr = isset($st_colors[$st_name]) ? $st_colors[$st_name] : (isset($st_colors[trim(@$order_sts['name_kz'])]) ? $st_colors[trim(@$order_sts['name_kz'])] : $order_sts['clr']);
?>
							<? $order_type = fun::order_type($buy_d['order_type']); ?>
							<? $branch_d = fun::branch($buy_d['branch_id']); ?>
							<? $ot = (int)$order_type['id']; // 1 Жеткізу, 2 Өзімен, 3 Сервис, 4 Кафе
								$pm_names = ['transfer' => t::w('Transfer', 'Аударым'), 'qr' => 'Kaspi QR', 'cash' => t::w('Cash', 'Қолма-қол'), 'mixed' => t::w('Mixed', 'Аралас'), 'unpaid' => t::w('Unpaid', 'Төленбеген')]; ?>

							<div class="uc_ui or_card or_t<?=$ot?><?=(@$buy_d['payment_method'] == 'unpaid' ? ' or_unpaid_c' : '')?>" data-oid="<?=$buy_d['id']?>"<?=(@$buy_d['preorder_dt'] && (int)$buy_d['order_status'] == 1 ? ' data-pre-left="'.(strtotime($buy_d['preorder_dt']) - time()).'"' : '')?> style="--st:<?=$st_clr?>;">
								<div class="uc_uil2" >
									<div class="uc_uil2_top">
										<div class="uc_uil2_nmb"><?=$branch_d['code']?><?=$buy_d['number']?></div>
										<div class="uc_uil2_topc">
											<div class="uc_uil2_date or_meta">
												<? // Предзаказ: негізгі уақыт — қабылданған уақыт (тарихтағы «Жасалды»), жанында қызыл таймер + предзаказ уақыты
													$or_acc = $buy_d['ins_dt']; $or_pre = (string)@$buy_d['preorder_dt']; $or_acc_ok = false;
													if ($or_pre !== '') { if (!isset($or_lg_on)) $or_lg_on = (bool)mysqli_num_rows(db::query("SHOW TABLES LIKE 'retail_orders_log'"));
														if ($or_lg_on) { $or_lg = mysqli_fetch_assoc(db::query("select ins_dt from retail_orders_log where order_id = '".(int)$buy_d['id']."' and field = 'created' order by id asc limit 1")); if ($or_lg) { $or_acc = $or_lg['ins_dt']; $or_acc_ok = true; } } }
													$or_pre_ts = $or_pre !== '' ? strtotime($or_pre) : 0; ?>
												<div class="or_dt<?=($or_pre !== '' && !$or_acc_ok ? ' or_dt_pre' : '')?>"<?=($or_pre !== '' ? ' title="'.t::w('Preorder', 'Предзаказ').'"' : '')?>><div class="or_tm_r"><b class="or_time"><?=date("H:i", strtotime($or_acc))?></b><? if ($or_acc_ok): ?><span class="or_pre_tm<?=((int)$buy_d['order_status'] != 1 ? ' or_pre_calm' : '')?>" title="<?=t::w('Preorder', 'Предзаказ')?>"><i class="far fa-stopwatch"></i><?=date("H:i", $or_pre_ts)?><? if (date('Y-m-d', $or_pre_ts) != date('Y-m-d', strtotime($or_acc))): ?><small><?=date("d.m", $or_pre_ts)?></small><? endif ?></span><? endif ?></div><span class="or_date"><?=date("d-m-Y", strtotime($or_acc))?></span></div>
												<span class="or_meta_r"><? if (@$buy_d['payment_method'] == 'unpaid' && !in_array((int)$buy_d['order_status'], [5, 6, 10])): // Төленбеген: «Төледі» батырмасы, төлем түрін таңдап, тапсырысты аяқтайды ?><div class="or_paid" data-id="<?=$buy_d['id']?>" data-total="<?=(int)$buy_d['total']?>"><span class="or_paid_btn"><i class="far fa-wallet"></i><?=t::w('Paid', 'Төледі')?></span><div class="or_paid_ch"><div class="or_paid_t"><?=t::w('How did they pay?', 'Немен төледі?')?></div><div class="or_paid_l"><div class="or_paid_o" data-m="cash"><i class="far fa-money-bill-wave"></i><span><?=t::w('Cash', 'Қолма-қол')?></span></div><div class="or_paid_o" data-m="qr"><i class="far fa-qrcode"></i><span>Kaspi QR</span></div><div class="or_paid_o" data-m="transfer"><i class="far fa-exchange-alt"></i><span><?=t::w('Transfer', 'Аударым')?></span></div><div class="or_paid_o" data-m="mixed"><i class="far fa-random"></i><span><?=t::w('Mixed', 'Аралас')?></span></div><? $red_pct_c = isset($cmp['red']) ? (int)$cmp['red'] : 10; if ($red_pct_c > 0): $red_sum_c = (int)$buy_d['total'] + (int)round((int)$buy_d['total'] * $red_pct_c / 100); ?><div class="or_paid_o or_paid_red" data-m="red"><i class="far fa-percent"></i><span>RED <?=$red_pct_c?>%</span><b><?=number_format($red_sum_c, 0, '', ' ')?> тг</b></div><? endif ?></div><div class="or_paid_cs"><div class="or_cs_t"><?=t::w('Customer gave:', 'Клиент берді:')?></div><div class="or_cs_bl"><span class="or_cs_b" data-v="1000">1 000</span><span class="or_cs_b" data-v="2000">2 000</span><span class="or_cs_b" data-v="5000">5 000</span><span class="or_cs_b" data-v="10000">10 000</span><span class="or_cs_b" data-v="20000">20 000</span></div><input type="tel" class="or_mx_in or_cs_in" placeholder="<?=t::w('Other amount', 'Басқа сома')?>" inputmode="numeric"><div class="or_mx_rest or_cs_ch"><span><?=t::w('Change:', 'Сдача (қайтарым):')?></span> <b>0 тг</b></div><div class="or_mx_btns"><span class="or_mx_back"><?=t::w('Back', 'Артқа')?></span><span class="or_cs_ok"><?=t::w('Confirm', 'Растау')?></span></div></div><div class="or_paid_mx"><div class="or_mx_r"><span><i class="far fa-money-bill-wave"></i><?=t::w('Cash', 'Қолма-қол')?></span><input type="tel" class="or_mx_in" data-k="cash" placeholder="0" inputmode="numeric"></div><div class="or_mx_r"><span><i class="far fa-qrcode"></i>Kaspi QR</span><input type="tel" class="or_mx_in" data-k="qr" placeholder="0" inputmode="numeric"></div><div class="or_mx_r"><span><i class="far fa-exchange-alt"></i><?=t::w('Transfer', 'Аударым')?></span><input type="tel" class="or_mx_in" data-k="transfer" placeholder="0" inputmode="numeric"></div><div class="or_mx_rest"><span><?=t::w('Left', 'Қалды')?>:</span> <b><?=number_format((int)$buy_d['total'], 0, '', ' ')?> тг</b></div><div class="or_mx_btns"><span class="or_mx_back"><?=t::w('Back', 'Артқа')?></span><span class="or_mx_ok or_mx_dis"><?=t::w('Confirm', 'Растау')?></span></div></div></div></div><? endif ?><i class="far fa-history or_hist" data-id="<?=$buy_d['id']?>" title="<?=t::w('Status history', 'Статус тарихы')?>"></i></span>
											</div>
										</div>
									</div>
									<div class="uc_uil2_topc1 or_sel">
												<div class="or_type">
													<select name="" id="" class="on_type" data-order-id="<?=$buy_d['id']?>" <?=(!in_array((int)$order_sts['id'], [3, 4, 5, 6, 10]) ? '':'disabled')?>>
														<? $orders_type = db::query("select * from retail_orders_type order by number asc"); $t_ico = [1 => 'fa-car', 2 => 'fa-walking', 3 => 'fa-backpack', 4 => 'fa-utensils']; ?>
														<? while ($orders_type_d = mysqli_fetch_assoc($orders_type)): ?>
															<? $type_name = $orders_type_d['nms']; ?>
															<? if ($cmp[$type_name] == 1): ?>
																<option data-id="<?=$orders_type_d['id']?>" data-ico="<?=@$t_ico[(int)$orders_type_d['id']]?>" <?=($order_type['id'] == $orders_type_d['id']?'selected':'')?> value="" ><?=t::w('type:'.$orders_type_d['name'], $orders_type_d['name'])?></option>
															<? endif ?>
														<? endwhile ?>
													</select>
												</div>
												<div class="or_status" style="background-color:<?=$st_clr?>;"> <?//=$order_sts['name_kz']?>
													<select name="" id="" class="on_status" data-order-id="<?=$buy_d['id']?>" <?=($by_cour ? 'disabled title="'.htmlspecialchars(t::w('Status set by the courier', 'Статусты курьер қойды')).'"' : ((int)$buy_d['order_status'] == 4 ? 'disabled title="'.htmlspecialchars(t::w('Completed order: only admin can change', 'Аяқталған тапсырысты тек admin өзгертеді')).'"' : ''))?>>
														<? $orders_status = db::query("select * from retail_orders_status order by number asc"); ?>
														<? while ($orders_status_d = mysqli_fetch_assoc($orders_status)): ?><? if (trim($orders_status_d['name_ru']) == 'Вопрос' && @$order_sts['id'] != $orders_status_d['id'] && @$_GET['status'] != $orders_status_d['id']) continue; // «Вопрос» тізімде көрсетілмейді, базада қалады ?><? if (!in_array((string)$ot, explode(',', (string)@$orders_status_d['types'])) && @$order_sts['id'] != $orders_status_d['id']) continue; // тапсырыс түріне жатпайтын статустар көрсетілмейді ?>
															<? $o_nm = trim($orders_status_d['name_ru']); $o_clr = isset($st_colors[$o_nm]) ? $st_colors[$o_nm] : (isset($st_colors[trim($orders_status_d['name_kz'])]) ? $st_colors[trim($orders_status_d['name_kz'])] : $orders_status_d['clr']); ?>
															<option data-id="<?=$orders_status_d['id']?>" data-clr="<?=htmlspecialchars((string)$o_clr)?>" <?=(in_array((int)$orders_status_d['id'], [11, 3, 4]) ? 'data-cour="1"' : '')?> <?=((int)$orders_status_d['id'] == 6 || ((int)$orders_status_d['id'] == 3 && $ot == 1) ? 'data-lock="1"' : '')?> <?=($ot == 1 && (int)@$order_sts['id'] == 4 && in_array((int)$orders_status_d['id'], [5, 6, 10]) ? 'data-done="1"' : '')?> <?=(in_array((int)$orders_status_d['id'], [5, 10]) ? 'data-refuse="1"' : '')?> <?=((int)$orders_status_d['id'] == 4 && @$buy_d['payment_method'] == 'unpaid' ? 'data-unp="1"' : '')?> <?=((int)$orders_status_d['id'] == 10 ? 'data-kept="'.(int)((int)@$buy_d['pay_prepay'] > 0 ? $buy_d['pay_prepay'] : $buy_d['pay_qr']).'"' : '')?> <?=($order_sts['id'] == $orders_status_d['id']?'selected':'')?> value="" ><?=t::w('st:'.$orders_status_d['name_ru'], $orders_status_d['name_ru'])?></option>
														<? endwhile ?>
													</select>
												</div>
											</div>
									<div class="or_sum">
										<b class="fr_price"><?=$buy_d['total']?></b>
										<span><?=($ot == 4 ? (@$pm_names[$buy_d['payment_method']] ?: '') : ($ot == 1 || $ot == 2 ? t::w('Prepayment', 'Предоплата').' '.number_format((int)$buy_d['pay_qr'], 0, '', ' ') : htmlspecialchars((string)@$buy_d['service'])))?></span>
									</div>
									<? if ($ot == 1): // Жеткізу: курьер басты орында ?>
										<div class="or_courier">
											<div class="or_courier_l"><i class="far fa-motorcycle"></i><span><?=t::w('Courier', 'Курьер')?></span></div>
											<div class="uc_uil2_date1">
												<select name="" id="" class="on_staff" data-order-id="<?=$buy_d['id']?>" <?=(!in_array((int)$order_sts['id'], [3, 4, 5, 6, 10]) ? '':'disabled')?>>
													<option value="" data-id="kr" <?=($buy_d['сourier_id']==null?'selected':'')?>><?=t::w('Courier: none', 'Курьер: таңдалмаған')?></option>
													<? $staff = db::query("select * from company_staff where positions_id = 6 and company_id = '$company'"); ?>
													<? while ($staff_d = mysqli_fetch_assoc($staff)): ?>
														<? $staff_user_d = fun::user($staff_d['user_id']); ?>
														<option value="" data-id="<?=$staff_d['user_id']?>" data-name="<?=htmlspecialchars($staff_user_d['name'])?>" data-phone="<?=htmlspecialchars((string)$staff_user_d['phone'])?>" <?=($buy_d['сourier_id'] == $staff_d['user_id']?'selected':'')?>><?=$staff_user_d['name']?> || <span class="fr_phone"><?=$staff_user_d['phone']?></span></option>
													<? endwhile ?>
												</select>
											</div>
										</div>
									<? endif ?>
									<? if ($ot == 3 && @$buy_d['service']): ?>
									<div class="uc_uil2_raz">
										<div class="uc_uil2_mi">
											<div class="uc_uil2_mi1"><?=t::w('Service:', 'Сервис:')?></div>
											<div class="uc_uil2_mi2"><?=htmlspecialchars($buy_d['service'])?></div>
										</div>
									</div>
									<? endif ?>
									<div class="uc_uil2_raz or_items">
										<div class="uc_uil2_trt">
											<div class="uc_uil2_trt1"><?=t::w('Name', 'Атауы')?></div>
											<div class="uc_uil2_trt2"><?=t::w('Qty', 'Саны')?></div>
											<div class="uc_uil2_trt3"><?=t::w('Price', 'Бағасы')?></div>
										</div>
										<div class="uc_uil2_trc">

											<? 	
												$cashbox_id = $buy_d['id'];
												$cashboxp = db::query("select * from retail_orders_products where order_id = '$cashbox_id' order by ins_dt asc");
												$number = 0; $total = 0; $it_cnt = mysqli_num_rows($cashboxp); $it_fold = $it_cnt > 3 && $ot != 4; // 3-еуге дейін бәрі көрінеді
											?>
											<? if (mysqli_num_rows($cashboxp) != 0): ?>
												<? while ($sel_d = mysqli_fetch_assoc($cashboxp)): ?>
													<? 
														$number++; 
														$sum = $sel_d['quantity'] * $sel_d['price']; 
														$total = $total + $sum;
														$product_d = product::product($sel_d['product_id']);
													?>
													<div class="uc_uil2_trt<?=($it_fold && $number > 2 ? ' or_more_i' : '')?>">
														<div class="uc_uil2_trt1"><?=$number?>. <?=$product_d['name_ru']?></div>
														<div class="uc_uil2_trt2"><?=$sel_d['quantity']?> шт</div>
														<!-- <div class=""><?=$sel_d['price']?></div> -->
														<div class="uc_uil2_trt3 fr_price"><?=$sum?></div>
													</div>
												<? endwhile ?>
												<? if ($it_fold): // 4 не одан көп болса (Кафеде емес): 2-еуі көрінеді, қалғаны батырмамен ?>
												<div class="or_more" data-more="+<?=$number - 2?> <?=t::w('more', 'тағы')?>" data-less="<?=t::w('Hide', 'Жасыру')?>"><span>+<?=$number - 2?> <?=t::w('more', 'тағы')?></span><i class="far fa-chevron-down"></i></div>
												<? endif ?>
											<? endif ?>
											
											<? if ($ot == 1): ?>
											<div class="uc_uil2_trt">
												<div class="uc_uil2_trt1"><?=t::w('Delivery fee', 'Доставка')?></div>
												<div class="uc_uil2_trt3 fr_price"><?=$buy_d['pay_delivery']?></div>
											</div>
											<? endif ?>
										</div>
										<div class="or_paybox">
										<div class="uc_uil2_trb">
											<div class="uc_uil2_trt1"><?=t::w('Total', 'Жалпы')?></div>
											<div class="uc_uil2_trt2"></div>
											<div class="uc_uil2_trt3 fr_price"><?=$buy_d['total']?></div>
										</div>
										<? if ($ot == 4 && @$buy_d['payment_method']): ?>
										<div class="uc_uil2_trc">
											<div class="uc_uil2_trt">
												<div class="uc_uil2_trt1"><?=t::w('Payment', 'Төлем')?></div>
												<div class="uc_uil2_trt2"></div>
												<div class="uc_uil2_trt3"><?=@$pm_names[$buy_d['payment_method']] ?: htmlspecialchars($buy_d['payment_method'])?></div>
											</div>
										</div>
										<? endif ?>
										<? if ($ot == 1 || $ot == 2): ?>
										<div class="uc_uil2_trc">
											<div class="uc_uil2_trt">
												<div class="uc_uil2_trt1"><?=t::w('Prepayment', 'Предоплата')?></div>
												<div class="uc_uil2_trt2">-</div>
												<div class="uc_uil2_trt3 fr_price"><?=$buy_d['pay_qr']?></div>
											</div>
											<div class="uc_uil2_trt">
												<div class="uc_uil2_trt1"><?=($ot == 1 ? t::w('To courier (cash)', 'Курьерге (нал)') : t::w('On pickup', 'Алғанда төлейді'))?></div>
												<div class="uc_uil2_trt2"></div>
												<div class="uc_uil2_trt3 fr_price"><?=$buy_d['pay_cash']?></div>
											</div>
										</div>
										<? endif ?>
										</div>
										<? $rf_r = trim((string)@$buy_d['refuse_reason']); $rf_k = (int)@$buy_d['kept_sum'];
										if (in_array((int)$buy_d['order_status'], [5, 6, 10]) && ($rf_r !== '' || $rf_k > 0)): // Отказ: себебі және бізде қалған сома ?>
										<div class="or_refuse">
											<span class="or_rf_ic"><i class="far fa-ban"></i></span>
											<div class="or_rf_tx">
												<small><?=t::w('Refusal reason', 'Отказ себебі')?></small>
												<b><?=($rf_r !== '' ? htmlspecialchars($rf_r) : '—')?></b>
												<? if ($rf_k > 0): ?><span class="or_rf_k"><i class="far fa-wallet"></i><?=t::w('Kept by us', 'Бізде қалды')?>: <b><?=number_format($rf_k, 0, '', ' ')?> тг</b></span><? endif ?>
											</div>
										</div>
										<? endif ?>
									</div>
									<? $show_addr = $ot == 1 && trim((string)$buy_d['address']) !== ''; $show_ph = ($ot == 1 || $ot == 2) && trim((string)$buy_d['phone']) !== ''; ?>
									<? if ($show_addr || $show_ph): // Кафе мен Сервисте, бос болса — шықпайды ?>
									<div class="uc_uil2_raz or_addr">
										<div class="oa_box">
											<? if ($show_addr): $fa = trim((string)@$buy_d['floor_apt']); ?>
											<div class="oa_ad"><i class="far fa-map-marker-alt oa_ic"></i><div class="oa_txt"><b><?=$buy_d['address']?></b><? if ($fa !== ''): ?><small><?=htmlspecialchars(str_replace(', ', ' · ', $fa))?></small><? endif ?></div></div>
											<? endif ?>
											<? if ($show_ph): ?>
											<div class="oa_ph"><i class="far fa-phone-alt oa_ic"></i><b class="fr_phone"><?=$buy_d['phone']?></b></div>
											<? endif ?>
										</div>
									</div>
									<? endif ?>
									<div class="uc_uil2_raz or_btns">
										<div class="uc_uil2_mib">
											<? if (in_array((int)$buy_d['order_status'], [4, 5, 6, 10])): // Аяқталды / Отказ: өзгертуге болмайды ?><span class="btn btn_cl on_ubd btn_lock" title="<?=t::w('Closed order', 'Жабық тапсырыс')?>"><?=t::w('Edit', 'Өзгерту')?></span><? else: ?><a class="btn btn_cl on_ubd" data-id="<?=$buy_d['id']?>" href="/cashbox/?id=<?=$buy_d['id']?>&type=ubd"><?=t::w('Edit', 'Өзгерту')?></a><? endif ?>
											<div class="btn  on_print" data-id="<?=$buy_d['id']?>"><?=t::w('Print', 'Печать')?></div>
										</div>
									</div>
								</div>
							</div>

