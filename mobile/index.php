<?
	// Телефоннан кірген адам осы бетке жіберіледі. Базаға қосылмайды.
	$r = (string)@$_GET['r'];
	if ($r === '' || $r[0] !== '/' || (isset($r[1]) && ($r[1] === '/' || $r[1] === '\\')) || strpos($r, '/mobile') === 0) $r = '/';
	header('Cache-Control: no-store');
	// Телефон нұсқасы: сол сайттың posm.* домені (posb.gprog.kz → posm.gprog.kz, pos.berik.c → posm.berik.c)
	$h = preg_replace('~[^a-z0-9.\-]~i', '', (string)@$_SERVER['HTTP_HOST']);
	$h_parts = explode('.', $h);
	$posm_url = '';
	if (count($h_parts) >= 2) {
		$h_parts[0] = 'posm';
		$c = (int)@$_COOKIE['c'];
		$posm_url = '//'.implode('.', $h_parts).'/'.($c ? '?c='.$c : '');
	}
?><!DOCTYPE html>
<html lang="kk">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow">
	<meta name="theme-color" content="#f6f6f6">
	<title>Телефон нұсқасы | Berik POS</title>
	<link rel="icon" href="/assets/img/logo/icon.png" type="image/x-icon">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" />
	<link rel="stylesheet" href="/assets/pl/fontawesome/css/all.min.css" />
	<style>
		*{ box-sizing: border-box; margin: 0; padding: 0; }
		html, body{ height: 100%; }
		body{
			min-height: 100%; display: flex; align-items: center; justify-content: center;
			padding: 24px 16px; background: #f6f6f6; color: #222;
			font-family: 'Nunito', -apple-system, 'Segoe UI', Roboto, Arial, sans-serif;
			-webkit-user-select: none; user-select: none;
		}
		.mb_c{ width: 100%; max-width: 420px; padding: 32px 24px 28px; background: #fff; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,.08); text-align: center; }
		.mb_ic{ position: relative; width: 88px; height: 88px; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; border-radius: 24px; background: #e7f6ef; color: #0EA768; font-size: 40px; }
		.mb_ic i + i{ position: absolute; right: -6px; bottom: -6px; width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: #0EA768; color: #fff; font-size: 16px; border: 3px solid #fff; }
		h1{ font-size: 22px; font-weight: 800; line-height: 1.25; }
		p{ margin-top: 12px; font-size: 15px; line-height: 1.5; color: #555; }
		.mb_ok{ margin-top: 20px; padding: 14px 16px; display: flex; gap: 12px; align-items: center; text-align: left; border-radius: 14px; background: #eef6fb; color: #1f75a8; font-size: 14px; font-weight: 700; line-height: 1.4; }
		.mb_ok i{ font-size: 22px; }
		.mb_ru{ margin-top: 18px; padding-top: 16px; border-top: 1px dashed #e7e7e7; font-size: 13px; color: #8a8f98; }
		.mb_back{ display: none; margin-top: 20px; height: 54px; align-items: center; justify-content: center; gap: 8px; border-radius: 12px; background: #0EA768; color: #fff; font-size: 16px; font-weight: 800; text-decoration: none; }
		.mb_back_on{ display: flex; }
		.mb_go{ margin-top: 20px; height: 54px; display: flex; align-items: center; justify-content: center; gap: 10px; border-radius: 12px; background: #0EA768; color: #fff; font-size: 16px; font-weight: 800; text-decoration: none; }
		.mb_back{ background: #fff; color: #0EA768; border: 2px solid #0EA768; }
	</style>
</head>
<body>
	<div class="mb_c">
		<div class="mb_ic"><i class="fal fa-desktop"></i><i class="fas fa-mobile"></i></div>
		<h1>Бұл сілтеме компьютерге арналған</h1>
		<p>Телефоннан кассаға бөлек сілтеме арқылы кіріңіз<?=($posm_url ? ': <b>'.htmlspecialchars(implode('.', $h_parts)).'</b>' : '')?>. Компьютер, планшет және сенсорлы моноблок осы сілтемеде қалады.</p>
		<? if ($posm_url): ?><a class="mb_go" href="<?=htmlspecialchars($posm_url)?>"><i class="far fa-mobile"></i><span>Телефон нұсқасын ашу</span></a><? endif ?>
		<div class="mb_ru">Эта ссылка для компьютера. С телефона откройте кассу по отдельной ссылке<?=($posm_url ? ' '.htmlspecialchars(implode('.', $h_parts)) : '')?>.</div>
		<a class="mb_back" href="<?=htmlspecialchars($r)?>"><i class="far fa-arrow-left"></i><span>Кассаға қайту</span></a>
	</div>
	<script>
		// Тек компьютер/планшетте терезені қайта үлкейтсе «Кассаға қайту» шығады (телефонда ешқашан)
		(function(){
			var touch = ('ontouchstart' in window) || (navigator.maxTouchPoints > 0);
			function mq(q){ return !!(window.matchMedia && window.matchMedia(q).matches) }
			function devPhone(){
				var s = Math.min(screen.width, screen.height), dpr = window.devicePixelRatio || 1, ow = window.outerWidth || 0;
				var coarse = mq('(pointer: coarse)') && !mq('(any-pointer: fine)');
				if (navigator.userAgentData && navigator.userAgentData.mobile && s < 700) return true;
				if (/iPhone|iPod|Android.+Mobile|Windows Phone|IEMobile|Opera Mini/i.test(navigator.userAgent)) return true;
				if (touch && s < 500) return true;
				if (touch && ow > 0 && ow < 500) return true;
				if (coarse && dpr >= 2 && s * dpr < 1300) return true;
				return false;
			}
			var b = document.querySelector('.mb_back');
			function ok(){ return !devPhone() && (window.innerWidth || 0) >= 700 }
			function upd(){ b.className = 'mb_back' + (ok() ? ' mb_back_on' : '') }
			// Компьютерде қате белгі қалып қойса, өшіреміз
			if (!devPhone()) { try { document.cookie = 'bp_phone=; path=/; max-age=0' } catch(e){} }
			upd(); window.addEventListener('resize', upd);
			history.pushState(null, '', location.href);
			window.addEventListener('popstate', function(){ if (!ok()) history.pushState(null, '', location.href) });
		})();
	</script>
</body>
</html>
