<? include "../config/core.php"; ?>

   <!--  -->
   <? if (isset($_GET['order_search'])): ?>
		<? $cmp = fun::company($company); ?>
		<? $search = addslashes(strip_tags(@$_POST['result'])); ?>

      <? $orders = db::query("select * from retail_orders where ins_dt BETWEEN '$start_cdate' and '$end_cdate' and paid = 1 and branch_id = '$branch' and ((number like '%$search%') or (phone like '%$search%') or (address like '%$search%')) order by number desc limit 50"); ?>
      <? if (mysqli_num_rows($orders)): ?>

         <? while ($buy_d = mysqli_fetch_assoc($orders)): ?>
            <? include __DIR__."/card.php"; ?>
         <? endwhile ?>

      <? else: ?>


      <? endif ?>

		<? exit(); ?>
	<? endif ?>