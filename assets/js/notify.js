// Сайттан (QR меню / жеке кабинет) келген жаңа тапсырыс хабары.
// Белгілері: block/modal.php (.onn_box, template.onn_tpl), «Тапсырыстар» батырмасындағы сан: block/menu.php (.onn_cnt), стилі: assets/css/notify.css
//  - экранда бір хабар тұрады; бірнеше келсе, ескілері кезекпен «Тапсырыстар»-ға ұшады, соңғысы қалады;
//  - 10 сек таймер тек бет белсенді болғанда (терезе алдыңғы жақта, тышқан бетте) жүреді, біткенде хабар «Тапсырыстар»-ға ұшады;
//  - дыбыс тапсырыс ашылғанша («Ашу» не «Тапсырыстар» беті) не статусы «Жаңа»-дан өзгергенше әр 5 сек шығады;
//  - рұқсат берілсе Windows хабарламасы.
// Әр 10 секунд сайын /orders/online.php сұралады.
$(document).ready(function() {

	var box = $('.onn_box')
	if (!box.length || window.__onNotify) return
	window.__onNotify = true

	var cid = box.data('cid') || 0
	var KEY = 'bp_online_last_' + cid, OPEN_KEY = 'bp_online_open_' + cid
	var LIFE = (box.data('life') || 10) * 1000, EVERY = 10000, RING = 5000, QUICK = 900
	var tpl = $('.onn_tpl').html()
	var btn = $('.mp_topi[href^="/orders/"]').first()
	var last = +get(KEY) || 0, cur = null, queue = [], flying = 0, srvCnt = 0, newIds = []
	var ringT = null, ac = null, busy = false, first = true, mouseIn = false, baseTitle = document.title



	// localStorage (жеке режимде қате берсе де бет істей берсін)
	function get(k) { try { return localStorage.getItem(k) } catch (e) { return null } }
	function set(k, v) { try { localStorage.setItem(k, v) } catch (e) {} }

	// «Ашу» басылған тапсырыстар (дыбыс солар үшін тоқтайды)
	function opened() { try { return JSON.parse(get(OPEN_KEY) || '[]') } catch (e) { return [] } }
	function markOpened(ids) {
		var o = opened()
		$.each(ids, function(i, id) { if (o.indexOf(id) < 0) o.push(id) })
		set(OPEN_KEY, JSON.stringify(o.slice(-300)))
		ring(false)
	}
	function pending() {
		var o = opened()
		return $.grep(newIds, function(id) { return o.indexOf(id) < 0 })
	}

	function money(v) { return String(v).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' ₸' }



	// ---------- Дыбыс (браузер адам бетке бір рет басқаннан кейін ғана рұқсат етеді) ----------
	function audio() {
		if (!ac) { var A = window.AudioContext || window.webkitAudioContext; if (A) try { ac = new A() } catch (e) {} }
		if (ac && ac.state === 'suspended') ac.resume()
		return ac
	}
	$(document).on('pointerdown keydown', audio)

	function tone(seq, vol) {
		var a = audio(); if (!a) return
		var t = a.currentTime
		$.each(seq, function(i, n) {
			var o = a.createOscillator(), g = a.createGain(), len = n[2] || .3
			o.type = 'sine'; o.frequency.value = n[0]
			g.gain.setValueAtTime(0.0001, t + n[1])
			g.gain.exponentialRampToValueAtTime(vol, t + n[1] + 0.02)
			g.gain.exponentialRampToValueAtTime(0.0001, t + n[1] + len)
			o.connect(g); g.connect(a.destination)
			o.start(t + n[1]); o.stop(t + n[1] + len + .02)
		})
	}
	function ding() { tone([[880, 0], [1320, .18], [880, .5], [1320, .68]], .5) }
	function plop() { tone([[1568, 0, .12], [2093, .07, .18]], .25) } // хабар «Тапсырыстар»-ға түскенде

	// now = true: бірден шырылдайды (жаңа тапсырыс келді); әйтпесе жүріп тұрған циклды бұзбаймыз
	function ring(now) {
		var n = pending().length
		if (!n) { clearTimeout(ringT); ringT = null; document.title = baseTitle; return }
		document.title = '(' + n + ') Жаңа тапсырыс!'
		if (ringT && !now) return
		clearTimeout(ringT)
		;(function loop() {
			if (!pending().length) { ringT = null; document.title = baseTitle; return }
			ding(); ringT = setTimeout(loop, RING)
		})()
	}



	// ---------- «Тапсырыстар» батырмасындағы сан ----------
	// Экранда тұрған, кезектегі не ұшып бара жатқан хабарлар әлі «түспеген», сондықтан саннан шегеріледі
	function renderBadge(bump) {
		var n = Math.max(0, srvCnt - (cur ? 1 : 0) - queue.length - flying)
		var b = btn.find('.onn_cnt')
		btn.toggleClass('onn_has', n > 0).attr('title', n ? 'Сайттан жаңа тапсырыс: ' + n : null)
		var v = n > 99 ? '99+' : String(n)
		if (bump && b.text() !== v) { b.removeClass('onn_bump'); void b[0].offsetWidth; b.addClass('onn_bump') }
		b.text(v)
	}



	// ---------- Бет белсенді ме: терезе алдыңғы жақта және тышқан бетте ----------
	$(document).on('mousemove', function() { mouseIn = true })
	$(document).on('mouseout', function(e) { if (!e.relatedTarget) mouseIn = false })
	$(window).on('blur', function() { mouseIn = false })
	function active() { return !document.hidden && document.hasFocus() && mouseIn }



	// ---------- Ұшу: доға бойымен «Тапсырыстар»-ға, кішірейіп, бұрылып ----------
	function curve(p, cx, cy, tx, ty) { var q = 1 - p; return [2 * q * p * cx + p * p * tx, 2 * q * p * cy + p * p * ty] }

	function fly(el, done) {
		if (!btn.length || !el[0].isConnected || !el[0].animate) { el.remove(); done && done(); return }
		var r = el[0].getBoundingClientRect(), t = btn[0].getBoundingClientRect()
		var f = el.clone().addClass('onn_fly').css({ left: r.left, top: r.top, width: r.width }).appendTo('body')
		var dot = $('<div class="onn_dot"></div>').css({ left: r.left + r.width / 2, top: r.top + r.height / 2 }).appendTo('body')
		el.remove(); flying++
		var tx = (t.left + t.width - 12) - (r.left + r.width / 2), ty = (t.top + 6) - (r.top + r.height / 2)
		var cx = tx * .35, cy = -90 // доғаның биіктігі
		var kf = [], dk = []
		for (var i = 0; i <= 12; i++) {
			var p = i / 12, xy = curve(p, cx, cy, tx, ty)
			var s = p < .15 ? 1 + p * .6 : Math.max(.04, 1.09 - (p - .15) * 1.25)
			kf.push({ transform: 'translate(' + xy[0] + 'px,' + xy[1] + 'px) scale(' + s + ') rotate(' + (-8 * p) + 'deg)', opacity: p < .8 ? 1 : 1 - (p - .8) * 4, filter: 'blur(' + (p > .6 ? (p - .6) * 4 : 0) + 'px)', offset: p })
			dk.push({ transform: 'translate(' + xy[0] + 'px,' + xy[1] + 'px)', opacity: p < .3 ? p * 3 : 1, offset: p })
		}
		dot[0].animate(dk, { duration: 900, easing: 'cubic-bezier(.55,0,.35,1)', fill: 'forwards' })
		f[0].animate(kf, { duration: 850, easing: 'cubic-bezier(.55,0,.35,1)', fill: 'forwards' }).onfinish = function() {
			f.remove(); dot.remove(); flying--
			btn.removeClass('onn_hit'); void btn[0].offsetWidth; btn.addClass('onn_hit')
			var rp = $('<span class="onn_ripple"></span>').appendTo(btn.addClass('onn_has'))
			setTimeout(function() { rp.remove() }, 750)
			plop(); renderBadge(true)
			done && done()
		}
	}



	// ---------- Хабар ----------
	function card(o) {
		var el = $(tpl).attr('data-id', o.id)
		el.find('.onn_src').text(o.src)
		el.find('.onn_num').text(o.number)
		el.find('.onn_type').text(o.type)
		el.find('.onn_sum').text(money(o.total))
		el.find('.onn_time').text(o.time)
		el.find('.onn_m').text(o.note || '')
		el.find('.onn_sec').text(LIFE / 1000)
		var C = 2 * Math.PI * 12
		el.find('.fg').attr({ 'stroke-dasharray': C, 'stroke-dashoffset': 0 })
		el.data('C', C)
		return el
	}

	// Windows хабарламасы (рұқсат берілсе)
	function osNote(o, el) {
		try {
			if (!window.Notification || Notification.permission !== 'granted') return
			var n = new Notification('Жаңа тапсырыс №' + o.number + ' · ' + o.src, { body: o.type + ' · ' + money(o.total) + (o.note ? '\n' + o.note : ''), tag: 'onn' + o.id, requireInteraction: true, icon: '/assets/img/logo/apple-touch-icon.png' })
			n.onclick = function() { window.focus(); n.close(); openOrder(o.id) }
			el.data('n', n)
		} catch (e) {}
	}

	// Экрандағы хабар «Тапсырыстар»-ға ұшады
	function dismiss(cb) {
		if (!cur) { cb && cb(); return }
		var el = cur; cur = null
		clearInterval(el.data('t'))
		fly(el, cb)
	}

	// Кезек: экранда бір хабар. Артында тағы бар болса, қазіргісі қысқа көрінеді де ұшады
	function next() {
		if (cur || !queue.length) return
		var o = queue.shift()
		var el = card(o).appendTo(box)
		cur = el
		osNote(o, el)
		if (queue.length) { setTimeout(function() { if (cur === el) dismiss(next) }, QUICK); return }

		var left = LIFE, hover = false, C = el.data('C')
		el.on('mouseenter', function() { hover = true }).on('mouseleave', function() { hover = false })
		el.data('t', setInterval(function() {
			if (queue.length) { if (cur === el) dismiss(next); return } // жаңасы келді — бұл ұшады
			var run = active() && !hover
			el.toggleClass('onn_pause', !run)
			if (!run) return
			left -= 250
			var p = Math.max(0, left / LIFE)
			el.find('.fg').attr('stroke-dashoffset', C * (1 - p))
			el.find('.onn_sec').text(Math.max(0, Math.ceil(left / 1000)))
			el.find('.onn_tm i').css('width', (p * 100) + '%')
			if (left <= 0 && cur === el) dismiss(next)
		}, 250))
	}

	function add(items) {
		$.each(items, function(i, o) {
			if (box.find('.onn[data-id="' + o.id + '"]').length || $.grep(queue, function(q) { return q.id === o.id }).length) return
			queue.push(o)
		})
		if (cur && queue.length) dismiss(next)
		else next()
		renderBadge(false)
	}

	// Батырмалар
	box.on('click', '.onn_x, .onn_ok', function() {
		var el = $(this).closest('.onn')
		try { el.data('n') && el.data('n').close() } catch (e) {}
		if (cur && el[0] === cur[0]) dismiss(next)
	})
	box.on('click', '.onn_open', function() {
		var el = $(this).closest('.onn')
		try { el.data('n') && el.data('n').close() } catch (e) {}
		openOrder(+el.attr('data-id'))
	})



	// ---------- Windows хабарламасына рұқсат (бір рет; тек https) ----------
	try {
		if (window.Notification && window.isSecureContext && Notification.permission === 'default' && !get('onn_perm_no')) $('.onn_perm').removeClass('dsp_n')
	} catch (e) {}
	$('.onn_perm_on').on('click', function() {
		try { Notification.requestPermission().then(function() { $('.onn_perm').remove() }) } catch (e) { $('.onn_perm').remove() }
	})
	$('.onn_perm_no').on('click', function() { set('onn_perm_no', '1'); $('.onn_perm').remove() })



	// ---------- Ашу ----------
	function openOrder(id) { markOpened([id]); location.href = '/orders/?hl=' + id }
	function onOrdersPage() { return /^\/orders\/?(index\.php)?$/.test(location.pathname) }

	var hl = /[?&]hl=(\d+)/.exec(location.search)
	if (hl) {
		var oc = $('.or_card[data-oid="' + hl[1] + '"]')
		if (oc.length) {
			oc[0].scrollIntoView({ block: 'center' }); oc.addClass('onn_hl')
			setTimeout(function() { oc.removeClass('onn_hl') }, 6000)
		}
	}



	// ---------- Сұрау ----------
	function poll() {
		if (busy) return
		busy = true
		$.ajax({
			url: '/orders/online.php',
			data: { after: last },
			dataType: 'json',
			cache: false,
			success: function(d) {
				if (!d || !d.ok) return
				var items = d.items || [], top = 0
				$.each(items, function(i, o) { if (o.id > top) top = o.id })
				last = Math.max(+d.last || 0, top)
				set(KEY, last)
				newIds = $.map(d.ids || [], Number)
				// «Тапсырыстар» бетін ашқанда, сол кездегі жаңа тапсырыстар көрінді деп саналады
				if (first && onOrdersPage()) markOpened($.grep(newIds, function(id) { return !$.grep(items, function(o) { return o.id === id }).length }))
				first = false
				srvCnt = +d.cnt || 0
				if (items.length) add(items)
				else renderBadge(false)
				ring(items.length > 0)
			},
			complete: function() { busy = false }
		})
	}
	poll()
	setInterval(poll, EVERY)

	// Басқа қойындыда ашылса, мұнда да дыбыс тоқтайды
	$(window).on('storage', function(e) {
		var k = e.originalEvent.key, v = e.originalEvent.newValue
		if (k === KEY && +v > last) last = +v
		if (k === OPEN_KEY) ring(false)
	})

})
