<? // Сайттан (QR меню / жеке кабинет) келген тапсырысты қабылдау терезесі. Ашатын: card.php .or_acc, логикасы: assets/js/accept.js, стилі: assets/css/accept.css ?>
<div class="oacc_bg">
	<div class="oacc">
		<div class="oacc_h">
			<span class="oacc_ic"><i class="far fa-check-circle"></i></span>
			<div>
				<b><?=t::w('Accept order', 'Тапсырысты қабылдау')?> №<span class="oacc_num"></span></b>
				<small><span class="oacc_src"><?=t::w('Order from the website', 'Сайттан келген тапсырыс')?></span> · <?=t::w('Total', 'Жалпы')?>: <b class="oacc_total"></b></small>
			</div>
			<span class="oacc_x" title="<?=t::w('Close', 'Жабу')?>"><i class="far fa-times"></i></span>
		</div>

		<div class="oacc_pay">
			<div class="oacc_lbl oacc_lbl_pre"><?=t::w('Prepayment', 'Предоплата')?></div>
			<div class="oacc_lbl oacc_lbl_cafe"><?=t::w('Payment', 'Төлем')?></div>
			<div class="oacc_amt">
				<span class="oacc_ab" data-a="0"><span class="oacc_t_pre"><?=t::w('No prepayment', 'Предоплатасыз')?></span><span class="oacc_t_cafe"><?=t::w('Pays later', 'Кейін төлейді')?></span></span>
				<span class="oacc_ab" data-a="full"><?=t::w('Full', 'Толық')?> <b class="oacc_full"></b></span>
			</div>
			<div class="oacc_in_w oacc_custom">
				<input type="tel" class="oacc_in" inputmode="numeric" placeholder="<?=t::w('Other amount', 'Басқа сома')?>">
				<span>тг</span>
			</div>

			<div class="oacc_m">
				<div class="oacc_lbl"><?=t::w('How did they pay?', 'Немен төледі?')?></div>
				<div class="oacc_ml">
					<div class="oacc_mo" data-m="qr"><i class="far fa-qrcode"></i><span>Kaspi QR</span></div>
					<div class="oacc_mo" data-m="transfer"><i class="far fa-exchange-alt"></i><span><?=t::w('Transfer', 'Аударым')?></span></div>
					<div class="oacc_mo" data-m="cash"><i class="far fa-money-bill-wave"></i><span><?=t::w('Cash', 'Қолма-қол')?></span></div>
					<div class="oacc_mo" data-m="mix"><i class="far fa-random"></i><span><?=t::w('Mixed', 'Аралас')?></span></div>
					<div class="oacc_mo oacc_red" data-m="red"><i class="far fa-percent"></i><span>RED <span class="oacc_red_p"></span>%</span><b class="oacc_red_s"></b></div>
				</div>
				<div class="oacc_mx">
					<div class="oacc_mx_r"><span><i class="far fa-qrcode"></i>Kaspi QR</span><input type="tel" class="oacc_mx_in" data-k="qr" placeholder="0" inputmode="numeric"></div>
					<div class="oacc_mx_r"><span><i class="far fa-exchange-alt"></i><?=t::w('Transfer', 'Аударым')?></span><input type="tel" class="oacc_mx_in" data-k="transfer" placeholder="0" inputmode="numeric"></div>
					<div class="oacc_mx_r"><span><i class="far fa-money-bill-wave"></i><?=t::w('Cash', 'Қолма-қол')?></span><input type="tel" class="oacc_mx_in" data-k="cash" placeholder="0" inputmode="numeric"></div>
					<div class="oacc_mx_rest"><span><?=t::w('Left', 'Қалды')?>:</span> <b>0 тг</b></div>
				</div>
			</div>

			<div class="oacc_rest">
				<span class="oacc_rest_l" data-t1="<?=htmlspecialchars(t::w('To courier (cash)', 'Курьерге (нал)'))?>" data-t2="<?=htmlspecialchars(t::w('On pickup', 'Алғанда төлейді'))?>" data-t4="<?=htmlspecialchars(t::w('Payment after the meal', 'Төлем кейін, «Төледі» батырмасымен'))?>"></span>
				<b class="oacc_rest_s"></b>
			</div>
		</div>

		<label class="oacc_print"><input type="checkbox" class="oacc_pr" checked><span><?=t::w('Print receipt', 'Чек шығару')?></span></label>
		<div class="oacc_err"></div>
		<div class="oacc_btns">
			<span class="oacc_no"><?=t::w('Cancel', 'Бас тарту')?></span>
			<span class="oacc_ok"><i class="far fa-check"></i><?=t::w('Accept', 'Қабылдау')?></span>
		</div>
	</div>
</div>
