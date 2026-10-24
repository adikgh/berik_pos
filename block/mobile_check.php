<? // Телефоннан кіруге болмайды: сервер жағынан тексеру (User-Agent). Планшет өтеді.
	$mob_ua = (string)@$_SERVER['HTTP_USER_AGENT'];
	$mob_by_ua = $mob_ua !== '' && !preg_match('~iPad|Tablet~i', $mob_ua)
		&& preg_match('~iPhone|iPod|Windows Phone|IEMobile|BlackBerry|BB10|Opera Mini|Android.*Mobile~i', $mob_ua);
	// «Компьютер нұсқасы» қосылса UA компьютердікі болады: бұрын телефон деп танылған құрылғыны cookie арқылы ұстаймыз
	$mob_by_ck = @$_COOKIE['bp_phone'] == '1';
	if ($mob_by_ua || $mob_by_ck || @$_SERVER['HTTP_SEC_CH_UA_MOBILE'] == '?1') {
		$mob_to = '/mobile/?r='.urlencode((string)@$_SERVER['REQUEST_URI']);
		if (!headers_sent()) { header('Location: '.$mob_to); exit; }
		echo '<script>location.replace('.json_encode($mob_to).')</script>'; exit;
	}
?>
