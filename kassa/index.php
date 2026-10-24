<? include "../config/core.php";

	// 
	// if (!$user_id) header('location: /');



    $cashbox = db::query("select * from report_1 where user_id = '$user_id' and paid = 0 and branch_id = '$branch' order by id desc limit 1");
	if (mysqli_num_rows($cashbox)) {
		$cashbox_d = mysqli_fetch_assoc($cashbox);
		$cashbox_id = $cashbox_d['id'];
	} else {
		$cashbox_id = (mysqli_fetch_assoc(db::query("SELECT * FROM `report_1` order by id desc")))['id'] + 1;
		$ins = db::query("INSERT INTO `report_1`(`id`, `user_id`, `branch_id`) VALUES ('$cashbox_id', '$user_id', '$branch')");
	}
	$cashboxp = db::query("select * from report_сourier where report_id = '$cashbox_id' order by ins_dt asc");
    




    


	// site setting
	$menu_name = 'main';
	$css = ['kassa'];
	$js = ['kassa'];
?>
<? include "../block/header.php"; ?>

	<div class="bl_c">

			<div class="table4">

				<table>

                    <tbody>

                        <? 
                            $onw['number'] = 0;
                            $onw['total'] = 0;
                            $onw['pay_qr'] = 0;
                            $onw['pay_cash'] = 0;
                            $onw['pay_delivery'] = 0;
                            $onw['rask'] = 0;
                            $onw['cash'] = 0;
                            $onw['kaspi'] = 0;
                            $staff = db::query("select * from user_staff where positions_id = 6");
                        ?>
                        <? while ($staff_d = mysqli_fetch_assoc($staff)): ?>
                            <? $staff_user_d = fun::user($staff_d['user_id']); ?>
                            <? $staff_id = $staff_d['user_id']; ?>
                            <? $orders = db::query("select * from retail_orders where ins_dt BETWEEN '$start_cdate' and '$end_cdate' and сourier_id  = '$staff_id' and branch_id = '$branch' order by number desc"); ?>
                            <? $report_сourier_d = fun::report_сourier($cashbox_id, $staff_id); ?>

                            <?
                                $allorder['total'] = 0;
                                $allorder['pay_qr'] = 0;
                                $allorder['pay_delivery'] = 0;
                                $allorder['pay_cash'] = 0;
                               
                                while ($buy_d = mysqli_fetch_assoc($orders)){
                                    $allorder['total'] = $allorder['total'] + $buy_d['total'];
								    $allorder['pay_qr'] = $allorder['pay_qr'] + $buy_d['pay_qr'];
								    $allorder['pay_cash'] = $allorder['pay_cash'] + $buy_d['pay_cash'];
								    $allorder['pay_delivery'] = $allorder['pay_delivery'] + $buy_d['pay_delivery'] + 500;
                                }

                                $onw['number'] = $onw['number'] + mysqli_num_rows($orders);
                                $onw['total'] = $onw['total'] + $allorder['total'];
                                $onw['pay_qr'] = $onw['pay_qr'] + $allorder['pay_qr'];
                                $onw['pay_cash'] = $onw['pay_cash'] + $allorder['pay_cash'];
                                $onw['pay_delivery'] = $onw['pay_delivery'] + $allorder['pay_delivery'];
                            ?>

                            <tr>
                                <td><?=$staff_user_d['name']?></td>
                                <td><?=mysqli_num_rows($orders)?></td>
                                <td class="fr_price"><?=$allorder['total']?></td>
                                <td class="fr_price"><?=$allorder['pay_qr']?></td>
                                <td class="fr_price"><?=$allorder['pay_cash']?></td>
                                <td class="fr_price"><?=$allorder['pay_delivery']?></td>
                                <td class="fr_price btype_start" data-rask="0" data-start="<?=$allorder['pay_cash'] - $allorder['pay_delivery']?>"><?=$allorder['pay_cash'] - $allorder['pay_delivery']?></td>
                                <td class="">
							        <input type="tel" data-id="<?=$cashbox_id?>" data-user-id="<?=$staff_id?>" class="form_txt fr_price btype_rask" placeholder="0" data-val="" value="<?=@$report_сourier_d['expenses']?>">
                                </td>
                                <td class="">
							        <input type="tel" data-id="<?=$cashbox_id?>" data-user-id="<?=$staff_id?>" class="form_txt fr_price btype_cash" placeholder="0" data-val="0" value="<?=@$report_сourier_d['cash']?>">
                                </td>
                                <td class="fr_price btype_kaspi"><?=$allorder['pay_cash']- $allorder['pay_delivery'] - @$report_сourier_d['expenses'] - @$report_сourier_d['cash']?></td>
                                <!-- <td class="fr_price btype_kaspi"><div class="btn btn_dd_cm"><i class="far fa-check-circle"></i></div></td> -->
                            </tr>

                            <? 
                                $onw['rask'] = $onw['rask'] + @$report_сourier_d['expenses'];
                                $onw['cash'] = $onw['cash'] + @$report_сourier_d['cash'];
                                $onw['kaspi'] = $onw['kaspi'] + ($allorder['pay_cash'] - ($allorder['pay_delivery'] + @$report_сourier_d['expenses'] + @$report_сourier_d['cash']));
                            ?>

                        <? endwhile ?>
                    
                    </tbody>

                    <thead>
                        <tr>
                            <td></td>
                            <td>Саны</td>
                            <td>Общий</td>
                            <td>Предоплата</td>
                            <td>Остаток</td>
                            <td>Зарплата</td>
                            <td>На кассу</td>
                            <td>Расходы</td>
                            <td>Наличный</td>
                            <td>Каспи</td>
                            <!-- <td>Статус</td> -->
                        </tr>
                        <tr>
                            <td>Барлыгы</td>
                            <td><?=$onw['number']?></td>
                            <td class="fr_price"><?=$onw['total']?></td>
                            <td class="fr_price"><?=$onw['pay_qr']?></td>
                            <td class="fr_price"><?=$onw['pay_cash']?></td>
                            <td class="fr_price"><?=$onw['pay_delivery']?></td>
                            <td class="fr_price"><?=$onw['pay_cash'] - $onw['pay_delivery']?></td>
                            <td class="fr_price"><?=$onw['rask']?></td>
                            <td class="fr_price"><?=$onw['cash']?></td>
                            <td class="fr_price"><?=$onw['kaspi']?></td>
                            <!-- <td><div class="btn">Отчетты сақтау</div></td> -->
                        </tr>
                    </thead>
                </table>

			</div>

            <br><br><br>
        
	</div>

<? include "../block/footer.php"; ?>