// start jquery
$(document).ready(function() {


   // 
   var cash_bl1_lq = new Swiper(".cash_bl1_lq", {
      slidesPerView: 'auto',
      spaceBetween: 20,
   });


	// cashbox_add
	$('html').on('click', '.cashbox_add', function () {
      btn = $(this)
      $.ajax({
         url: "/cashbox/get.php?cashbox_add",
         type: "POST",
         dataType: "html",
         data: ({
            id: btn.data('id'),
            oid: btn.data('oid'),
         }),
         success: function(data){ 
            if (data == 'yes') cart_refresh();
            // else if (data == 0) mess(w('Out of stock', 'Тауар қалмады'));
            else console.log(data);
            // console.log(data);
         },
         beforeSend: function(){ },
         error: function(data){ }
      })
	})


   // cashbox_search
   $('.catalog_ubd').on('click', function() {
      btn = $(this)

      $.ajax({
         url: "/cashbox/product_ct.php?catalog",
         type: "POST",
         dataType: "html",
         data: ({
            catalog_id: $(this).data('id'),
            cashbox_id: $(this).data('cashbox-id'),
         }),
         success: function(data){ 
            $('.hup_rcb').html(data)
            // $('.lazy_img').lazy({effect:"fadeIn", effectTime:300, threshold:0})
            console.log(data)

            $('.catalog_ubd').removeClass('hup_rcti_act')
            btn.addClass('hup_rcti_act')
         },
         beforeSend: function(){ },
         error: function(data){ }
      })
   })


   /* Санаттар карусель скролы: уақытша өшірулі, кейін қосамыз
   // Санаттар карусель: дөңгелек, сүйреу, белсенді санатқа жылжу
   ;(function () {
      var rct = $('.hup_rct'), el = rct[0]
      if (!el) return
      var act = rct.find('.hup_rcti_act')[0]
      if (act) el.scrollLeft = act.offsetLeft - 40
      el.addEventListener('wheel', function (e) {
         if (el.scrollWidth <= el.clientWidth) return
         if (Math.abs(e.deltaY) > Math.abs(e.deltaX)) { e.preventDefault(); el.scrollLeft += e.deltaY }
      }, { passive: false })
      var down = false, moved = false, startX = 0, startLeft = 0
      rct.on('mousedown', function (e) { down = true; moved = false; startX = e.pageX; startLeft = el.scrollLeft })
      $(document).on('mousemove', function (e) {
         if (!down) return
         var dx = e.pageX - startX
         if (!moved && Math.abs(dx) > 5) { moved = true; rct.addClass('hup_rct_drag') }
         if (moved) el.scrollLeft = startLeft - dx
      })
      $(document).on('mouseup', function () {
         if (!down) return
         down = false
         setTimeout(function () { rct.removeClass('hup_rct_drag') }, 0)
      })
   })()
   */


   // hup_search: кассада тауарды атауы бойынша іздеу
   var hupSearchTimer = null, hupSearchXhr = null
   function hupSearchReset() {
      $('.hup_search_clear').addClass('dsp_n')
      $('.hup_rct').removeClass('hup_rct_off')
      var act = $('.catalog_ubd.hup_rcti_act')
      if (act.length) act.trigger('click'); else $('.catalog_ubd').first().trigger('click')
   }
   $('.hup_search_inp').on('input', function () {
      var inp = $(this), q = $.trim(inp.val())
      clearTimeout(hupSearchTimer)
      if (q == '') { if (hupSearchXhr) hupSearchXhr.abort(); hupSearchReset(); return }
      $('.hup_search_clear').removeClass('dsp_n')
      $('.hup_rct').addClass('hup_rct_off')
      hupSearchTimer = setTimeout(function () {
         if (hupSearchXhr) hupSearchXhr.abort()
         hupSearchXhr = $.ajax({
            url: "/cashbox/product_ct.php?search",
            type: "POST",
            dataType: "html",
            data: ({ q: q, cashbox_id: inp.data('cashbox-id') }),
            success: function(data){ $('.hup_rcb').html(data) },
         })
      }, 250)
   })
   $('.hup_search_inp').on('keydown', function (e) {
      if (e.key === 'Escape') { $(this).val('').trigger('input') }
      if (e.key === 'Enter') {
         e.preventDefault()
         var items = $('.hup_rcb .cashbox_add')
         if (items.length == 1) items.first().trigger('click')
      }
   })
   $('.hup_search_clear').on('click', function () { $('.hup_search_inp').val('').trigger('input').focus() })
   $('.catalog_ubd').on('mousedown', function () {
      if ($('.hup_search_inp').val() != '') { $('.hup_search_inp').val(''); $('.hup_search_clear').addClass('dsp_n'); $('.hup_rct').removeClass('hup_rct_off') }
   })


   // cashbox_search
   $('.cashbox_search').on('input', function() {
      if ($('.cashbox_search').val() == '') {
         $('.cash_bl1_l').removeClass('cash_bl1_act')
         $('.cash_bl1_lm').removeClass('dsp_n')
      } else {
         $('.cash_bl1_lm').addClass('dsp_n')
         $('.cash_bl1_l').addClass('cash_bl1_act') 
         $.ajax({
            url: "/cashbox/get.php?cashbox_search",
            type: "POST",
            dataType: "html",
            data: ({
               inp: $('.cashbox_search').val(),
               oid: $('.cashbox_search').data('oid'),
            }),
            success: function(data){ 
               if (data == 'yes') cart_refresh();
               else if (data == 0) mess(w('Out of stock', 'Тауар қалмады'));
               else console.log(data);
            },
            beforeSend: function(){ },
            error: function(data){ }
         })
         $.ajax({
            url: "/cashbox/search.php?product_search",
            type: "POST",
            dataType: "html",
            data: ({ 
               result: $('.cashbox_search').val(),
               oid: $('.cashbox_search').data('oid'),
            }),
            success: function(data){
               $('.cash_bl1_lsr .uc_uc').html(data)
               $('.lazy_img').lazy({effect:"fadeIn", effectTime:300, threshold:0})
               console.log(data)
            },
            beforeSend: function(){ },
            error: function(data){ }
         })
      }
   })

	// cashbox_remove
	$('html').on('click', '.cashbox_remove', function () {
		btn = $(this)
      $.ajax({
         url: "/cashbox/get.php?cashbox_remove",
         type: "POST",
         dataType: "html",
         data: ({ id: btn.data('id'), }),
         success: function(data){ 
            if (data == 'yes') cart_refresh();
            else console.log(data);
         },
         beforeSend: function(){ },
         error: function(data){ }
      })
	})

   // cashbox_remove
	$('html').on('click', '.cashbox_plus', function () {
		btn = $(this)
      $.ajax({
         url: "/cashbox/get.php?cashbox_plus",
         type: "POST",
         dataType: "html",
         data: ({ id: btn.data('id'), }),
         success: function(data){ 
            if (data == 'yes') cart_refresh();
            else console.log(data);
         },
         beforeSend: function(){ },
         error: function(data){ }
      })
	})

   // cashbox_remove
	$('html').on('click', '.cashbox_minus', function () {
		btn = $(this)
      $.ajax({
         url: "/cashbox/get.php?cashbox_minus",
         type: "POST",
         dataType: "html",
         data: ({ id: btn.data('id'), }),
         success: function(data){ 
            if (data == 'yes') cart_refresh();
            else console.log(data);
         },
         beforeSend: function(){ },
         error: function(data){ }
      })
	})




   // cashbox_pr
	$('html').on('input', '.cashbox_pr', function () {
		btn = $(this)
      sum = btn.attr('data-val') * btn.parents('.uc_ui').attr('data-qn')
      btn.parent().siblings('.cashbox_sum').html(sum + ' тг')

      $.ajax({
         url: "/cashbox/get.php?cashbox_pr",
         type: "POST",
         dataType: "html",
         data: ({
            id: btn.parents('.uc_ui').data('id'),
            pr: btn.attr('data-val'),
         }),
         success: function(data){
            
            total = $('.cashbox_total').attr('data-total') - btn.parents('.uc_ui').attr('data-sum') + sum
            $('.cashbox_total').html(total + ' тг')

            btn.parents('.uc_ui').attr('data-pr', btn.attr('data-val'))
            btn.parents('.uc_ui').attr('data-sum', sum)
            $('.cashbox_total').attr('data-total', total)
            $('.cash_bl1_rb').removeClass('dsp_n')

            console.log(data);
         },
         beforeSend: function(){ },
         error: function(data){ }
      })
	})






   $('.sel_type .form_im_slo_i').click(function(){
      $('.sel_type .form_im_slo_i').removeClass('form_im_slo_act')
		$(this).toggleClass('form_im_slo_act')
      $('.sel_type').attr('data-val', $(this).attr('data-type'))
	})

   // Заказ түріне қарай өрістер: 1 Жеткізу — бәрі, 2 Собой — адрес/жеткізу жоқ, 4 Кафе — нөмір/адрес/жеткізу жоқ, 3 Сервис — жеткізу жоқ
   function sel_type_fields() {
      var t = Number($('.sel_type').attr('data-val')) || 1
      $('.tf_phone').toggleClass('dsp_n', t == 4 || t == 3)
      $('.tf_address').toggleClass('dsp_n', t == 2 || t == 4 || t == 3)
      $('.tf_dlv').toggleClass('dsp_n', t != 1)
      $('.sel_srv').toggleClass('dsp_n', t != 3)
      $('.tf_preorder').toggleClass('dsp_n', t == 4 || t == 3)
      $('.tf_add').toggleClass('dsp_n', t == 4) // Кафеде «Қосымша ақпарат» керек емес
      if (t == 3 && $('.form_im_slo_red').hasClass('form_im_slo_act')) $('.form_im_slo_red').click()
      // Кафеде RED батырмасы «Төлем түрі» қатарының ішінде тұрады
      var red_slo = $('.form_im_slo_red').parent()
      // Жеткізуде «Төлем түрі» жоқ (қалғанын курьер алады), сондықтан RED өз орнында
      if (t == 1 || t == 2) { $('.tf_red').append(red_slo).addClass('dsp_n') } // Жеткізуде RED предоплата түрлерінің ішінде
      else { $('.sel_pay_mix').parent().before(red_slo); $('.tf_red').addClass('dsp_n') }
      // Сервис: төлем сервис арқылы, предоплата мен қолма-қол керек емес
      if (t == 3 && Number($('.btype_qr').attr('data-val')) > 0) { $('.btype_qr').val('0 тг').attr('data-val', 0).trigger('input'); qr_chip_reset() }
      $('.cashbox_pay_btype, .cashbox_pay_bsem').toggleClass('dsp_n', t == 3 || t == 4)
      $('.tf_paytype').toggleClass('dsp_n', t == 3 || t == 1 || t == 2)
      $('.pay_box').toggleClass('dsp_n', t == 3)
      $('.srv_inp').toggleClass('dsp_n', t != 3 || !$('.sel_srv_other').hasClass('form_im_slo_act'))
      if (t != 1 && Number($('.btype_delivery').attr('data-val')) > 0) $('.sel_dlv .form_im_slo_i[data-type="0"]').click()
   }
   $('.sel_type .form_im_slo_i').click(sel_type_fields)
   setTimeout(sel_type_fields, 0)

   // Сервис түрі: Яндекс / Глово / Волт / Басқа (Басқа — өріс ашылады)
   $('.sel_srv .form_im_slo_i').click(function(){
      $('.sel_srv .form_im_slo_i').removeClass('form_im_slo_act')
      $(this).addClass('form_im_slo_act')
      if ($(this).attr('data-type') == 'other') {
         $('.srv_inp').removeClass('dsp_n')
         $('.sel_srv').attr('data-val', $.trim($('.btype_srv').val()))
         $('.btype_srv').focus()
      } else {
         $('.srv_inp').addClass('dsp_n')
         $('.sel_srv').attr('data-val', $(this).attr('data-type'))
      }
   })
   $('.btype_srv').on('input', function(){ $('.sel_srv').attr('data-val', $.trim($(this).val())) })


   // 
   $('.sel_branch .form_im_slo_i').click(function(){
      $('.sel_branch .form_im_slo_i').removeClass('form_im_slo_act')
		$(this).toggleClass('form_im_slo_act')
      $('.sel_branch').attr('data-val', $(this).attr('data-type'))
      pay_types_sync()
	})
   // Төлем түрлері точкадан: company_branch.pay_types = «transfer,qr,cash,mix,unpaid». Бос болса — барлығы
   function pay_types_sync() {
      var b = $('.sel_branch .form_im_slo_act'), list = $.trim(b.attr('data-pay') || '')
      if (!b.length) b = $('.sel_branch .form_im_slo_i').first(), list = $.trim(b.attr('data-pay') || '')
      var keys = list ? list.split(',').map(function(k){ return $.trim(k) }) : null
      $('.sel_pay .form_im_slo_i').not('.form_im_slo_red').each(function(){
         var hide = keys && keys.indexOf($(this).attr('data-type')) < 0
         $(this).parent().toggleClass('dsp_n', !!hide)
         if (hide && $(this).hasClass('form_im_slo_act')) { $(this).removeClass('form_im_slo_act'); $('.sel_pay').attr('data-val', ''); $('.pay_mix, .pay_cash').addClass('dsp_n') }
      })
   }
   pay_types_sync()





   // $('.cash_bl1_dc_slo_i').click(function(){
	// 	$('.cash_bl1_dc_slo_i').removeClass('form_im_slo_act')
	// 	$(this).addClass('form_im_slo_act')
   //    $('.form_im_slo').attr('data-type-name', $(this).attr('data-type'))
	// 	$(this).addClass('form_im_slo_act')
	// })
   // $('.cash_bl1_dc_slo_ic').click(function(){
   //    total = Number($(this).parent().attr('data-total'))
   //    total_min = Number($(this).parent().attr('data-val'))
   //    total = total - total_min + Number($(this).attr('data-val'))

   //    $(this).parent().attr('data-val', $(this).attr('data-val'))

   //    // $('.cashbox_pay_btype .btype_qr input').val(total + ' тг')
   //    // $('.cashbox_pay_btype .btype_qr input').attr('data-val', total)

   //    $('.cashbox_total').attr('data-total', total)
   //    $('.cashbox_total').html(total + ' тг')

   //    console.log(total);
	// })
   // $('.cash_bl1_dc_slo_ici').on('click', function(){
   //    total = Number($(this).parent().attr('data-total'))
   //    total_min = Number($(this).parent().attr('data-val'))
   //    $(this).parent().attr('data-val', 0)

   //    total_plus = Number($(this).children('input').attr('data-val'))
   //    total = total - total_min + total_plus

   //    // $(this).parent().attr('data-val', $(this).attr('data-val'))
   //    $('.cashbox_total').attr('data-total', total)
   //    $('.cashbox_total').html(total + ' тг')

   //    console.log(total);
	// })
   // $('.cash_bl1_dc_slo_i input').on('input', function(){
   //    total = Number($(this).parent().parent().attr('data-total'))
   //    total = total + Number($(this).attr('data-val'))

   //    if (total > 0) {
   //       $('.cashbox_total').attr('data-total', total)
   //       $('.cashbox_total').html(total + ' тг')
   //    } else {
   //       $('.cashbox_total').attr('data-total', 0)
   //       $('.cashbox_total').html(0 + ' тг')
   //    }

   //    console.log(total);
	// })







   
	// $('.form_im_slo_i').click(function(){
	// 	$('.form_im_slo_i').removeClass('form_im_slo_act')
	// 	$(this).addClass('form_im_slo_act')
   //    $('.form_im_slo').attr('data-type-name', $(this).attr('data-type'))
	// 	$(this).addClass('form_im_slo_act')

   //    total = $('.cashbox_pay_btotol_c').attr('data-val')

   //    $('.cashbox_pay_btype .form_im').addClass('dsp_n')
   //    // $('.cashbox_pay_bsem').addClass('dsp_n')
   //    $('.cashbox_pay_btype .form_im input').attr('disabled', '')
   //    $('.cashbox_pay_btype .btype_cash input').removeAttr('disabled')
   //    $('.cashbox_pay_btype .form_im input').val('')
   //    $('.cashbox_pay_btype .form_im input').attr('data-val', '')

   //    $('.cashbox_pay_bsemt').html(total + ' тг')
   //    $('.cashbox_pay_bsems').html(0 + ' тг')

   //    if ($(this).attr('data-type') == 'qr') {
   //       $('.cashbox_pay_btype .btype_qr').removeClass('dsp_n')
   //       $('.cashbox_pay_btype .btype_qr input').val(total + ' тг')
   //       $('.cashbox_pay_btype .btype_qr input').attr('data-val', total)
   //    } else if ($(this).attr('data-type') == 'cash') {
   //       $('.cashbox_pay_btype .btype_transfer').removeClass('dsp_n')
   //       $('.cashbox_pay_btype .btype_transfer input').val(total + ' тг')
   //       $('.cashbox_pay_btype .btype_transfer input').attr('data-val', total)
   //    // } else if ($(this).attr('data-type') == 'cash') {
   //    //    $('.cashbox_pay_btype .btype_cash').removeClass('dsp_n')
   //    //    $('.cashbox_pay_bsem').removeClass('dsp_n')
   //    } else if ($(this).attr('data-type') == 'card') {
   //       $('.cashbox_pay_btype .btype_card').removeClass('dsp_n')
   //       $('.cashbox_pay_btype .btype_card input').val(total + ' тг')
   //       $('.cashbox_pay_btype .btype_card input').attr('data-val', total)
   //    } else if ($(this).attr('data-type') == 'mixed') {
   //       $('.cashbox_pay_btype .form_im').removeClass('dsp_n')
   //       $('.cashbox_pay_btype .form_im input').removeAttr('disabled')
   //       $('.cashbox_pay_btype .btype_qr input').val(total + ' тг')
   //       $('.cashbox_pay_btype .btype_qr input').attr('data-val', total)
   //       // $('.cashbox_pay_bsem').removeClass('dsp_n')
   //    }

   //    // $('.btype_delivery').removeClass('dsp_n')
   //    // $('.btype_delivery input').removeAttr('disabled')

	// })

   $('html').on('input', '.btype_qr', function () {
      total = Number($('.cashbox_pay_btotol_c').attr('data-val'))
      sum = total - Number($('.btype_qr').attr('data-val'))         
      $('.btype_cash').html(sum + ' тг')
      $('.btype_cash').attr('data-val', sum)
   })


   // Предоплата: дайын сомалар, «Толық» — жалпы сома, «+» басқанда астынан өріс шығады
   function qr_chip_reset() {
      $('.sel_qr .form_im_slo_i').removeClass('form_im_slo_act')
      $('.sel_qr .form_im_slo_i[data-type="0"]').addClass('form_im_slo_act')
      $('.qr_inp').addClass('dsp_n')
   }
   $('.sel_qr .form_im_slo_i').click(function(){
      $('.sel_qr .form_im_slo_i').removeClass('form_im_slo_act')
      $(this).addClass('form_im_slo_act')
      var tp = $(this).attr('data-type')
      if (tp == 'add') {
         $('.qr_inp').removeClass('dsp_n')
         $('.btype_qr').val('').attr('data-val', 0).trigger('input').focus()
         $('.btype_qr').attr('data-val', 0)
      } else {
         var sum = tp == 'full' ? Number($('.cashbox_pay_btotol_c').attr('data-val')) || 0 : Number(tp) || 0
         $('.qr_inp').addClass('dsp_n')
         $('.btype_qr').val(sum).attr('data-val', sum).trigger('input')
         $('.btype_qr').attr('data-val', sum)
      }
   })
   $('html').on('input', '.btype_delivery', function () { qr_chip_reset() })
   // Предоплата немен төленді: тек предоплата > 0 болса шығады
   $('.sel_qrm .form_im_slo_i').click(function(){
      $('.sel_qrm .form_im_slo_i').removeClass('form_im_slo_act')
      $(this).addClass('form_im_slo_act')
      $('.sel_qrm').attr('data-val', $(this).attr('data-type'))
      $('.qrm_mix').toggleClass('dsp_n', $(this).attr('data-type') != 'mix')
      // RED таңдалса — RED пайызы қосылады, басқасы таңдалса — алынады
      var red_on = $(this).attr('data-type') == 'red', red_btn = $('.form_im_slo_red')
      if (($('.sel_type').attr('data-val') == 1 || $('.sel_type').attr('data-val') == 2) && red_btn.length && red_on != red_btn.hasClass('form_im_slo_act')) red_btn.click()
   })
   function qrm_mix_sum() { var n = 0; $('.btype_qrmix').each(function(){ n += Number($(this).attr('data-val')) || 0 }); return n }
   $('html').on('input', '.btype_qr', function(){ setTimeout(function(){ pay_mix_rest(); pay_change() }, 0) })
   function qrm_show() { var on = Number($('.btype_qr').attr('data-val')) > 0; $('.sel_qrm').toggleClass('dsp_n', !on); $('.qrm_mix').toggleClass('dsp_n', !on || $('.sel_qrm').attr('data-val') != 'mix') }
   $('html').on('input', '.btype_qr', function(){ setTimeout(qrm_show, 0) })
   $(document).on('click', '.sel_qr .form_im_slo_i, .sel_dlv .form_im_slo_i, .sel_type .form_im_slo_i', function(){ setTimeout(qrm_show, 0) })

   // Кафе: төлем түрі. «Аралас» басылса, әр түрге сома жазылады
   $('.sel_pay .form_im_slo_i').click(function(){
      // Кафеде RED те төлем түрі: басқасы таңдалса RED өшеді (пайызы алынады)
      var red_b = $('.form_im_slo_red')
      if (red_b.hasClass('form_im_slo_act') && !$(this).is(red_b)) red_b.click()
      $('.sel_pay .form_im_slo_i').not(red_b).removeClass('form_im_slo_act')
      $(this).addClass('form_im_slo_act')
      $('.sel_pay').attr('data-val', $(this).attr('data-type'))
      $('.pay_mix').toggleClass('dsp_n', $(this).attr('data-type') != 'mix')
      $('.pay_cash').toggleClass('dsp_n', $(this).attr('data-type') != 'cash')
      pay_change()
      pay_mix_rest()
   })
   function pay_mix_sum() {
      var sum = 0
      $('.btype_mix').each(function(){ sum += Number($(this).attr('data-val')) || 0 })
      return sum
   }
   // Төлем түрі арқылы төленетін сома: Кафеде — жалпы сома, Жеткізу/Өзімен-де — предоплатадан қалғаны
   function pay_rest() {
      var t = Number($('.sel_type').attr('data-val')) || 1
      var total = Number($('.cashbox_pay_btotol_c').attr('data-val')) || 0
      if (t == 4) return total
      return total - (Number($('.btype_qr').attr('data-val')) || 0)
   }
   function pay_mix_rest() {
      var rest = pay_rest() - pay_mix_sum()
      $('.pay_mix_rest').html(rest == 0 ? '' : (rest > 0 ? w('Left:', 'Қалды:') + ' ' : w('Over:', 'Артық:') + ' ') + Math.abs(rest) + ' тг').toggleClass('pay_mix_over', rest < 0)
   }
   $('html').on('input', '.btype_mix', function(){ setTimeout(pay_mix_rest, 0) })
   // Қолма-қол: клиент берген сома және сдача
   function fmt_tg(n) { return String(Math.round(n)).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' тг' }
   function pay_change() {
      var given = Number($('.btype_given').attr('data-val')) || 0
      var ch = given - pay_rest()
      $('.pay_change_c').html(given ? (ch >= 0 ? fmt_tg(ch) : w('short', 'жетпейді') + ' ' + fmt_tg(-ch)) : '0 тг')
      $('.pay_change').toggleClass('pay_change_bad', given > 0 && ch < 0)
   }
   $('.sel_given .form_im_slo_i').click(function(){
      $('.sel_given .form_im_slo_i').removeClass('form_im_slo_act')
      $(this).addClass('form_im_slo_act')
      var v = Number($(this).attr('data-type')) || 0
      $('.btype_given').val(v).attr('data-val', v).trigger('input')
      $('.btype_given').attr('data-val', v)
      pay_change()
   })
   $('html').on('input', '.btype_given', function(e){
      if (e.originalEvent) $('.sel_given .form_im_slo_i').removeClass('form_im_slo_act')
      setTimeout(pay_change, 0)
   })
   // Сақтауға жіберілетін төлем мәндері: Сервис — 0, Кафе — төлем түріне қарай (payment_method: transfer / qr / cash / mixed), қалғаны бұрынғыдай
   function pay_vals() {
      var t = Number($('.sel_type').attr('data-val')) || 1
      if (t == 3) return { qr: 0, cash: 0, transfer: 0, method: '' }
      var v = { qr: 0, cash: 0, transfer: 0, prepay: 0, prepay_method: '' }
      // Предоплата (Жеткізу / Өзімен): Аударым → pay_transfer, Kaspi QR → pay_qr
      if (t != 4) {
         var pre = Number($('.btype_qr').attr('data-val')) || 0
         // RED (Kaspi Red) сомасы pay_qr-ға қосылады, ал prepay_method-та «red» болып тұрады
         var m = $('.sel_qrm').attr('data-val') || 'qr', col = function(k){ return k == 'red' ? 'qr' : k }
         if (pre > 0) {
            v.prepay = pre; v.prepay_method = m
            if (m == 'mix') $('.btype_qrmix').each(function(){ v[col($(this).attr('data-name'))] += Number($(this).attr('data-val')) || 0 })
            else v[col(m)] += pre
         }
      }
      // Қалған сома — «Төлем түрі» бойынша (таңдалмаса — қолма-қол)
      // Жеткізуде қалғанын курьер алады — қолма-қол деп жазылады
      var rest = pay_rest(), pt = (t == 1 || t == 2 ? '' : $('.sel_pay').attr('data-val')) || 'cash'
      // Кафе: «Төленбеген» — ақша әлі түскен жоқ, кейін тапсырысты өзгертіп төлем қабылданады
      if (t == 4 && pt == 'unpaid') { v.method = 'unpaid'; return v }
      if (pt == 'mix') $('.btype_mix').each(function(){ v[$(this).attr('data-name')] += Number($(this).attr('data-val')) || 0 })
      else if (rest > 0) v[pt] += rest
      var kinds = ['transfer', 'qr', 'cash'].filter(function(k){ return v[k] > 0 })
      v.method = kinds.length > 1 ? 'mixed' : (kinds[0] || 'cash')
      return v
   }
   // «Толық» батырмасында жалпы сома тұрады, сома өзгерсе жаңарады
   function qr_full_label() {
      var t = Number($('.cashbox_pay_btotol_c').attr('data-val')) || 0
      $('.sel_qr_full').html(String(Math.round(t)).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' тг')
   }
   $(document).on('click input', '.cashbox_pay_block, .cashbox_pay', function(){ setTimeout(qr_full_label, 0) })


   // Жеткізу сомасы: батырмамен таңдау, «+» басқанда астынан өріс шығады
   $('.sel_dlv .form_im_slo_i').click(function(){
      $('.sel_dlv .form_im_slo_i').removeClass('form_im_slo_act')
      $(this).addClass('form_im_slo_act')
      if ($(this).attr('data-type') == 'add') {
         $('.dlv_inp').removeClass('dsp_n')
         $('.btype_delivery').val('').attr('data-val', 0).trigger('input').focus()
      } else {
         var sum = Number($(this).attr('data-type')) || 0
         $('.dlv_inp').addClass('dsp_n')
         $('.btype_delivery').val(sum).attr('data-val', sum).trigger('input')
         $('.btype_delivery').attr('data-val', sum)
      }
      $('.sel_dlv').attr('data-val', $('.btype_delivery').attr('data-val'))
   })


   $('html').on('input', '.btype_delivery', function () {
      delivery = Number($('.btype_delivery').attr('data-val'))
      sum = Number($('.cashbox_pay_btotol_c').attr('data-on-val'))
      $('.cashbox_pay_btotol_c').attr('data-val', delivery + sum)
      $('.cashbox_pay_btotol_c').html((delivery + sum) + ' тг')
      $('.btype_qr').val(0 + ' тг')
      $('.btype_qr').attr('data-val', 0)
      $('.cashbox_pay_bsemt').html((delivery + sum) + ' тг')
      $('.btype_cash').html(delivery + sum + ' тг')
      $('.btype_cash').attr('data-val', delivery + sum)
   })


   $('.form_im_slo_red').click(function(){
		// $('.form_im_slo_red').removeClass('form_im_slo_act')
      // $('.form_im_slo').attr('data-type-name', $(this).attr('data-type'))
		// $(this).addClass('form_im_slo_act')

      if (!$(this).hasClass('form_im_slo_act')) {
         sum = Number($('.cashbox_pay_btotol_c').attr('data-val'))
         $(this).attr('data-nall', sum)

         red_pct = Number($(this).attr('data-type')) || 10
         sum_red = Math.round(sum + (sum * red_pct / 100))
         $('.cashbox_pay_btotol_c').attr('data-val', sum_red)
         $('.cashbox_pay_btotol_c').html(sum_red + ' тг')
         $('.cashbox_pay_bsemt').html(sum_red + ' тг')
         $('.btype_cash').html(sum_red + ' тг')
         $('.btype_cash').attr('data-val', sum_red)
      } else {
         sum = Number($(this).attr('data-nall'))
         $('.cashbox_pay_btotol_c').attr('data-val', sum)
         $('.cashbox_pay_btotol_c').html(sum + ' тг')
         $('.cashbox_pay_bsemt').html(sum + ' тг')
         $('.btype_cash').html(sum + ' тг')
         $('.btype_cash').attr('data-val', sum)
      }

		$(this).toggleClass('form_im_slo_act')
	})
   // Кафе: RED таңдалса, басқа төлем түрлері өшеді (RED ақшасы Kaspi арқылы — qr болып жазылады)
   $('.form_im_slo_red').click(function(){
      if ($('.sel_type').attr('data-val') != 4) return
      var on = $(this).hasClass('form_im_slo_act')
      if (on) {
         $('.sel_pay .form_im_slo_i').not(this).removeClass('form_im_slo_act')
         $('.sel_pay').attr('data-val', 'qr')
         $('.pay_mix, .pay_cash').addClass('dsp_n')
      } else if (!$('.sel_pay .form_im_slo_i.form_im_slo_act').not(this).length) $('.sel_pay').attr('data-val', '')
      if (typeof pay_change == 'function') { pay_change(); pay_mix_rest() }
   })

   $('.btype_ynd').click(function(){
      if (!$(this).hasClass('form_im_slo_act')) {
         $(this).attr('data-val', 'ynd')
      } else {
         $(this).attr('data-val', '')
      }

		$(this).toggleClass('form_im_slo_act')
	})


   // $('html').on('input', '.cashbox_pay_btype input', function () {
   //    total = 0;
   //    $('.cashbox_pay_btype input').each(function () {
   //       total = total + Number($(this).attr('data-val'))         
   //    })
   //    $('.cashbox_pay_bsemt').html(total + ' тг')
   //    sum = Number($('.cashbox_pay_btotol_c').attr('data-val'))
   //    change = total - sum
   //    if (change > 0) $('.cashbox_pay_bsems').html(change + ' тг')
   // })


	// cashbox_pay
	$('html').on('click', '.cashbox_pay', function(){
		$('.cashbox_pay_block').addClass('pop_bl_act');
		$('#html').addClass('ovr_h');
	})
	$('.cashbox_pay_back').click(function(){
		$('.cashbox_pay_block').removeClass('pop_bl_act');
      $('.pop_usr_info').removeClass('pop_usr_info_act')
		$('#html').removeClass('ovr_h');
	})


   
   // cashbox_pay
   clc = 1;
	$('.cashbox_pay2').on('click', function () {
      if ($('.sel_type').attr('data-val') == 4) {
         if (!$('.sel_pay').attr('data-val')) { mess(w('Choose payment type', 'Төлем түрін таңдаңыз')); return }
      }
      if ($('.sel_type').attr('data-val') != 3 && $('.sel_type').attr('data-val') != 4 && Number($('.btype_qr').attr('data-val')) > 0 && $('.sel_qrm').attr('data-val') == 'mix' && qrm_mix_sum() != Number($('.btype_qr').attr('data-val'))) { mess(w('Prepay mix mismatch', 'Предоплата аралас сомасы предоплатаға тең болуы керек')); return }
      if ($('.sel_type').attr('data-val') == 4 && $('.sel_pay').attr('data-val') == 'mix' && pay_mix_sum() != pay_rest()) { mess(w('Pay mix mismatch', 'Аралас төлем сомасы төленетін сомаға тең болуы керек')); return }
      var pv = pay_vals()
      if (clc == 1) {
         btn = $(this)
         $.ajax({
            url: "/cashbox/get.php?cashbox_pay",
            type: "POST",
            dataType: "html",
            data: ({ 
               id: btn.data('id'),
               phone: $('.btype_phone').attr('data-val'),
               address: ($('.btype_address').attr('data-val') || '') + ($.trim($('.btype_kv').val() || '') ? ', кв. ' + $.trim($('.btype_kv').val()) : ''),
               add: $('.btype_add').attr('data-val'),
               preorder: $('.btype_preorder').attr('data-val'),
               total: $('.cashbox_pay_btotol_c').attr('data-val'),
               qr: pv.qr,
               transfer: pv.transfer,
               payment_method: pv.method,
               prepay: pv.prepay || 0,
               red: $('.form_im_slo_red').hasClass('form_im_slo_act') ? Math.round((Number($('.cashbox_pay_btotol_c').attr('data-val')) || 0) - (Number($('.form_im_slo_red').attr('data-nall')) || 0)) : 0,
               prepay_method: pv.prepay_method || '',
               cash: pv.cash,
               delivery: $('.btype_delivery').attr('data-val'),
               order_type: $('.sel_type').attr('data-val'),
               order_branch: $('.sel_branch').attr('data-val'),
               service: $('.sel_srv').attr('data-val') || '',
               type: btn.attr('data-type'),
            }),
            success: function(data){
               if (data == 'yes') {
                  draft_clear()
                  if (btn.attr('data-print') == '1') {
                     window.open("/orders/" + "order_print.php?" + "&orderID=" + btn.attr('data-id'), "mywin","width=570,height=570,left=250,top=50");
                  }
                  if (btn.attr('data-type') == 'ubd') location.href = '/orders/';
                  else location.reload();
               } else if (data == 0) mess(w('Fill all fields', 'Барлық өрісті толтырыңыз'))
               console.log(data);
            },
            beforeSend: function(){ },
            error: function(data){ }
         })
         clc++;
      } else mess(w('Please wait', 'Күтіңіз, тапсырма орындалуда'))
	})




   // function orderPrint(id,param) {
   //    tooltip("order_print_baloon_" + id);
   //    if(gs_print_frame) {
   //       $("#print_frame").attr("src","/orders/"+param+"&orderID="+id+"&noprint");
   //       $("#print_frame").load(function(){
   //          window.frames["print_frame"].focus();
   //          window.frames["print_frame"].print();
   //       });
   //    } else {
   //       window.open("/orders/"+param+"&orderID="+id,"mywin","width=570,height=570,left=250,top=50");
   //    }
   // }
   
	$('.pay_print').on('click', function () {
      window.open("/orders/" + "order_print.php?" + "&orderID=" + $(this).attr('data-id'), "mywin","width=570,height=570,left=250,top=50");
      // orderPrint($(this).attr('data-id'), 'order_print.php?')
      // location.reload();
	})





   // 
   // Нөмір толық жазылса, базадағы адресті автомат қою (адрес бос болса ғана)
   var addr_auto = ''
   $('html').on('input', '.btype_phone', function () {
      var ph = $(this).attr('data-val') || ''
      if ($(this).attr('data-sel') != 1 || ph.length < 11) return
      var a = $('.btype_address')
      if (!a.length) return
      if (a.val() != '' && a.val() != addr_auto) return
      $.ajax({
         url: "/cashbox/get.php?phone_addr",
         type: "POST",
         dataType: "json",
         data: ({ phone: ph }),
         success: function(r){
            if (!r || !r.address) return
            if (a.val() != '' && a.val() != addr_auto) return
            addr_auto = r.address
            a.val(r.address).attr('data-val', r.address).trigger('input')
            mess(w('Address filled', 'Бұрынғы адрес қойылды'))
         }
      })
   })

   $('html').on('input', '.btype_phone', function () {
      if ($('.btype_phone').attr('data-sel') == 0) {
         $('.pop_usr_info').removeClass('pop_usr_info_act')
      } else {
         $.ajax({
            url: "/cashbox/get.php?search_now",
            type: "POST",
            dataType: "html",
            data: ({ inp: $('.btype_phone').attr('data-val'), }),
            success: function(data){ 
               if (data == 'yes') {
                  $.ajax({
                     url: "/cashbox/search_now.php?search_now",
                     type: "POST",
                     dataType: "html",
                     data: ({ inp: $('.btype_phone').attr('data-val'), }),
                     success: function(dt){ 
                        $('.pop_usr_info').addClass('pop_usr_info_act')
                        $('.pop_usr_info_c').html(dt)
                     },
                     beforeSend: function(){ },
                     error: function(data){ }
                  })
               } else console.log(data);
            },
            beforeSend: function(){ },
            error: function(data){ }
         })
      }
   })
   



   // add product
	$('.product_add_pop').click(function(){
		$('.product_add_block').addClass('pop_bl_act');
		$('#html').addClass('ovr_h');
	})
	$('.product_add_back').click(function(){
		$('.product_add_block').removeClass('pop_bl_act');
		$('#html').removeClass('ovr_h');
	})
	$('html').on('click', '.price1_clc', function() { $('.price1_bl').toggleClass('price1_bl_act') });
   // $('.pr_img_add').click(function(){ $(this).siblings('.pr_img').click() })

	$('.product_add').on('click', function() {
		if ($('.pr_article').attr('data-sel') != 1) {
			// if ($('.pr_name').attr('data-sel') != 1) mess('Введите свой данный')
			if ($('.pr_article').attr('data-sel') != 1) mess('Введите свой данный')
		} else {
			$.ajax({
				url: "/cashbox/get.php?product_add",
				type: "POST",
				dataType: "html",
				data: ({
               article: $('.pr_article').attr('data-val'),
               barcode: $('.pr_barcode').attr('data-val'),
					quantity: $('.pr_quantity').attr('data-val'),
               price: $('.pr_price').attr('data-val'), 
					oid: $('.product_add').data('oid'),
               name: $('.pr_name').attr('data-val'),
				}),
				success: function(data){
					if (data == 'yes') {
						mess('Успешно')
						setTimeout(function() { location.reload(); }, 500);
					} else mess('Ошибка!'); console.log(data);
				},
				beforeSend: function(){ mess('Отправка..') },
				error: function(data){ }
			})
		}
	})




               
					// warehouses: $('.pr_warehouses').attr('data-val'),
               // catalog: $('.pr_catalog').attr('data-val'),
					// purchase_price: $('.pr_purchase_price').attr('data-val'),
               // discount_price: $('.pr_discount_price').attr('data-val'),
					// img: $('.pr_img').attr('data-val'),
               // brand: $('.pr_brand').attr('data-val'),
					// color: $('.pr_color').attr('data-val'),
               // size: $('.pr_size').attr('data-val'),




   // ---------- Төлем терезесінің деректері өшпеуі үшін: әр өзгерісте браузерге сақталады ----------
   var draft_key = 'cashbox_draft_' + ($('.cashbox_pay2').first().attr('data-id') || '0')
   var draft_busy = false
   var draft_inputs = ['.btype_phone', '.btype_address', '.btype_add', '.btype_preorder', '.btype_delivery', '.btype_qr', '.btype_given']
   function draft_clear() { try { localStorage.removeItem(draft_key) } catch (e) {} }
   function draft_save() {
      if (draft_busy || !$('.cashbox_pay_block').length) return
      var d = {
         type: $('.sel_type').attr('data-val'), branch: $('.sel_branch').attr('data-val'),
         dlv: $('.sel_dlv .form_im_slo_act').attr('data-type'), qr: $('.sel_qr .form_im_slo_act').attr('data-type'),
         qrm: $('.sel_qrm').attr('data-val'), pay: $('.sel_pay').attr('data-val'), srv: $('.sel_srv').attr('data-val'),
         given: $('.sel_given .form_im_slo_act').attr('data-type'),
         red: $('.form_im_slo_red').hasClass('form_im_slo_act'), f: {}, mix: {}
      }
      $.each(draft_inputs, function(i, sel){ d.f[sel] = [$(sel).val(), $(sel).attr('data-val')] })
      $('.btype_mix').each(function(){ d.mix[$(this).attr('data-name')] = [$(this).val(), $(this).attr('data-val')] })
      d.qrmix = {}; $('.btype_qrmix').each(function(){ d.qrmix[$(this).attr('data-name')] = [$(this).val(), $(this).attr('data-val')] })
      try { localStorage.setItem(draft_key, JSON.stringify(d)) } catch (e) {}
   }
   function draft_click(box, val) { if (val !== undefined && val !== null && val !== '') $(box + ' .form_im_slo_i[data-type="' + val + '"]').first().click() }
   function draft_set(sel, v) { if (v && v[0] !== undefined && v[0] !== '') $(sel).val(v[0]).attr('data-val', v[1]) }
   function draft_restore() {
      var d = null
      try { d = JSON.parse(localStorage.getItem(draft_key) || 'null') } catch (e) {}
      if (!d) return
      draft_busy = true
      draft_click('.sel_branch', d.branch)
      draft_click('.sel_type', d.type)
      $.each(['.btype_phone', '.btype_address', '.btype_add', '.btype_preorder'], function(i, sel){ draft_set(sel, d.f[sel]) })
      // Жеткізу (өзгерсе предоплата 0-ге түседі, сондықтан алдымен)
      draft_click('.sel_dlv', d.dlv)
      if (d.dlv == 'add') { draft_set('.btype_delivery', d.f['.btype_delivery']); $('.btype_delivery').trigger('input'); $('.btype_delivery').attr('data-val', (d.f['.btype_delivery'] || [])[1]) ; $('.btype_delivery').trigger('input') }
      if (d.red && !$('.form_im_slo_red').hasClass('form_im_slo_act')) $('.form_im_slo_red').click()
      // Предоплата
      draft_click('.sel_qr', d.qr)
      if (d.qr == 'add') { draft_set('.btype_qr', d.f['.btype_qr']); $('.btype_qr').trigger('input'); $('.btype_qr').attr('data-val', (d.f['.btype_qr'] || [])[1]); $('.btype_qr').trigger('input') }
      draft_click('.sel_qrm', d.qrm)
      $.each(d.qrmix || {}, function(k, v){ draft_set('.btype_qrmix[data-name="' + k + '"]', v) })
      qrm_show()
      draft_click('.sel_pay', d.pay)
      draft_click('.sel_srv', d.srv)
      $.each(d.mix || {}, function(k, v){ draft_set('.btype_mix[data-name="' + k + '"]', v) })
      draft_click('.sel_given', d.given)
      if (!d.given) draft_set('.btype_given', d.f['.btype_given'])
      draft_busy = false
      if (typeof pay_mix_rest == 'function') pay_mix_rest()
      if (typeof pay_change == 'function') pay_change()
      if (typeof qr_full_label == 'function') qr_full_label()
   }
   $(document).on('input change', '.cashbox_pay_block input', function(){ setTimeout(draft_save, 0) })
   $(document).on('click', '.cashbox_pay_block .form_im_slo_i', function(){ setTimeout(draft_save, 0) })
   setTimeout(draft_restore, 60)


   // ---------- Себетті бетті жаңартпай фонда жаңарту ----------
   var cart_busy = 0
   function cart_refresh() {
      cart_busy++
      $.ajax({ url: location.href, cache: false, dataType: 'html', success: function(html){
         if (--cart_busy > 0) return // бірнеше рет тез басылса, тек соңғысын қолданамыз
         var $h = $('<div>').append($.parseHTML(html))
         var sc = $('.cash_bl1_rc').scrollTop()
         $('.cash_bl1').html($h.find('.cash_bl1').html())
         $('.cash_bl1_rc').scrollTop(sc)
         if ($.fn.mask) { $('.cash_bl1 .fr_price').mask('# ##0 тг', {reverse: true}); $('.cash_bl1 .fr_number3').mask('# ##0 шт', {reverse: true}) }
         // Төлем терезесіндегі сомалар (жеткізу, предоплата, RED сақталады)
         var T = Number($h.find('.cashbox_pay_btotol_c').attr('data-on-val')) || 0
         var red = $('.form_im_slo_red'), red_on = red.hasClass('form_im_slo_act')
         if (red_on) red.click()
         var dl = Number($('.btype_delivery').attr('data-val')) || 0, pre = Number($('.btype_qr').attr('data-val')) || 0
         $('.cashbox_pay_btotol_c').attr('data-on-val', T).attr('data-val', T + dl).html((T + dl) + ' тг')
         $('.btype_cash').attr('data-val', T + dl - pre).html((T + dl - pre) + ' тг')
         if (red_on) red.click()
         if (typeof qr_full_label == 'function') qr_full_label()
         if (typeof pay_mix_rest == 'function') pay_mix_rest()
         if (typeof pay_change == 'function') pay_change()
      }, error: function(){ cart_busy = 0; location.reload() } })
   }


   // Қосымша өрістер (Қосымша ақпарат, Предзаказ): батырма басылғанда ғана ашылады
   function opt_sync() {
      $('.opt_btn').each(function(){
         var box = $($(this).attr('data-target')), inp = box.find('input')
         var on = !box.hasClass('opt_off')
         $(this).toggleClass('opt_btn_act', on).toggleClass('dsp_n', box.hasClass('dsp_n'))
      })
   }
   $('html').on('click', '.opt_btn', function(){
      var box = $($(this).attr('data-target'))
      box.toggleClass('opt_off')
      if (!box.hasClass('opt_off')) box.find('input').first().focus()
      opt_sync()
   })
   $(document).on('click', '.sel_type .form_im_slo_i', function(){ setTimeout(opt_sync, 0) })
   setTimeout(function(){
      // мәні бар болса (сақталған жазбадан) — ашық тұрады
      // Предзаказда күн өрісінде әрдайым бүгінгі күн тұрады, сондықтан тек дайын мәнге (btype_preorder) қараймыз
      $('.tf_add, .tf_preorder').each(function(){ var v = $(this).is('.tf_preorder') ? $('.btype_preorder').val() : $(this).find('input').val(); if (v && v !== '0') $(this).removeClass('opt_off') })
      opt_sync()
   }, 120)


   // Предзаказ: күн (Бүгін / Ертең / Басқа күн) + уақыт → btype_preorder = «YYYY-MM-DD HH:MM»
   function pre_sync() {
      var day = $('.sel_pre_day').attr('data-day') || $('.sel_pre_day .form_im_slo_act').attr('data-type')
      if (day == 'other') {
         // Предзаказ: ең көбі 1 апта алдыға
         var pd = $('.pre_date'), mn = pd.attr('min'), mx = pd.attr('max')
         if (pd.val() && mx && pd.val() > mx) pd.val(mx)
         if (pd.val() && mn && pd.val() < mn) pd.val(mn)
         day = pd.val()
      }
      var tm = $('.pre_time').val()
      var v = (day && tm) ? day + ' ' + tm : ''
      $('.btype_preorder').val(v).attr('data-val', v || 0)
      var today = $('.sel_pre_day .form_im_slo_i').first().attr('data-type')
      $('.pre_note').toggleClass('dsp_n', !(v && day > today))
   }
   $('.sel_pre_day .form_im_slo_i').click(function(){
      $('.sel_pre_day .form_im_slo_i').removeClass('form_im_slo_act')
      $(this).addClass('form_im_slo_act')
      $('.sel_pre_day').attr('data-day', $(this).attr('data-type'))
      $('.pre_date_im').toggleClass('dsp_n', $(this).attr('data-type') != 'other')
      if (!$('.pre_time').val()) $('.pre_time').focus()
      pre_sync(); setTimeout(draft_save, 0)
   })
   $('html').on('input change', '.pre_date, .pre_time', function(){ pre_sync(); setTimeout(draft_save, 0) })
   // Сақталған жазбадан қалпына келтіру
   function pre_restore() {
      var v = $('.btype_preorder').val()
      if (!v || v.length < 16) return
      var day = v.substr(0, 10), tm = v.substr(11, 5)
      var chip = $('.sel_pre_day .form_im_slo_i[data-type="' + day + '"]')
      if (chip.length) chip.click(); else { $('.pre_date').val(day); $('.sel_pre_day .sel_pre_other').click() }
      $('.pre_time').val(tm); pre_sync()
      $('.tf_preorder').removeClass('opt_off'); if (typeof opt_sync == 'function') opt_sync()
   }
   setTimeout(pre_restore, 100)

}) // end jquery

// Іздеу өрісі бос тұрғанда тауар атаулары анимациямен «жазылып-өшіріліп» тұрады
(function(){
   var inp = $('.hup_search_inp'); if (!inp.length) return
   var hints = []; try { hints = JSON.parse(inp.attr('data-hints') || '[]') } catch (e) {}
   if (!hints.length) return
   var base = inp.attr('placeholder'), pre = (inp.attr('data-hint-pre') || '') + ' '
   var i = 0, pos = 0, del = false, timer = null
   function idle() { return !inp.is(':focus') && !inp.val() }
   function tick() {
      if (!idle()) { inp.attr('placeholder', base); timer = setTimeout(tick, 600); return }
      var word = hints[i % hints.length], wait = 70
      if (!del) { pos++; if (pos >= word.length) { del = true; wait = 1600 } }
      else { pos -= 2; wait = 35; if (pos <= 0) { pos = 0; del = false; i++; wait = 400 } }
      inp.attr('placeholder', pre + word.slice(0, pos) + (del ? '' : '|'))
      timer = setTimeout(tick, wait)
   }
   inp.on('focus', function(){ inp.attr('placeholder', base) })
   timer = setTimeout(tick, 1200)
})();
