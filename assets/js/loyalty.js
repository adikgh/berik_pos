// Кассадағы лоялдық: кэшбэкпен төлеу және N-ші тапсырысқа сыйлық.
// Тек .lc_box бар болса (компанияда кэшбэк не сыйлық қосулы) жұмыс істейді.
$(function () {
   var box = $('.lc_box')
   if (!box.length) return
   var info = null, cb_on = false, cb_amt = 0, gift_on = false, last_ph = ''
   var tg = function (n) { return String(Math.round(n)).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' тг' }
   var tot = $('.cashbox_pay_btotol_c')

   // Жалпы сомаға қосу/азайту (RED қосулы болса, алдымен өшіріп, кейін қайта қосамыз)
   function total_add(d) {
      var red = $('.form_im_slo_red'), red_on = red.hasClass('form_im_slo_act')
      if (red_on) red.click()
      var s = (Number(tot.attr('data-val')) || 0) + d
      tot.attr('data-val', s).html(s + ' тг')
      $('.cashbox_pay_bsemt').html(s + ' тг')
      $('.btype_cash').html(s + ' тг').attr('data-val', s)
      if (red_on) red.click()
      $('.btype_qr').trigger('input')
   }

   function cb_set(on) {
      if (on == cb_on) return
      if (on) {
         var grand = Number(tot.attr('data-val')) || 0
         cb_amt = Math.max(0, Math.min(info.available, grand))
         if (!cb_amt) return
         total_add(-cb_amt)
      } else total_add(cb_amt)
      cb_on = on
      render()
   }

   function render() {
      if (!info || !info.on) { box.addClass('dsp_n').html(''); return }
      var h = ''
      if (info.cashback) {
         h += '<div class="lc_row"><div class="lc_lbl"><i class="far fa-coins"></i> ' + w('Cashback', 'Кэшбэк') + ' ' + info.percent.toString().replace(/\.0+$/, '') + '%</div>'
         h += '<div class="lc_val">' + w('Balance', 'Балансы') + ': <b>' + tg(info.available) + '</b></div></div>'
         if (info.available > 0) {
            var a = cb_on ? cb_amt : Math.min(info.available, Number(tot.attr('data-val')) || 0)
            h += '<div class="form_im_slo"><div class="form_im_slo_i lc_cb ' + (cb_on ? 'form_im_slo_act' : '') + '"><i class="far fa-coins pay_ic"></i>' + w('Pay with cashback', 'Кэшбэкпен төлеу') + ' −' + tg(a) + '</div></div>'
         }
      }
      if (info.gift) {
         var n = info.gift_every, c = Math.min(info.gift_cnt, n - 1)
         if (info.gift_due) {
            h += '<div class="lc_gift_due"><i class="far fa-gift"></i> ' + n + w('-th order: gift', '-шы тапсырыс. Сыйлық') + ': <b>' + $('<i>').text(info.gift_name).html() + '</b>'
            h += '<small>' + w('Add the gift to the order with price 0', 'Сыйлықты тапсырысқа қосып, бағасын 0 қойыңыз') + '</small></div>'
            h += '<div class="form_im_slo"><div class="form_im_slo_i lc_gift ' + (gift_on ? 'form_im_slo_act' : '') + '"><i class="far fa-gift pay_ic"></i>' + w('Gift given', 'Сыйлық берілді') + '</div></div>'
         } else {
            h += '<div class="lc_row"><div class="lc_lbl"><i class="far fa-gift"></i> ' + w('Gift', 'Сыйлық') + '</div><div class="lc_val">' + c + ' / ' + n + ' · ' + (n - 1 - c) + ' ' + w('orders left', 'тапсырыс қалды') + '</div></div>'
         }
      }
      box.html(h).toggleClass('dsp_n', h == '')
   }

   function load(ph) {
      $.ajax({
         url: '/cashbox/loyalty.php?info', type: 'POST', dataType: 'json',
         data: { phone: ph, oid: box.attr('data-oid') },
         success: function (r) {
            if (ph != last_ph) return
            if (cb_on) cb_set(false)
            info = r
            gift_on = !!(r && r.gift_given)
            render()
            if (r && r.used > 0) cb_set(true) // өзгертуде: бұрын кэшбэкпен төленген
         }
      })
   }

   function phone_check() {
      var p = $('.btype_phone'), ph = String(p.attr('data-val') || p.val() || '').replace(/\D+/g, '')
      if (ph.length == 10) ph = '8' + ph
      if (ph.length != 11) ph = ''
      if (ph == last_ph) return
      last_ph = ph
      if (cb_on) cb_set(false)
      info = null; gift_on = false
      render()
      if (ph) load(ph)
   }
   $('html').on('input', '.btype_phone', function () { setTimeout(phone_check, 0) })
   setTimeout(phone_check, 300)

   $('html').on('click', '.lc_cb', function () { cb_set(!cb_on) })
   $('html').on('click', '.lc_gift', function () { gift_on = !gift_on; render() })

   // Жеткізу сомасы өзгерсе, cashbox.js жалпы соманы қайта есептейді: кэшбэкті қайта шегереміз
   $('html').on('input', '.btype_delivery', function () { if (cb_on) { cb_on = false; setTimeout(function () { cb_set(true); render() }, 0) } })

   // Сақтағанда: кэшбэк сомасы мен сыйлық белгісін қосып жібереміз (info жүктелмесе — ештеңе жібермейміз, бұрынғысы қалады)
   $.ajaxPrefilter(function (o) {
      if (!o.url || o.url.indexOf('cashbox_pay') < 0 || !info || !info.on) return
      if (typeof o.data != 'string') return
      o.data += '&lc=1&cashback=' + (cb_on ? cb_amt : 0) + '&gift=' + (gift_on ? 1 : 0)
   })
})
