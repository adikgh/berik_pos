// start jquery
$(document).ready(function() {

	// btn_return_sn
	$('.btn_return_sn').on('click', function () {
		btn = $(this)
      $.ajax({
         url: "/orders/get.php?return_sn",
         type: "POST",
         dataType: "html",
         data: ({ id: btn.data('id'), }),
         success: function(data){ 
            if (data == 'yes') location.reload();
            console.log(data);
         },
         beforeSend: function(){ },
         error: function(data){ }
      })
	})



	// 
	$('.on_delete').on('click', function () {
		btn = $(this)
      $.ajax({
         url: "/orders/get.php?delete",
         type: "POST",
         dataType: "html",
         data: ({ id: btn.data('id'), }),
         success: function(data){ 
            if (data == 'yes') location.reload();
            console.log(data);
         },
         beforeSend: function(){ },
         error: function(data){ }
      })
	})


   // Карточкадағы тип / статус / курьер: бетті жаңартпай, тек сол карточка жаңарады
   function or_card_refresh(oid) {
      var card = $('.or_card[data-oid="' + oid + '"]')
      if (card.closest('.bl_ors').length) { $('.order_search_in').trigger('input'); return }
      $.ajax({ url: location.href, type: "GET", dataType: "html", cache: false,
         success: function(html){
            var doc = $('<div>').append($.parseHTML(html)) // скрипттер қайта орындалмайды
            var nc = doc.find('.bl_orm .or_card[data-oid="' + oid + '"]')
            if (nc.length) {
               var sl = card.find('.tp_sl')[0], tf = sl ? getComputedStyle(sl).transform : '' // тип фоны қай жерде тұрғаны
               card.replaceWith(nc)
               nc.find('.fr_price').mask('# ##0 тг', {reverse: true})
               nc.find('.fr_phone').mask('8 (700) 000-00-00')
               if (window.or_dd) or_dd(nc)
               // Жаңа карточкада тип фоны сол жерден жылжуын жалғастырады (анимация үзілмейді)
               var ns = nc.find('.tp_sl')[0]
               if (ns && tf && tf != 'none') { ns.style.transition = 'none'; ns.style.transform = tf; ns.offsetWidth; ns.style.transition = ''; ns.style.transform = '' }
            } else card.fadeOut(200, function(){ card.remove() }) // сүзгіге енді сай келмейді
            // Жоғарғы жалпы сомалар
            var re = /\$\('\.(pp_\w+)'\)\.html\('([^']*)'\)/g, m
            while ((m = re.exec(html))) $('.' + m[1]).html(m[2])
         },
         error: function(){ location.reload() }
      })
   }
   // Статус тарихы: оң жақ жоғарғы батырма, астынан шағын терезе
   $('html').on('click', '.or_hist', function (e) {
      e.stopPropagation()
      var b = $(this), pop = $('.oh_pop')
      if (!pop.length) pop = $('<div class="oh_pop"></div>').appendTo('body')
      if (pop.hasClass('oh_open') && pop.data('id') == b.data('id')) { pop.removeClass('oh_open'); return }
      var r = this.getBoundingClientRect(), w = 280
      var left = Math.max(10, Math.min(r.right - w, window.innerWidth - w - 10))
      var id = b.data('id')
      pop.data('id', id).css({ top: r.bottom + window.scrollY + 8, left: left + window.scrollX, width: w }).html(oh_cache[id] || '<div class="oh_load">...</div>').addClass('oh_open')
      // Кэште болса бірден көрсетеді, артынан тексеріп, өзгерсе ғана ауыстырады
      oh_get(id, function(html, changed){ if (pop.data('id') == id && changed) pop.html(html) })
   })
   // Тарих: алдын ала жүктеу (тышқан иконкаға келгенде) және кэш
   var oh_cache = {}, oh_wait = {}
   window.or_card_refresh = or_card_refresh; window.oh_cache = oh_cache // «Төледі» блогы үшін
   function oh_get(id, cb){
      if (oh_wait[id]) { oh_wait[id].push(cb); return } oh_wait[id] = [cb]
      $.ajax({ url: "/orders/get.php?status_log", type: "POST", dataType: "html", data: ({ id: id }),
         success: function(html){ var ch = oh_cache[id] !== html; oh_cache[id] = html; $.each(oh_wait[id], function(k, f){ f && f(html, ch) }) },
         complete: function(){ delete oh_wait[id] } })
   }
   $('html').on('mouseenter', '.or_hist', function () { var id = $(this).data('id'); if (!oh_cache[id]) oh_get(id) })
   $(document).on('click', function(e){ if (!$(e.target).closest('.oh_pop').length) $('.oh_pop').removeClass('oh_open') })
   // Тарих терезесі: Статус / Өзгерістер қойындылары
   $('html').on('click', '.oh_tab', function () {
      var t = $(this).attr('data-t'), pop = $(this).closest('.oh_pop')
      pop.find('.oh_tab').removeClass('oh_tab_act'); $(this).addClass('oh_tab_act')
      pop.find('.oh_pane').addClass('dsp_n').filter('[data-p="' + t + '"]').removeClass('dsp_n')
   })
   $(window).on('scroll resize', function(){ $('.oh_pop').removeClass('oh_open') })

   // Тағамдар тізімі: 2-еуден көп болса ашу/жабу
   $('html').on('click', '.or_more', function () {
      var b = $(this), card = b.closest('.or_card').toggleClass('or_items_open')
      b.find('span').text(card.hasClass('or_items_open') ? b.attr('data-less') : b.attr('data-more'))
   })

   var or_urls = { on_staff: 'change_staff', on_status: 'change_status', on_type: 'change_type' }
   $('html').on('change', '.on_staff, .on_status, .on_type', function () {
      var sel = $(this), oid = sel.attr('data-order-id'), act = ''
      $.each(or_urls, function(c, u){ if (sel.hasClass(c)) act = u })
      var card = sel.closest('.or_card')
      var opt = sel.children('option:selected')
      var kept = sel.hasClass('on_status') ? opt.attr('data-kept') : undefined // Отказ вне: бізде қалған сома
      var need_r = sel.hasClass('on_status') && opt.attr('data-refuse') == '1' // Отказ: себебі міндетті
      var cancel = function(){ or_card_refresh(oid) }
      var step2 = function(k){
         if (!need_r) return go(k, '')
         or_ask({ kind: 'reason', title: 'Отказ себебі', text: '№' + (card.find('.uc_uil2_nmb').first().text() || '') + ' тапсырысқа отказ қойылады. Себебін жазыңыз.', quick: ['Клиент бас тартты', 'Клиент телефон алмады', 'Мекенжай қате', 'Ұзақ күтті', 'Тағам біткен'], ok: 'Отказ қою' }, function(r){ go(k, r) }, cancel)
      }
      if (kept !== undefined) or_ask({ kind: 'sum', title: 'Бізде қалған сома', text: 'Тапсырыстан бізде қанша ақша қалды?', value: kept, ok: 'Растау' }, function(v){ step2(String(v).replace(/\D/g, '') || '0') }, cancel)
      else step2(undefined)
      function go(kept, reason) {
         if (sel.hasClass('on_status')) { var c = sel.children('option:selected').attr('data-clr'); if (c) card[0].style.setProperty('--st', c) }
         card.addClass('or_busy')
         delete oh_cache[oid]
         $.ajax({
            url: "/orders/get.php?" + act,
            type: "POST",
            dataType: "html",
            data: ({ id: sel.children('option:selected').attr('data-id'), order_id: oid, kept: kept === undefined ? '' : kept, reason: reason }),
            success: function(data){
               if (data == 'yes') or_card_refresh(oid)
               else if (data == 'locked') { mess('Жабық тапсырысты өзгертуге болмайды'); or_card_refresh(oid) }
               else if (data == 'only_courier') { mess('Бұл статусты тек курьер қояды'); or_card_refresh(oid) }
               else if (data == 'unpaid') { mess('Төленбеген тапсырысты аяқтауға болмайды'); or_card_refresh(oid) }
               else if (data == 'need_reason') { mess('Отказ себебін жазыңыз'); or_card_refresh(oid) }
               else if (data == 'by_courier') { mess('Статусты курьер қойды, өзгертуге болмайды'); or_card_refresh(oid) }
               else if (data == 'done') { mess('Курьер аяқтаған тапсырысқа отказ қоюға болмайды'); or_card_refresh(oid) }
               else if (data == 'bad_type') { mess('Бұл статус осы тапсырыс түріне жатпайды'); or_card_refresh(oid) }
               else if (data == 'no_courier') { mess('Алдымен курьерді таңдаңыз'); or_card_refresh(oid) }
               else { card.removeClass('or_busy'); console.log(data) }
            },
            error: function(){ card.removeClass('or_busy'); mess('Байланыс қатесі') }
         })
      }
   })
   
   
   
   // 
	$('.on_sort_status').on('change', function () {
      var val = $(this).children('option:selected').attr('data-id');
      const url = new URL(window.location);
      url.searchParams.set('status', val); 
      history.pushState(null, null, url);
      location.reload();
	})
   // 
	// $('.on_sort_time').on('change', function () {
   //    var val = $(this).children('option:selected').attr('data-val');
   //    const url = new URL(window.location);
   //    url.searchParams.set('time', val);
   //    history.pushState(null, null, url);
   //    location.reload();
	// })

   $('.on_sort_time').on('change', function () {
      var val = $(this).val();
      const url = new URL(window.location);
      url.searchParams.set('time', val);
      history.pushState(null, null, url);
      location.reload();
   })
   
   
   // 
	$('.on_sort_type').on('change', function () {
      var val = $(this).children('option:selected').attr('data-id');
      const url = new URL(window.location);
      url.searchParams.set('type', val); 
      history.pushState(null, null, url);
      location.reload();
	})

   // 
	$('.on_sort_staff').on('change', function () {
      var val = $(this).children('option:selected').attr('data-id');
      const url = new URL(window.location);
      url.searchParams.set('staff', val); 
      history.pushState(null, null, url);
      location.reload();
	})


   // 
	$('.on_sort_branch').on('change', function () {
      var val = $(this).children('option:selected').attr('data-id');
      const url = new URL(window.location);
      url.searchParams.set('branch', val); 
      history.pushState(null, null, url);
      location.reload();
	})


















   $('html').on('click', '.on_print', function () {
      window.open("/orders/" + "order_print.php?" + "&orderID=" + $(this).attr('data-id'), "mywin","width=570,height=570,left=250,top=50");
	})







   // cashbox_pay
	$('.on_info').click(function(){
		$('.on_info_block').addClass('pop_bl_act');
		$('#html').addClass('ovr_h');

      $.ajax({
         url: "/orders/info.php",
         type: "POST",
         dataType: "html",
         data: ({ 
            id: $(this).attr('data-id'),
         }),
         success: function(data){
            $('.osigoi').html(data)
         },
         beforeSend: function(){ },
         error: function(data){ }
      })
	})
	$('.on_info_back').click(function(){
		$('.on_info_block').removeClass('pop_bl_act');
		$('#html').removeClass('ovr_h');
	})














	// cashbox_pay
	$('.cashbox_pay').click(function(){
		$('.cashbox_pay_block').addClass('pop_bl_act');
		$('#html').addClass('ovr_h');
	})
	$('.cashbox_pay_back').click(function(){
		$('.cashbox_pay_block').removeClass('pop_bl_act');
		$('#html').removeClass('ovr_h');
	})

   // cashbox_pay
	$('.cashbox_pay2').on('click', function () {
		btn = $(this)
      $.ajax({
         url: "/orders/get.php?cashbox_pay",
         type: "POST",
         dataType: "html",
         data: ({ 
            number: $('.order_number_sel').attr('data-val'),
            total: $('.btype_totol').attr('data-val'),
            delivery: $('.btype_delivery').attr('data-val'),
            qr: $('.btype_qr').attr('data-val'),
         }),
         success: function(data){
            if (data == 'yes') location.reload();
            else if (data == 0) mess(w('Fill all fields', 'Барлық өрісті толтырыңыз'))
            console.log(data);
         },
         beforeSend: function(){ },
         error: function(data){ }
      })
	})








   // cashbox_search
   $('.order_search_in').on('input', function() {
      if ($('.order_search_in').val() == '') {
         $('.bl_ors').addClass('dsp_n')
         $('.bl_orm').removeClass('dsp_n')
      } else {

         $.ajax({
            url: "/orders/search.php?order_search",
            type: "POST",
            dataType: "html",
            data: ({ 
               result: $('.order_search_in').val(),
            }),
            success: function(data){
               
               $('.bl_ors').removeClass('dsp_n')
               $('.bl_orm').addClass('dsp_n')

               $('.bl_ors .uc_u').html(data)
               // Іздеу нәтижесі де тізімдегі жаңа карточкамен бірдей: маска мен әдемі таңдаулар
               var res = $('.bl_ors .uc_u')
               res.find('.fr_price').mask('# ##0 тг', {reverse: true})
               res.find('.fr_phone').mask('8 (700) 000-00-00')
               if (window.or_dd) or_dd(res.find('.or_card'))
               $('.lazy_img').lazy({effect:"fadeIn", effectTime:300, threshold:0})
            },
            beforeSend: function(){ },
            error: function(data){ }
         })
      }
   })














}) // end jquery
   // Аяқталған / Отказ тапсырысы: «Өзгерту» бұғатталған
   $('html').on('click', '.btn_lock', function(){ mess('Жабық тапсырысты өзгертуге болмайды') })


