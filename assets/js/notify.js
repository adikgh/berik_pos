// Сайттан (QR меню / жеке кабинет) келген жаңа тапсырыс: касса жоғарғы жағында хабар + дыбыс,
// «Тапсырыстар» батырмасында сан, рұқсат берілсе Windows хабарламасы.
// Әр 10 секунд сайын /orders/online.php сұралады. «Көрдім» немесе «Ашу» басылғанша дыбыс 20 сек сайын қайталанады.
(function () {
	if (window.__onNotify) return; window.__onNotify = true
	var KEY = 'bp_online_last_' + (window.BP_CID || 0), LIFE = 10000, srvCnt = 0, EVERY = 10000, RING = 20000, RING_MAX = 15
	var last = 0, box, ac, ringT, ringN = 0, baseTitle = document.title, busy = false

	function get(k) { try { return localStorage.getItem(k) } catch (e) { return null } }
	function set(k, v) { try { localStorage.setItem(k, v) } catch (e) {} }

	// Стиль (head-ке тиіспеу үшін осы жерде)
	var css = document.createElement('style')
	css.textContent = '' +
		'.onn_box{position:fixed;left:50%;top:14px;transform:translateX(-50%);z-index:99999;display:flex;flex-direction:column;gap:10px;width:420px;max-width:calc(100vw - 32px);font-family:Nunito,Arial,sans-serif}' +
		'.onn{position:relative;overflow:hidden;background:#fff;border-radius:16px;box-shadow:0 12px 40px rgba(0,0,0,.22);border-left:6px solid #0EA768;padding:14px 16px 16px 14px;animation:onn_in .35s cubic-bezier(.2,.9,.3,1.2)}' +
		'.onn_t{display:flex;align-items:center;gap:8px;font-size:13px;font-weight:800;color:#0EA768;text-transform:uppercase;letter-spacing:.3px}' +
		'.onn_t i{font-size:16px;animation:onn_bell 1s ease-in-out infinite}' +
		'.onn_x{margin-left:auto;color:#999;cursor:pointer;font-size:18px;line-height:1;padding:2px 4px}' +
		'.onn_n{font-size:22px;font-weight:800;color:#1a1a1a;margin:6px 0 2px}' +
		'.onn_d{font-size:15px;color:#333}.onn_d b{font-weight:800}' +
		'.onn_m{font-size:13px;color:#777;margin-top:4px}' +
		'.onn_b{display:flex;gap:8px;margin-top:12px}' +
		'.onn_tm{position:absolute;left:0;right:0;bottom:0;height:4px;background:#e7f7ef}.onn_tm i{display:block;height:100%;width:100%;background:#0EA768;transform-origin:left;animation:onn_tm linear forwards}' +
		'.onn:hover .onn_tm i{animation-play-state:paused}' +
		'.onn_sec{margin-left:6px;font-size:12px;font-weight:700;color:#999;text-transform:none;letter-spacing:0}' +
		'.onn_fly{position:fixed;z-index:100000;pointer-events:none;transition:transform .7s cubic-bezier(.5,0,.3,1),opacity .7s ease;transform-origin:center}' +
		'.onn_cnt.onn_bump{animation:onn_bump .5s ease}' +
		'@keyframes onn_tm{from{transform:scaleX(1)}to{transform:scaleX(0)}}' +
		'@keyframes onn_bump{0%{transform:scale(1)}40%{transform:scale(1.7)}100%{transform:scale(1)}}' +
		'.onn_b span{flex:1;text-align:center;padding:10px;border-radius:10px;font-weight:700;cursor:pointer;font-size:15px}' +
		'.onn_open{background:#0EA768;color:#fff}.onn_ok{background:#f1f3f2;color:#333}' +
		'.or_src{display:inline-block;margin-left:6px;padding:2px 7px;border-radius:7px;background:#e7f7ef;color:#0EA768;font-size:11px;font-weight:800;vertical-align:middle;white-space:nowrap}' +
		'.or_card.onn_hl{box-shadow:0 0 0 3px #0EA768 !important;transition:box-shadow .3s}' +
		'@keyframes onn_in{from{opacity:0;transform:translateY(-40px)}to{opacity:1;transform:none}}' +
		'.onn_cnt{position:absolute;top:-7px;right:-7px;min-width:20px;height:20px;padding:0 5px;border-radius:10px;background:#E5484D;color:#fff;font-size:12px;font-weight:800;line-height:20px;text-align:center;box-shadow:0 0 0 2px #fff;animation:onn_pulse 1.6s ease-in-out infinite}' +
		'.mp_topi.onn_has{position:relative}' +
		'@keyframes onn_pulse{0%,100%{transform:scale(1)}50%{transform:scale(1.15)}}' +
		'.onn_perm{display:flex;align-items:center;gap:10px;background:#1f2937;color:#fff;border-radius:14px;padding:10px 12px;font-size:14px;box-shadow:0 10px 30px rgba(0,0,0,.25)}' +
		'.onn_perm b{flex:1;font-weight:700}.onn_perm span{background:#0EA768;border-radius:9px;padding:7px 12px;font-weight:800;cursor:pointer;white-space:nowrap}.onn_perm i{cursor:pointer;opacity:.7;font-style:normal;padding:0 4px}' +
		'@keyframes onn_bell{0%,100%{transform:rotate(0)}25%{transform:rotate(-15deg)}75%{transform:rotate(15deg)}}'
	document.head.appendChild(css)

	// Дыбыс: браузер дыбысты адам бетке бір рет басқаннан кейін ғана рұқсат етеді
	function audio() {
		if (!ac) { var A = window.AudioContext || window.webkitAudioContext; if (A) try { ac = new A() } catch (e) {} }
		if (ac && ac.state === 'suspended') ac.resume()
		return ac
	}
	function unlock() { audio() }
	document.addEventListener('pointerdown', unlock, true)
	document.addEventListener('keydown', unlock, true)

	function ding() {
		var a = audio(); if (!a) return
		var t = a.currentTime
		;[[880, 0], [1320, .18], [880, .5], [1320, .68]].forEach(function (n) {
			var o = a.createOscillator(), g = a.createGain()
			o.type = 'sine'; o.frequency.value = n[0]
			g.gain.setValueAtTime(0.0001, t + n[1])
			g.gain.exponentialRampToValueAtTime(0.5, t + n[1] + 0.02)
			g.gain.exponentialRampToValueAtTime(0.0001, t + n[1] + 0.3)
			o.connect(g); g.connect(a.destination)
			o.start(t + n[1]); o.stop(t + n[1] + 0.32)
		})
	}
	function ring() {
		clearTimeout(ringT)
		var n = box ? box.querySelectorAll('.onn').length : 0
		if (!n) { ringN = 0; document.title = baseTitle; return }
		ding(); ringN++
		document.title = '(' + n + ') Жаңа тапсырыс!'
		if (ringN < RING_MAX) ringT = setTimeout(ring, RING)
	}

	function money(v) { return String(v).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' ₸' }
	function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c] }) }

	// fly = true: хабар «Тапсырыстар» батырмасына ұшып барып, сан +1 болады
	function close(el, fly) {
		if (el._gone) return; el._gone = true
		clearInterval(el._t)
		try { if (el._n && !fly) el._n.close() } catch (e) {}
		var a = fly && document.querySelector('.mp_topi[href^="/orders/"]')
		if (a) {
			var r = el.getBoundingClientRect(), t = a.getBoundingClientRect()
			var f = el.cloneNode(true); f.classList.add('onn_fly'); f.style.animation = 'none'
			f.style.left = r.left + 'px'; f.style.top = r.top + 'px'; f.style.width = r.width + 'px'; f.style.margin = '0'
			document.body.appendChild(f)
			el.remove()
			var dx = (t.left + t.width - 10) - (r.left + r.width / 2), dy = (t.top + 4) - (r.top + r.height / 2)
			requestAnimationFrame(function () { requestAnimationFrame(function () {
				f.style.transform = 'translate(' + dx + 'px,' + dy + 'px) scale(.06)'; f.style.opacity = '.3'
			}) })
			setTimeout(function () { f.remove(); renderBadge(true) }, 720)
		} else { el.remove(); renderBadge(false) }
		if (!box.querySelector('.onn')) { clearTimeout(ringT); ringN = 0; document.title = baseTitle }
	}

	function mkbox() { if (!box) { box = document.createElement('div'); box.className = 'onn_box'; document.body.appendChild(box) } return box }

	// «Тапсырыстар» батырмасындағы сан (әлі «Жаңа» тұрған сайт тапсырыстары)
	function badge(n) { srvCnt = n; renderBadge(false) }
	function renderBadge(bump) {
		var open = box ? box.querySelectorAll('.onn').length : 0, n = Math.max(0, srvCnt - open)
		var a = document.querySelector('.mp_topi[href^="/orders/"]'); if (!a) return
		var b = a.querySelector('.onn_cnt')
		if (!n) { if (b) b.remove(); a.classList.remove('onn_has'); return }
		if (!b) { b = document.createElement('span'); b.className = 'onn_cnt'; a.appendChild(b); a.classList.add('onn_has') }
		var v = n > 99 ? '99+' : String(n)
		if (b.textContent !== v && bump) { b.classList.remove('onn_bump'); void b.offsetWidth; b.classList.add('onn_bump') }
		b.textContent = v
		a.title = 'Сайттан жаңа тапсырыс: ' + n
	}

	// Windows хабарламасына рұқсат сұрайтын жолақ (бір рет; тек https-те жұмыс істейді)
	function askPerm() {
		try {
			if (!window.Notification || !window.isSecureContext || Notification.permission !== 'default' || get('onn_perm_no')) return
		} catch (e) { return }
		var p = document.createElement('div'); p.className = 'onn_perm'
		p.innerHTML = '<b>🔔 Сайттан тапсырыс келгенде Windows хабарламасы шықсын ба?</b><span>Қосу</span><i title="Кейін">×</i>'
		p.querySelector('span').onclick = function () { try { Notification.requestPermission().then(function () { p.remove() }) } catch (e) { p.remove() } }
		p.querySelector('i').onclick = function () { set('onn_perm_no', '1'); p.remove() }
		mkbox().insertBefore(p, box.firstChild)
	}

	function show(o) {
		mkbox()
		if (box.querySelector('[data-id="' + o.id + '"]')) return
		var el = document.createElement('div')
		el.className = 'onn'; el.setAttribute('data-id', o.id)
		el.innerHTML = '<div class="onn_t"><i class="far fa-bell"></i>Сайттан жаңа тапсырыс · ' + esc(o.src) + '<span class="onn_sec">' + (LIFE / 1000) + ' с</span><span class="onn_x" title="Жабу">×</span></div>' +
			'<div class="onn_n">№' + esc(o.number) + '</div>' +
			'<div class="onn_d">' + esc(o.type) + ' · <b>' + money(o.total) + '</b> · ' + esc(o.time) + '</div>' +
			(o.note ? '<div class="onn_m">' + esc(o.note) + '</div>' : '') +
			'<div class="onn_b"><span class="onn_open">Ашу</span><span class="onn_ok">Көрдім</span></div>' +
			'<div class="onn_tm"><i style="animation-duration:' + LIFE + 'ms"></i></div>'
		el.querySelector('.onn_x').onclick = el.querySelector('.onn_ok').onclick = function () { try { if (el._n) el._n.close() } catch (e) {} close(el, true) }
		el.querySelector('.onn_open').onclick = function () { close(el); openOrder(o.id) }
		box.appendChild(el)
		// 10 секунд таймер: тышқан үстінде тұрса тоқтайды, біткенде «Тапсырыстар»-ға ұшады
		var left = LIFE, sec = el.querySelector('.onn_sec'), hover = false
		el.onmouseenter = function () { hover = true }; el.onmouseleave = function () { hover = false }
		el._t = setInterval(function () {
			if (hover) return
			left -= 250
			sec.textContent = Math.max(0, Math.ceil(left / 1000)) + ' с'
			if (left <= 0) close(el, true)
		}, 250)

		// Windows хабарламасы (касса басқа терезеде тұрса), рұқсат берілген болса
		try {
			if (window.Notification && Notification.permission === 'granted') {
				var n = new Notification('Жаңа тапсырыс №' + o.number + ' · ' + o.src, { body: o.type + ' · ' + money(o.total) + (o.note ? '\n' + o.note : ''), tag: 'onn' + o.id, requireInteraction: true, icon: '/assets/img/logo/apple-touch-icon.png' })
				n.onclick = function () { window.focus(); n.close(); close(el); openOrder(o.id) }
				el._n = n
			}
		} catch (e) {}
	}

	// Тапсырыстар бетінде болсақ, тізімді жаңартып, карточканы белгілейміз
	function openOrder(id) {
		var url = '/orders/?hl=' + id
		location.href = url
	}
	function highlight() {
		var m = /[?&]hl=(\d+)/.exec(location.search); if (!m) return
		var c = document.querySelector('.or_card[data-oid="' + m[1] + '"]'); if (!c) return
		c.scrollIntoView({ block: 'center' }); c.classList.add('onn_hl')
		setTimeout(function () { c.classList.remove('onn_hl') }, 6000)
	}

	function poll() {
		if (busy) return; busy = true
		var x = new XMLHttpRequest()
		x.open('GET', '/orders/online.php?after=' + last + '&_=' + Date.now())
		x.onload = function () {
			busy = false
			var d; try { d = JSON.parse(x.responseText) } catch (e) { return }
			if (!d || !d.ok) return
			var got = false, top = 0
			;(d.items || []).forEach(function (o) { show(o); got = true; if (o.id > top) top = o.id })
			// Бірінші рет — тек осыдан кейінгілер; Жаңа емес (өңделіп қойған) тапсырыстарды өткізіп жібереміз
			last = Math.max(+d.last || 0, top)
			badge(+d.cnt || 0)
			set(KEY, last)
			if (got) ring()
		}
		x.onerror = function () { busy = false }
		x.send()
	}

	last = +get(KEY) || 0
	function ready() { highlight(); askPerm() }
	if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', ready); else ready()
	poll(); setInterval(poll, EVERY)
	// Басқа қойындыда «Көрдім» басылса да, осы жерде қайта шықпауы үшін last ортақ
	window.addEventListener('storage', function (e) { if (e.key === KEY && +e.newValue > last) last = +e.newValue })
})()
