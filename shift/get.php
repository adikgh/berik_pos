<? include "../config/core.php";

	if (!$user_id) { echo 'none'; exit(); }

	// Сменді жабу: кассирдің өз қабылдаған ақшасын есептеп, retail_shifts-ке жазамыз
	if (isset($_GET['close'])) {
		if (!mysqli_num_rows(db::query("SHOW COLUMNS FROM `retail_shifts` LIKE 'company_id'"))) { echo 'nocol'; exit(); }
		$start = @$_POST['start']; $end = @$_POST['end'];
		$re = '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/';
		if (!preg_match($re, $start) || !preg_match($re, $end)) { echo 'bad'; exit(); }
		$comment = mysqli_real_escape_string(db::$connect, trim(strip_tags(@$_POST['comment'])));

		include __DIR__.'/calc.php';
		$s = shift_calc($company, $branch, $user_id, $start, $end);

		$number = (int)mysqli_fetch_assoc(db::query("select count(*) c from retail_shifts where company_id = '$company' and branch_id = '$branch'"))['c'] + 1;
		$ins = db::query("INSERT INTO `retail_shifts` (`number`, `user_id`, `company_id`, `branch_id`, `point_sale_id`, `totol`, `orders_cnt`, `pay_cash`, `pay_qr`, `pay_transfer`, `pay_red`, `pay_delivery`, `comment`, `ins_dt`, `close_dt`, `upd_dt`)
			VALUES ('$number', '$user_id', '$company', '$branch', '$branch', '".(int)$s['my']."', '".(int)$s['cnt']."', '".(int)$s['cash']."', '".(int)$s['qr']."', '".(int)$s['transfer']."', '".(int)$s['red']."', 0, '$comment', '$start', '$end', '$datetime')");
		if ($ins && mysqli_num_rows(db::query("SHOW COLUMNS FROM `retail_shifts` LIKE 'pay_service'"))) {
			$sid = mysqli_insert_id(db::$connect);
			db::query("UPDATE `retail_shifts` SET `pay_service` = '".(int)$s['srv_sum']."' WHERE `id` = '$sid'");
		}
		echo $ins ? 'yes' : 'none';
		exit();
	}