// ---------- Сүзгі жолағы: әдемі дата (← күн →, өз күнтізбесі) және ашылмалы тізімдер (админдегідей) ----------
$(document).ready(function () {
   var ru = ($('html').attr('lang') || '').indexOf('ru') === 0
   var MON = ru ? ['январь','февраль','март','апрель','май','июнь','июль','август','сентябрь','октябрь','ноябрь','декабрь'] : ['қаңтар','ақпан','наурыз','сәуір','мамыр','маусым','шілде','тамыз','қыркүйек','қазан','қараша','желтоқсан']
   var MON_G = ru ? ['января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря'] : MON
   var DAY = ru ? ['воскресенье','понедельник','вторник','среда','четверг','пятница','суббота'] : ['жексенбі','дүйсенбі','сейсенбі','сәрсенбі','бейсенбі','жұма','сенбі']
   var WD = ru ? ['Пн','Вт','Ср','Чт','Пт','Сб','Вс'] : ['Дс','Сс','Ср','Бс','Жм','Сб','Жс']
   var T_TODAY = ru ? 'Сегодня' : 'Бүгін'
   function ymd(d) { return d.getFullYear() + '-' + ('0' + (d.getMonth() + 1)).slice(-2) + '-' + ('0' + d.getDate()).slice(-2) }
   function parse(v) { var p = (v || '').split('-'); return p.length == 3 ? new Date(+p[0], +p[1] - 1, +p[2]) : null }
   function workToday() { return ymd(new Date(Date.now() - 6 * 3600 * 1000)) } // жұмыс күні 06:00-де басталады
   function nice(v) { var d = parse(v); return d ? d.getDate() + ' ' + MON_G[d.getMonth()] + ', ' + DAY[d.getDay()] : v }
   function cap(s) { return s.charAt(0).toUpperCase() + s.slice(1) }
   // Ашылатын бөлікті батырманың астына қою (fixed — жылжитын жолақ кесіп тастамайды)
   function place(el, anchor) {
      var r = anchor[0].getBoundingClientRect()
      el.css({ minWidth: r.width })
      var w = el.outerWidth()
      el.css({ top: r.bottom + 6, left: Math.max(8, Math.min(r.left, window.innerWidth - w - 8)) })
   }
   function close_all() { $('.bsel').removeClass('bsel_open'); $('.bdt').removeClass('bdt_open'); $('.bsel_list, .bcal').removeClass('pop_on') }

   $('.uc_ui69 input.on_sort_time').each(function () {
      var inp = $(this)
      if (inp.closest('.bdt').length) return
      var today = workToday(), sel = inp.val() || today
      var box = $('<div class="bdt"></div>')
      var main = $('<div class="bdt_main"><i class="fal fa-calendar-alt"></i><span class="bdt_txt"></span><i class="far fa-angle-down bdt_dd"></i></div>')
      var cal = $('<div class="bcal"></div>')
      inp.before(box)
      box.append('<div class="bdt_arr bdt_prev" title="' + (ru ? 'Предыдущий день' : 'Алдыңғы күн') + '"><i class="far fa-angle-left"></i></div>', main, '<div class="bdt_arr bdt_next" title="' + (ru ? 'Следующий день' : 'Келесі күн') + '"><i class="far fa-angle-right"></i></div>')
      $('body').append(cal) // карточкалардың астында қалмауы үшін body-ге
      main.append(inp)
      if (sel == today) box.addClass('bdt_is_today')
      main.find('.bdt_txt').text((sel == today ? T_TODAY + ' · ' : '') + nice(sel))
      function go(v) { if (v && v != inp.val()) inp.val(v).trigger('change') }
      box.on('click', '.bdt_prev, .bdt_next', function () {
         var d = parse(inp.val()) || new Date()
         d.setDate(d.getDate() + ($(this).hasClass('bdt_next') ? 1 : -1))
         go(ymd(d))
      })

      // Өз күнтізбесі
      var vm = parse(sel); vm.setDate(1)
      function draw() {
         var y = vm.getFullYear(), m = vm.getMonth()
         var h = '<div class="bcal_head"><div class="bcal_nav bcal_pm"><i class="far fa-angle-left"></i></div><b>' + cap(MON[m]) + ' ' + y + '</b><div class="bcal_nav bcal_nm"><i class="far fa-angle-right"></i></div></div><div class="bcal_grid">'
         $.each(WD, function (i, n) { h += '<span class="bcal_wd' + (i > 4 ? ' bcal_we' : '') + '">' + n + '</span>' })
         var start = new Date(y, m, 1), shift = (start.getDay() + 6) % 7
         start.setDate(1 - shift)
         for (var i = 0; i < 42; i++) {
            var d = new Date(start); d.setDate(start.getDate() + i)
            var v = ymd(d), c = 'bcal_d'
            if (d.getMonth() != m) c += ' bcal_out'
            if (v == today) c += ' bcal_tod'
            if (v == sel) c += ' bcal_sel'
            h += '<div class="' + c + '" data-v="' + v + '">' + d.getDate() + '</div>'
            if (i == 34 && d.getMonth() != m) break // 5 жол жетсе, 6-шы жол керек емес
         }
         h += '</div><div class="bcal_foot"><div class="bcal_today">' + T_TODAY + '</div></div>'
         cal.html(h)
      }
      main.on('click', function (e) {
         e.stopPropagation()
         var open = box.hasClass('bdt_open')
         close_all()
         if (!open) { vm = parse(sel); vm.setDate(1); draw(); place(cal, box); box.addClass('bdt_open'); cal.addClass('pop_on') }
      })
      cal.on('click', function (e) { e.stopPropagation() })
      cal.on('click', '.bcal_pm, .bcal_nm', function () { vm.setMonth(vm.getMonth() + ($(this).hasClass('bcal_nm') ? 1 : -1)); draw() })
      cal.on('click', '.bcal_d', function () { close_all(); go($(this).attr('data-v')) })
      cal.on('click', '.bcal_today', function () { close_all(); go(today) })
   })

   // Тип: ашылмалы тізім емес, батырмалар (иконка + сөз). Нағыз <select> жасырын қалады, басқанда соған 'change' жіберіледі
   var TP_ICO = {1: 'fa-car', 2: 'fa-walking', 3: 'fa-backpack', 4: 'fa-utensils'}
   $('.uc_ui69 select.on_sort_type').each(function () {
      var s = $(this)
      if (s.data('bsel')) return
      s.data('bsel', 1)
      var seg = $('<div class="bseg"></div>')
      s.after(seg); seg.append(s)
      s.find('option').each(function (i) {
         var id = $(this).attr('data-id'), txt = id ? $(this).text() : (ru ? 'Все' : 'Барлығы')
         var b = $('<div class="bseg_i"></div>').attr('data-i', i).attr('title', $(this).text()).toggleClass('bseg_act', this.selected)
         b.append(id ? '<i class="fal ' + (TP_ICO[id] || 'fa-tag') + '"></i>' : '<i class="fal fa-th-large"></i>').append($('<span></span>').text(txt))
         seg.append(b)
      })
      // Сырғитын жасыл фон (карточкадағы тип батырмалары сияқты)
      seg.prepend('<div class="bseg_sl"></div>')
      seg.css('--i', s.prop('selectedIndex'))
      seg.on('click', '.bseg_i', function () {
         var i = +$(this).attr('data-i')
         if (i == s.prop('selectedIndex')) return
         seg.find('.bseg_i').removeClass('bseg_act'); $(this).addClass('bseg_act')
         seg.css('--i', i)
         s.prop('selectedIndex', i)
         setTimeout(function () { s.trigger('change') }, 260) // алдымен сырғығаны көрінсін
      })
   })

   // Ашылмалы тізімдер: нағыз <select> жасырын қалады, таңдағанда соған 'change' жіберіледі (бұрынғы логика өзгермейді)
   $('.uc_ui69 select').each(function () {
      var s = $(this)
      if (s.data('bsel') || s.prop('multiple')) return
      s.data('bsel', 1)
      var wrap = $('<div class="bsel"></div>')
      s.after(wrap); wrap.append(s)
      var btn = $('<div class="bsel_btn"><span class="bsel_txt"></span><i class="far fa-angle-down"></i></div>')
      var list = $('<div class="bsel_list"></div>')
      wrap.append(btn); $('body').append(list) // карточкалардың астында қалмауы үшін body-ге
      function sync() {
         // data-clr болса (статус), жанында түсті белгі шығады
         function dot(o) { var c = $(o).attr('data-clr'); return c ? $('<em class="bsel_dot"></em>').css('background', c) : null }
         var so = s.find('option:selected')
         btn.find('.bsel_txt').text(so.text()).prepend(dot(so[0]))
         wrap.toggleClass('bsel_on', s.prop('selectedIndex') > 0)
         list.empty()
         s.find('option').each(function (i) { list.append($('<div class="bsel_i"></div>').text($(this).text()).prepend(dot(this)).attr('data-i', i).toggleClass('bsel_act', this.selected)) })
      }
      sync()
      btn.on('click', function (e) {
         e.stopPropagation()
         var open = wrap.hasClass('bsel_open')
         close_all()
         if (!open) { place(list, btn); wrap.addClass('bsel_open'); list.addClass('pop_on') }
      })
      list.on('click', '.bsel_i', function (e) {
         e.stopPropagation()
         var i = +$(this).attr('data-i')
         close_all()
         if (i == s.prop('selectedIndex')) return
         s.prop('selectedIndex', i); sync(); s.trigger('change')
      })
      s.on('change.bsel', sync)
   })
   $(document).on('click', close_all)
   $(window).on('resize scroll', close_all)
   $('.uc_ui69').on('scroll', close_all)
   if (window.hscroll) hscroll('.uc_ui69')
   $(document).on('keydown', function (e) { if (e.key == 'Escape') close_all() })
})

