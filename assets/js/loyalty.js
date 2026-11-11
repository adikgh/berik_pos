// Кассадағы лоялдық: кэшбэкпен төлеу және N-ші тапсырысқа сыйлық.
// Тек .lc_box бар болса (компанияда кэшбэк не сыйлық қосулы) жұмыс істейді.
$(function () {
   var box = $('.lc_box')
   if (!box.length) return
   var info = null, cb_on = false, cb_amt = 0, gift_on = false, gd_amt = 0, last_ph = ''
   var esc = function (t) { return $('<i>').text(t || '').html() }
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

   // Компания шегі: кэшбэкпен жалпы соманың ең көбі pay_max % төленеді (0 = шек жоқ)
   function cap(grand) { var pm = Number(info && info.pay_max) || 0; return pm > 0 ? Math.floor(grand * pm / 100) : grand }

   function cb_set(on) {
      if (on == cb_on) return
      if (on) {
         var grand = Number(tot.attr('data-val')) || 0
         cb_amt = Math.max(0, Math.min(info.available, cap(grand)))
         if (!cb_amt) return
         total_add(-cb_amt)
      } else total_add(cb_amt)
      cb_on = on
      render()
   }

   // Сыйлық белгісі. Жеңілдік деңгейі болса жалпы сома азаяды (кэшбэк одан кейін есептеледі)
   function gift_set(on) {
      if (on == gift_on) return
      var cb = cb_on
      if (cb) cb_set(false)
      if (on) { gd_amt = Math.min(info.gift_discount || 0, Number(tot.attr('data-val')) || 0); if (gd_amt) total_add(-gd_amt) }
      else { if (gd_amt) total_add(gd_amt); gd_amt = 0 }
      gift_on = on
      if (cb) cb_set(true)
      render()
   }

   function render() {
      if (!info || !info.on) { box.addClass('dsp_n').html(''); return }
      var h = ''
      if (info.cashback) {
         h += '<div class="lc_row"><div class="lc_lbl"><i class="far fa-coins"></i> ' + w('Cashback', 'Кэшбэк') + ' ' + (info.label || (info.percent.toString().replace(/\.0+$/, '') + '%')) + '</div>'
         h += '<div class="lc_val">' + w('Balance', 'Балансы') + ': <b>' + tg(info.available) + '</b></div></div>'
         if (info.available > 0) {
            var a = cb_on ? cb_amt : Math.min(info.available, cap(Number(tot.attr('data-val')) || 0))
            h += '<div class="form_im_slo"><div class="form_im_slo_i lc_cb ' + (cb_on ? 'form_im_slo_act' : '') + '"><i class="far fa-coins pay_ic"></i>' + w('Pay with cashback', 'Кэшбэкпен төлеу') + ' −' + tg(a) + '</div></div>'
            if (info.pay_max > 0) h += '<div class="lc_row"><small>' + w('Cashback can cover up to ', 'Кэшбэкпен соманың ең көбі ') + info.pay_max + w('% of the order', '% төленеді') + '</small></div>'
         }
      }
      if (info.gift) {
         if (info.gift_due) {
            h += '<div class="lc_gift_due"><i class="far fa-gift"></i> ' + info.gift_k + w('-th order: gift', '-шы тапсырыс. Сыйлық') + ': <b>' + esc(info.gift_name) + '</b>'
            if (info.gift_product) h += '<small>' + w('The gift is added to the order at 0 tg', 'Сыйлық тапсырысқа 0 тг болып қосылады') + '</small>'
            else if (info.gift_discount > 0) h += '<small>' + w('Discount', 'Жеңілдік') + ' −' + tg(info.gift_discount) + '</small>'
            h += '</div>'
            h += '<div class="form_im_slo"><div class="form_im_slo_i lc_gift ' + (gift_on ? 'form_im_slo_act' : '') + '"><i class="far fa-gift pay_ic"></i>' + w('Give the gift', 'Сыйлықты беру') + (gd_amt ? ' −' + tg(gd_amt) : '') + '</div></div>'
         }
         var nx = info.next
         if (nx && nx.tier) h += '<div class="lc_row"><div class="lc_lbl"><i class="far fa-gift"></i> ' + w('Next gift', 'Келесі сыйлық') + '</div><div class="lc_val">' + nx.k + w('-th', '-шы') + ': ' + esc(nx.tier.name) + ' · ' + (nx.left ? nx.left + ' ' + w('orders left', 'тапсырыс қалды') : w('next order', 'келесі тапсырыс')) + '</div></div>'
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
            if (gift_on) gift_set(false)
            info = r
            render()
            if (r && r.gift_given) gift_set(true) // өзгертуде: сыйлық бұрын берілген
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
      if (gift_on) gift_set(false)
      info = null
      render()
      if (ph) load(ph)
   }
   $('html').on('input', '.btype_phone', function () { setTimeout(phone_check, 0) })
   setTimeout(phone_check, 300)

   $('html').on('click', '.lc_cb', function () { cb_set(!cb_on) })
   $('html').on('click', '.lc_gift', function () { gift_set(!gift_on) })

   // Жеткізу сомасы өзгерсе, cashbox.js жалпы соманы қайта есептейді: жеңілдік пен кэшбэкті қайта шегереміз
   $('html').on('input', '.btype_delivery', function () {
      var g = gift_on, c = cb_on
      if (!g && !c) return
      gift_on = false; gd_amt = 0; cb_on = false
      setTimeout(function () { if (g) gift_set(true); if (c) cb_set(true); render() }, 0)
   })

   // Акция өзгерсе (promo.js): сыйлық пен кэшбэкті алып, жаңа сомадан қайта шегереміз
   var pr_g = false, pr_c = false
   $(document).on('promo:before', function () {
      pr_g = gift_on; pr_c = cb_on
      if (cb_on) cb_set(false)
      if (gift_on) gift_set(false)
   })
   $(document).on('promo:after', function () {
      if (pr_g) gift_set(true)
      if (pr_c) cb_set(true)
      pr_g = pr_c = false
   })

   // Сақтағанда: кэшбэк сомасы мен сыйлық белгісін қосып жібереміз (info жүктелмесе — ештеңе жібермейміз, бұрынғысы қалады)
   $.ajaxPrefilter(function (o) {
      if (!o.url || o.url.indexOf('cashbox_pay') < 0 || !info || !info.on) return
      if (typeof o.data != 'string') return
      o.data += '&lc=1&cashback=' + (cb_on ? cb_amt : 0) + '&gift=' + (gift_on ? 1 : 0) + '&gift_disc=' + (gift_on ? gd_amt : 0)
   })
})
