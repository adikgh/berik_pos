<? include "../config/core.php";

	// 
	if (!$user_id) header('location: /');

	// Смена: соңғы жабылған сменадан (не бүгінгі жұмыс күнінің басынан) қазірге дейін
	$has_shift = mysqli_num_rows(db::query("SHOW TABLES LIKE 'retail_shifts'")) > 0
		&& mysqli_num_rows(db::query("SHOW COLUMNS FROM `retail_shifts` LIKE 'company_id'")) > 0;
	include __DIR__.'/calc.php';

	$sh_start = $start_cdate;
	$last_shift = null;
	if ($has_shift) {
		$last_shift = mysqli_fetch_assoc(db::query("select * from retail_shifts where company_id = '$company' and branch_id = '$branch' and user_id = '$user_id' and close_dt is not null order by close_dt desc limit 1"));
		if ($last_shift && $last_shift['close_dt'] > $sh_start) $sh_start = $last_shift['close_dt'];
	}
	$sh_end = date('Y-m-d H:i:s');

	$s = shift_calc($company, $branch, $user_id, $sh_start, $sh_end);

	// Соңғы жабылған сменалар
	$history = [];
	if ($has_shift) {
		$q = db::query("select * from retail_shifts where company_id = '$company' and branch_id = '$branch' and user_id = '$user_id' and close_dt is not null order by close_dt desc limit 10");
		while ($r = mysqli_fetch_assoc($q)) $history[] = $r;
	}

	$money = function($n) { return number_format((int)$n, 0, '', ' ').' тг'; };

	// site setting
	$menu_name = 'shift';
	$css = [];
	$js = [];
