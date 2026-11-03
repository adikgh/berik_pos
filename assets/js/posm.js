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

	// ---------- Төменгі мәзір ----------
	var $acc = $('.acc_menu').first()
	if ($('.aheader').length) {
		var br = (($('.mp_topi[href*="/orders/"]').attr('href') || '/orders/').split('?')[1] || '')
		var items = [
			['cashbox', '/cashbox/', 'fa-plus-circle', T('New order', 'Жаңа тапсырыс')],
			['orders', '/orders/' + (br ? '?' + br : ''), 'fa-receipt', T('Orders', 'Тапсырыстар')],
			['shift', '/shift/', 'fa-cash-register', T('Shift', 'Смена')],
			['more', 'javascript:;', 'fa-bars', T('Menu', 'Мәзір')]
		]
		var $nav = $('<nav class="posm_nav"></nav>')
		items.forEach(function (it) {
			$nav.append('<a class="posm_nav_i posm_nav_' + it[0] + (page == it[0] ? ' posm_nav_i_act' : '') + '" href="' + it[1] + '"><i class="' + (page == it[0] ? 'fas' : 'fal') + ' ' + it[2] + '"></i><span>' + it[3] + '</span></a>')
		})
		$nav.appendTo('body')
		// «Мәзір»: аккаунт мәзірі төменнен ашылады
		var $more = sheet('posm_more', T('Menu', 'Мәзір'))
		if ($acc.length) $more.append($acc)
		$nav.find('.posm_nav_more').on('click', function (e) { e.preventDefault(); open($more) })
		$(document).on('click', '.posm_more .acc_open', function () { close() })
	}

	// ---------- Кіру беті: менеджер таңдау + PIN-код пернетақтасы ----------
	if ($('.loginq_block').length) {
		document.body.classList.add('posm_pg_login')
		$('.usign_c').first().before('<div class="posm_brand"><img src="/assets/img/logo/berik-icon.svg" alt="" width="56" height="56"><div>BERIK <b>POS</b></div><small>' + T('Mobile cashbox', 'Касса · телефон нұсқасы') + '</small></div>')
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
		// Іздеу мен категориялар жоғарыда тұрып қалады
		var $top = $('<div class="posm_top"></div>')
		$('.hup_search').first().before($top)
		$top.append($('.hup_search').first()).append($('.hup_rct').first().parent())

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
