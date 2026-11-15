<?
	// Бірнеше касса қатары (2026-11-15): кассир бір уақытта бірнеше тапсырыс ашады (Касса-1, Касса-2 ... ).
	// Әр касса = төленбеген тапсырыс: retail_orders.paid = 0, retail_orders.cart_no = 1..N (кассирдің user_id бойынша).
	// Баптау компанияда: company.multi_cart (0 өшік, бұрынғыдай бір ғана; 1 қосулы), company.multi_cart_max (ең көбі, 15-тен аспайды).
	// Бағандар жоқ болса функция өшік тұрады, касса бұрынғыдай жұмыс істейді.
	class carts {
		private static $ok = null;
		private static $cfg = [];
		const LIMIT = 15;

		// Бағандар бар ма
		public static function ready() {
			if (self::$ok === null) {
				self::$ok = @mysqli_num_rows(db::query("SHOW COLUMNS FROM `retail_orders` LIKE 'cart_no'")) > 0
					&& @mysqli_num_rows(db::query("SHOW COLUMNS FROM `company` LIKE 'multi_cart'")) > 0;
			}
			return self::$ok;
		}

		// Компанияда қосулы ма, қосулы болса ең көбі неше касса (өшік болса 0)
		public static function max($company) {
			$company = (int)$company;
			if (isset(self::$cfg[$company])) return self::$cfg[$company];
			$m = 0;
			if (self::ready()) {
				$r = mysqli_fetch_assoc(db::query("select multi_cart, multi_cart_max from company where id = '$company' limit 1"));
				if ($r && (int)$r['multi_cart']) $m = max(2, min(self::LIMIT, (int)$r['multi_cart_max'] ?: self::LIMIT));
			}
			return self::$cfg[$company] = $m;
		}
		public static function on($company) { return self::max($company) > 0; }

		// Кассирдің ашық кассалары: [cart_no => [id, cart_no, cnt, total]]
		public static function open($user_id, $company) {
			$user_id = (int)$user_id; $company = (int)$company; $list = [];
			if (!$user_id || !self::ready()) return $list;
			$q = db::query("select o.id, o.cart_no, coalesce(sum(p.quantity), 0) cnt, coalesce(sum(p.quantity * p.price), 0) total
				from retail_orders o left join retail_orders_products p on p.order_id = o.id
				where o.user_id = '$user_id' and o.company_id = '$company' and o.paid = 0 and o.cart_no > 0
				group by o.id, o.cart_no order by o.cart_no asc, o.id asc");
			while ($q && ($r = mysqli_fetch_assoc($q))) if (!isset($list[(int)$r['cart_no']])) $list[(int)$r['cart_no']] = $r;
			return $list;
		}

		// Бос нөмір (1..max), жоқ болса 0
		public static function free_no($list, $max) {
			for ($i = 1; $i <= $max; $i++) if (!isset($list[$i])) return $i;
			return 0;
		}

		// Жаңа төленбеген тапсырыс (касса қатары) жасау, id қайтарады
		public static function create($user_id, $company, $branch, $no) {
			$user_id = (int)$user_id; $company = (int)$company; $branch = (int)$branch; $no = (int)$no;
			$last = mysqli_fetch_assoc(db::query("SELECT max(id) m FROM `retail_orders`"));
			$id = (int)@$last['m'] + 1;
			db::query("INSERT INTO `retail_orders`(`id`, `user_id`, `company_id`, `branch_id`, `cart_no`) VALUES ('$id', '$user_id', '$company', '$branch', '$no')");
			return $id;
		}
	}
