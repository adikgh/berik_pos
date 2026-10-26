<?


   // ini_set('display_errors', 'Off');
   // ini_set('display_startup_errors', 'Off');
   // error_reporting(0);


   // setting
   $ver = 4.008;

   // 
   $site_set = [
   'menu' => true,
   'search' => true,
   // 'swiper' => false,
   // 'plyr' => false,
   // 'aos' => false,
   ];
   $scss = ['norm', 'main'];
   $sjs = ['norm', 'main'];
   $css = [];
   $js = [];
   $code = rand(1000, 9999);

   // lang — интерфейс тілі ($ui_lang): kz (стандарт), en, uz, ky, ru
   $langs = ['kz' => 'Қазақша', 'en' => 'English', 'uz' => 'O‘zbekcha', 'ky' => 'Кыргызча', 'ru' => 'Русский'];
   $langs_short = ['kz' => 'ҚАЗ', 'en' => 'ENG', 'uz' => 'UZB', 'ky' => 'KGZ', 'ru' => 'РУС'];
   $ui_lang = 'kz';
   if (isset($_GET['lang']) && isset($langs[$_GET['lang']])) {
      $_SESSION['lang'] = $_GET['lang'];
      setcookie('lang', $_GET['lang'], time() + 3600 * 24 * 365, '/');
      // ?lang= параметрін алып тастап, сол бетке қайта жібереміз
      $q = $_GET; unset($q['lang']);
      $back = explode('?', $_SERVER['REQUEST_URI'])[0].($q ? '?'.http_build_query($q) : '');
      if (!headers_sent()) { header('Location: '.$back); exit; }
   }
   if (isset($_COOKIE['lang']) && isset($langs[$_COOKIE['lang']])) $ui_lang = $_COOKIE['lang'];
   elseif (isset($_SESSION['lang']) && isset($langs[$_SESSION['lang']])) $ui_lang = $_SESSION['lang'];
   // Деректер тілі (name_kz / name_ru): тек kz немесе ru
   $lang = ($ui_lang == 'ru') ? 'ru' : 'kz';

   $view_pr = null;
   if (isset($_GET['view_pr'])) $_SESSION['view_pr'] = $_GET['view_pr'];
   if (isset($_SESSION['view_pr']) && $_SESSION['view_pr'] == 'list') $view_pr = 2; else $view_pr = null;

   // date - time
   $date = date("Y-m-d", time());
   $time = date("H:i:s", time());
   $datetime = date('Y-m-d H:i:s', time());

   if ($time > "00:00:00" && $time < "06:00:00") {
      $start_cdate = date('Y-m-d 06:00:00', strtotime("$date -1 day"));
      $end_cdate = date('Y-m-d 06:00:00');
   } else {
      $start_cdate = date('Y-m-d 06:00:00');
      $end_cdate = date("Y-m-d 06:00:00", strtotime("$start_cdate +1 day"));
   }

   //  $start_cdate = date('Y-m-d 06:00:00', strtotime("$date -1 day"));
   //  $end_cdate = date("Y-m-d 06:00:00", strtotime("$start_cdate +1 day"));

   // url
   $url = $url_full = $_SERVER['REQUEST_URI'];
   $url = explode('?', $url);
   $url = $url[0];

   // token telegram
   $token = "1581082911:AAEKW20w_-5V0Wx9tzhyThV2pjCgZtCjyc8";

