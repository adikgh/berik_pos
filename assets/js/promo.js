// Кассадағы акция (2026-11-11): админ жасаған % жеңілдік, тек тағам сомасынан.
// Жалпы сома = тағам − акция + жеткізу (+RED, −сыйлық, −кэшбэк).
// cashbox.js жеткізу мен себет өзгергенде window.promo_amt() арқылы жеңілдікті қайта шегереді.
$(function () {
   var box = $('.sel_promo')
   window.promo_amt = function () { return 0 }
   window.promo_set = function () {}
   if (!box.length) return
   var tot = $('.cashbox_pay_btotol_c'), cur = 0 // қазір шегерілген жеңілдік
   var tg = function (n) { return String(Math.round(n)).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' тг' }
   function pct() { return Number(box.find('.form_im_slo_act').attr('data-pct')) || 0 }
   window.promo_amt = function (food) {
      if (food == null) food = Number(tot.attr('data-on-val')) || 0
      return Math.max(0, Math.round(food * pct() / 100))
   }
   window.promo_set = function (v) { cur = v; info() }

   function info() {
      var p = pct()
      $('.promo_info').html(p && cur ? w('Discount', 'Жеңілдік') + ': <b>−' + tg(cur) + '</b>' : '').toggleClass('dsp_n', !(p && cur))
   }

   function apply() {
      var want = window.promo_amt()
      if (want == cur) { info(); return }
      $(document).trigger('promo:before') // loyalty.js: сыйлық пен кэшбэкті уақытша алады
      var red = $('.form_im_slo_red'), red_on = red.hasClass('form_im_slo_act')
      if (red_on) red.click()
      var s = (Number(tot.attr('data-val')) || 0) + cur - want
      cur = want
      tot.attr('data-val', s).html(s + ' тг')
      $('.cashbox_pay_bsemt').html(s + ' тг')
      $('.btype_cash').html(s + ' тг').attr('data-val', s)
      if (red_on) red.click()
      $('.btype_qr').trigger('input')
      $(document).trigger('promo:after')
      info()
   }

   box.on('click', '.form_im_slo_i', function () {
      box.find('.form_im_slo_i').removeClass('form_im_slo_act')
      $(this).addClass('form_im_slo_act')
      box.attr('data-val', $(this).attr('data-type'))
      apply()
   })

   // Сақтағанда акцияны қосып жібереміз (сервер жеңілдікті қайта есептейді)
   $.ajaxPrefilter(function (o) {
      if (!o.url || o.url.indexOf('cashbox_pay') < 0 || typeof o.data != 'string') return
      o.data += '&promo=1&promo_id=' + (Number(box.attr('data-val')) || 0) + '&promo_disc=' + cur
   })

   apply() // өзгертуде: тапсырыстағы акция бірден шегеріледі
})
