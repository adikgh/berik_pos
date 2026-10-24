<? include "../config/core.php";
   
   // returns
	if(isset($_GET['return_sn'])) {
		$id = strip_tags($_POST['id']);
      $order_d = fun::retailo($id);
      $total = $order_d['total'];
      $quantity_all = $order_d['quantity'];

      $return_id = (mysqli_fetch_assoc(db::query("SELECT * FROM `retail_returns` order by id desc")))['id'] + 1;
      $ins = db::query("INSERT INTO `retail_returns`(`id`, `user_id`, `returns`, `total`, `quantity`) VALUES ('$return_id', '$user_id', 1, '$total', '$quantity_all')");
      $upd = db::query("UPDATE retail_orders SET returned = 1 WHERE id = '$id'");

      $op = db::query("select * from retail_orders_products where order_id = '$id'");
		if (mysqli_num_rows($op)) {
         while ($op_d = mysqli_fetch_array($op)) {
            $product_id = $op_d['product_id'];
            $item_id = $op_d['product_item_id'];
            $quantity = $op_d['quantity'];
            $price = $op_d['price'];
            $ins = db::query("INSERT INTO `retail_returns_products`(`return_id`, `product_id`, `product_item_id`, `quantity`, `price`) VALUES ('$return_id', '$product_id', '$item_id', '$quantity', '$price')");
            
            $pq_id = (mysqli_fetch_array(db::query("select * from product_item_quantity where item_id = '$item_id' limit 1")))['id'];
            $upd = db::query("UPDATE `product_item_quantity` SET quantity = quantity + '$quantity' WHERE id = '$pq_id'");
         
         }

         $arr = array(
            'Номер возврата: ' => $return_id,
            'Сумма: ' => $total.' тг',
            'Количество: '	=> $quantity_all.' шт',
            'Продавец: ' => $user['name'],
         );
         foreach($arr as $key => $value) {$txt .= "<b>".$key."</b> ".$value."%0A";};
         $sendToTelegram = fopen("https://api.telegram.org/bot{$token}/sendMessage?chat_id={$chat_id}&parse_mode=html&text={$txt}","r");
         // if ($sendToTelegram) echo "yes"; else echo "error";

         echo 'yes';
      } else echo 'none';

      exit();
	}
   
   
   
 

   // 
	if(isset($_GET['delete'])) {
      $id = strip_tags($_POST['id']);
      $del = db::query("DELETE FROM `retail_orders` where id = '$id'");
      if ($del) echo 'yes';

      exit();
	}



   // 
	if(isset($_GET['change_status'])) {
		$id = strip_tags($_POST['id']);
		$order_id = strip_tags($_POST['order_id']);

      $upd = db::query("UPDATE `retail_orders` SET order_status = '$id' WHERE id = '$order_id'");
      echo 'yes';

      exit();
	}

   // 
	if(isset($_GET['change_type'])) {
		$id = strip_tags($_POST['id']);
		$order_id = strip_tags($_POST['order_id']);

      if ($id != 1) $upd = db::query("UPDATE `retail_orders` SET total = total - pay_delivery, pay_delivery = 0, сourier_id = null, `upd_dt` = '$datetime' WHERE id = '$order_id'");
      $upd = db::query("UPDATE `retail_orders` SET order_type = '$id', order_status = 2, `upd_dt` = '$datetime' WHERE id = '$order_id'");

      echo 'yes';

      exit();
	}
   
   // 
   if(isset($_GET['change_staff'])) {
      $id = strip_tags($_POST['id']);
      $order_id = strip_tags($_POST['order_id']);

      if ($id == 'kr') $upd = db::query("UPDATE `retail_orders` SET сourier_id = null, order_status = 2, `upd_dt` = '$datetime' WHERE id = '$order_id'");
      else $upd = db::query("UPDATE `retail_orders` SET сourier_id = '$id', order_status = 3, `upd_dt` = '$datetime' WHERE id = '$order_id'");
      echo 'yes';

      exit();
   }














   // cashbox_pay
	if(isset($_GET['cashbox_pay'])) {
		$cashbox_number = strip_tags($_POST['number']);
		$total = strip_tags($_POST['total']);
		$delivery = strip_tags($_POST['delivery']);
		$qr = strip_tags($_POST['qr']);
		$cash = $total - $qr;

      if ($delivery) $ins = db::query("INSERT INTO `retail_orders`(`user_id`, `number`, `paid`, `total`, `pay_qr`, `pay_cash`, `pay_delivery`, `order_type`, `company_id`, `branch_id`, `ins_dt`) VALUES (1, '$cashbox_number', 1, '$total', '$qr', '$cash', '$delivery', 1, '$company', '$branch', '$datetime')");
      else $ins = db::query("INSERT INTO `retail_orders`(`user_id`, `number`, `paid`, `total`, `pay_qr`, `pay_cash`, `order_type`, `company_id`, `branch_id`, `ins_dt`) VALUES (1, '$cashbox_number', 1, '$total', '$qr', '$cash', 2, '$company', '$branch', '$datetime')");
      
      if ($ins) echo 'yes';
      else echo 'none';

      exit();
	}








   // on_info