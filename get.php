<? include "config/core.php";

	// sign in code
	if(isset($_GET['code'])) {
		$code = strip_tags($_POST['code']);
		$user_mn = db::query("SELECT * FROM user_management WHERE code = '$code'");
		if (mysqli_num_rows($user_mn)) {
			$user_mnd = mysqli_fetch_assoc($user_mn);
			$id = $user_mnd['user_id'];
			$user = db::query("SELECT * FROM user WHERE id = '$id'");
			$user_d = mysqli_fetch_assoc($user);
			$_SESSION['uph'] = $user_d['phone'];
			$_SESSION['ups'] = $user_d['password'];
			echo 'yes';
		} else echo 'none';
		exit();
	}


	// sign in phone
	if(isset($_GET['sign'])) {
		$user_id = strip_tags($_POST['user_id']);
		$code = strip_tags($_POST['code']);
		$user = db::query("SELECT * FROM user WHERE id = '$user_id' and `code` = '$code' and `right` = 1");
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
		$n_name = strip_tags($_POST['n_name']);
		$surname = strip_tags($_POST['surname']);
		$sex = strip_tags($_POST['sex']);
		$age = strip_tags($_POST['age']);
		$mail = strip_tags($_POST['mail']);
		$phone = strip_tags($_POST['phone']);
		$password = strip_tags($_POST['password']);
		
		$upd = db::query("UPDATE `user` SET `name`='$n_name', `surname`='$surname', `sex`='$sex', `age`='$age', `mail`='$mail', `phone`='$phone', `password`='$password', `upd_dt`='$datetime' WHERE id = '$user_id'");

		$_SESSION['uph'] = $phone;
		$_SESSION['upm'] = $mail;
		$_SESSION['ups'] = $password;
		setcookie('uph', $phone, time() + 3600*24*30);
		setcookie('upm', $mail, time() + 3600*24*30);
		setcookie('ups', $password, time() + 3600*24*30);

		echo "yes";
		exit();
	}



