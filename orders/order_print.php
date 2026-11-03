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

            // Лоялдық: кэшбэкпен төленгені, сыйлық, осы тапсырыстан түсетін кэшбэк және баланс
            require_once "../config/loyalty.php";
            $lc_p = null;
            if (loyalty::on() && loyalty::any($cashbox_d['company_id'])) {
                $lc_cfg = loyalty::cfg($cashbox_d['company_id']);
                $lc_cl = $cashbox_d['client_id'] ? mysqli_fetch_assoc(db::query("select cashback_balance from company_client where id = '".(int)$cashbox_d['client_id']."'")) : null;
                $lc_p = [
                    'paid' => (int)@$cashbox_d['pay_cashback'],
                    'gift' => trim((string)@$cashbox_d['gift_name']),
                    'disc' => (int)@$cashbox_d['gift_discount'],
                    'earn' => $lc_cl && $lc_cfg['cashback'] ? ((int)$cashbox_d['cashback_sum'] ?: loyalty::earn_preview($cashbox_d['company_id'], $cashbox_d['id'], @$cashbox_d['pay_cashback'], @$cashbox_d['gift_discount'])) : 0,
                    'bal' => $lc_cl && $lc_cfg['cashback'] ? (int)$lc_cl['cashback_balance'] : null,
                ];
            }
        }
    }

    // Чек ені: print_width (80 немесе 58), баған болмаса 80. Тексеру үшін ?w=58
    // Филиалдан (company_branch), бос болса 80 мм / SUSHI - PIZZA
    $pw = (int)(@$_GET['w'] ?: @$branch_d['print_width']);
    $ptitle = trim((string)@$branch_d['print_title']) ?: 'SUSHI - PIZZA';
    if ($pw != 58) $pw = 80;

    $number = 1;

?>

<link rel="stylesheet" href="/assets/css/print.css?v=<?=@filemtime('../assets/css/print.css')?>" />

<div class="app p<?=$pw?>">
    <div class="head">
        <div class="branch_name"><?=htmlspecialchars($ptitle)?></div>
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
        <? if ($lc_p && $lc_p['gift'] !== ''): ?>
        <tr class="tr" valign="top">
            <td align="left" colspan="2">Подарок: <?=htmlspecialchars($lc_p['gift'])?></td>
            <td align="right"><?=($lc_p['disc'] ? '- '.$lc_p['disc'] : '')?></td>
        </tr>
        <? endif ?>
        <? if ($lc_p && $lc_p['paid']): ?>
        <tr class="tr" valign="top">
            <td align="left">Оплачено кэшбэком</td>
            <td align="center"></td>
            <td align="right">- <?=$lc_p['paid']?></td>
        </tr>
        <? endif ?>
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
            <? $fa = trim((string)@$cashbox_d['floor_apt']); if ($fa !== ''): ?>
            <p><b><?=htmlspecialchars($fa)?></b></p>
            <? endif ?>
            <? if ($lc_p && $lc_p['bal'] !== null): ?>
            <p>Кэшбэк за заказ: <b>+<?=$lc_p['earn']?></b></p>
            <p>Баланс кэшбэка: <b><?=$lc_p['bal']?></b></p>
            <? endif ?>
        </div>
    </div>

</div>


<script type="text/javascript">
    <? if ($pw == 58): ?>document.body.className += ' b58'<? endif ?>

    window.onload = function() {
	    window.print();
    }
</script>