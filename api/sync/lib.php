<?php
	// Офлайн касса (Windows программасы) үшін синхрон API: ортақ функциялар.
	// Сессия/cookie қолданылмайды, кіру тек құрылғы токенімен (company_device.token).

	require __DIR__.'/../../config/db.php';
	new db;
	mysqli_set_charset(db::$connect, 'utf8mb4');
	date_default_timezone_set('Asia/Aqtau');
	header('Content-Type: application/json; charset=utf-8');
	header('Cache-Control: no-store');

	const SYNC_API_VER = 1;

	function out($data, $code = 200) {
		http_response_code($code);
		echo json_encode($data, JSON_UNESCAPED_UNICODE);
		exit();
	}
	function fail($msg, $code = 400) { out(['ok' => false, 'error' => $msg], $code); }

	function esc($v) { return mysqli_real_escape_string(db::$connect, (string)$v); }
	function sqlv($v) { return $v === null ? 'NULL' : "'".esc($v)."'"; }

	function rows($sql) {
		$q = db::query($sql); $r = [];
		if ($q) while ($a = mysqli_fetch_assoc($q)) $r[] = $a;
		return $r;
	}
	function row($sql) { $q = db::query($sql); return $q ? mysqli_fetch_assoc($q) : null; }

	// Кестенің бағандары (кэшпен)
	function cols($table) {
		static $c = [];
		if (!isset($c[$table])) {
			$c[$table] = [];
			foreach (rows("SHOW COLUMNS FROM `".esc($table)."`") as $r) $c[$table][$r['Field']] = $r;
		}
		return $c[$table];
	}
	function has_col($table, $col) { return isset(cols($table)[$col]); }
	function table_exists($table) { return (bool)row("SHOW TABLES LIKE '".esc($table)."'"); }

	// JSON денесін оқу
	function body() {
		$raw = file_get_contents('php://input');
		$d = json_decode($raw, true);
		return is_array($d) ? $d : [];
	}

	// Құрылғы токенін тексеру. Қайтарады: company_device жолы
	function device() {
		$tok = '';
		if (!empty($_SERVER['HTTP_X_DEVICE_TOKEN'])) $tok = $_SERVER['HTTP_X_DEVICE_TOKEN'];
		elseif (!empty($_SERVER['HTTP_AUTHORIZATION']) && stripos($_SERVER['HTTP_AUTHORIZATION'], 'Bearer ') === 0) $tok = substr($_SERVER['HTTP_AUTHORIZATION'], 7);
		$tok = preg_replace('/[^a-f0-9]/', '', strtolower($tok));
		if (strlen($tok) != 64) fail('no_token', 401);
		$d = row("SELECT * FROM company_device WHERE token = '$tok' AND arh = 0 LIMIT 1");
		if (!$d) fail('bad_token', 401);
		$b = row("SELECT id FROM company_branch WHERE id = '".(int)$d['branch_id']."' AND company_id = '".(int)$d['company_id']."'");
		if (!$b) fail('branch_not_found', 403);
		$ver = substr(preg_replace('/[^0-9.]/', '', (string)@$_SERVER['HTTP_X_APP_VER']), 0, 20);
		db::query("UPDATE company_device SET last_dt = '".date('Y-m-d H:i:s')."', last_ip = ".sqlv(substr((string)@$_SERVER['REMOTE_ADDR'], 0, 45)).($ver ? ", ver = '$ver'" : '')." WHERE id = '".(int)$d['id']."'");
		return $d;
	}

	// Жолды кестеге қосу/жаңарту: тек серверде бар бағандар алынады, id алынбайды
	function save_row($table, $data, $where_id = null) {
		$c = cols($table); $set = [];
		foreach ($data as $k => $v) {
			if ($k == 'id' || !isset($c[$k])) continue;
			$set[] = "`$k` = ".sqlv($v);
		}
		if (!$set) return $where_id;
		if ($where_id) {
			db::query("UPDATE `$table` SET ".implode(', ', $set)." WHERE id = '".(int)$where_id."'");
			return (int)$where_id;
		}
		// id AUTO_INCREMENT болмаса, max+1 береміз
		if (stripos((string)@$c['id']['Extra'], 'auto_increment') === false) {
			$nid = (int)@row("SELECT max(id) m FROM `$table`")['m'] + 1;
			$set[] = "`id` = '$nid'";
			db::query("INSERT INTO `$table` SET ".implode(', ', $set));
			return $nid;
		}
		db::query("INSERT INTO `$table` SET ".implode(', ', $set));
		return (int)mysqli_insert_id(db::$connect);
	}
