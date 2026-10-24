<? include "../config/core.php";
   
   
   // 
	if(isset($_GET['cashbox_add'])) {
      $pid = strip_tags($_POST['id']);
		$oid = strip_tags($_POST['oid']);
      $pitem_d = product::product($pid);
      if ($pitem_d['price']) $price = $pitem_d['price']; else $price = 0;

      $retailop = db::query("select * from retail_orders_products where order_id = '$oid' and product_id = '$pid'");
      if (mysqli_num_rows($retailop)) $upd = db::query("UPDATE `retail_orders_products` SET quantity = quantity + 1 WHERE `order_id`='$oid' and product_id = '$pid'");
      else $ins = db::query("INSERT INTO `retail_orders_products`(`order_id`, `product_id`, `quantity`, `price`) VALUES ('$oid', '$pid', 1, $price)");
      if (@$upd || @$ins) echo 'yes';
      
      exit();
	}

   // cashbox_search
   if(isset($_GET['cashbox_search'])) {
		$search = strip_tags($_POST['inp']);
		$oid = strip_tags($_POST['oid']);

      $pitem = db::query("select * from product_item where (barcode = '$search' and barcode is not null) or (article = '$search' and article is not null)");
      if (mysqli_num_rows($pitem)) {
         $pitem_d = mysqli_fetch_assoc($pitem);
         $pitem_id = $pitem_d['id'];
         $product_d = product::product($pitem_d['product_id']);
         $product_id = $pitem_d['product_id'];
         if ($pitem_d['price']) $price = $pitem_d['price']; else $price = 0;

         $quantity = db::query("select * from product_item_quantity where item_id = '$pitem_id' and quantity != 0 limit 1");
         if (mysqli_num_rows($quantity)) {
            $quantity = mysqli_fetch_array($quantity);
            $quantity_id = $quantity['id'];

            $retailop = db::query("select * from retail_orders_products where order_id = '$oid' and product_id = '$product_id' and product_item_id = '$pitem_id'");
            if (mysqli_num_rows($retailop))  $upd = db::query("UPDATE `retail_orders_products` SET quantity = quantity + 1 WHERE `order_id`='$oid' and `product_id`='$product_id' and product_item_id = '$pitem_id'");
            else $ins = db::query("INSERT INTO `retail_orders_products`(`order_id`, `product_id`, `product_item_id`, `quantity`, `price`) VALUES ('$oid', '$product_id', '$pitem_id', 1, $price)");
            if ($upd || $ins) echo 'yes';
         } else echo 0;
      } else echo 'none';
      exit();
	}


   // cashbox_remove
	if(isset($_GET['cashbox_remove'])) {
		$id = strip_tags($_POST['id']);
      $del = db::query("DELETE FROM `retail_orders_products` where id = '$id'");
      if ($del) echo 'yes';

      // $retailop_d = fun::retailop($id);
      // $pitem_id = $retailop_d['product_item_id'];
      // $qn = $retailop_d['quantity'];
      // $quantity = db::query("select * from product_item_quantity where item_id = '$pitem_id' limit 1");
      // if (mysqli_num_rows($quantity)) {
      //    $quantity = mysqli_fetch_array($quantity);
      //    $quantity_id = $quantity['id'];
      //    $upd2 = db::query("UPDATE `product_item_quantity` SET quantity = quantity + '$qn' WHERE id = '$quantity_id'");
      // }

      exit();
	}


   // cashbox_pr
	if(isset($_GET['cashbox_pr'])) {
		$id = strip_tags($_POST['id']);
		$pr = strip_tags($_POST['pr']);
      $upd = db::query("UPDATE `retail_orders_products` SET price = '$pr' WHERE `id`='$id'");
      if ($upd) echo 'yes'; else echo 'none';

      exit();
	}

   // cashbox_qn
	if(isset($_GET['cashbox_qn'])) {
		$id = strip_tags($_POST['id']);
		$qn = strip_tags($_POST['qn']);
      $retailop_d = fun::retailop($id);
      $pitem_id = $retailop_d['product_id'];
      $pitem_d = product::product($pitem_id);

      $quantity = db::query("select * from product_item_quantity where product_id = '$pitem_id' limit 1");
      if (mysqli_num_rows($quantity)) {
         $quantity = mysqli_fetch_array($quantity);
         $quantity_id = $quantity['id'];

         // $min_qn = ($quantity['quantity'] + $retailop_d['quantity']) - $qn;
         // $upd = db::query("UPDATE `product_item_quantity` SET quantity = '$min_qn' WHERE id = '$quantity_id'");
         $upd2 = db::query("UPDATE `retail_orders_products` SET quantity = '$qn' WHERE `id`='$id'");
         if ($upd2) echo 'yes'; else echo 'none';
      } else echo 0;

      exit();
	}

   // cashbox_plus
	if(isset($_GET['cashbox_plus'])) {
		$id = strip_tags($_POST['id']);
      $upd1 = db::query("UPDATE `retail_orders_products` SET `quantity` = `quantity` + 1 WHERE id = '$id';");
      if ($upd1) echo 'yes'; else echo 'none';
      exit();
	}

   // cashbox_minus
	if(isset($_GET['cashbox_minus'])) {
		$id = strip_tags($_POST['id']);
      $upd1 = db::query("UPDATE `retail_orders_products` SET `quantity` = `quantity` - 1 WHERE id = '$id';");
      if ($upd1) echo 'yes'; else echo 'none';
      exit();
	}


   // cashbox_pay
	if(isset($_GET['cashbox_pay'])) {

      $cmp = fun::company($company);

		$id = strip_tags($_POST['id']);
		$total = strip_tags($_POST['total']);
		$qr = @strip_tags($_POST['qr']);
		$cash = @strip_tags($_POST['cash']);
		$delivery = @strip_tags($_POST['delivery']);
		$address = @strip_tags($_POST['address']);
		$add = @strip_tags($_POST['add']);
		$preorder = @trim(strip_tags($_POST['preorder']));
		if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $preorder)) $preorder = '';
		if ($preorder && substr($preorder, 0, 10) > date('Y-m-d', strtotime('+7 day'))) $preorder = ''; // ең көбі 1 апта
		$type = @strip_tags($_POST['type']);
		$order_type = @strip_tags($_POST['order_type']);
		$order_branch = @strip_tags($_POST['order_branch']);
		$service = @mysqli_real_escape_string(db::$connect, trim(strip_tags($_POST['service'])));
		$payment_method = @trim($_POST['payment_method']);
		if (!in_array($payment_method, ['transfer', 'qr', 'cash', 'mixed'], true)) $payment_method = '';
		$transfer = (int)@$_POST['transfer'];
		$prepay = (int)@$_POST['prepay'];
		$red = (int)@$_POST['red'];
		$prepay_method = @trim($_POST['prepay_method']);
		if (!in_array($prepay_method, ['transfer', 'qr', 'cash', 'red', 'mix'], true)) $prepay_method = '';
		// $ynd = @strip_tags($_POST['ynd']);

		$phone = @strip_tags($_POST['phone']);
      $phone2 = substr($phone, 1);

      // Предзаказ: ins_dt = предзаказ уақыты, нөмірі сол күннің ретімен
      $ins_dt = $datetime; $n_start = $start_cdate; $n_end = $end_cdate;
      if ($preorder) {
         $ins_dt = $preorder.':00';
         if ($ins_dt < $start_cdate || $ins_dt >= $end_cdate) {
            $n_start = date('Y-m-d 06:00:00', strtotime($ins_dt.' -6 hours'));
            $n_end = date('Y-m-d 06:00:00', strtotime($n_start.' +1 day'));
         }
      }
      $cashbox_number = product::next_number_order($n_start, $n_end, $company, $order_branch);

      if ($type != 'ubd') {
         $upd = db::query("UPDATE `retail_orders` SET `number` = '$cashbox_number', `branch_id` = '$order_branch', `paid` = 1, `total` = '$total', `ins_dt` = '$ins_dt', `upd_dt` = '$datetime' WHERE `id`='$id'");
      } else {
         $upd = db::query("UPDATE `retail_orders` SET `paid` = 1, `total` = '$total', `ubd` = `ubd` + 1, `upd_dt` = '$datetime' WHERE `id`='$id'");
      }

      
      if ($qr) $upd = db::query("UPDATE `retail_orders` SET `pay_qr` = '$qr', `upd_dt` = '$datetime' WHERE `id`='$id'");
      else $upd = db::query("UPDATE `retail_orders` SET `pay_qr` = 0, `upd_dt` = '$datetime' WHERE `id`='$id'");
      
      // if ($delivery) $upd = db::query("UPDATE `retail_orders` SET `pay_delivery` = '$delivery', `order_type` = 1, `upd_dt` = '$datetime' WHERE `id`='$id'");
      // else $upd = db::query("UPDATE `retail_orders` SET `pay_delivery` = 0, `order_type` = 2, `upd_dt` = '$datetime' WHERE `id`='$id'");
      if ($delivery) $upd = db::query("UPDATE `retail_orders` SET `pay_delivery` = '$delivery',`upd_dt` = '$datetime' WHERE `id`='$id'");
      if ($order_type) $upd = db::query("UPDATE `retail_orders` SET `order_type` = $order_type, `upd_dt` = '$datetime' WHERE `id`='$id'");

      // Төлем түрі (Кафе): payment_method (transfer / qr / cash / mixed), аударым сомасы pay_transfer бағанына (бағаны бар болса)
      if ($payment_method !== '') {
         $upd = db::query("UPDATE `retail_orders` SET `payment_method` = '$payment_method', `upd_dt` = '$datetime' WHERE `id`='$id'");
         if (mysqli_num_rows(db::query("SHOW COLUMNS FROM `retail_orders` LIKE 'pay_transfer'"))) {
            $upd = db::query("UPDATE `retail_orders` SET `pay_transfer` = '$transfer', `upd_dt` = '$datetime' WHERE `id`='$id'");
         }
      }

      // Предоплата бөлек: pay_prepay (сома) және prepay_method (transfer / qr), бағандары бар болса
      if (mysqli_num_rows(db::query("SHOW COLUMNS FROM `retail_orders` LIKE 'pay_prepay'"))) {
         $upd = db::query("UPDATE `retail_orders` SET `pay_prepay` = '$prepay', `upd_dt` = '$datetime' WHERE `id`='$id'");
      }
      if (mysqli_num_rows(db::query("SHOW COLUMNS FROM `retail_orders` LIKE 'prepay_method'"))) {
         $pm_val = $prepay_method !== '' ? "'$prepay_method'" : 'NULL';
         $upd = db::query("UPDATE `retail_orders` SET `prepay_method` = $pm_val, `upd_dt` = '$datetime' WHERE `id`='$id'");
      }

      // RED (қызмет ақысы) сомасы бөлек: pay_red бағаны бар болса
      if (mysqli_num_rows(db::query("SHOW COLUMNS FROM `retail_orders` LIKE 'pay_red'"))) {
         $upd = db::query("UPDATE `retail_orders` SET `pay_red` = '$red', `upd_dt` = '$datetime' WHERE `id`='$id'");
      }

      // Сервис (Яндекс / Глово / Волт / басқа): retail_orders.service бағаны бар болса ғана жазылады
      if (mysqli_num_rows(db::query("SHOW COLUMNS FROM `retail_orders` LIKE 'service'"))) {
         $service_val = ($order_type == 3 && $service !== '') ? "'$service'" : 'NULL';
         $upd = db::query("UPDATE `retail_orders` SET `service` = $service_val, `upd_dt` = '$datetime' WHERE `id`='$id'");
      }


      if ($cash) $upd = db::query("UPDATE `retail_orders` SET `pay_cash` = '$cash', `upd_dt` = '$datetime' WHERE `id`='$id'");
      else $upd = db::query("UPDATE `retail_orders` SET `pay_cash` = 0, `upd_dt` = '$datetime' WHERE `id`='$id'");

      if ($phone) $upd = db::query("UPDATE `retail_orders` SET `phone` = '$phone2', `upd_dt` = '$datetime' WHERE `id`='$id'");
      if ($address) $upd = db::query("UPDATE `retail_orders` SET `address` = '$address', `upd_dt` = '$datetime' WHERE `id`='$id'");
      if ($add) $upd = db::query("UPDATE `retail_orders` SET `additional` = '$add', `upd_dt` = '$datetime' WHERE `id`='$id'");
      if ($preorder) $upd = db::query("UPDATE `retail_orders` SET `preorder_dt` = '$preorder', `ins_dt` = '$preorder:00', `upd_dt` = '$datetime' WHERE `id`='$id'");
      
      // if ($ynd) $upd = db::query("UPDATE `retail_orders` SET `order_type` = 3, `upd_dt` = '$datetime' WHERE `id`='$id'");
      


      // Клиент: client кестесіне бірден қосу/жаңарту (нөмір 10 санға келтіріледі)
      $cl_ph = preg_replace('/\D+/', '', (string)$phone);
      if (strlen($cl_ph) == 11 && ($cl_ph[0] == '8' || $cl_ph[0] == '7')) $cl_ph = substr($cl_ph, 1);
      if (strlen($cl_ph) == 10 && $cl_ph[0] == '7' && mysqli_num_rows(db::query("SHOW TABLES LIKE 'client'"))) {
         $cl_cmp = (int)$company;
         $cl_addr = trim(preg_replace('/\s+/', ' ', (string)$address));
         $cl_has_list = mysqli_num_rows(db::query("SHOW COLUMNS FROM `client` LIKE 'addresses'"));
         $cl = mysqli_fetch_assoc(db::query("select * from client where company_id = '$cl_cmp' and phone = '$cl_ph' limit 1"));
         $cl_e = function ($v) { return mysqli_real_escape_string(db::$connect, (string)$v); };
         if (!$cl) {
            db::query("INSERT IGNORE INTO `client` (`company_id`, `phone`, `address`, `first_dt`, `last_dt`, `ins_dt`)
               VALUES ('$cl_cmp', '$cl_ph', ".($cl_addr !== '' ? "'".$cl_e($cl_addr)."'" : 'NULL').", '$ins_dt', '$ins_dt', '$datetime')");
            $cl = mysqli_fetch_assoc(db::query("select * from client where company_id = '$cl_cmp' and phone = '$cl_ph' limit 1"));
         }
         if ($cl) {
            $set = ["`upd_dt` = '$datetime'"];
            if ($cl_addr !== '') $set[] = "`address` = '".$cl_e($cl_addr)."'"; // соңғы адрес
            if (!$cl['last_dt'] || $cl['last_dt'] < $ins_dt) $set[] = "`last_dt` = '$ins_dt'";
            // Барлық адрестер бір бағанда, «; » белгісімен (қайталанбайды)
            if ($cl_has_list && $cl_addr !== '') {
               $list = trim((string)$cl['addresses']);
               if (mb_stripos('; '.$list.'; ', '; '.$cl_addr.'; ') === false) {
                  $list = $list === '' ? $cl_addr : $list.'; '.$cl_addr;
                  $set[] = "`addresses` = '".$cl_e($list)."'";
               }
            }
            db::query("UPDATE `client` SET ".implode(', ', $set)." WHERE `id` = '".(int)$cl['id']."'");
            if (mysqli_num_rows(db::query("SHOW COLUMNS FROM `retail_orders` LIKE 'client_id'"))) {
               db::query("UPDATE `retail_orders` SET `client_id` = '".(int)$cl['id']."' WHERE `id` = '".(int)$id."'");
            }
         }
      }

      // Жауапты бірден қайтарамыз (чек кідірмейді), Telegram артынан фонда жіберіледі
      $resp = $upd ? 'yes' : 'error';
      ignore_user_abort(true);
      set_time_limit(60);
      if (session_status() == PHP_SESSION_ACTIVE) session_write_close();
      if (function_exists('fastcgi_finish_request')) {
         echo $resp;
         fastcgi_finish_request();
      } else {
         while (ob_get_level()) ob_end_clean();
         header('Connection: close');
         header('Content-Encoding: none');
         header('Content-Length: '.strlen($resp));
         echo $resp;
         flush();
      }

      if ($type != 'ubd' && $cmp['chat_send'] == 1) {
         $chat_id = $cmp['chat_id'];
         $tg = function ($v) { return htmlspecialchars(trim(strip_tags((string)$v)), ENT_QUOTES, 'UTF-8'); };
         $tg_sum = function ($v) { return number_format((int)$v, 0, '', ' ').' тг'; };
         $type_names = [1 => 'Жеткізу', 2 => 'Өзімен', 3 => 'Сервис', 4 => 'Кафе'];
         $pm_names = ['transfer' => 'Аударым', 'qr' => 'Kaspi QR', 'cash' => 'Қолма-қол', 'red' => 'RED', 'mix' => 'Аралас', 'mixed' => 'Аралас'];
         $branch_d = $order_branch ? fun::branch($order_branch) : null;
         $branch_cnt = mysqli_num_rows(db::query("select id from company_branch where company_id = '".(int)$cmp['id']."'"));

         // Тауарлар тізімі
         $items = ''; $items_sum = 0;
         $has_comp = mysqli_num_rows(db::query("SHOW COLUMNS FROM `product_item` LIKE 'comp_id'")) > 0;
         $op = db::query("select * from retail_orders_products where order_id = '$id' order by ins_dt asc");
         while ($op_d = mysqli_fetch_assoc($op)) {
            $pr_d = product::product($op_d['product_id']);
            $line_sum = (int)$op_d['price'] * (int)$op_d['quantity'];
            $items_sum += $line_sum;
            $items .= "• ".$tg($pr_d['name_kz'] ?: $pr_d['name_ru']).": ".(int)$op_d['quantity']." × ".$tg_sum($op_d['price'])." = ".$tg_sum($line_sum)."\n";
            // Сеттің құрамы (product_item: comp_id × quantity)
            $pi = $has_comp ? db::query("select * from product_item where product_id = '".(int)$op_d['product_id']."' and comp_id is not null") : false;
            while ($pi && $pi_d = mysqli_fetch_assoc($pi)) {
               $comp_d = product::product($pi_d['comp_id']);
               if ($comp_d) $items .= "      ◦ ".$tg($comp_d['name_kz'] ?: $comp_d['name_ru']).((int)$pi_d['quantity'] > 1 ? " × ".(int)$pi_d['quantity'] : "")."\n";
            }
         }

         $txt = "<b>Тапсырыс:</b> #".$tg($cashbox_number)."\n";
         $info = [
            'Точка'      => ($branch_d && $branch_cnt > 1) ? $branch_d['name'] : '',
            'Формат'     => ($type_names[(int)$order_type] ?? '').($order_type == 3 && $service !== '' ? ' ('.$service.')' : ''),
            'Телефон'    => (strlen(preg_replace('/\D+/', '', (string)$phone)) > 4 ? $phone : ''),
            'Адрес'      => $address,
            'Қосымша'    => $add,
            'Предзаказ'  => $preorder,
         ];
         foreach ($info as $k => $v) if (trim((string)$v) !== '' && $v !== '0') $txt .= "<b>$k:</b> ".$tg($v)."\n";

         if ($items) $txt .= "\n<b>Тауарлар:</b>\n".$items;

         $txt .= "\n";
         if ($items_sum) $txt .= "<b>Тауар сомасы:</b> ".$tg_sum($items_sum)."\n";
         if ((int)$delivery) $txt .= "<b>Жеткізу:</b> ".$tg_sum($delivery)."\n";
         if ($red) $txt .= "<b>RED:</b> ".$tg_sum($red)."\n";
         $txt .= "<b>Жалпы:</b> ".$tg_sum($total)."\n";

         $pay = '';
         if ($prepay) $pay .= "<b>Предоплата:</b> ".$tg_sum($prepay).($prepay_method ? ' ('.$pm_names[$prepay_method].')' : '')."\n";
         if ((int)$transfer) $pay .= "<b>Аударым:</b> ".$tg_sum($transfer)."\n";
         if ((int)$qr) $pay .= "<b>Kaspi QR:</b> ".$tg_sum($qr)."\n";
         if ((int)$cash) $pay .= "<b>Қолма-қол:</b> ".$tg_sum($cash)."\n";
         if ($payment_method) $pay .= "<b>Төлем түрі:</b> ".$pm_names[$payment_method]."\n";
         if ($pay) $txt .= "\n".$pay;

         // URL запроса
         $url = "https://api.telegram.org/bot{$token}/sendMessage";

         // Настройки curl
         $ch = curl_init();
         curl_setopt($ch, CURLOPT_URL, $url);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
         curl_setopt($ch, CURLOPT_TIMEOUT, 15);
         curl_setopt($ch, CURLOPT_POST, true);
         curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'chat_id'    => $chat_id,
            'parse_mode' => 'html',
            'text'       => $txt
         ]);
         $response = curl_exec($ch);
         if (curl_errno($ch)) error_log('telegram: ' . curl_error($ch));
         curl_close($ch);
      }

      exit();
	}

















   // product_add
	if(isset($_GET['product_add'])) {
      $article = strip_tags($_POST['article']);
		$barcode = strip_tags($_POST['barcode']);
		$quantity = strip_tags($_POST['quantity']);
		$price = strip_tags($_POST['price']);
      $oid = strip_tags($_POST['oid']);
		$name = strip_tags($_POST['name']);
		$warehouses_id = 5;
		
      $id = (mysqli_fetch_assoc(db::query("SELECT max(id) FROM `product`")))['max(id)'] + 1;
      $ins = db::query("INSERT INTO `product`(`id`, `arh`) VALUES ('$id', 1)");
      if ($ins) {
         if ($name) $sql = db::query("UPDATE `product` SET `name_kz` = '$name', `name_ru` = '$name' WHERE `id`='$id'");

			$item_id = (mysqli_fetch_assoc(db::query("SELECT max(id) FROM `product_item`")))['max(id)'] + 1;
         $ins_item = db::query("INSERT INTO `product_item`(`id`, `product_id`, `arh`) VALUES ('$item_id', '$id', 1)");
			if ($ins_item) {
				if ($article) $sql = db::query("UPDATE `product_item` SET `article`='$article' WHERE id = '$item_id'");
				if ($barcode) $sql = db::query("UPDATE `product_item` SET `barcode`='$barcode' WHERE id = '$item_id'");
				if ($price) $sql = db::query("UPDATE `product_item` SET `price`='$price' WHERE id = '$item_id'"); else $price = 0;

				if ($quantity) {
               $view_id = (mysqli_fetch_assoc(db::query("SELECT max(id) FROM `product_item_quantity`")))['max(id)'] + 1;
               $ins_view = db::query("INSERT INTO `product_item_quantity`(`id`, `product_id`, `item_id`, `warehouses_id`, `quantity`) VALUES ('$view_id', '$id', '$item_id', '$warehouses_id', '$quantity')");
               $ins = db::query("INSERT INTO `retail_orders_products`(`order_id`, `product_id`, `product_item_id`, `quantity`, `price`) VALUES ('$oid', '$id', '$item_id', '$quantity', '$price')");
            }
			}

         echo 'yes';
      } else echo 'error';

      exit();
	}









   // 
   if(isset($_GET['product_add_arh'])) {
      $catalog = strip_tags($_POST['catalog']);
		$brand = strip_tags($_POST['brand']);
		$purchase_price = strip_tags($_POST['purchase_price']);
		$discount_price = strip_tags($_POST['discount_price']);
		$img = strip_tags($_POST['img']);
		$color = strip_tags($_POST['color']);
		$size = strip_tags($_POST['size']);
		$warehouses_id = strip_tags($_POST['warehouses']);

      if ($catalog) $sql = db::query("UPDATE `product` SET `catalog_id`='$catalog' WHERE `id`='$id'");
      if ($brand) {
         $brand_id = product::pr_brand_name($brand);
         if ($brand_id == 0) {
            $brand_id = (mysqli_fetch_assoc(db::query("SELECT max(id) FROM `product_ch_brand`")))['max(id)'] + 1;
            $ins = db::query("INSERT INTO `product_ch_brand`(`id`, `name`) VALUES ('$brand_id', '$brand')");
         }
         $upd = db::query("UPDATE `product` SET `brand_id`='$brand_id' WHERE `id`='$id'");
      }

      if ($purchase_price) $sql = db::query("UPDATE `product_item` SET `purchase_price`='$purchase_price' WHERE id = '$item_id'");
      if ($discount_price) $sql = db::query("UPDATE `product_item` SET `discount_price`='$discount_price' WHERE id = '$item_id'");
      if ($img) $sql = db::query("UPDATE `product_item` SET `img`='$img' WHERE id = '$item_id'");
      if ($color) {
         $color_id = product::pr_color_name($color);
         if ($color_id == 0) {
            $color_id = (mysqli_fetch_assoc(db::query("SELECT max(id) FROM `product_item_color`")))['max(id)'] + 1;
            $ins = db::query("INSERT INTO `product_item_color`(`id`, `name_ru`) VALUES ('$color_id', '$color')");
         }
         $upd = db::query("UPDATE `product_item` SET `color_id` = '$color_id' WHERE id = '$item_id'");
      }
      if ($size) {
         $size_id = product::pr_size_name($size);
         if ($size_id == 0) {
            $size_id = (mysqli_fetch_assoc(db::query("SELECT max(id) FROM `product_item_size`")))['max(id)'] + 1;
            $ins = db::query("INSERT INTO `product_item_size`(`id`, `name`) VALUES ('$size_id', '$size')");
         }
         $upd = db::query("UPDATE `product_item` SET `size_id` = '$size_id' WHERE id = '$item_id'");
      }
   
   }




  // cashbox_plus
	// Нөмір бойынша бұрынғы адрес: алдымен client, болмаса соңғы тапсырыс
	if(isset($_GET['phone_addr'])) {
		$d = preg_replace('/\D+/', '', (string)@$_POST['phone']);
		if (strlen($d) == 11 && ($d[0] == '8' || $d[0] == '7')) $d = substr($d, 1);
		$res = ['address' => '', 'name' => ''];
		if (strlen($d) == 10) {
			$cmp = (int)$company;
			if (mysqli_num_rows(db::query("SHOW TABLES LIKE 'client'"))) {
				$cl = db::query("select name, address from client where company_id = '$cmp' and phone = '$d' and archive = 0 limit 1");
				if ($cl && $c = mysqli_fetch_assoc($cl)) $res = ['address' => (string)$c['address'], 'name' => (string)$c['name']];
			}
			if ($res['address'] == '') {
				$ro = db::query("select address from retail_orders where company_id = '$cmp' and phone like '%$d' and address is not null and address <> '' order by ins_dt desc limit 1");
				if ($ro && $r = mysqli_fetch_assoc($ro)) $res['address'] = (string)$r['address'];
			}
		}
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($res, JSON_UNESCAPED_UNICODE);
		exit();
	}

	if(isset($_GET['search_now'])) {
		$search = strip_tags($_POST['inp']);
      $search2 = substr($search, 1);
      $order = db::query("select * from retail_orders where ins_dt BETWEEN '$start_cdate' and '$end_cdate' and `phone` LIKE '%$search2%' limit 1");
      if (mysqli_num_rows($order)) echo 'yes';
      else echo 'none';
      exit();
	}



   