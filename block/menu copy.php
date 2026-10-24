<? if ($site_set['menu']): ?>
	<div class="aheader">
		<div class="bl_c">
			<div class="aheader_c">
				<div class="alogo" href="/">
					<!-- <div><?=$site['name']?></div> -->
					<p>Касса: 1</p>
					<!-- <p>Кассир: <?=$user['name']?> <?=$user['surname']?></p> -->
					<p>Время: <span class="toDate">00:00</span></p>
					<? if ($user_right['positions_id'] != 4): ?>
						<div class="">
							<a class="" href="?branch=1" style="margin-right: 15px; color:#fff">Банзай</a>
							<a class="" href="?branch=2" style="color:#fff">СушиМастер</a>
						</div>
					<? endif ?>
				</div>
				<div class="ahead">
					<div class="mp_top">
						<div class="mp_topc">
							<a class="mp_topi <?=($menu_name=='cashbox'?'mp_topi_act':'')?>" href="/cashbox/">Жаңадан косу</a>
							<? if ($user_right['positions_id'] == 4): ?>
								<a class="mp_topi <?=($menu_name=='orders'?'mp_topi_act':'')?>" href="/orders/">Тапсырыстар</a>
							<? else: ?>
								<a class="mp_topi <?=($menu_name=='orders'?'mp_topi_act':'')?>" href="/orders/all.php">Тапсырыстар</a>
								<a class="mp_topi <?=($menu_name=='main'?'mp_topi_act':'')?>" href="/kassa/">Касса</a>
								<!-- <a class="mp_topi <?=($menu_name=='products'?'mp_topi_act':'')?>" href="/products/">Тауар</a> -->
							<? endif ?>
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
						<div class="menu_c">
							<a class="menu_ci" href="#">
								<div class="menu_cin"><i class="fal fa-cash-register"></i></div>
								<div class="menu_cih">Сменді жабу</div>
							</a> 
							<!-- <a class="menu_ci" href="/products/">
								<div class="menu_cin"><i class="fal fa-boxes-alt"></i></div>
								<div class="menu_cih">Корректировка</div>
							</a>
							<a class="menu_ci" href="/remains/">
								<div class="menu_cin"><i class="fal fa-boxes-alt"></i></div>
								<div class="menu_cih">Остатки</div>
							</a>
							<a class="menu_ci" href="/displacement/">
								<div class="menu_cin"><i class="fal fa-boxes-alt"></i></div>
								<div class="menu_cih">Перемещения</div>
							</a> -->
							<a class="menu_ci" href="#/acc/">
								<div class="menu_cin"><i class="fal fa-user"></i></div>
								<div class="menu_cih">Аккаунт</div>
							</a>
							<a class="menu_ci" href="/exit.php">
								<div class="menu_cin"><i class="fal fa-sign-out"></i></div>
								<div class="menu_cih">Выход</div>
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
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