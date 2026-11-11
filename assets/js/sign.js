// Касса кіру беті: қызметкер таңдау → 4 таңбалы код (/get.php?sign)
$(function () {
	var $list = $('.ps_list'), $pin = $('.ps_pin'), $dots = $('.ps_dots'), $err = $('.ps_err');
	var code = '', uid = 0, busy = false;

	// Сағат пен күн
	var days = ['жексенбі', 'дүйсенбі', 'сейсенбі', 'сәрсенбі', 'бейсенбі', 'жұма', 'сенбі'];
	var mons = ['қаңтар', 'ақпан', 'наурыз', 'сәуір', 'мамыр', 'маусым', 'шілде', 'тамыз', 'қыркүйек', 'қазан', 'қараша', 'желтоқсан'];
	function p2(n) { return (n < 10 ? '0' : '') + n }
	function tick() {
		var d = new Date();
		$('.ps_js_time').text(p2(d.getHours()) + ':' + p2(d.getMinutes()));
		$('.ps_js_date').text(w('day:' + d.getDay(), days[d.getDay()]) + ', ' + d.getDate() + ' ' + w('mon:' + d.getMonth(), mons[d.getMonth()]));
	}
	tick(); setInterval(tick, 10000);
	if (!$pin.length) return;

	function show($s) {
		$('.ps_step').removeClass('ps_on').attr('aria-hidden', 'true');
		$s.addClass('ps_on').attr('aria-hidden', 'false');
	}
	function draw() {
		$dots.removeClass('ps_bad').find('i').each(function (i) { $(this).toggleClass('on', i < code.length) });
	}
	function reset() { code = ''; busy = false; $dots.removeClass('ps_wait'); draw() }

	// 1 → 2
	$('.ps_user').on('click', function () {
		var $u = $(this), img = $u.data('img');
		uid = $u.data('id');
		var $av = $('.ps_js_av').attr('class', 'ps_av ps_av_lg ps_js_av ps_av' + (uid % 6));
		$av.html('<em></em>').find('em').text($u.data('ini'));
		if (img) $('<img alt="">').attr('src', img).on('error', function () { $(this).remove() }).appendTo($av);
		$('.ps_js_name').text($u.data('name'));
		$('.ps_js_role').text($u.data('role') || '');
		$err.html('&nbsp;');
		reset(); show($pin);
	});
	// 2 → 1
	$('.ps_back').on('click', function () { uid = 0; reset(); show($list) });

	function send() {
		busy = true; $dots.addClass('ps_wait');
		$.ajax({
			url: '/get.php?sign', type: 'POST', dataType: 'html',
			data: { user_id: uid, code: code },
			success: function (data) {
				if ($.trim(data) == 'yes') { location.href = '/cashbox/'; return }
				$err.text($err.data('txt'));
				$dots.removeClass('ps_wait').addClass('ps_bad');
				setTimeout(function () { reset() }, 450);
			},
			error: function () {
				$err.text(w('net_err', 'Байланыс қатесі, қайта көріңіз'));
				reset();
			},
		});
	}
	function key(k) {
		if (busy || !uid) return;
		if (k == 'b') code = code.slice(0, -1);
		else if (k == 'c') code = '';
		else if (code.length < 4) { code += k; $err.html('&nbsp;') }
		draw();
		if (code.length == 4) setTimeout(send, 120);
	}
	$('.ps_pad button').on('click', function () { key(String($(this).data('k'))) });

	// Пернетақта: сандар, Backspace, Esc
	$(document).on('keydown', function (e) {
		if (!$pin.hasClass('ps_on')) return;
		var k = null;
		if (/^[0-9]$/.test(e.key)) k = e.key;
		else if (e.key == 'Backspace') k = 'b';
		else if (e.key == 'Delete') k = 'c';
		else if (e.key == 'Escape') { $('.ps_back').click(); return }
		if (k === null) return;
		e.preventDefault(); key(k);
		var $b = $('.ps_pad button[data-k="' + k + '"]').addClass('ps_hit');
		setTimeout(function () { $b.removeClass('ps_hit') }, 120);
	});
});
