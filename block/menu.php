<? if ($site_set['menu']): ?>
	<div class="aheader">
		<div class="bl_c">
			<div class="aheader_c">
				<div class="alogo" href="/">
					<!-- <div><?=$site['name']?></div> -->
					<!-- <p title="Касса"><i class="fal fa-cash-register"></i> 1</p> -->
					<!-- <p>Кассир: <?=$user['name']?> <?=$user['surname']?></p> -->
					<!-- <p title="Время"><i class="fal fa-clock"></i> <span class="toDate">00:00</span></p> -->
				</div>
				<div class="ahead">
					<div class="mp_top">
						<div class="mp_topc">
							<a class="mp_topi <?=($menu_name=='cashbox'?'mp_topi_act':'')?>" href="/cashbox/"><i class="fal fa-plus-circle"></i><span><?=t::w('New order', 'Жаңадан қосу')?></span></a>
							<a class="mp_topi <?=($menu_name=='orders'?'mp_topi_act':'')?>" href="/orders/?branch=<?=$branch?>"><i class="fal fa-receipt"></i><span><?=t::w('Orders', 'Тапсырыстар')?></span></a>
							<!-- <a class="mp_topi <?=($menu_name=='orders'?'mp_topi_act':'')?>" href="/orders/all.php">Тапсырыстар</a> -->
							<!-- <a class="mp_topi <?=($menu_name=='main'?'mp_topi_act':'')?>" href="/kassa/">Касса</a> -->
							<!-- <a class="mp_topi <?=($menu_name=='products'?'mp_topi_act':'')?>" href="/products/">Тауар</a> -->
							<!-- <a class="mp_topi <?=($menu_name=='dashboard'?'mp_topi_act':'')?>" href="/dashboard/">Статистика</a> -->
							<!-- <a class="mp_topi <?=($menu_name=='main'?'mp_topi_act':'')?>" href="/kassa/">Касса</a> -->
							<!-- <a class="mp_topi <?=($menu_name=='return'?'mp_topi_act':'')?>" href="/return/">Возврат</a> -->
							<!-- <a class="mp_topi <?=($menu_name=='main'?'mp_topi_act':'')?>" href="/change/">Смена</a> -->
						</div>
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