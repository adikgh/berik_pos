<? if ($site_set['menu']): ?>
	<div class="aheader">
		<div class="bl_c">
			<div class="aheader_c">
				<div class="alogo" href="/">
					<a href="/cashbox/" class="bp_brand" title="BERIK POS" style="display:flex;align-items:center;gap:8px;margin-right:12px;text-decoration:none;color:inherit;white-space:nowrap"><img src="/assets/img/logo/berik-icon.svg" alt="BERIK POS" width="34" height="34" style="display:block;width:34px;height:34px"><span style="font-family:'Nunito',sans-serif;font-style:italic;font-weight:900;font-size:19px;letter-spacing:.3px;line-height:1">BERIK <b style="color:#0EA768;font-weight:800">POS</b></span></a>
					<!-- <div><?=$site['name']?></div> -->
					<!-- <p title="Касса"><i class="fal fa-cash-register"></i> 1</p> -->
					<!-- <p>Кассир: <?=$user['name']?> <?=$user['surname']?></p> -->
					<!-- <p title="Время"><i class="fal fa-clock"></i> <span class="toDate">00:00</span></p> -->
				</div>
				<div class="ahead">
					<div class="mp_top">
						<div class="mp_topc">
							<? // Бірнеше касса қатары қосулы болса «Жаңадан қосу» ашық кассалар тізімін ашады (config/carts.php)
								require_once __DIR__.'/../config/carts.php';
								$mc_mx = carts::max($company);
								if ($mc_mx && empty($mc_list)) $mc_list = carts::open($user_id, $company);
							?>
							<a class="mp_topi <?=($menu_name=='cashbox'?'mp_topi_act':'')?> <?=($mc_mx?'mc_dd_btn':'')?>" href="/cashbox/"><i class="fal fa-plus-circle"></i><span><?=t::w('New order', 'Жаңадан қосу')?></span><? if ($mc_mx): ?><i class="fal fa-angle-down mc_car"></i><? endif ?></a>
							<a class="mp_topi <?=($menu_name=='orders'?'mp_topi_act':'')?>" href="/orders/?branch=<?=$branch?>"><i class="fal fa-receipt"></i><span><?=t::w('Orders', 'Тапсырыстар')?></span><b class="onn_cnt"></b></a>
							<!-- <a class="mp_topi <?=($menu_name=='orders'?'mp_topi_act':'')?>" href="/orders/all.php">Тапсырыстар</a> -->
							<!-- <a class="mp_topi <?=($menu_name=='main'?'mp_topi_act':'')?>" href="/kassa/">Касса</a> -->
							<!-- <a class="mp_topi <?=($menu_name=='products'?'mp_topi_act':'')?>" href="/products/">Тауар</a> -->
							<!-- <a class="mp_topi <?=($menu_name=='dashboard'?'mp_topi_act':'')?>" href="/dashboard/">Статистика</a> -->
							<!-- <a class="mp_topi <?=($menu_name=='main'?'mp_topi_act':'')?>" href="/kassa/">Касса</a> -->
							<!-- <a class="mp_topi <?=($menu_name=='return'?'mp_topi_act':'')?>" href="/return/">Возврат</a> -->
							<!-- <a class="mp_topi <?=($menu_name=='main'?'mp_topi_act':'')?>" href="/change/">Смена</a> -->
						</div>
						<? if ($mc_mx): ?>
							<div class="mc_dd">
								<div class="mc_dd_h"><?=t::w('Open kassas', 'Ашық кассалар')?></div>
								<div class="mc_dd_l">
									<? foreach ($mc_list as $mc_no => $mc_d): ?>
										<a class="mc_dd_i <?=(($menu_name=='cashbox' && $mc_no == @$_SESSION['cart_no']) ? 'mc_dd_cur' : '')?>" href="/cashbox/?cart=<?=$mc_no?>">
											<b><?=t::w('Kassa', 'Касса')?>-<?=$mc_no?></b>
											<span><?=((int)$mc_d['cnt'] ? (int)$mc_d['cnt'].' '.t::w('Kassa items', 'тағам') : t::w('empty', 'бос'))?></span>
											<em><?=((int)$mc_d['cnt'] ? number_format((int)$mc_d['total'], 0, '', ' ').' тг' : '')?></em>
										</a>
									<? endforeach ?>
								</div>
								<? if (count($mc_list) < $mc_mx): ?>
									<a class="mc_dd_i mc_dd_new" href="/cashbox/?cart=new" target="_blank"><i class="fal fa-plus"></i> <?=t::w('Open new kassa', 'Жаңа касса ашу')?> (<?=count($mc_list)?>/<?=$mc_mx?>)</a>
								<? else: ?>
									<div class="mc_dd_i mc_dd_full"><?=t::w('All kassas are open', 'Барлық касса ашық')?> (<?=$mc_mx?>/<?=$mc_mx?>)</div>
								<? endif ?>
							</div>
						<? endif ?>
					</div>
					<div class="ub1_lx">
						<div class="ub1_lt" href="/user/">
							<div class="ub1_ltf">
								<div class=""><?=@$user['name']?> <?=(@$user['surname']?substr(@$user['surname'],0,1).'.':'')?></div>
								<span><?//=fun::user_staff_name($user_right['staff_id'])?></span>
							</div>
							<div class="ub1_lti lazy_img" data-src="/assets/uploads/users/<?=@$user['img']?>"><? if (!@$user['img']): ?><i class="fal fa-user"></i><? endif ?></div>
						</div>
						<div class="menu_c acc_menu">
							<div class="acc_hd">
								<div class="acc_hd_av lazy_img" data-src="/assets/uploads/users/<?=@$user['img']?>"><? if (!@$user['img']): ?><i class="fal fa-user"></i><? endif ?></div>
								<div class="acc_hd_t">
									<div><?=@$user['name']?> <?=@$user['surname']?></div>
									<? if (@$user['phone']): ?><span><?=@$user['phone']?></span><? endif ?>
								</div>
							</div>
							<div class="acc_lang">
								<div class="acc_lang_h"><i class="fal fa-globe"></i> <?=t::w('Language', 'Тіл')?></div>
								<div class="acc_lang_c">
									<? foreach ($langs_short as $lk => $lv): ?>
										<a class="acc_lang_i <?=($ui_lang==$lk?'acc_lang_act':'')?>" href="?<?=http_build_query(array_merge($_GET, ['lang' => $lk]))?>" title="<?=$langs[$lk]?>"><?=$lv?></a>
									<? endforeach ?>
								</div>
							</div>
							<div class="acc_list">
								<label class="menu_ci kph_tg">
									<div class="menu_cin"><i class="fal fa-image"></i></div>
									<div class="menu_cih"><?=t::w('Show photos', 'Фотоларды көрсету')?></div>
									<input type="checkbox" class="kph_cb"><span class="kph_sw"></span>
								</label>
								<a class="menu_ci" href="/shift/">
									<div class="menu_cin"><i class="fal fa-cash-register"></i></div>
									<div class="menu_cih"><?=t::w('Close shift', 'Сменді жабу')?></div>
								</a>
								<a class="menu_ci acc_open" href="javascript:;">
									<div class="menu_cin"><i class="fal fa-user"></i></div>
									<div class="menu_cih"><?=t::w('Account', 'Аккаунт')?></div>
								</a>
								<a class="menu_ci acc_exit" href="/exit.php">
									<div class="menu_cin"><i class="fal fa-sign-out"></i></div>
									<div class="menu_cih"><?=t::w('Log out', 'Шығу')?></div>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<style>
		/* «Фотоларды көрсету»: тағам батырмаларында фото (әдепкі өшік, әр құрылғыда өзі сақталады) */
		.kph_img{ display: none; }
		html.kph_on .kph_img{ display: block; width: 100%; aspect-ratio: 4 / 3; object-fit: cover; border-radius: 8px; margin: 0 0 8px; background: #eef2f6; }
		html.kph_on body.posm .hup_rcbi > div:first-child:has(.kph_img){ display: block; }
		.acc_menu .kph_tg{ cursor: pointer; position: relative; display: flex; align-items: center; }
		.acc_menu .kph_tg .menu_cih{ width: auto; flex: 1; }
		.kph_tg input{ position: absolute; opacity: 0; pointer-events: none; }
		.kph_sw{ width: 42px; height: 24px; border-radius: 24px; background: #d5dbe3; position: relative; transition: .2s; flex-shrink: 0; }
		.kph_sw:after{ content: ''; position: absolute; top: 3px; left: 3px; width: 18px; height: 18px; border-radius: 50%; background: #fff; transition: .2s; box-shadow: 0 1px 2px rgba(0,0,0,.2); }
		.kph_tg input:checked + .kph_sw{ background: #0EA768; }
		.kph_tg input:checked + .kph_sw:after{ left: 21px; }
	</style>
	<script>
		(function(){
			var on = false; try { on = localStorage.getItem('kph') === '1' } catch (e) {}
			document.documentElement.classList.toggle('kph_on', on)
			var cb = document.querySelector('.kph_cb'); if (!cb) return
			cb.checked = on
			cb.addEventListener('change', function(){
				try { localStorage.setItem('kph', cb.checked ? '1' : '0') } catch (e) {}
				document.documentElement.classList.toggle('kph_on', cb.checked)
			})
		})()
	</script>
	<? if ($mc_mx): ?>
	<style>
		/* «Жаңадан қосу»: ашық кассалар тізімі */
		.mp_top{ position: relative; }
		.mc_car{ font-size: 13px !important; margin-left: 2px; transition: .2s; }
		.mc_dd_open .mc_car{ transform: rotate(180deg); }
		.mc_dd{ display: none; position: absolute; top: calc(100% + 8px); left: 0; z-index: 9000; width: 330px; max-width: calc(100vw - 24px); background: #fff; border-radius: 14px; box-shadow: 0 12px 32px rgba(0,0,0,.16); padding: 8px; }
		.mc_dd_on .mc_dd{ display: block; }
		.mc_dd_h{ margin: 6px 10px 8px; font-size: 12px; color: var(--bp-muted, #8a8f98); text-transform: uppercase; letter-spacing: .5px; font-weight: 700; }
		.mc_dd_l{ max-height: 360px; overflow-y: auto; display: flex; flex-direction: column; gap: 2px; }
		.mc_dd_i{ display: flex; align-items: center; gap: 10px; min-height: 48px; padding: 0 12px; border-radius: 10px; color: var(--bp-text, #222); text-decoration: none; font-size: 15px; }
		.mc_dd_i:hover{ background: var(--bp-pill, #f5f5f5); }
		.mc_dd_cur, .mc_dd_cur:hover{ background: #e3f5ec; }
		.mc_dd_i b{ min-width: 76px; font-weight: 800; }
		.mc_dd_i span{ flex: 1; color: var(--bp-muted, #8a8f98); font-size: 14px; }
		.mc_dd_i em{ font-style: normal; font-weight: 700; font-variant-numeric: tabular-nums; white-space: nowrap; }
		.mc_dd_new, .mc_dd_full{ margin-top: 6px; border-top: 1px solid var(--bp-line, #e7e7e7); border-radius: 0 0 10px 10px; font-weight: 800; }
		.mc_dd_new{ color: var(--bp-green, #10a96a); }
		.mc_dd_full{ color: var(--bp-muted, #8a8f98); }
	</style>
	<script>
		// «Жаңадан қосу» басылғанда ашық кассалар тізімі ашылады, сыртын басса жабылады
		document.addEventListener('click', function(e){
			var top = document.querySelector('.mp_top'); if (!top) return
			var btn = e.target.closest('.mc_dd_btn')
			if (btn) { e.preventDefault(); var on = !top.classList.contains('mc_dd_on'); top.classList.toggle('mc_dd_on', on); btn.classList.toggle('mc_dd_open', on); return }
			if (!e.target.closest('.mc_dd')) { top.classList.remove('mc_dd_on'); var b = document.querySelector('.mc_dd_btn'); if (b) b.classList.remove('mc_dd_open') }
		})
	</script>
	<? endif ?>
	<script>
		// Аккаунт мәзірі: ішін басқанда жабылмайды, сыртын басқанда жабылады
		document.addEventListener('click', function(e){
			var m = document.querySelector('.acc_menu'); if (!m) return
			if (m.contains(e.target) && !e.target.closest('a')) { e.stopPropagation(); return }
			if (!e.target.closest('.ub1_lx')) { var p = document.querySelector('.ub1_lx'); if (p && p.parentNode) p.parentNode.classList.remove('menu_act') }
		}, true)
	</script>

	<!-- Аккаунт панелі: оң жақтан ашылады -->
	<div class="acc_drw">
		<div class="acc_drw_a"></div>
		<div class="acc_drw_c">
			<div class="acc_drw_hd">
				<h4><?=t::w('Account', 'Аккаунт')?></h4>
				<div class="btn btn_dd acc_drw_x"><i class="fal fa-times"></i></div>
			</div>
			<div class="acc_hd">
				<div class="acc_hd_av lazy_img" data-src="/assets/uploads/users/<?=@$user['img']?>"><? if (!@$user['img']): ?><i class="fal fa-user"></i><? endif ?></div>
				<div class="acc_hd_t"><div class="acc_drw_nm"><?=@$user['name']?> <?=@$user['surname']?></div></div>
			</div>
			<div class="form_im">
				<div class="form_span"><?=t::w('First name:', 'Аты:')?></div>
				<input type="text" class="form_txt acc_f_name" value="<?=htmlspecialchars((string)@$user['name'])?>">
				<i class="fal fa-user form_icon"></i>
			</div>
			<div class="form_im">
				<div class="form_span"><?=t::w('Last name:', 'Тегі:')?></div>
				<input type="text" class="form_txt acc_f_surname" value="<?=htmlspecialchars((string)@$user['surname'])?>">
				<i class="fal fa-user form_icon"></i>
			</div>
			<div class="form_im">
				<div class="form_span"><?=t::w('Phone:', 'Нөмір:')?></div>
				<input type="tel" class="form_txt fr_phone acc_f_phone" data-lenght="11" value="<?=htmlspecialchars(substr((string)@$user['phone'], 1))?>" placeholder="8 (700) 000-00-00">
				<i class="fal fa-phone form_icon"></i>
			</div>
			<div class="form_im">
				<div class="btn acc_f_save"><i class="far fa-check"></i><span><?=t::w('Save', 'Сақтау')?></span></div>
			</div>
		</div>
	</div>
	<script>
		$(document).on('click', '.acc_open', function(){ $('.acc_drw').addClass('acc_drw_act'); $('.ub1_lx').parent().removeClass('menu_act') })
		$(document).on('click', '.acc_drw_a, .acc_drw_x', function(){ $('.acc_drw').removeClass('acc_drw_act') })
		$(document).on('click', '.acc_f_save', function(){
			var btn = $(this); if (btn.hasClass('acc_busy')) return
			var name = $.trim($('.acc_f_name').val())
			if (!name) { mess(<?=json_encode(t::w('Enter name', 'Атыңызды жазыңыз'), JSON_UNESCAPED_UNICODE)?>); return }
			btn.addClass('acc_busy')
			$.ajax({
				url: '/acc/get.php?upd', type: 'POST', dataType: 'html',
				data: { name: name, surname: $.trim($('.acc_f_surname').val()), phone: $('.acc_f_phone').val() },
				success: function(data){
					btn.removeClass('acc_busy')
					if (data == 'yes') { mess(<?=json_encode(t::w('Saved', 'Сақталды'), JSON_UNESCAPED_UNICODE)?>); setTimeout(function(){ location.reload() }, 600) }
					else if (data == 'phone') mess(<?=json_encode(t::w('Wrong phone', 'Нөмір қате'), JSON_UNESCAPED_UNICODE)?>)
					else mess(<?=json_encode(t::w('Error', 'Қате шықты'), JSON_UNESCAPED_UNICODE)?>)
				},
				error: function(){ btn.removeClass('acc_busy'); mess(<?=json_encode(t::w('Error', 'Қате шықты'), JSON_UNESCAPED_UNICODE)?>) }
			})
		})
	</script>
<? endif ?>

<!-- <? if ($site_set['menu'] == true): ?>
   <div class="pmenu">
      <div class="pmenu_c">
         <a class="pmenu_i <?=($menu_name=='cashbox'?'pmenu_i_act':'')?>" href="/cashbox/">
				<i class="far fa-shopping-bag"></i>
				<span>Касса</span>
			</a>
         <a class="pmenu_i <?=($menu_name=='return'?'pmenu_i_act':'')?>" href="/return/">
				<i class="far fa-undo"></i>
				<span>Возврат</span>
			</a>
         <a class="pmenu_i <?=($menu_name=='orders'?'pmenu_i_act':'')?>" href="/orders/">
				<i class="far fa-list-ul"></i>
				<span>История</span>
			</a>
         <a class="pmenu_i <?=($menu_name=='acc'?'pmenu_i_act':'')?>" href="/acc/">
				<i class="far fa-user"></i>
				<span>Аккаунт</span>
			</a>
      </div>
   </div>
<? endif ?> -->