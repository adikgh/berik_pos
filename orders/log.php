<?
   // Тапсырыс тарихы: әр өзгеріс retail_orders_log кестесіне (кесте болса ғана)
   if (!function_exists('order_log')) {
      function order_log_on() {
         static $h = null;
         if ($h === null) $h = (bool)mysqli_num_rows(db::query("SHOW TABLES LIKE 'retail_orders_log'"));
         return $h;
      }
      function order_log($oid, $field, $old, $new) {
         global $user_id, $datetime; // уақыт тапсырыстағы ins_dt / upd_dt-мен бірдей көзден
         if (!order_log_on() || !(int)$oid) return;
         $old = ($old === null) ? '' : trim((string)$old);
         $new = ($new === null) ? '' : trim((string)$new);
         if ($old === $new) return;
         $e = function ($v) { return $v === '' ? 'NULL' : "'".mysqli_real_escape_string(db::$connect, mb_substr($v, 0, 1000))."'"; };
         db::query("INSERT INTO `retail_orders_log` (`order_id`, `field`, `old_value`, `new_value`, `user_id`, `ins_dt`) VALUES ('".(int)$oid."', '".mysqli_real_escape_string(db::$connect, $field)."', ".$e($old).", ".$e($new).", ".((int)$user_id ? "'".(int)$user_id."'" : 'NULL').", '".($datetime ?: date('Y-m-d H:i:s'))."')");
      }
      // Тағамдар тізімі бір жолда: «Мия ×2; Париж ×1»
      function order_items_snap($oid) {
         $r = db::query("select product_id, quantity, price from retail_orders_products where order_id = '".(int)$oid."' order by id asc");
         $a = [];
         while ($p = mysqli_fetch_assoc($r)) { $pd = product::product($p['product_id']); $a[] = trim(html_entity_decode(strip_tags((string)@$pd['name_ru']))).' ×'.(int)$p['quantity']; }
         return implode('; ', $a);
      }
      function order_row($oid) { return mysqli_fetch_assoc(db::query("select * from retail_orders where id = '".(int)$oid."' limit 1")); }
   }
