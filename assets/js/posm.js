// BERIK POS — кассаның телефон нұсқасы (posm.* домені)
// Бар беттерді өзгертпей, телефонға ыңғайлы құрылым жасайды:
// төменгі мәзір, себет батырмасы, төменнен ашылатын себет, «Мәзір» терезесі.
jQuery(function ($) {
	if (!document.body.classList.contains('posm')) return
	var path = location.pathname
	var page = /^\/cashbox/.test(path) ? 'cashbox' : /^\/orders/.test(path) ? 'orders' : /^\/shift/.test(path) ? 'shift' : 'other'
	document.body.classList.add('posm_pg_' + page)
	var T = function (k, d) { return (typeof w == 'function') ? w(k, d) : d }
	var fmt = function (n) { return String(Math.round(Number(n) || 0)).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' тг' }

	// ---------- Ортақ терезе ----------
	var $bd = $('<div class="posm_bd"></div>').appendTo('body'), openSheet = null
	function sheet(cls, title) {
		var $s = $('<div class="posm_sheet ' + cls + '"><div class="posm_sheet_hd"><h4>' + title + '</h4><div class="posm_x"><i class="fal fa-times"></i></div></div></div>').appendTo('body')
		$s.find('.posm_x').on('click', close)
		return $s
	}
	function open($s) { if (openSheet) openSheet.removeClass('posm_sheet_on'); openSheet = $s; $s.addClass('posm_sheet_on'); $bd.addClass('posm_bd_on'); $('body').addClass('posm_lock') }
	function close() { if (openSheet) openSheet.removeClass('posm_sheet_on'); openSheet = null; $bd.removeClass('posm_bd_on'); $('body').removeClass('posm_lock') }
	$bd.on('click', close)
	// Төмен сырғытып жабу
	$(document).on('touchstart', '.posm_sheet_hd', function (e) { var y0 = e.originalEvent.touches[0].clientY, $s = $(this).closest('.posm_sheet')
		$(document).on('touchmove.posm', function (ev) { var dy = ev.originalEvent.touches[0].clientY - y0; if (dy > 0) $s.css({ transform: 'translateY(' + dy + 'px)', transition: 'none' }) })
		$(document).one('touchend.posm', function (ev) { $(document).off('touchmove.posm'); var dy = ev.originalEvent.changedTouches[0].clientY - y0; $s.css({ transform: '', transition: '' }); if (dy > 80) close() })
	})

	// ---------- Навигация ----------
	// Төменгі мәзір жоқ: оң жақ астында тек «Тапсырыстар» батырмасы.
	// Тапсырыстар бетінде жоғарыда «+» (жаңа тапсырыс) және «Мәзір» батырмалары.
	var $acc = $('.acc_menu').first()
	if ($('.aheader').length) {
		var br = (($('.mp_topi[href*="/orders/"]').attr('href') || '/orders/').split('?')[1] || '')
		var $more = sheet('posm_more', T('Menu', 'Мәзір'))
		if ($acc.length) $more.append($acc)
		$(document).on('click', '.posm_more .acc_open', function () { close() })
		if (page == 'orders') {
			// Оң жақ астында: үстінде «+» (жаңа тапсырыс), астында «Мәзір»
			var $hb = $('<div class="posm_fabs"><a class="posm_fab posm_fab_add" href="/cashbox/" title="' + T('New order', 'Жаңа тапсырыс') + '"><i class="far fa-plus"></i></a><a class="posm_fab posm_fab_menu" href="javascript:;" title="' + T('Menu', 'Мәзір') + '"><i class="far fa-bars"></i></a></div>').appendTo('body')
			$hb.find('.posm_fab_menu').on('click', function (e) { e.preventDefault(); open($more) })
		} else {
			$('body').append('<a class="posm_fab" href="/orders/' + (br ? '?' + br : '') + '" title="' + T('Orders', 'Тапсырыстар') + '"><i class="far fa-receipt"></i></a>')
		}
	}

	// ---------- Кіру беті: менеджер таңдау + PIN-код пернетақтасы ----------
	if ($('.loginq_block').length) {
		document.body.classList.add('posm_pg_login')
		var hr = new Date().getHours(), hi = hr < 5 ? T('Good night', 'Қайырлы түн') : hr < 12 ? T('Good morning', 'Қайырлы таң') : hr < 18 ? T('Good afternoon', 'Қайырлы күн') : T('Good evening', 'Қайырлы кеш')
		$('.sbl2').first().prepend('<div class="posm_hero"><div class="posm_hero_lg"><img src="/assets/img/logo/berik-icon.svg" alt="" width="64" height="64"></div><div class="posm_hero_nm">BERIK <b>POS</b></div><div class="posm_hero_hi">' + hi + ' 👋</div><div class="posm_hero_sub">' + T('Choose your name and enter your code', 'Атыңызды таңдап, жеке кодыңызды енгізіңіз') + '</div></div>')
		// Менеджер карточкасы: аты бойынша бас әріп пен түс
		var pal = ['#0EA768', '#1f75a8', '#e08a00', '#7a5af5', '#d64545', '#0d9488', '#c2410c', '#2563eb']
		$('.sbl2_lroi').each(function (i) {
			var nm = $.trim($(this).find('p').text()), parts = nm.split(/[\s\-–]+/).filter(Boolean)
			var ini = ((parts[0] || '?')[0] + (parts[1] ? parts[1][0] : '')).toUpperCase(), h = 0
			for (var k = 0; k < nm.length; k++) h = (h * 31 + nm.charCodeAt(k)) >>> 0
			$(this).prepend('<span class="posm_av" style="--c:' + pal[h % pal.length] + '">' + ini + '</span>')
			$(this).find('p').wrap('<div class="posm_mn"></div>').after('<small>' + T('Tap to sign in', 'Кіру үшін басыңыз') + '</small>')
			$(this).css('animation-delay', (i * 50) + 'ms')
		})
		$('.sbl2').first().append('<div class="posm_foot"><i class="far fa-shield-check"></i> ' + T('Secure sign-in with personal code', 'Жеке кодпен қауіпсіз кіру') + '</div>')
		var $code = $('.loginq_block .code'), $pad = $('<div class="posm_pin"><div class="posm_pin_d"><i></i><i></i><i></i><i></i></div><div class="posm_pin_k"></div></div>')
		;['1','2','3','4','5','6','7','8','9','','0','del'].forEach(function (k) {
			$pad.find('.posm_pin_k').append(k === '' ? '<span></span>' : '<button type="button" class="posm_pin_b" data-k="' + k + '">' + (k == 'del' ? '<i class="far fa-backspace"></i>' : k) + '</button>')
		})
		$code.attr({ readonly: 'readonly', inputmode: 'none' }).closest('.form_im').after($pad)
		var dots = function () { var n = $code.val().length; $pad.find('.posm_pin_d i').each(function (i) { $(this).toggleClass('on', i < n) }) }
		$pad.on('click', '.posm_pin_b', function () {
			var k = $(this).data('k') + '', v = $code.val()
			if (k == 'del') v = v.slice(0, -1); else if (v.length < 4) v += k; else return
			$code.val(v).attr('data-sel', v.length ? 1 : 0); $('.password').attr('data-sel', v.length ? 1 : 0); dots()
			try { navigator.vibrate && navigator.vibrate(8) } catch (e) {}
			if (v.length == 4) { $code.trigger('input'); setTimeout(function () { $code.val(''); dots() }, 900) }
		})
		$('.loginq_clc').on('click', function () { $code.val(''); dots(); $('.loginq_block .head_c h4').text($(this).find('p').text().trim() || T('Enter code', 'Код-ты енгізіңіз')) })
	}

	// ---------- Жаңа тапсырыс ----------
	if (page == 'cashbox' && $('.hup_r').length) {
		// Категориялар жоғарыда тұрып қалады, іздеу экранның ең астында (бас бармақпен басуға ыңғайлы)
		var $top = $('<div class="posm_top"></div>')
		$('.hup_search').first().before($top)
		$top.append($('.hup_rct').first().parent())
		var $bot = $('<div class="posm_bot"></div>').appendTo('body')
		$bot.append($('.hup_search').first()).append($('.posm_fab'))
		// Телефон пернетақтасы ашылғанда іздеу оның үстінде тұрсын
		if (window.visualViewport) {
			var vv = window.visualViewport, kb = function () { var d = Math.max(0, window.innerHeight - vv.height - vv.offsetTop); document.body.style.setProperty('--pm-kb', d + 'px'); document.body.classList.toggle('posm_kb', d > 80) }
			vv.addEventListener('resize', kb); vv.addEventListener('scroll', kb)
		}
		// Іздегенде нәтиже басынан көрінсін
		$('.hup_search_inp').on('input', function () { if (window.scrollY > 0) window.scrollTo({ top: 0, behavior: 'instant' }) })

		// Себет терезесі
		var $cart = sheet('posm_cart', T('Cart', 'Себет') + '<small class="posm_cart_n"></small>')
		$cart.append($('.cash_bl1').first())

		// Себет батырмасы
		var $bar = $('<div class="posm_cbar posm_cbar_off"><div class="posm_cbar_ic"><i class="fas fa-shopping-basket"></i><span class="posm_cbar_n">0</span></div><div class="posm_cbar_t"><small>' + T('Cart', 'Себет') + '</small><b class="posm_cbar_s">0 тг</b></div><div class="posm_cbar_go"><span>' + T('Next', 'Әрі қарай') + '</span><i class="far fa-arrow-right"></i></div></div>').appendTo('body')
		$bar.on('click', function () { open($cart) })

		var lastN = -1
		function sync() {
			var n = 0, qn = {}
			$('.posm_cart .uc_ui').each(function () {
				var q = Number($(this).attr('data-qn')) || 0, id = $(this).attr('data-item-id'); n += q; qn[id] = (qn[id] || 0) + q
			})
			var tot = Number($('.posm_cart .cashbox_total').attr('data-total')) || 0
			$('.posm_cbar_n').text(n); $('.posm_cbar_s').text(fmt(tot)); $('.posm_cart_n').text(n ? '· ' + n : '')
			$bar.toggleClass('posm_cbar_off', n == 0)
			if (lastN >= 0 && n > lastN) { $bar.removeClass('posm_bump'); void $bar[0].offsetWidth; $bar.addClass('posm_bump') }
			if (n == 0 && openSheet && openSheet.is($cart)) close()
			lastN = n
			// Себеттегі тауардың санын карточкада көрсету
			$('.hup_rcb .cashbox_add').each(function () {
				var q = qn[$(this).attr('data-id')] || 0, $b = $(this).children('.posm_qn')
				$(this).toggleClass('posm_in', q > 0)
				if (q > 0) { if (!$b.length) $b = $('<span class="posm_qn"></span>').appendTo(this); $b.text(q) } else $b.remove()
			})
		}
		sync()
		// Себет (cart_refresh) не тауарлар тізімі (категория, іздеу) жаңарғанда
		var mo = new MutationObserver(function () { clearTimeout(mo.t); mo.t = setTimeout(sync, 30) })
		mo.observe($('.posm_cart .cash_bl1')[0], { childList: true, subtree: true, attributes: true, attributeFilter: ['data-total', 'data-qn'] })
		if ($('.hup_rcb')[0]) mo.observe($('.hup_rcb')[0], { childList: true })

		// Тауар басылғанда: карточка жыпылықтайды, телефон дірілдейді
		$(document).on('click', '.hup_rcb .cashbox_add', function () {
			var c = $(this); c.removeClass('posm_pop'); void this.offsetWidth; c.addClass('posm_pop')
			try { navigator.vibrate && navigator.vibrate(12) } catch (e) {}
		})
		// Категория таңдалғанда тізімнің басына
		$(document).on('click', '.catalog_ubd', function () {
			var el = this; el.scrollIntoView && el.scrollIntoView({ inline: 'center', block: 'nearest', behavior: 'smooth' })
			var y = $('.hup_rcb').offset().top - $top.outerHeight() - 4; if (window.scrollY > y) window.scrollTo({ top: y, behavior: 'smooth' })
		})
		var $act = $('.catalog_ubd.hup_rcti_act')[0]; if ($act && $act.scrollIntoView) $act.scrollIntoView({ inline: 'center', block: 'nearest' })

		// Телефонда чек шығарылмайды: тек сақтау
		$('.cashbox_pay2[data-print="1"]').each(function () {
			if ($(this).attr('data-type') == 'ubd') $(this).remove()
			else $(this).attr('data-print', '0').find('span').text(T('Save', 'Сақтау')).end().find('i').attr('class', 'far fa-check')
		})

		// «Сақтау» басылғанда себет жабылады, төлем терезесі ашылады
		$(document).on('click', '.posm_cart .cashbox_pay', function () { close() })

		// Тұтынушы жайлы (кэшбэк, соңғы заказдар): телефонда батырмадан ашылады
		var $ui = $('.pop_usr_info').first()
		if ($ui.length) {
			var $uib = $('<div class="posm_ui_btn"><i class="far fa-user-circle"></i><span>' + T('Customer info', 'Тұтынушы жайлы') + '</span><i class="far fa-chevron-up"></i></div>')
			$('.cashbox_pay_block .pop_bl_cl .form_c').first().prepend($uib)
			if (!$ui.find('.posm_x').length) $ui.find('.head_c').first().append('<div class="posm_x posm_ui_x"><i class="fal fa-times"></i></div>')
			$uib.on('click', function () { $ui.addClass('posm_ui_open') })
			$(document).on('click', '.posm_ui_x', function () { $ui.removeClass('posm_ui_open') })
			$(document).on('click', '.cashbox_pay_back', function () { $ui.removeClass('posm_ui_open') })
			new MutationObserver(function () { $uib.toggleClass('posm_ui_btn_on', $ui.hasClass('pop_usr_info_act')); if (!$ui.hasClass('pop_usr_info_act')) $ui.removeClass('posm_ui_open') })
				.observe($ui[0], { attributes: true, attributeFilter: ['class'] })
		}
	}
})
