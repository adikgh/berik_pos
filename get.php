<? include "config/core.php";

	// sign in code: 2026-11-21 жабылды. Ешбір бет шақырмайды (кіру ?sign арқылы), логинсіз ашылатын,
	// code SQL-ге экрандалмай кіретін (SQL injection), сәтті болса сессияға телефон мен пароль хэші жазылатын.
	if(isset($_GET['code'])) { echo 'none'; exit(); }


	// sign in phone
	if(isset($_GET['sign'])) {
		$user_id = (int)@$_POST['user_id'];
		$code = preg_replace('/\D/', '', (string)@$_POST['code']);
		// POS коды company_staff кестесінде
		$user = db::query("SELECT u.* FROM user u JOIN company_staff s ON s.user_id = u.id WHERE u.id = '$user_id' and s.code = '$code' and u.`right` = 1 limit 1");
		if (mysqli_num_rows($user)) {
			$user_d = mysqli_fetch_array($user);
			$_SESSION['upi'] = $user_d['id'];
			$_SESSION['upc'] = $code;
			setcookie('upi', $user_d['id'], time() + 3600*24*30*6, '/');
			setcookie('upc', $code, time() + 3600*24*30*6, '/');
			echo 'yes';
		} else echo 'none';
		exit();
	}











	// ubd user
	if(isset($_GET['ubd_acc'])) {
		if (!$user_id) exit('none');
		$n_name = strip_tags($_POST['n_name']);
		$surname = strip_tags($_POST['surname']);
		$sex = strip_tags($_POST['sex']);
		$age = strip_tags($_POST['age']);
		$mail = strip_tags($_POST['mail']);
		$phone = substr(preg_replace('/\D/', '', (string)@$_POST['phone']), -10);
		$password = trim((string)@$_POST['password']);
		
		// SQL-ге экрандау; телефон тек 10 таңба болса және басқа адамда болмаса ауысады
		$e = function ($v) { return mysqli_real_escape_string(db::$connect, trim((string)$v)); };
		$set_ph = '';
		if (strlen($phone) == 10 && !mysqli_num_rows(db::query("SELECT id FROM `user` WHERE `phone` = '$phone' AND id <> '".(int)$user_id."' LIMIT 1"))) $set_ph = ", `phone`='$phone'";
		$upd = db::query("UPDATE `user` SET `name`='".$e($n_name)."', `surname`='".$e($surname)."', `sex`='".$e($sex)."', `age`='".$e($age)."', `mail`='".$e($mail)."'$set_ph, `upd_dt`='$datetime' WHERE id = '".(int)$user_id."'");
		if ($password !== '') db::query("UPDATE `user` SET `password` = '".mysqli_real_escape_string(db::$connect, password_hash($password, PASSWORD_DEFAULT))."' WHERE id = '$user_id'");


		echo "yes";
		exit();
	}



