	<!-- body end -->
	</div>

	<!-- main js -->
	<? foreach ($sjs as $i): ?> <script src="/assets/js/<?=$i?>.js?v=<?=$ver?>"></script> <? endforeach ?>
	<? foreach ($js as $i): ?> <script src="/assets/js/<?=$i?>.js?v=<?=$ver?>-<?=@filemtime($_SERVER['DOCUMENT_ROOT'].'/assets/js/'.$i.'.js')?>"></script> <? endforeach ?>
	<? if (!empty($user_id) && empty($posm)): // сайттан (QR меню) келген жаңа тапсырыс хабары ?> <script src="/assets/js/notify.js?v=<?=$ver?>-<?=@filemtime($_SERVER['DOCUMENT_ROOT'].'/assets/js/notify.js')?>"></script> <? endif ?>
	<? if (!empty($posm)): // телефон нұсқасы ?> <script src="/assets/js/posm.js?v=<?=$ver?>-<?=@filemtime($_SERVER['DOCUMENT_ROOT'].'/assets/js/posm.js')?>"></script> <? endif ?>
		
</body>
</html>

	<? include "modal.php"; ?>