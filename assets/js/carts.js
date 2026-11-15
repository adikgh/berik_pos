// Бірнеше касса қатары: бос кассаны × арқылы жабу (config/carts.php, cashbox/get.php?cart_close)
$(document).on('click', '.ctab_x', function (e) {
	e.preventDefault(); e.stopPropagation()
	var tab = $(this).closest('.ctab')
	if (tab.hasClass('ctab_busy')) return
	tab.addClass('ctab_busy')
	$.ajax({
		url: '/cashbox/get.php?cart_close', type: 'POST', dataType: 'html',
		data: { no: $(this).attr('data-no') },
		success: function (data) {
			if (data == 'yes') location.href = '/cashbox/'
			else {
				tab.removeClass('ctab_busy')
				if (data == 'full' && typeof mess == 'function') mess(typeof w == 'function' ? w('Kassa is not empty', 'Кассада тағам бар, жабылмайды') : 'Кассада тағам бар, жабылмайды')
			}
		},
		error: function () { tab.removeClass('ctab_busy') }
	})
})
