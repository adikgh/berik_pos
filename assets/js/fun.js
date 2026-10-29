// Хабарлама (toast): оң жақ жоғарыдан жылжып шығады, × жабады, 4 сек кейін өзі кетеді, тышқан үстінде тұрса тоқтайды
// Түрі мәтінге қарай: сәтті (жасыл), қате (қызыл), қалғаны ескерту (сары). Қажет болса: mess('...', 'ok' | 'err' | 'warn')
window.mess = function (e, type) {
   var txt = $('<div>').html(e).text()
   if (!type) type = /сәтті|cәтті|сақтал|көшірілді|успешно/i.test(txt) ? 'ok' : (/қате|ошибка|error|байланыс/i.test(txt) ? 'err' : 'warn')
   var ico = { ok: 'fa-check-circle', err: 'fa-times-circle', warn: 'fa-exclamation-triangle' }[type]
   var box = $('.tst_box'); if (!box.length) box = $('<div class="tst_box"></div>').appendTo('body')
   box.find('.tst').filter(function(){ return $(this).data('t') === txt }).remove() // бірдей хабар қайталанбайды
   var t = $('<div class="tst tst_' + type + '"><i class="far ' + ico + ' tst_i"></i><div class="tst_t"></div><div class="tst_x"><i class="fal fa-times"></i></div><span class="tst_bar"></span></div>')
   t.data('t', txt).find('.tst_t').html(e)
   box.append(t); if (box.children().length > 3) box.children().first().remove()
   var left = 4000, start = Date.now(), timer
   function go(){ start = Date.now(); t.removeClass('tst_pause'); timer = setTimeout(close, left) }
   function close(){ clearTimeout(timer); t.addClass('tst_out'); setTimeout(function(){ t.remove() }, 250) }
   t.on('mouseenter', function(){ clearTimeout(timer); left -= Date.now() - start; t.addClass('tst_pause') }).on('mouseleave', go)
   t.on('click', '.tst_x', close)
   requestAnimationFrame(function(){ t.addClass('tst_in') }); go()
}










// 
function copytext(e) {
	var $tmp = $("<input>");
	$("body").append($tmp);
	$tmp.val(e).select();
	document.execCommand("copy");
	$tmp.remove();
   mess('Cәтті көшірілді!');
}


// 
function clcBack(e) {
	history.back();
}
function clcPP(e) {
	window.history.forward();
}
function clcP2(e) {
	history.go();
}

// Көлденең жылжитын қатар: саусақпен / тышқанмен сырғытады, сыймаса екі жағында ‹ › батырма шығады
// Қолданылуы: hscroll('.hup_rct')
window.hscroll = function (sel) {
   $(sel).each(function () {
      var el = this, $el = $(el)
      if ($el.data('hsc')) return
      $el.data('hsc', 1).addClass('hsc_el')
      var wrap = $('<div class="hsc"></div>')
      $el.before(wrap); wrap.append($el)
      var bl = $('<div class="hsc_btn hsc_l"><i class="far fa-angle-left"></i></div>'), br = $('<div class="hsc_btn hsc_r"><i class="far fa-angle-right"></i></div>')
      wrap.append(bl, br)
      function upd() {
         var max = el.scrollWidth - el.clientWidth
         bl.toggleClass('hsc_on', el.scrollLeft > 4)
         br.toggleClass('hsc_on', el.scrollLeft < max - 4)
         // батырма қатардың ортасында тұрсын (қатардың өз margin-і болуы мүмкін)
         var top = el.offsetTop + el.clientHeight / 2
         bl.css('top', top); br.css('top', top)
      }
      function go(dir) { el.scrollBy({ left: dir * Math.max(160, el.clientWidth * 0.7), behavior: 'smooth' }) }
      bl.on('click', function (e) { e.stopPropagation(); go(-1) })
      br.on('click', function (e) { e.stopPropagation(); go(1) })
      $el.on('scroll', upd)
      $(window).on('resize', upd)
      if (window.ResizeObserver) new ResizeObserver(upd).observe(el)
      upd(); setTimeout(upd, 300)

      // Тышқанмен сүйреп жылжыту (телефонда саусақпен өзі жылжиды)
      var down = false, moved = false, sx = 0, sl = 0
      $el.on('mousedown', function (e) {
         if (e.button !== 0 || $(e.target).is('input, textarea, select')) return
         down = true; moved = false; sx = e.pageX; sl = el.scrollLeft
      })
      $(document).on('mousemove', function (e) {
         if (!down) return
         var dx = e.pageX - sx
         if (!moved && Math.abs(dx) > 6) { moved = true; $el.addClass('hsc_drag') }
         if (moved) el.scrollLeft = sl - dx
      })
      $(document).on('mouseup', function () {
         if (!down) return
         down = false
         setTimeout(function () { $el.removeClass('hsc_drag'); moved = false }, 0)
      })
      // сүйреп болғанда кездейсоқ басылып кетпесін
      el.addEventListener('click', function (e) { if (moved) { e.stopPropagation(); e.preventDefault() } }, true)
   })
}
