// Сайттан (QR меню / жеке кабинет) келген жаңа тапсырыс: кассада терезе + дыбыс.
// Әр 10 секунд сайын /orders/online.php сұралады. «Көрдім» немесе «Ашу» басылғанша дыбыс 20 сек сайын қайталанады.
(function () {
	if (window.__onNotify) return; window.__onNotify = true
	var KEY = 'bp_online_last_' + (window.BP_CID || 0), EVERY = 10000, RING = 20000, RING_MAX = 15
	var last = 0, box, ac, ringT, ringN = 0, baseTitle = document.title, busy = false

	function get(k) { try { return localStorage.getItem(k) } catch (e) { return null } }
	function set(k, v) { try { localStorage.setItem(k, v) } catch (e) {} }

	// Стиль (head-ке тиіспеу үшін осы жерде)
	var css = document.createElement('style')
	css.textContent = '' +
		'.onn_box{position:fixed;right:20px;bottom:20px;z-index:99999;display:flex;flex-direction:column;gap:12px;width:340px;max-width:calc(100vw - 32px);font-family:Nunito,Arial,sans-serif}' +
		'.onn{background:#fff;border-radius:16px;box-shadow:0 12px 40px rgba(0,0,0,.22);border-left:6px solid #0EA768;padding:14px 16px 14px 14px;animation:onn_in .3s ease}' +
		'.onn_t{display:flex;align-items:center;gap:8px;font-size:13px;font-weight:800;color:#0EA768;text-transform:uppercase;letter-spacing:.3px}' +
		'.onn_t i{font-size:16px;animation:onn_bell 1s ease-in-out infinite}' +
		'.onn_x{margin-left:auto;color:#999;cursor:pointer;font-size:18px;line-height:1;padding:2px 4px}' +
		'.onn_n{font-size:22px;font-weight:800;color:#1a1a1a;margin:6px 0 2px}' +
		'.onn_d{font-size:15px;color:#333}.onn_d b{font-weight:800}' +
		'.onn_m{font-size:13px;color:#777;margin-top:4px}' +
		'.onn_b{display:flex;gap:8px;margin-top:12px}' +
		'.onn_b span{flex:1;text-align:center;padding:10px;border-radius:10px;font-weight:700;cursor:pointer;font-size:15px}' +
		'.onn_open{background:#0EA768;color:#fff}.onn_ok{background:#f1f3f2;color:#333}' +
		'.or_src{display:inline-block;margin-left:6px;padding:2px 7px;border-radius:7px;background:#e7f7ef;color:#0EA768;font-size:11px;font-weight:800;vertical-align:middle;white-space:nowrap}' +
		'.or_card.onn_hl{box-shadow:0 0 0 3px #0EA768 !important;transition:box-shadow .3s}' +
		'@keyframes onn_in{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:none}}' +
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
		if (!box || !box.children.length) { ringN = 0; document.title = baseTitle; return }
		ding(); ringN++
		document.title = '(' + box.children.length + ') Жаңа тапсырыс!'
		if (ringN < RING_MAX) ringT = setTimeout(ring, RING)
	}

	function money(v) { return String(v).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' ₸' }
	function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c] }) }

	function close(el) {
		el.remove()
		if (!box.children.length) { clearTimeout(ringT); ringN = 0; document.title = baseTitle }
	}

	function show(o) {
		if (!box) { box = document.createElement('div'); box.className = 'onn_box'; document.body.appendChild(box) }
		if (box.querySelector('[data-id="' + o.id + '"]')) return
		var el = document.createElement('div')
		el.className = 'onn'; el.setAttribute('data-id', o.id)
		el.innerHTML = '<div class="onn_t"><i class="far fa-bell"></i>Сайттан жаңа тапсырыс · ' + esc(o.src) + '<span class="onn_x" title="Жабу">×</span></div>' +
			'<div class="onn_n">№' + esc(o.number) + '</div>' +
			'<div class="onn_d">' + esc(o.type) + ' · <b>' + money(o.total) + '</b> · ' + esc(o.time) + '</div>' +
			(o.note ? '<div class="onn_m">' + esc(o.note) + '</div>' : '') +
			'<div class="onn_b"><span class="onn_open">Ашу</span><span class="onn_ok">Көрдім</span></div>'
		el.querySelector('.onn_x').onclick = el.querySelector('.onn_ok').onclick = function () { close(el) }
		el.querySelector('.onn_open').onclick = function () { close(el); openOrder(o.id) }
		box.appendChild(el)

		// Windows хабарламасы (касса басқа терезеде тұрса), рұқсат берілген болса
		try {
			if (window.Notification && Notification.permission === 'granted' && document.hidden) {
				var n = new Notification('Жаңа тапсырыс №' + o.number + ' · ' + o.src, { body: o.type + ' · ' + money(o.total), tag: 'onn' + o.id })
				n.onclick = function () { window.focus(); n.close() }
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
			set(KEY, last)
			if (got) ring()
		}
		x.onerror = function () { busy = false }
		x.send()
	}

	last = +get(KEY) || 0
	// Windows хабарламасына рұқсат: адам бірінші басқанда бір рет сұраймыз
	document.addEventListener('pointerdown', function ask() {
		document.removeEventListener('pointerdown', ask, true)
		try { if (window.Notification && Notification.permission === 'default') Notification.requestPermission() } catch (e) {}
	}, true)

	if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', highlight); else highlight()
	poll(); setInterval(poll, EVERY)
	// Басқа қойындыда «Көрдім» басылса да, осы жерде қайта шықпауы үшін last ортақ
	window.addEventListener('storage', function (e) { if (e.key === KEY && +e.newValue > last) last = +e.newValue })
})()
