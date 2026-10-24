<? include "../config/core.php"; ?>
    <? if (isset($_GET['search_now'])): ?>
        <? $search = strip_tags($_POST['inp']); ?>
        <? $search2 = substr($search, 1); ?>
        <? $order = db::query("select * from retail_orders where ins_dt BETWEEN '$start_cdate' and '$end_cdate' and `phone` LIKE '%$search2%' limit 1"); ?>

        <? if (mysqli_num_rows($order)): ?>
            <? $order_d = mysqli_fetch_assoc($order); ?>
            <h6><?=t::w('Ordered today', 'Бұл нөмерде бүгін заказ болған')?></h6>
            <div class="uc_ui">
                <div class="uc_uil2" >
                    <div class="uc_uil2_top">
                        <div class="uc_uil2_nmb"><?=$order_d['number']?></div>
                    </div>
                    <div class="uc_uil2_date">
                        <div class=""><?=date("d-m-Y", strtotime($order_d['ins_dt']))?> ⌛ <?=date("H:i", strtotime($order_d['ins_dt']))?></div>
                    </div>
                </div>
            </div>
        <? endif ?>

        <? exit(); ?>
	<? endif ?>