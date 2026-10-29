<? include "../config/core.php";
	require_once "../config/loyalty.php";

	// Кассадағы клиент ақпараты: кэшбэк балансы және сыйлық күйі (JSON)
	if (!$user_id) exit('error');
	header('Content-Type: application/json; charset=utf-8');
	$res = ['on' => false];
	if (isset($_GET['info']) && loyalty::on() && loyalty::any($company)) {
		$cmp = (int)$company;
		$oid = (int)@$_POST['oid'];
		$d = preg_replace('/\D+/', '', (string)@$_POST['phone']);
		if (strlen($d) == 11 && ($d[0] == '8' || $d[0] == '7')) $d = substr($d, 1);
		if (strlen($d) == 10) {
			loyalty::sync($cmp, true);
			$cl = mysqli_fetch_assoc(db::query("select * from company_client where company_id = '$cmp' and phone = '$d' limit 1"));
			$res = loyalty::client_info($cmp, $cl, $oid);
			$res['on'] = true;
			$res['client'] = $cl ? true : false;
		}
	}
	echo json_encode($res, JSON_UNESCAPED_UNICODE);
