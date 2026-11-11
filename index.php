<? include "config/core.php";

   //
   if ($user_id) header('location: /cashbox');


   $staff = [];
   if (@$_GET['c'] || @$_COOKIE['c'] || @$_SESSION['c']) {
      if (@$_GET['c']) {
         $comp = $_GET['c'];
         $_SESSION['c'] = $comp;
         setcookie('c', $comp, time() + 3600*24*30*6, '/');
      } else {
         $comp = @$_SESSION['c'];
         $comp = @$_COOKIE['c'];
      }
      $comp = (int)$comp;

      $company_d = fun::company($comp);
      $open = true; $result = 0;
      if ($company_d['ins_dt'] != null && $company_d['end_dt'] != null) {
         $result = intval((strtotime($company_d['end_dt']) - strtotime(date("d.m.Y"))) / (60*60*24));
         if ($result <= 0) $open = false;
      }

      // кассаға кіретіндер: Менеджер (4) мен Кассир (5)
      $user_mn = db::query("select * from company_staff where positions_id in (4, 5) and company_id = '$comp'");
      while ($user_mnd = mysqli_fetch_assoc($user_mn)) {
         $u = fun::user($user_mnd['user_id']);
         if (!$u) continue;
         $staff[] = ['id' => $u['id'], 'name' => trim($u['name'].' '.$u['surname']), 'img' => @$u['img'], 'pos' => (int)$user_mnd['positions_id']];
      }
   }

   // Аты-жөнінің бас әріптері (аватар үшін)
   function ps_ini($n) {
      $p = preg_split('/\s+/u', trim((string)$n)); $r = '';
      foreach (array_slice($p, 0, 2) as $x) $r .= mb_strtoupper(mb_substr($x, 0, 1, 'UTF-8'), 'UTF-8');
      return $r !== '' ? $r : '?';
   }
   $ps_roles = [4 => t::w('Manager', 'Менеджер'), 5 => t::w('Cashier', 'Кассир')];


	// site setting
	$menu_name = 'main';
	$site_set['menu'] = false;
	$css = ['sign'];
	$js = ['sign'];