?>
<? include "../block/header.php"; ?>

	<div class="flex_clm_rev">
		<div class="bl_c">
			<div class="sh_c">

				<div class="sh_card">
					<div class="sh_hd">
						<div>
							<h4><i class="fal fa-cash-register"></i> <?=t::w('Current shift', 'Ағымдағы смена')?></h4>
							<p><?=date('d.m.Y H:i', strtotime($sh_start))?> — <?=date('d.m.Y H:i', strtotime($sh_end))?></p>
						</div>
						<div class="sh_user"><i class="fal fa-user"></i> <?=@$user['name']?> <?=@$user['surname']?></div>
					</div>

					<div class="sh_grid">
						<div class="sh_i sh_i_main"><span><?=t::w('My received money', 'Менің қабылдаған ақшам')?></span><b><?=$money($s['my'])?></b></div>
						<div class="sh_i"><span><?=t::w('Cash', 'Қолма-қол')?></span><b><?=$money($s['cash'])?></b></div>
						<div class="sh_i"><span>Kaspi QR</span><b><?=$money($s['qr'])?></b></div>
						<div class="sh_i"><span><?=t::w('Transfer', 'Аударым')?></span><b><?=$money($s['transfer'])?></b></div>
						<div class="sh_i"><span>RED</span><b><?=$money($s['red'])?></b></div>
					</div>

					<div class="sh_sub"><?=t::w('Where from', 'Қайдан түсті')?></div>
					<div class="sh_rows">
						<div class="sh_row">
							<span><?=t::w('Delivery / takeaway prepayments', 'Жеткізу / Өзімен — предоплата')?></span>
							<span><?=(int)$s['pre_cnt']?> <?=t::w('pcs', 'дана')?></span>
							<b><?=$money($s['pre_sum'])?></b>
						</div>
						<div class="sh_row">
							<span><?=t::w('Cafe', 'Кафе')?></span>
							<span><?=(int)$s['cafe_cnt']?> <?=t::w('pcs', 'дана')?></span>
							<b><?=$money($s['cafe_sum'])?></b>
						</div>
						<? if ($s['off']): ?>
							<div class="sh_row sh_row_m">
								<span><?=t::w('Refused orders', 'Отказ тапсырыстар')?></span>
								<span><?=(int)$s['off']?> <?=t::w('pcs', 'дана')?></span>
								<b>—</b>
							</div>
						<? endif ?>
					</div>

					<? if ($s['srv']): ?>
						<div class="sh_sub"><?=t::w('Service orders (separate)', 'Сервис (бөлек есеп, ақшасы сервистен түседі)')?></div>
						<div class="sh_rows">
							<? foreach ($s['srv'] as $sn => $sv): ?>
								<div class="sh_row">
									<span><?=($sn == 'Басқа' ? t::w('Other', 'Басқа') : htmlspecialchars($sn))?></span>
									<span><?=(int)$sv['cnt']?> <?=t::w('pcs', 'дана')?></span>
									<b><?=$money($sv['sum'])?></b>
								</div>
							<? endforeach ?>
						</div>
					<? endif ?>

					<? if ($has_shift): ?>
						<div class="sh_close">
							<div class="form_im">
								<div class="form_span"><?=t::w('Shift comment', 'Пікір (міндетті емес):')?></div>
								<input type="text" class="form_txt sh_comment" placeholder="<?=t::w('Shift comment example', 'Мысалы: кассада 2 000 тг артық')?>">
								<i class="fal fa-comment-alt form_icon"></i>
							</div>
							<div class="btn sh_close_btn" data-start="<?=$sh_start?>" data-end="<?=$sh_end?>"><i class="far fa-check"></i><span><?=t::w('Close shift', 'Сменді жабу')?></span></div>
						</div>
					<? else: ?>
						<div class="pre_note"><?=t::w('Shift SQL needed', 'Сменді жабу үшін базаға retail_shifts бағандарын қосу керек.')?></div>
					<? endif ?>
				</div>

				<? if ($history): ?>
					<div class="sh_card">
						<div class="sh_sub" style="margin-top:0"><?=t::w('Closed shifts', 'Жабылған сменалар')?></div>
						<div class="sh_rows">
							<? foreach ($history as $h): $hu = fun::user($h['user_id']); ?>
								<div class="sh_row">
									<span><?=date('d.m H:i', strtotime($h['ins_dt']))?> — <?=date('d.m H:i', strtotime($h['close_dt']))?></span>
									<span><?=@$hu['name']?> · <?=(int)$h['orders_cnt']?> <?=t::w('pcs', 'дана')?></span>
									<b><?=$money($h['totol'])?></b>
								</div>
								<? if ($h['comment']): ?><div class="sh_row_c"><?=htmlspecialchars($h['comment'])?></div><? endif ?>
							<? endforeach ?>
						</div>
					</div>
				<? endif ?>

			</div>
		</div>
	</div>

	<script>
		$(document).on('click', '.sh_close_btn', function(){
			var btn = $(this)
			if (btn.hasClass('sh_busy')) return
			if (!confirm(<?=json_encode(t::w('Close shift confirm', 'Сменді жабасыз ба?'), JSON_UNESCAPED_UNICODE)?>)) return
			btn.addClass('sh_busy')
			$.ajax({
				url: '/shift/get.php?close', type: 'POST', dataType: 'html',
				data: { start: btn.attr('data-start'), end: btn.attr('data-end'), comment: $('.sh_comment').val() },
				success: function(data){
					if (data == 'yes') { mess(<?=json_encode(t::w('Shift closed', 'Смена жабылды'), JSON_UNESCAPED_UNICODE)?>); setTimeout(function(){ location.reload() }, 600) }
					else { btn.removeClass('sh_busy'); mess(<?=json_encode(t::w('Error', 'Қате шықты'), JSON_UNESCAPED_UNICODE)?>); console.log(data) }
				},
				error: function(){ btn.removeClass('sh_busy'); mess(<?=json_encode(t::w('Error', 'Қате шықты'), JSON_UNESCAPED_UNICODE)?>) }
			})
		})
	</script>

<? include "../block/footer.php"; ?>
