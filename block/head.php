<? $menu = mysqli_fetch_array(db::query("select * from `site_menu` where name = '$menu_name' and type = 'admin'")); ?>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, maximum-scale=1.0"/> -->

<!-- Телефоннан кіруге болмайды: экран өлшемін тексеру (ашылғанда және кішірейткенде).
     Телефонда «Компьютер нұсқасы» қосулы болса да ұстайды: құрылғының өз экраны бойынша тексереді. -->
<script>
(function(){
	var MIN_W = 700, TO = '/mobile/';
	var touch = ('ontouchstart' in window) || (navigator.maxTouchPoints > 0);
	function mq(q){ return !!(window.matchMedia && window.matchMedia(q).matches) }
	// Құрылғы телефон ба (браузердің «компьютер нұсқасы» режиміне қарамайды)
	function devPhone(){
		var s = Math.min(screen.width, screen.height), dpr = window.devicePixelRatio || 1;
		var ow = window.outerWidth || 0;
		var coarse = mq('(pointer: coarse)') && !mq('(any-pointer: fine)');
		if (navigator.userAgentData && navigator.userAgentData.mobile && s < 700) return true;
		if (/iPhone|iPod|Android.+Mobile|Windows Phone|IEMobile|Opera Mini/i.test(navigator.userAgent)) return true;
		if (touch && s < 500) return true;                       // экранның қысқа жағы 500px-ден аз
		if (touch && ow > 0 && ow < 500) return true;            // браузер терезесінің нақты ені
		if (coarse && dpr >= 2 && s * dpr < 1300) return true;   // экранның физикалық пикселі телефондікі
		return false;
	}
	function fit(){
		var m = document.querySelector('meta[name="viewport"]'); if (!m || !touch) return;
		var a = Math.max(screen.width, screen.height), b = Math.min(screen.width, screen.height);
		var cw = mq('(orientation: landscape)') ? a : b;
		m.setAttribute('content', (cw >= MIN_W && cw < 800) ? 'width=800' : 'width=device-width, initial-scale=1');
	}
	function go(){ location.replace(TO + '?r=' + encodeURIComponent(location.pathname + location.search)) }
	function check(){
		var dp = devPhone(), w = window.innerWidth || document.documentElement.clientWidth || 9999;
		if (dp) { try { document.cookie = 'bp_phone=1; path=/; max-age=' + (3600*24*30) } catch(e){} }
		if (dp || w < MIN_W) { document.documentElement.style.display = 'none'; go(); return true }
	}
	if (check()) return;
	fit();
	var t; function later(){ clearTimeout(t); t = setTimeout(function(){ fit(); check() }, 150) }
	window.addEventListener('resize', later);
	window.addEventListener('orientationchange', later);
})();
</script>

<title><?=@$menu['title_'.$lang]?> | <?=@$site['name_kz']?></title>
<meta name="description" content="<?=@$menu['disc_'.$lang]?> <?=@$site['phone_view']?>">
<meta name="keywords" content="<?=@$menu['keyw_'.$lang]?>">
<meta name="theme-color" content="<?=@$site['color']?>">

<!-- icon -->
<link rel="icon" href="/assets/img/logo/icon.png" type="image/x-icon">
<link rel="shortcut icon" type="image/icon" href="/assets/img/logo/icon.png">

<!-- Open Graph -->
<meta property="og:type" content="website" />
<meta property="og:url" content="https://<?=$site['site']?>.kz" />
<meta property="og:site_name" content="<?=$site['name']?>" />
<meta property="og:title" content="<?=$menu['title_'.$lang]?> | <?=$site['name']?>" />
<meta property="og:description" content="<?=$menu['disc_'.$lang]?> <?=$site['phone_view']?>" />
<meta property="og:image" content="/assets/img/logo/logo.jpg" />

<!-- apple -->
<meta name="application-name" lang="<?=$lang?>" content="<?=$site['site']?>">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

<!-- ms -->
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta http-equiv="cleartype" content="on">
<meta name="msapplication-tooltip" content="<?=$menu['title_'.$lang]?> | <?=$site['name']?>">
<meta name="msapplication-TileColor" content="<?=@$site['color']?>">
<meta name="msapplication-starturl" content="https://<?=$site['site']?>">


<!-- css -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@200;300;400;600;700;800;900&display=swap" />
<link rel="stylesheet" href="/assets/pl/fontawesome/css/all.min.css" />
<link rel="stylesheet" type="text/css" href="/assets/css/anim.css?v=<?=$ver?>" />
<? if (@$site_set['swiper'] == true): ?> <link rel="stylesheet" href="/assets/pl/swiper-bundle.min.css" /> <? endif ?>
<? if (@$site_set['plyr'] == true): ?> <link rel="stylesheet" href="/assets/pl/plyr.css" /> <? endif ?>

<!-- main css -->
<? foreach ($scss as $i): ?> <link rel="stylesheet" type="text/css" href="/assets/css/<?=$i?>.css?v=<?=$ver?>" /> <? endforeach ?>
<? foreach ($css as $i): ?> <link rel="stylesheet" type="text/css" href="/assets/css/<?=$i?>.css?v=<?=$ver?>" /> <? endforeach ?>
<link rel="stylesheet" type="text/css" href="/assets/css/berik.css?v=<?=$ver?>-<?=@filemtime($_SERVER['DOCUMENT_ROOT'].'/assets/css/berik.css')?>" />
<!-- планшет және сенсорлы моноблок (769–1366px) -->
<link rel="stylesheet" type="text/css" href="/assets/css/tablet.css?v=<?=$ver?>-<?=@filemtime($_SERVER['DOCUMENT_ROOT'].'/assets/css/tablet.css')?>" />


<!-- js -->
<script src="/assets/pl/jquery.min.js"></script>
<script src="/assets/pl/jquery.lazy.min.js"></script>
<script src="/assets/pl/jquery.lazy.plugins.min.js"></script>
<script src="/assets/pl/jquery.mask.min.js"></script>
<? if (@$site_set['swiper'] == true): ?> <script src="/assets/pl/swiper-bundle.min.js"></script> <? endif ?>
<? if (@$site_set['plyr'] == true): ?> <script src="/assets/pl/plyr.polyfilled.js"></script> <? endif ?>
<? if (@$site_set['aos'] == true): ?> <script src="/assets/pl/aos.js"></script> <? endif ?>
<? if (@$site_set['autosize'] == true): ?> <script src="/assets/pl/autosize.min.js"></script> <? endif ?>
<script src="/assets/js/fun.js?v=<?=$ver?>"></script>
<script>var W = <?=json_encode(t::all(), JSON_UNESCAPED_UNICODE)?>; function w(k, d){ return (W && W[k]) ? W[k] : (d || k) }</script>

