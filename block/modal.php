<!--  -->
<div class="bl_mess"><div class="bl_mess_sam"></div></div>

<!-- block form -->
<? if (@$site_set['form']): ?>
   <div class="pop_bl fr">
      <div class="pop_bl_a zabr_back"></div>
      <div class="pop_bl_c">
         <div class="head_c txt_c">
            <h4>Курска жазыламын</h4>
            <p>Атыңыз бен номеріңізді төменге қалдырыңыз</p>
         </div>
         <div class="form_c">
            <div class="form_im">
               <input type="text" class="form_im_txt name" placeholder="Есіміңіз ..">
               <div class="form_icon"><i class="far fa-user"></i></div>
            </div>
            <div class="form_im">
               <input type="tel" class="form_im_txt phone fr_phone" placeholder="8 (700) 000-00-00">
               <div class="form_icon"><i class="far fa-phone-alt"></i></div>
            </div>
            <div class="form_im form_im_bn">
               <div class="btn orderSend">
                  <span>Жіберу</span>
               </div>
            </div>
         </div>
      </div>
   </div>
<? endif ?>


<!--  -->
<? if (@$site_set['cl_wh']): ?>
	<!-- phone -->
	<a target="_blank" href="https://wa.me/<?=$site['whatsapp']?>">
		<div type="button" class="callback-bt">
		   <i class="fab fa-whatsapp"></i>
		</div>
	</a>
<? endif ?>

<!-- Сайттан (QR меню / жеке кабинет) келген жаңа тапсырыс хабары. Стилі: assets/css/notify.css, логикасы: assets/js/notify.js -->
<? if (!empty($user_id) && empty($posm)): ?>
	<div class="onn_box" data-cid="<?=(int)@$company?>" data-life="10">
		<!-- Windows хабарламасына рұқсат (тек https-те шығады) -->
		<div class="onn_perm dsp_n">
			<b>🔔 Сайттан тапсырыс келгенде Windows хабарламасы шықсын ба?</b>
			<span class="onn_perm_on">Қосу</span>
			<i class="onn_perm_no" title="Кейін">×</i>
		</div>
	</div>

	<!-- Бір хабардың үлгісі: JS көшіріп толтырады -->
	<template class="onn_tpl">
		<div class="onn">
			<div class="onn_t">
				<i class="far fa-bell"></i>
				<span class="onn_tt">Сайттан жаңа тапсырыс · <span class="onn_src"></span></span>
				<span class="onn_rg" title="Бет белсенді болғанда санайды">
					<svg viewBox="0 0 30 30"><circle class="bg" cx="15" cy="15" r="12"/><circle class="fg" cx="15" cy="15" r="12"/></svg>
					<b class="onn_sec">10</b>
				</span>
				<span class="onn_x" title="Жабу">×</span>
			</div>
			<div class="onn_n">№<span class="onn_num"></span></div>
			<div class="onn_d"><span class="onn_type"></span> · <b class="onn_sum"></b> · <span class="onn_time"></span></div>
			<div class="onn_m"></div>
			<div class="onn_b">
				<span class="onn_open">Ашу</span>
				<span class="onn_ok">Көрдім</span>
			</div>
			<div class="onn_tm"><i></i></div>
		</div>
	</template>
<? endif ?>