?>
<? include "block/header.php"; ?>

	<div class="ps">

		<!-- Сол жақ: бренд, сағат, компания -->
		<aside class="ps_side">
			<div class="ps_brand">
				<div class="ps_logo"><img src="/assets/img/logo/berik-icon.svg" alt="BERIK POS"></div>
				<div class="ps_name"><span>BERIK <b>POS</b></span><small><?=t::w('Cash desk', 'Касса')?></small></div>
			</div>

			<div class="ps_clock">
				<div class="ps_time ps_js_time">--:--</div>
				<div class="ps_date ps_js_date">&nbsp;</div>
				<? if (@$company_d['name']): ?>
					<div class="ps_comp"><i class="fal fa-store"></i> <?=htmlspecialchars($company_d['name'])?></div>
				<? endif ?>
			</div>

			<ul class="ps_steps">
				<li><i>1</i><?=t::w('Choose your name', 'Атыңызды таңдаңыз')?></li>
				<li><i>2</i><?=t::w('Enter your 4-digit code', '4 таңбалы кодыңызды енгізіңіз')?></li>
				<li><i>3</i><?=t::w('Start taking orders', 'Тапсырыс қабылдай бастаңыз')?></li>
			</ul>
		</aside>

		<!-- Оң жақ -->
		<main class="ps_main">

			<? if (!@$comp): ?>
				<div class="ps_state">
					<div class="ps_state_i"><i class="fal fa-link"></i></div>
					<h1 class="ps_h"><?=t::w('Wrong link', 'Сілтеме қате')?></h1>
					<p class="ps_p"><?=t::w('Open the cash desk with your company link.', 'Кассаны компанияңыздың сілтемесі арқылы ашыңыз.')?></p>
				</div>

			<? elseif (!@$open): ?>
				<div class="ps_state">
					<div class="ps_state_i ps_state_i_r"><i class="fal fa-calendar-times"></i></div>
					<h1 class="ps_h"><?=t::w('Subscription expired', 'Қолдану мерзімі аяқталды')?></h1>
					<p class="ps_p"><?=t::w('Dear client, your period of use has ended. Please contact us to extend it.', 'Құрметті клиент, сіздің қолдану уақытыңыз аяқталған. Ұзарту үшін бізге хабарласыңыз.')?></p>
				</div>

			<? else: ?>

				<!-- 1-қадам: қызметкер таңдау -->
				<section class="ps_step ps_list ps_on">
					<div class="ps_head">
						<span class="ps_badge"><i class="far fa-cash-register"></i> <?=t::w('Cash desk', 'Касса')?></span>
						<h1 class="ps_h"><?=t::w('Who is working?', 'Кім жұмыс істейді?')?></h1>
						<p class="ps_p"><?=t::w('Choose your name to sign in', 'Кіру үшін атыңызды таңдаңыз')?></p>
					</div>

					<? if ($result > 0 && $result <= 7): ?>
						<div class="ps_warn"><i class="far fa-exclamation-circle"></i> <?=t::w('Subscription ends in', 'Қолдану мерзімі аяқталуына')?> <b><?=$result?> <?=t::w('days left', 'күн қалды')?></b></div>
					<? endif ?>

					<? if ($staff): ?>
						<div class="ps_grid">
							<? foreach ($staff as $s): ?>
								<button type="button" class="ps_user" data-id="<?=$s['id']?>" data-name="<?=htmlspecialchars($s['name'])?>" data-role="<?=htmlspecialchars(@$ps_roles[$s['pos']])?>" data-ini="<?=htmlspecialchars(ps_ini($s['name']))?>" data-img="<?=$s['img'] ? htmlspecialchars('/assets/uploads/users/'.$s['img']) : ''?>">
									<span class="ps_av ps_av<?=$s['id'] % 6?>">
										<? if ($s['img']): ?><img src="/assets/uploads/users/<?=htmlspecialchars($s['img'])?>" alt="" onerror="this.remove()"><? endif ?>
										<em><?=htmlspecialchars(ps_ini($s['name']))?></em>
									</span>
									<span class="ps_un"><?=htmlspecialchars($s['name'])?></span>
									<span class="ps_ur"><?=htmlspecialchars(@$ps_roles[$s['pos']])?></span>
									<i class="far fa-arrow-right ps_ugo"></i>
								</button>
							<? endforeach ?>
						</div>
					<? else: ?>
						<div class="ps_empty">
							<i class="fal fa-user-slash"></i>
							<p><?=t::w('No staff for the cash desk yet. Add a Manager or Cashier in the admin panel.', 'Кассаға кіретін қызметкер әлі жоқ. Админнен Менеджер не Кассир қосыңыз.')?></p>
						</div>
					<? endif ?>
				</section>

				<!-- 2-қадам: PIN код -->
				<section class="ps_step ps_pin" aria-hidden="true">
					<button type="button" class="ps_back"><i class="far fa-arrow-left"></i> <?=t::w('Another person', 'Басқа адам')?></button>

					<div class="ps_who">
						<span class="ps_av ps_av_lg ps_js_av"><em></em></span>
						<div class="ps_who_n ps_js_name"></div>
						<div class="ps_ur ps_js_role"></div>
					</div>

					<p class="ps_p ps_pin_t"><?=t::w('Enter your personal code', 'Жеке кодыңызды енгізіңіз')?></p>
					<div class="ps_dots"><i></i><i></i><i></i><i></i></div>
					<div class="ps_err" data-txt="<?=htmlspecialchars(t::w('Wrong code, try again', 'Код қате, қайта енгізіңіз'))?>">&nbsp;</div>

					<div class="ps_pad">
						<? foreach ([1,2,3,4,5,6,7,8,9] as $n): ?><button type="button" data-k="<?=$n?>"><?=$n?></button><? endforeach ?>
						<button type="button" data-k="c" class="ps_pad_f" title="<?=t::w('Clear', 'Тазалау')?>"><i class="far fa-times"></i></button>
						<button type="button" data-k="0">0</button>
						<button type="button" data-k="b" class="ps_pad_f" title="<?=t::w('Erase', 'Өшіру')?>"><i class="far fa-backspace"></i></button>
					</div>
				</section>

			<? endif ?>

			<div class="ps_made"><a class="gprog_bl gp_made" href="https://gprog.kz" target="_blank" rel="noopener"><span>#gprog-та</span><span class="gprog_heart"><i class="fas fa-heart"></i></span><span>жасалған</span></a></div>
		</main>
	</div>

<? include "block/footer.php"; ?>