// Төленбеген тапсырыс: «Төледі» → төлем түрі → тапсырыс аяқталады
$(document).ready(function() {
   $('html').on('click', '.or_paid_btn', function (e) {
      e.stopPropagation()
      var w = $(this).closest('.or_paid')
      $('.or_paid').not(w).removeClass('or_paid_on or_paid_mxon or_paid_cson')
      w.toggleClass('or_paid_on'); w.removeClass('or_paid_mxon or_paid_cson')
   })
   function or_paid_send(w, d) {
      var oid = w.attr('data-id'), card = w.closest('.or_card')
      if (card.hasClass('or_busy')) return
      card.addClass('or_busy')
      d.order_id = oid
      $.ajax({
         url: "/orders/get.php?mark_paid",
         type: "POST",
         dataType: "html",
         data: d,
         success: function(data){
            if (data == 'yes') { mess('Төлем қабылданды, тапсырыс аяқталды'); if (window.oh_cache) delete window.oh_cache[oid]; window.or_card_refresh(oid) }
            else if (data == 'already') { mess('Бұл тапсырыс төленген'); window.or_card_refresh(oid) }
            else if (data == 'locked') { mess('Жабық тапсырысты өзгертуге болмайды'); window.or_card_refresh(oid) }
            else if (data == 'sum') { card.removeClass('or_busy'); mess('Сомалардың қосындысы жалпы сомаға тең болуы керек') }
            else { card.removeClass('or_busy'); mess('Қате, қайта көріңіз') }
         },
         error: function(){ card.removeClass('or_busy'); mess('Байланыс қатесі') }
      })
   }
   function or_mx_calc(w) {
      var tot = +w.attr('data-total') || 0, sum = 0, n = 0
      w.find('.or_paid_mx .or_mx_in').each(function(){ var v = +($(this).val() || '').replace(/\D/g, '') || 0; sum += v; if (v) n++ })
      var r = tot - sum
      w.find('.or_mx_rest b').text((r < 0 ? '−' : '') + Math.abs(r).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' тг')
      w.find('.or_mx_rest').toggleClass('or_mx_ok_r', r == 0).toggleClass('or_mx_bad', r < 0)
      w.find('.or_mx_ok').toggleClass('or_mx_dis', !(r == 0 && n >= 2))
   }
   $('html').on('click', '.or_paid_o', function (e) {
      e.stopPropagation()
      var w = $(this).closest('.or_paid'), m = $(this).attr('data-m')
      if (m == 'cash') { w.addClass('or_paid_cson'); w.find('.or_cs_in').val(''); w.find('.or_cs_b').removeClass('on'); or_cs_calc(w); return }
      if (m == 'mixed') { w.addClass('or_paid_mxon'); or_mx_calc(w); setTimeout(function(){ w.find('.or_paid_mx .or_mx_in').first().focus() }, 50); return }
      or_paid_send(w, { method: m })
   })
   $('html').on('input', '.or_mx_in', function () {
      var v = $(this).val().replace(/\D/g, ''); $(this).val(v ? (+v).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') : '')
      or_mx_calc($(this).closest('.or_paid'))
   })
   $('html').on('click', '.or_mx_back', function (e) { e.stopPropagation(); $(this).closest('.or_paid').removeClass('or_paid_mxon or_paid_cson') })
   // Қолма-қол: клиент берген сома және сдача
   function or_fmt(n) { return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' тг' }
   function or_cs_calc(w) {
      var tot = +w.attr('data-total') || 0, g = +(w.find('.or_cs_in').val() || '').replace(/\D/g, '') || 0
      var bx = w.find('.or_cs_ch'), ch = g - tot
      bx.find('b').text(g ? (ch >= 0 ? or_fmt(ch) : '−' + or_fmt(-ch)) : '0 тг')
      bx.toggleClass('or_mx_ok_r', g > 0 && ch >= 0).toggleClass('or_mx_bad', g > 0 && ch < 0)
   }
   $('html').on('click', '.or_cs_b', function (e) {
      e.stopPropagation()
      var w = $(this).closest('.or_paid')
      w.find('.or_cs_b').removeClass('on'); $(this).addClass('on')
      w.find('.or_cs_in').val(or_fmt(+$(this).attr('data-v')).replace(' тг', '')); or_cs_calc(w)
   })
   $('html').on('input', '.or_cs_in', function () { var w = $(this).closest('.or_paid'); w.find('.or_cs_b').removeClass('on'); or_cs_calc(w) })
   $('html').on('click', '.or_cs_ok', function (e) {
      e.stopPropagation()
      var w = $(this).closest('.or_paid'), tot = +w.attr('data-total') || 0, g = +(w.find('.or_cs_in').val() || '').replace(/\D/g, '') || 0
      if (g && g < tot) { mess('Клиент берген сома жетпейді'); return }
      or_paid_send(w, { method: 'cash' })
   })
   $('html').on('click', '.or_mx_ok', function (e) {
      e.stopPropagation()
      if ($(this).hasClass('or_mx_dis')) { mess('Сомалардың қосындысы жалпы сомаға тең болуы керек'); return }
      var w = $(this).closest('.or_paid'), d = { method: 'mixed' }
      w.find('.or_paid_mx .or_mx_in').each(function(){ d[$(this).attr('data-k')] = ($(this).val() || '').replace(/\D/g, '') || 0 })
      or_paid_send(w, d)
   })
   $(document).on('click', function (e) { if (!$(e.target).closest('.or_paid').length) $('.or_paid').removeClass('or_paid_on or_paid_mxon or_paid_cson') })
})


// Предзаказ: уақытына 20 минут қалғанда карточка үсті мен таймер қызыл болып жыпылықтайды,
// және сұрыптауға қарамай тізімнің басына шығады (ең жақыны бірінші)
// ?pre_demo=1 — көрсету үшін барлық ашық предзаказдарда бірден қосылады
$(document).ready(function() {
   var demo = /[?&]pre_demo=1/.test(location.search)
   function pre_soon() {
      var now = Date.now()
      $('.or_card[data-pre-left]').each(function(){
         var c = $(this)
         if (!c.attr('data-pre-due')) c.attr('data-pre-due', now + (+c.attr('data-pre-left') || 0) * 1000) // сервер уақытымен есептеледі
         var left = (+c.attr('data-pre-due') - now) / 1000
         c.toggleClass('or_pre_soon', demo || left <= 1200)
      })
      // Жыпылықтайтындарды өз тізімінің басына шығарамыз
      var groups = {}
      $('.or_card.or_pre_soon').each(function(){ var p = this.parentNode; (groups[p.__k || (p.__k = Math.random())] = groups[p.__k] || { p: p, a: [] }).a.push(this) })
      $.each(groups, function(k, g){
         g.a.sort(function(x, y){ return (+$(x).attr('data-pre-due') || 0) - (+$(y).attr('data-pre-due') || 0) })
         var cur = $(g.p).children('.or_card').slice(0, g.a.length).get(), same = true
         for (var i = 0; i < g.a.length; i++) if (cur[i] !== g.a[i]) same = false
         if (same) return // орны дұрыс болса, қозғамаймыз (анимация үзілмесін)
         for (var j = g.a.length - 1; j >= 0; j--) g.p.insertBefore(g.a[j], $(g.p).children('.or_card')[0])
      })
   }
   pre_soon(); setInterval(pre_soon, 5000)
   $(document).ajaxComplete(function(){ setTimeout(pre_soon, 50) }) // карточка жаңарғанда
})


// Өзіміздің стильдегі сұрау терезесі (prompt орнына): or_ask({ kind: 'reason' | 'sum', title, text, value, quick, ok }, onOk, onCancel)
function or_ask(o, onOk, onCancel) {
   $('.oask_bg').remove()
   var sum = o.kind == 'sum', esc = function(t){ return $('<div>').text(t || '').html() }
   var bg = $('<div class="oask_bg"><div class="oask' + (sum ? ' oask_sum' : ' oask_ref') + '">'
      + '<div class="oask_h"><span class="oask_ic"><i class="far ' + (sum ? 'fa-wallet' : 'fa-ban') + '"></i></span><div><b>' + esc(o.title) + '</b>' + (o.text ? '<small>' + esc(o.text) + '</small>' : '') + '</div><span class="oask_x"><i class="far fa-times"></i></span></div>'
      + (sum ? '<div class="oask_in_w"><input type="tel" class="oask_in" inputmode="numeric" placeholder="0"><span>тг</span></div>'
             : '<div class="oask_q"></div><textarea class="oask_in" rows="3" placeholder="Себебін жазыңыз..."></textarea>')
      + '<div class="oask_err"></div><div class="oask_btns"><span class="oask_no">Бас тарту</span><span class="oask_ok">' + esc(o.ok || 'Растау') + '</span></div></div></div>').appendTo('body')
   var inp = bg.find('.oask_in'), fmt = function(v){ v = String(v || '').replace(/\D/g, ''); return v ? (+v).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') : '' }
   if (sum) { inp.val(fmt(o.value)); inp.on('input', function(){ inp.val(fmt(inp.val())) }) }
   $.each(o.quick || [], function(i, q){ $('<span class="oask_qb"></span>').text(q).appendTo(bg.find('.oask_q')) })
   bg.on('click', '.oask_qb', function(){ bg.find('.oask_qb').removeClass('on'); $(this).addClass('on'); inp.val($(this).text()).focus(); bg.find('.oask_err').text('') })
   var done = false
   var close = function(ok){
      if (done) return
      var v = $.trim(inp.val())
      if (ok && !sum && !v) { bg.find('.oask_err').text('Себебін жазыңыз'); inp.focus(); return }
      done = true; bg.removeClass('oask_on'); setTimeout(function(){ bg.remove() }, 180)
      if (ok) onOk(sum ? v.replace(/\D/g, '') || '0' : v); else if (onCancel) onCancel()
   }
   bg.on('click', '.oask_ok', function(){ close(true) })
   bg.on('click', '.oask_no, .oask_x', function(){ close(false) })
   bg.on('mousedown', function(e){ if (e.target === bg[0]) close(false) })
   inp.on('keydown', function(e){ if (e.key == 'Enter' && (sum || !e.shiftKey)) { e.preventDefault(); close(true) } if (e.key == 'Escape') close(false) })
   setTimeout(function(){ bg.addClass('oask_on'); inp.focus(); if (sum) inp[0].select() }, 10)
}
