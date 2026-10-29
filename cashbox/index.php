<? include "../config/core.php";
	require_once "../config/loyalty.php";

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
		if (@$_GET['type'] == 'ubd' && in_array((int)$cashbox_d['order_status'], [4, 5, 6, 10])) { header('location: /orders/'); exit(); } // жабық тапсырыс өзгертілмейді
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
	$css = ['cashbox', 'usr_info'];
	$js = ['cashbox'];
	$lc_on = loyalty::on() && loyalty::any($company); // кэшбэк не сыйлық қосулы болса ғана
	if ($lc_on) { $css[] = 'loyalty'; $js[] = 'loyalty'; }
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
	<!-- Жеткізу картасы: экран ортасында ашылады -->
	<div class="dlv_map_pop">
		<div class="dlv_map_bg"></div>
		<div class="dlv_map_box">
			<div class="dlv_map_head">
				<h4><?=t::w('Delivery map', 'Жеткізу картасы')?></h4>
				<div class="btn btn_dd dlv_map_close"><i class="fal fa-times"></i></div>
			</div>
			<? // Көшелер / аудандар: company_delivery_zone кестесінен (кесте жоқ болса бос)
				$dz_list = [];
				if (mysqli_num_rows(db::query("SHOW TABLES LIKE 'company_delivery_zone'"))) {
					$dz_br = mysqli_num_rows(db::query("SHOW COLUMNS FROM company_delivery_zone LIKE 'branch_id'")) > 0; // точкаға байланған ба
					$dz = db::query("select id, name, price".($dz_br ? ", branch_id" : "")." from company_delivery_zone where company_id = '".(int)$company."' order by sort asc, name asc");
					while ($dz_d = mysqli_fetch_assoc($dz)) $dz_list[] = $dz_d;
				}
			?>
			<div class="dlv_map_body">
				<div class="dz_search"><i class="fal fa-search"></i><input type="text" class="dz_q" placeholder="<?=t::w('Search street', 'Көшені іздеу')?>"></div>
				<div class="dz_grid">
					<? foreach ($dz_list as $dz_d): ?>
						<div class="dz_i" data-price="<?=(int)$dz_d['price']?>" data-branch="<?=(int)@$dz_d['branch_id']?>" data-name="<?=htmlspecialchars($dz_d['name'])?>"><span><?=htmlspecialchars($dz_d['name'])?></span><b><?=number_format((int)$dz_d['price'], 0, '', ' ')?> тг</b></div>
					<? endforeach ?>
					<div class="dz_empty <?=($dz_list ? 'dsp_n' : '')?>"><i class="fal fa-map-marked-alt"></i><span class="dz_empty_0"><?=t::w('No streets yet', 'Бұл точкаға көше әлі қосылмаған')?></span><span class="dz_empty_q dsp_n" data-tpl="<?=htmlspecialchars(t::w('Street not found', '«%s» бойынша көше табылмады'))?>"></span></div>
				</div>
			</div>
		</div>
	</div>

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
						$is_edit = !empty($cashbox_d['paid']); // «Өзгерту»: тапсырыстың бұрынғы мәндері таңдалып тұрады
						if ($is_edit && @$cashbox_d['branch_id']) $sel_branch = $cashbox_d['branch_id'];
						if (count($branch_list) == 1) $sel_branch = $branch_list[0]['id'];
						// company.show_branch = 0 болса точка таңдауы жасырылады, кассирдің өз точкасы қойылады (баған жоқ не бос болса — көрінеді)
						$branch_hide = count($branch_list) < 2 || (isset($cmp['show_branch']) && $cmp['show_branch'] !== null && (int)$cmp['show_branch'] === 0);
						if ($branch_hide && !$sel_branch && $branch_list) $sel_branch = $branch_list[0]['id'];
					?>
					<div class="form_im form_imss sel_branch <?=($branch_hide ? 'dsp_n' : '')?>" data-val="<?=$sel_branch?>">
						<div class="form_span"><?=t::w('Branch:', 'Точка:')?></div>
						<? foreach ($branch_list as $branch_d): ?>
							<div class="form_im_slo " >
								<div class="form_im_slo_i <?=($sel_branch == $branch_d['id']?'form_im_slo_act':'')?>"  data-type="<?=$branch_d['id']?>" data-pay="<?=htmlspecialchars((string)@$branch_d['pay_types'])?>" data-pay-def="<?=htmlspecialchars((string)@$branch_d['pay_default'])?>"><?=$branch_d['name']?></div>
							</div>
						<? endforeach ?>
					</div>

					<? // Заказ түрі: компанияда қосулы түрлер, стандарты company.type_def (1 Жеткізу, 2 Собой, 4 Кафе, 3 Сервис)
						$type_on = [];
						foreach ([1 => 'delivery', 2 => 'yourself', 4 => 'coffee', 3 => 'service'] as $t_id => $t_col) if (@$cmp[$t_col]) $type_on[] = $t_id;
						$type_def = (int)@$cmp['type_def'];
						if (!in_array($type_def, $type_on)) $type_def = $type_on ? $type_on[0] : 1;
						$type_first = $type_def; // компанияның стандарты бірінші тұрады
						if (!empty($cashbox_d['paid']) && in_array((int)@$cashbox_d['order_type'], $type_on)) $type_def = (int)$cashbox_d['order_type'];
					?>
					<div class="form_im form_imss sel_type" data-val="<?=$type_def?>">
						<div class="form_span"><?=t::w('Order type:', 'Заказ түрі:')?></div>
						<? // Бір блок ішінде ауысатын батырмалар: --n саны, --i таңдалғанның орны
							$tp_on = []; foreach ([1 => 'delivery', 2 => 'yourself', 4 => 'coffee', 3 => 'service'] as $tk => $tn) if ($cmp[$tn]) $tp_on[] = $tk;
							$tp_i = array_search((int)$type_def, $tp_on); if ($tp_i === false) $tp_i = 0;
						?>
						<div class="tp_seg" style="--n:<?=max(1, count($tp_on))?>; --i:<?=$tp_i?>;"><span class="tp_seg_sl"></span>
						<? if ($cmp['delivery']): ?>
							<div class="form_im_slo<?=($type_first == 1 ? ' sel_type_def' : '')?>">
								<div class="form_im_slo_i <?=($type_def == 1 ? 'form_im_slo_act' : '')?>"  data-type="1"><i class="far fa-car tp_ico"></i><span><?=t::w('Delivery', 'Жеткізу')?></span></div>
							</div>
						<? endif ?>
						<? if ($cmp['yourself']): ?>
							<div class="form_im_slo<?=($type_first == 2 ? ' sel_type_def' : '')?>">
								<div class="form_im_slo_i <?=($type_def == 2 ? 'form_im_slo_act' : '')?>"  data-type="2"><i class="far fa-walking tp_ico"></i><span><?=t::w('Takeaway', 'Өзімен')?></span></div>
							</div>
						<? endif ?>
						<? if ($cmp['coffee']): ?>
							<div class="form_im_slo<?=($type_first == 4 ? ' sel_type_def' : '')?>">
								<div class="form_im_slo_i <?=($type_def == 4 ? 'form_im_slo_act' : '')?>"  data-type="4"><i class="far fa-utensils tp_ico"></i><span><?=t::w('Cafe', 'Кафе')?></span></div>
							</div>
						<? endif ?>
						<? if ($cmp['service']): ?>
							<div class="form_im_slo<?=($type_first == 3 ? ' sel_type_def' : '')?>">
								<div class="form_im_slo_i <?=($type_def == 3 ? 'form_im_slo_act' : '')?>" data-type="3" btype_ynd><i class="far fa-backpack tp_ico"></i><span><?=t::w('Service', 'Сервис')?></span></div>
							</div>
						<? endif ?>
						</div>
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
							<? // Сақталған адрес: «..., подъезд 10» бөлек өріске бөлінеді (қайта сақтағанда қайталанбайды)
								$ad_full = trim((string)@$cashbox_d['address']); $ad_entr = '';
								if (!empty($cmp['kv']) && preg_match('/^(.*?)[\s,]*(?:подъезд\s*)+(.+)$/u', $ad_full, $am)) { $ad_full = $am[1]; $ad_entr = trim($am[2]); }
								$ad_full = rtrim($ad_full, " ,");
							?>
							<input type="text" class="form_txt btype_address" placeholder="" data-val="<?=htmlspecialchars($ad_full)?>" value="<?=htmlspecialchars($ad_full)?>">
							<i class="fal fa-text form_icon"></i>
						</div>
						<? if (!empty($cmp['kv'])): // company.kv = 1 болса ғана ?>
						<div class="form_im ">
							<div class="form_span"><?=t::w('Block / entrance:', 'Блок / подъезд:')?></div>
							<input type="text" class="form_txt btype_entr" placeholder="" data-val="" value="<?=htmlspecialchars(@$ad_entr)?>">
							<i class="fal fa-building form_icon"></i>
						</div>
						<div class="form_2col">
							<div class="form_im ">
								<div class="form_span"><?=t::w('Floor:', 'Этаж:')?></div>
								<input type="text" class="form_txt btype_floor" placeholder="" value="<?=(preg_match('/(\S+) этаж/u', (string)@$cashbox_d['floor_apt'], $m) ? htmlspecialchars($m[1]) : '')?>">
								<i class="fal fa-layer-group form_icon"></i>
							</div>
							<div class="form_im ">
								<div class="form_span"><?=t::w('Apartment:', 'Квартира:')?></div>
								<input type="text" class="form_txt btype_apt" placeholder="" value="<?=(preg_match('/(\S+) кв/u', (string)@$cashbox_d['floor_apt'], $m) ? htmlspecialchars($m[1]) : '')?>">
								<i class="fal fa-door-closed form_icon"></i>
							</div>
						</div>
						<? endif ?>
					</div>

					<br>

					<div class="opt_btns">
						<div class="opt_btn" data-target=".tf_add"><i class="fal fa-plus"></i> <?=t::w('Additional info', 'Қосымша ақпарат')?></div>
						<div class="opt_btn" data-target=".tf_preorder"><i class="fal fa-plus"></i> <?=t::w('Preorder', 'Предзаказ')?></div>
					</div>

					<div class="tf_add opt_off">
						<div class="form_span add_head"><i class="far fa-comment-alt"></i><span><?=t::w('Additional info', 'Қосымша ақпарат')?><small><?=t::w('Notes if needed', 'Қосымша қажет болса жазыңыз')?></small></span><em class="add_close" title="<?=t::w('Close', 'Жабу')?>"><i class="fal fa-minus"></i></em></div>
						<div class="form_im ">
							<input type="text" class="form_txt btype_add" placeholder="<?=t::w('Notes example', 'Мысалы: Замена пицца или суши')?>" data-val="0">
							<i class="fal fa-text form_icon"></i>
						</div>
					</div>

					<br>

					<div class="tf_preorder opt_off">
						<div class="form_im form_imss sel_pre_day">
							<div class="form_span pre_head"><i class="far fa-clock"></i><span><?=t::w('Preorder', 'Предзаказ')?><small><?=t::w('Pre-order for a set time', 'Алдын-ала тапсырыс, уақытын таңдаңыз')?></small></span><em class="pre_close" title="<?=t::w('Close', 'Жабу')?>"><i class="fal fa-minus"></i></em></div>
							<div class="form_im_slo "><div class="form_im_slo_i form_im_slo_act" data-type="<?=date('Y-m-d')?>"><b><?=t::w('Today', 'Бүгін')?></b><small><?=date('d.m')?></small></div></div>
							<div class="form_im_slo "><div class="form_im_slo_i" data-type="<?=date('Y-m-d', strtotime('+1 day'))?>"><b><?=t::w('Tomorrow', 'Ертең')?></b><small><?=date('d.m', strtotime('+1 day'))?></small></div></div>
							<div class="form_im_slo "><div class="form_im_slo_i sel_pre_other" data-type="other"><b><i class="fal fa-calendar-alt"></i>&nbsp; <?=t::w('Another day', 'Басқа күн')?></b><small><?=t::w('up to 7 days', '7 күнге дейін')?></small></div></div>
						</div>
						<div class="pre_dt_row">
							<div class="form_im pre_date_im dsp_n">
								<? // Басқа күн: браузер күнтізбесі емес, келесі 6 күн батырмамен (ең көбі 1 апта алдыға) ?>
								<input type="hidden" class="pre_date" min="<?=date('Y-m-d')?>" max="<?=date('Y-m-d', strtotime('+7 day'))?>" value="<?=date('Y-m-d', strtotime('+2 day'))?>">
								<div class="pre_days">
									<? $wd = [1 => 'Дс', 2 => 'Сс', 3 => 'Ср', 4 => 'Бс', 5 => 'Жм', 6 => 'Сб', 7 => 'Жс']; ?>
									<? for ($pd_i = 2; $pd_i <= 7; $pd_i++): $pd_t = strtotime('+'.$pd_i.' day'); ?>
										<div class="pre_day_i <?=($pd_i == 2 ? 'pre_day_act' : '')?>" data-date="<?=date('Y-m-d', $pd_t)?>"><small><?=$wd[(int)date('N', $pd_t)]?></small><b><?=date('d', $pd_t)?></b><small><?=date('m', $pd_t)?></small></div>
									<? endfor ?>
								</div>
							</div>
							<div class="pre_time_im">
								<? // Уақыт: тәулік бөлігі (Таңғы / Түскі / Кешкі) + 30 минут сайынғы слоттар ?>
								<input type="hidden" class="pre_time" value="">
								<div class="pre_tm_head"><span><?=t::w('Time', 'Уақыты')?></span><b class="pre_tm_val">--:--</b></div>
								<? $pre_parts = [[8, 12, t::w('Morning', 'Таңғы')], [12, 17, t::w('Afternoon', 'Түскі')], [17, 24, t::w('Evening', 'Кешкі')]]; ?>
								<div class="pre_parts" data-p="">
									<? foreach ($pre_parts as $pp_i => $pp): ?><div class="pre_pt" data-p="<?=$pp_i?>"><b><?=$pp[2]?></b><small><?=sprintf('%02d', $pp[0])?>–<?=sprintf('%02d', $pp[1])?></small></div><? endforeach ?>
								</div>
								<div class="pre_slots">
									<? foreach ($pre_parts as $pp_i => $pp): for ($m = $pp[0] * 60; $m < $pp[1] * 60; $m += 30): $pt = sprintf('%02d:%02d', intdiv($m, 60), $m % 60); ?><div class="pre_s" data-p="<?=$pp_i?>" data-t="<?=$pt?>"><?=$pt?></div><? endfor; endforeach ?>
								</div>
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
						<div class="form_im_slo ">
							<div class="form_im_slo_i dlv_map" title="<?=t::w('Map', 'Карта')?>"><i class="far fa-map-marked-alt"></i></div>
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
							<div class="form_im_slo_i form_im_slo_red btype_red " data-type="<?=$red_pct?>"><i class="far fa-percent pay_ic"></i>RED <?=$red_pct?>%</div>
						</div>
					</div>
					<? endif ?>
					<? if ($lc_on): ?><div class="lc_box dsp_n" data-oid="<?=$cashbox_id?>"></div><? endif ?>

					<br><br>
					
					<div class="pay_box">
					<div class="pay_box_h"><?=t::w('Payment', 'Төлем')?></div>
					<? // Предоплата: дайын сомалар, «Толық» — жалпы сома, «+» — басқа сома
						$qr_list = [0, 1000, 2000, 3000, 5000, 10000];
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
						<? $qrm_cur = (!empty($cashbox_d['paid']) && in_array((string)@$cashbox_d['prepay_method'], ['transfer','qr','cash','red','mix'])) ? $cashbox_d['prepay_method'] : 'qr'; ?>
						<div class="form_im form_imss sel_qrm <?=($qr_cur > 0 ? '' : 'dsp_n')?>" data-val="<?=$qrm_cur?>">
							<div class="form_span"><?=t::w('Prepaid with:', 'Предоплата немен төленді:')?></div>
							<div class="form_im_slo "><div class="form_im_slo_i <?=($qrm_cur == 'transfer' ? 'form_im_slo_act' : '')?>" data-type="transfer"><i class="far fa-exchange-alt pay_ic"></i><?=t::w('Transfer', 'Аударым')?></div></div>
							<div class="form_im_slo "><div class="form_im_slo_i <?=($qrm_cur == 'qr' ? 'form_im_slo_act' : '')?>" data-type="qr"><i class="far fa-qrcode pay_ic"></i>Kaspi QR</div></div>
							<div class="form_im_slo "><div class="form_im_slo_i <?=($qrm_cur == 'cash' ? 'form_im_slo_act' : '')?>" data-type="cash"><i class="far fa-money-bill-wave pay_ic"></i><?=t::w('Cash', 'Қолма-қол')?></div></div>
							<? if (@$red_pct > 0): // company.red = 0 болса RED шықпайды ?>
							<div class="form_im_slo "><div class="form_im_slo_i <?=($qrm_cur == 'red' ? 'form_im_slo_act' : '')?>" data-type="red"><i class="far fa-percent pay_ic"></i>RED <?=$red_pct?>%</div></div>
							<? endif ?>
							<div class="form_im_slo "><div class="form_im_slo_i sel_pay_mix2" data-type="mix"><i class="far fa-random pay_ic"></i><?=t::w('Mixed', 'Аралас')?></div></div>
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
					<? $pay_ic = ['transfer' => 'fa-exchange-alt', 'qr' => 'fa-qrcode', 'cash' => 'fa-money-bill-wave']; // төлем түрлерінің иконкалары ?>
					<div class="tf_paytype dsp_n">
						<? $pay_edit = !empty($cashbox_d['paid']) ? (string)@$cashbox_d['payment_method'] : ''; if ($pay_edit == 'mixed') $pay_edit = 'mix'; ?>
						<div class="form_im form_imss sel_pay" data-val="" data-edit="<?=htmlspecialchars($pay_edit)?>">
							<div class="form_span"><?=t::w('Payment type:', 'Төлем түрі:')?></div>
							<? foreach ($pay_list as $pay_key => $pay): ?>
								<div class="form_im_slo ">
									<div class="form_im_slo_i" data-type="<?=$pay_key?>"><? if (!empty($pay_ic[$pay_key])): ?><i class="far <?=$pay_ic[$pay_key]?> pay_ic"></i><? endif ?><?=$pay?></div>
								</div>
							<? endforeach ?>
							<div class="form_im_slo ">
								<div class="form_im_slo_i sel_pay_mix" data-type="mix"><i class="far fa-random pay_ic"></i><?=t::w('Mixed', 'Аралас')?></div>
							</div>
							<div class="form_im_slo ">
								<div class="form_im_slo_i" data-type="unpaid"><i class="far fa-clock pay_ic"></i><?=t::w('Unpaid', 'Төленбеген')?></div>
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

