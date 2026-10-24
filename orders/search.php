<? include "../config/core.php"; ?>

   <!--  -->
   <? if (isset($_GET['order_search'])): ?>
		<? $search = strip_tags($_POST['result']); ?>

      <? $orders = db::query("select * from retail_orders where ins_dt BETWEEN '$start_cdate' and '$end_cdate' and paid = 1 and branch_id = '$branch' and ((number like '%$search%') or (phone like '%$search%') or (address like '%$search%')) order by number desc limit 50"); ?>
      <? if (mysqli_num_rows($orders)): ?>

         <? while ($buy_d = mysqli_fetch_assoc($orders)): ?>
            <? $order_sts = fun::order_sts($buy_d['order_status']); ?>
            <? // Статус түсі (атауы бойынша), тізімде жоқ болса базадағы түс
	$st_colors = ['Жаңа'=>'#1f75a8', 'Дайын'=>'#e9b949', 'Курьерде'=>'#d9480f', 'Аяқталған'=>'#0EA768', 'Отказ'=>'#e5484d', 'Отказ при получение'=>'#e86fa0', 'Вопрос'=>'#9aa0a6'];
	$st_name = trim(@$order_sts['name_ru']);
	$st_clr = isset($st_colors[$st_name]) ? $st_colors[$st_name] : (isset($st_colors[trim(@$order_sts['name_kz'])]) ? $st_colors[trim(@$order_sts['name_kz'])] : $order_sts['clr']);
