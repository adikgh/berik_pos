<? include "../config/core.php";

    $print = 0;

    if (@$_GET['orderID']) {
        $orderID = $_GET['orderID'];

        $cashbox = db::query("select * from retail_orders where id = '$orderID'");
        if (mysqli_num_rows($cashbox)) {
            $cashbox_d = mysqli_fetch_assoc($cashbox);
            $upd = db::query("UPDATE `retail_orders` SET `print` = `print` + 1 WHERE `id`='$orderID'");
            if  ($cashbox_d['print'] > 0) $print = 1;

            $branch_d = fun::branch($cashbox_d['branch_id']);
            $cmp_p = fun::company($cashbox_d['company_id'] ?: $company);
        }
    }

    // Чек ені: company.print_width (80 немесе 58), баған болмаса 80. Тексеру үшін ?w=58
    $pw = (int)(@$_GET['w'] ?: @$cmp_p['print_width']);
    if ($pw != 58) $pw = 80;

    $number = 1;

?>

<link rel="stylesheet" href="/assets/css/print.css?v=<?=@filemtime('../assets/css/print.css')?>" />

<div class="app p<?=$pw?>">
    <div class="head">
        <div class="branch_name"><?=htmlspecialchars(trim((string)@$cmp_p['print_title']) ?: 'SUSHI - PIZZA')?></div>
        <? if ($cashbox_d['preorder_dt']): ?>
            <div class="pred_order">
                <div>ПРЕДЗАКАЗ</div>
                <p><?=$cashbox_d['preorder_dt']?></p>
            </div>
        <? endif ?>
        <? if ($cashbox_d['ubd'] > 0): ?>
            <div class="pred_order">
                <div>ИЗМЕНЕНО-РАСП.</div>
            </div>
        <? elseif ($print == 1): ?>
            <div class="pred_order">
                <div>РАСПЕЧАТАНО</div>
            </div>
        <? endif ?>
    </div>
    <? if ($cashbox_d['order_type'] > 1): ?>
        <? $type = fun::order_type($cashbox_d['order_type']) ?>
        <div class="soboi"><?=$type['name']?></div>
    <? endif ?>

    
    <div class="head2">
        <div class="number"><?=$branch_d['code']?><?=$cashbox_d['number']?></div>
        <div class="head_bd">
            <div class=""><b><?=date('d-m-Y, H:i', strtotime($cashbox_d['upd_dt']))?></b></div>
        </div>
        <? if ($cashbox_d['additional']): ?>
            <div class="additional"><?=$cashbox_d['additional']?></div>
        <? endif ?>
    </div>

    <table>
        <tr class="tr_border">
            <td><b>Наименование</b></td>
            <td width="40" align="center"><b>Кол.</b></td>
            <td width="50" align="right"><b>Сумма</b></td>
        </tr>

        <? $cashboxp = db::query("select * from retail_orders_products where order_id = '$orderID' order by ins_dt asc"); ?>
        <? while ($sel_d = mysqli_fetch_assoc($cashboxp)): ?>
            <? $product = product::product($sel_d['product_id'])?>
            <tr class="tr" valign="top">
                <td align="left"><?=$number?>. <?=$product['name_ru']?></td>
                <td class="san" align="center"><?=$sel_d['quantity']?></td>
                <td align="right"><?=($sel_d['quantity'] * $sel_d['price'])?></td>
            </tr>
            <? $number++ ?>
        <? endwhile ?>
        <tr class="tr" valign="top">
            <td align="left">Доставка</td>
            <td align="center"></td>
            <td align="right"><?=$cashbox_d['pay_delivery']?></td>
        </tr>
        <tr class="tr_sep2"><td colspan="3"></td></tr>
        <tr class="tr_sep1"><td colspan="3"></td></tr>
        <tr class="tr_sep2"><td colspan="3"></td></tr>
        <tr class="tr">
            <td align="left"><b>ОБЩИЙ</b></td>
            <td></td>
            <td align="right"><b><?=$cashbox_d['total']?></b></td>
        </tr>


        <tr class="tr_sep2"><td colspan="3"></td></tr>
        <tr class="tr_sep2"><td colspan="3"></td></tr>
        <tr class="tr" valign="top">
            <td align="left">Предоплата</td>
            <td align="center"></td>
            <td align="right">- <?=$cashbox_d['pay_qr']?></td>
        </tr>
        <tr class="tr_sep2"><td colspan="3"></td></tr>
        <tr class="tr_sep1"><td colspan="3"></td></tr>
        <tr class="tr_sep2"><td colspan="3"></td></tr>
        <tr class="tr">
            <td align="left"><b>К ОПЛАТЕ</b></td>
            <td></td>
            <td align="right"><b><?=$cashbox_d['pay_cash']?></b></td>
        </tr>
    </table>

    <div class="clens">
        <div class="clens1">Клиент</div>
        <div class="clens2">
            <p>Номер: <b>8<?=$cashbox_d['phone']?></b></p>
            <p>Адрес: <b><?=$cashbox_d['address']?></b></p>
        </div>
    </div>

</div>


<script type="text/javascript">
    <? if ($pw == 58): ?>document.body.className += ' b58'<? endif ?>

    window.onload = function() {
	    window.print();
    }
</script>