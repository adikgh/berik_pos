// Сайттан (QR меню / жеке кабинет) келген тапсырысты қабылдау: «Қабылдау» (card.php .or_acc) → терезе (orders/accept_pop.php)
// → предоплата + статус «Дайындалуда» (orders/get.php?accept_order) → чек (order_print.php)
$(document).ready(function() {
   var bg = $('.oacc_bg'), box = bg.find('.oacc'), st = null
   if (!bg.length) return
   var num = function(v){ return +String(v || '').replace(/\D/g, '') || 0 }
   var fmt = function(n){ return String(Math.round(n)).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') }
   var t = function(k, d){ return window.w ? w(k, d) : d }

   function open(b) {
      st = { id: b.attr('data-id'), total: +b.attr('data-total') || 0, type: +b.attr('data-type') || 0, unpaid: b.attr('data-unpaid') == '1', red: +b.attr('data-red') || 0, amt: 0, m: '' }
      st.cafe = !(st.type == 1 || st.type == 2)
      bg.find('.oacc_num').text(b.attr('data-num'))
      bg.find('.oacc_src').text(b.attr('data-src') || '')
      bg.find('.oacc_total').text(fmt(st.total) + ' тг')
      bg.find('.oacc_full').text(fmt(st.total) + ' тг')
      bg.find('.oacc_red_p').text(st.red)
      bg.find('.oacc_red_s').text(fmt(st.total + Math.round(st.total * st.red / 100)) + ' тг')
      bg.find('.oacc_in, .oacc_mx_in').val('')
      bg.find('.oacc_err').text('')
      bg.find('.oacc_print').addClass('oacc_pr_on')
      box.toggleClass('oacc_cafe', st.cafe).toggleClass('oacc_nopay', !st.unpaid).removeClass('oacc_busy')
      set_amt(0, '0')
      bg.addClass('oacc_vis'); setTimeout(function(){ bg.addClass('oacc_on') }, 10)
   }
   function close() {
      bg.removeClass('oacc_on'); setTimeout(function(){ bg.removeClass('oacc_vis') }, 180); st = null
   }

   // Предоплата сомасы: 0 / толық / басқа сома
   function set_amt(a, mode) {
      st.amt = Math.max(0, Math.min(a, st.total))
      bg.find('.oacc_ab').removeClass('on')
      if (mode != 'custom') bg.find('.oacc_ab[data-a="' + mode + '"]').addClass('on')
      if (st.amt <= 0 || (st.m == 'red' && st.amt != st.total)) set_m('')
      draw()
   }
   function set_m(m) {
      st.m = m
      bg.find('.oacc_mo').removeClass('on'); if (m) bg.find('.oacc_mo[data-m="' + m + '"]').addClass('on')
      box.toggleClass('oacc_mx_on', m == 'mix')
      if (m == 'mix') { mx_calc(); setTimeout(function(){ bg.find('.oacc_mx_in').first().focus() }, 50) }
   }
   function mx_sum() { var s = 0, n = 0; bg.find('.oacc_mx_in').each(function(){ var v = num($(this).val()); s += v; if (v) n++ }); return { s: s, n: n } }
   function mx_calc() {
      var r = st.amt - mx_sum().s, el = bg.find('.oacc_mx_rest')
      el.find('b').text((r < 0 ? '−' : '') + fmt(Math.abs(r)) + ' тг')
      el.toggleClass('oacc_ok_r', r == 0).toggleClass('oacc_bad', r < 0)
   }
   function draw() {
      box.toggleClass('oacc_m_on', st.amt > 0)
      bg.find('.oacc_red').toggleClass('oacc_dis', !(st.red > 0 && st.amt == st.total))
      var rl = bg.find('.oacc_rest_l'), rb = bg.find('.oacc_rest_s'), rest = st.total - st.amt
      if (!st.cafe) { rl.text(rl.attr('data-t' + st.type)); rb.text(fmt(rest) + ' тг'); bg.find('.oacc_rest').toggleClass('dsp_n', rest <= 0) }
      else { rl.text(rl.attr('data-t4')); rb.text(''); bg.find('.oacc_rest').toggleClass('dsp_n', st.amt > 0) }
      if (st.m == 'mix') mx_calc()
      bg.find('.oacc_err').text('')
   }

   $('html').on('click', '.or_acc', function (e) { e.stopPropagation(); open($(this)) })
   bg.on('click', '.oacc_ab', function () {
      var a = $(this).attr('data-a')
      bg.find('.oacc_in').val('')
      set_amt(a == 'full' ? st.total : 0, a)
   })
   bg.on('input', '.oacc_in', function () {
      var v = num($(this).val()); if (v > st.total) v = st.total
      $(this).val(v ? fmt(v) : '')
      set_amt(v, v == st.total ? 'full' : (v ? 'custom' : '0'))
   })
   bg.on('click', '.oacc_mo', function () { set_m($(this).attr('data-m')); bg.find('.oacc_err').text('') })
   bg.on('input', '.oacc_mx_in', function () { var v = num($(this).val()); $(this).val(v ? fmt(v) : ''); mx_calc() })
   bg.on('click', '.oacc_print', function () { $(this).toggleClass('oacc_pr_on') }) // «Чек шығару» белгісі
   bg.on('click', '.oacc_no, .oacc_x', close)
   bg.on('mousedown', function(e){ if (e.target === bg[0]) close() })
   $(document).on('keydown', function(e){ if (e.key == 'Escape' && bg.hasClass('oacc_vis')) close() })

   bg.on('click', '.oacc_ok', function () {
      if (!st || box.hasClass('oacc_busy')) return
      var err = bg.find('.oacc_err'), d = { order_id: st.id, amount: 0, method: '' }
      if (st.unpaid && st.amt > 0) {
         if (!st.m) { err.text(t('Choose how they paid', 'Немен төлегенін таңдаңыз')); return }
         if (st.m == 'mix') {
            var ms = mx_sum()
            if (ms.s != st.amt || ms.n < 2) { err.text(t('Parts must add up to the amount', 'Сомалардың қосындысы предоплатаға тең болуы керек')); return }
            bg.find('.oacc_mx_in').each(function(){ d[$(this).attr('data-k')] = num($(this).val()) })
         }
         d.amount = st.amt; d.method = st.m
      }
      // Чек терезесі басқан сәтте ашылады (кейін ашса браузер бөгеуі мүмкін), жауап келгенде чек жүктеледі
      var win = bg.find('.oacc_print').hasClass('oacc_pr_on') ? window.open('', 'mywin', 'width=570,height=570,left=250,top=50') : null
      var oid = st.id
      box.addClass('oacc_busy')
      $.ajax({
         url: '/orders/get.php?accept_order',
         type: 'POST',
         dataType: 'html',
         data: d,
         success: function(data){
            box.removeClass('oacc_busy')
            if (data == 'yes') {
               if (win) win.location.href = '/orders/order_print.php?&orderID=' + oid
               close(); mess(t('Order accepted', 'Тапсырыс қабылданды'))
               if (window.oh_cache) delete window.oh_cache[oid]
               if (window.or_card_refresh) window.or_card_refresh(oid); else location.reload()
               return
            }
            if (win) win.close()
            if (data == 'not_new') { close(); mess(t('The order is already accepted', 'Тапсырыс бұрын қабылданған')); if (window.or_card_refresh) window.or_card_refresh(oid) }
            else if (data == 'sum') err.text(t('Parts must add up to the amount', 'Сомалардың қосындысы предоплатаға тең болуы керек'))
            else if (data == 'red_full' || data == 'cafe_full') err.text(t('Only the full amount', 'Тек толық сомамен'))
            else err.text(t('Error, try again', 'Қате, қайта көріңіз'))
         },
         error: function(){ box.removeClass('oacc_busy'); if (win) win.close(); err.text(t('Connection error', 'Байланыс қатесі')) }
      })
   })
})