?>

            <div class="uc_ui or_card" style="--st:<?=$st_clr?>;">
               <div class="uc_uil2" >
                  <div class="uc_uil2_top">
                     <div class="uc_uil2_nmb"><?=$buy_d['number']?></div>
                     <div class="uc_uil2_date">
                        <div class="uc_uil2_date1">
                           <? if ($buy_d['сourier_id']): $сourier_d = fun::user($buy_d['сourier_id']); ?>
                              <?=$сourier_d['name']?> <br> <span class="fr_phone"><?=$сourier_d['phone']?></span>
                           <? else: ?>
                              <select name="" id="" class="on_stype" data-order-id="<?=$buy_d['id']?>" >
                                 <option value="" <?=($buy_d['order_type']==1?'selected':'')?> data-id="kr"><?=t::w('Courier', 'Курьер')?></option>
                                 <option value="" <?=($buy_d['order_type']==2?'selected':'')?> data-id="sb"><?=t::w('Takeaway', 'Өзімен')?></option>
                                 
                                 <? $staff = db::query("select * from user_staff where positions_id = 6 and company_id = '$company'"); ?>
                                 <? while ($staff_d = mysqli_fetch_assoc($staff)): ?>
                                    <? $staff_user_d = fun::user($staff_d['user_id']); ?>
                                    <option value="" data-id="<?=$staff_d['user_id']?>"><?=$staff_user_d['name']?></option>
                                 <? endwhile ?>

                              </select>
                           <? endif ?>
                        </div>
                     </div>
                     <div class="or_status" style="background-color:<?=$st_clr?>;"> <?//=$order_sts['name_kz']?>
                        <select name="" id="" class="on_status" data-order-id="<?=$buy_d['id']?>" >
                           <? $orders_status = db::query("select * from retail_orders_status"); ?>
                           <? while ($orders_status_d = mysqli_fetch_assoc($orders_status)): ?><? if (trim($orders_status_d['name_ru']) == 'Вопрос' && @$order_sts['id'] != $orders_status_d['id'] && @$_GET['status'] != $orders_status_d['id']) continue; // «Вопрос» тізімде көрсетілмейді, базада қалады ?>
                              <option data-id="<?=$orders_status_d['id']?>" <?=($order_sts['id'] == $orders_status_d['id']?'selected':'')?> value="" ><?=t::w('st:'.$orders_status_d['name_ru'], $orders_status_d['name_ru'])?></option>
                           <? endwhile ?>
                        </select>
                     </div>
                  </div>
                  <br>
                  <div class="uc_uil2_date">
                     <div class=""><?=date("d-m-Y", strtotime($buy_d['ins_dt']))?> ⌛ <?=date("H:i", strtotime($buy_d['ins_dt']))?> <?=($buy_d['preorder_dt']?'| 🔴':'')?>  <?=($buy_d['preorder_dt']?$buy_d['preorder_dt']:'')?></div>
                  </div>
                  <div class="uc_uil2_raz">
                     <div class="uc_uil2_mi">
                        <div class="uc_uil2_mi1"><?=t::w('Address', 'Адрес:')?></div>
                        <div class="uc_uil2_mi2"><?=$buy_d['address']?></div>
                     </div>
                     <div class="uc_uil2_mi">
                        <div class="uc_uil2_mi1"><?=t::w('Number', 'Номер:')?></div>
                        <div class="uc_uil2_mi2 fr_phone"><?=$buy_d['phone']?></div>
                     </div>
                  </div>
                  <div class="uc_uil2_raz">
                     <div class="uc_uil2_trt">
                        <div class="uc_uil2_trt1"><?=t::w('Name', 'Атауы')?></div>
                        <div class="uc_uil2_trt2"><?=t::w('Qty', 'Саны')?></div>
                        <div class="uc_uil2_trt3"><?=t::w('Price', 'Бағасы')?></div>
                     </div>
                     <div class="uc_uil2_trc">

                        <? 	
                           $cashbox_id = $buy_d['id'];
                           $cashboxp = db::query("select * from retail_orders_products where order_id = '$cashbox_id' order by ins_dt asc");
                           $number = 0; $total = 0;
                        ?>
                        <? if (mysqli_num_rows($cashboxp) != 0): ?>
                           <? while ($sel_d = mysqli_fetch_assoc($cashboxp)): ?>
                              <? 
                                 $number++; 
                                 $sum = $sel_d['quantity'] * $sel_d['price']; 
                                 $total = $total + $sum;
                                 $product_d = product::product($sel_d['product_id']);
                              ?>
                              <div class="uc_uil2_trt">
                                 <div class="uc_uil2_trt1"><?=$number?>. <?=$product_d['name_ru']?></div>
                                 <div class="uc_uil2_trt2"><?=$sel_d['quantity']?> шт</div>
                                 <!-- <div class=""><?=$sel_d['price']?></div> -->
                                 <div class="uc_uil2_trt3 fr_price"><?=$sum?></div>
                              </div>
                           <? endwhile ?>
                        <? endif ?>
                        
                        <div class="uc_uil2_trt">
                           <div class="uc_uil2_trt1"><?=t::w('Delivery fee', 'Доставка')?></div>
                           <div class="uc_uil2_trt3 fr_price"><?=$buy_d['pay_delivery']?></div>
                        </div>
                     </div>
                     <div class="uc_uil2_trb">
                        <div class="uc_uil2_trt1"><?=t::w('Total', 'Жалпы')?></div>
                        <div class="uc_uil2_trt2"></div>
                        <div class="uc_uil2_trt3 fr_price"><?=$buy_d['total']?></div>
                     </div>
                     <div class="uc_uil2_trc">
                        <div class="uc_uil2_trt">
                           <div class="uc_uil2_trt1"><?=t::w('Prepayment', 'Предоплата')?></div>
                           <div class="uc_uil2_trt2">-</div>
                           <div class="uc_uil2_trt3 fr_price"><?=$buy_d['pay_qr']?></div>
                        </div>
                        <div class="uc_uil2_trt">
                           <div class="uc_uil2_trt1"><?=t::w('To courier (cash)', 'Курьерге (нал)')?></div>
                           <div class="uc_uil2_trt2"></div>
                           <div class="uc_uil2_trt3 fr_price"><?=$buy_d['pay_cash']?></div>
                        </div>
                     </div>
                  </div>
                  <div class="uc_uil2_raz">
                     <div class="uc_uil2_mib">
                        <a class="btn btn_cl on_ubd" data-id="<?=$buy_d['id']?>" href="/cashbox/?id=<?=$buy_d['id']?>&type=ubd"><?=t::w('Edit', 'Өзгерту')?></a>
                        <div class="btn on_print" data-id="<?=$buy_d['id']?>"><?=t::w('Print', 'Печать')?></div>
                     </div>
                  </div>
               </div>
            </div>

         <? endwhile ?>

      <? else: ?>


      <? endif ?>

		<? exit(); ?>
	<? endif ?>