<? include "../config/core.php";

	if (!$user_id) { echo 'none'; exit(); }

	// Аккаунт: аты, тегі, нөмірі (тек өз аккаунты)
	if (isset($_GET['upd'])) {
		$name = mysqli_real_escape_string(db::$connect, trim(strip_tags(@$_POST['name'])));
		$surname = mysqli_real_escape_string(db::$connect, trim(strip_tags(@$_POST['surname'])));
		$phone = substr(preg_replace('/\D/', '', (string)@$_POST['phone']), -10);
		if ($name === '') { echo 'name'; exit(); }
		if ($phone !== '' && strlen($phone) < 10) { echo 'phone'; exit(); }
		$phone_sql = $phone === '' ? 'NULL' : "'$phone'";
		$upd = db::query("UPDATE `user` SET `name` = '$name', `surname` = '$surname', `phone` = $phone_sql, `upd_dt` = '$datetime' WHERE `id` = '$user_id'");
		echo $upd ? 'yes' : 'none';
		exit();
	}
