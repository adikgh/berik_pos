<? include "config/core.php";

   // 
   if ($user_id) header('location: /cashbox');
   // header('location: /cashbox/');


   if (@$_GET['c'] || @$_COOKIE['c'] || @$_SESSION['c']) {
      if (@$_GET['c']) {
         $comp = $_GET['c'];
         $_SESSION['c'] = $comp; 
         setcookie('c', $comp, time() + 3600*24*30*6, '/');
      } else {
         $comp = @$_SESSION['c']; 
         $comp = @$_COOKIE['c'];
      }

      $company_d = fun::company($comp);
      $open = true; $result = 0; $access = 0; $precent = 0;
      if ($company_d['ins_dt'] != null && $company_d['end_dt'] != null) {
         $result = intval((strtotime($company_d['end_dt']) - strtotime(date("d.m.Y"))) / (60*60*24));
         if ($result <= 0) $open = false;

         // $access = intval((strtotime($company_d['end_dt']) - strtotime($company_d['ins_dt'])) / (60*60*24));
         // if (($access - $result) == 0) $precent = 0; elseif (($access - $result) < $access) $precent = round(100 / ($access / ($access - $result))); else $precent = 100;
      }

      $user_mn = db::query("select * from company_staff where positions_id in (4) and company_id = '$comp'");
   } // else $user_mn = db::query("select * from company_staff where positions_id in (3, 4)");



	// site setting
	$menu_name = 'main';
	$site_set['menu'] = false;
	$css = ['sign'];
	// $js = [''];
?>
<? include "block/header.php"; ?>

	<div class="">

      <? if (@$comp): ?>

         <? if (@$open && $comp): ?>

            <div class="sbl2">

               <div class="sbl2_ln">
                  <div class="sbl2_lns">
                     <div class="sbl2_lnsb lazy_img" data-src="/assets/img/bag/woman-working-call-c.jpg"></div>
                  </div>
               </div>

               <div class="sign">
                  <div class="bl_c">
                     <div class="usign_c">

                        <div class="usign_head">
                           <h5 class="usign_h">Менеджерді таңдаңыз</h5>
                        </div>
                        <div class="usign_cn">

                           <div class="sbl2_lro">
                              <? while ($user_mnd = mysqli_fetch_assoc($user_mn)): ?>
                                 <? $user_ds = fun::user($user_mnd['user_id']); ?>
                                 <div class="sbl2_lroi loginq_clc user_id" data-id="<?=$user_ds['id']?>">
                                    <div class="lazy_img" data-src="/assets/uploads/users/Sample_User_Icon.png"></div>
                                    <p class=""><?=$user_ds['name']?> <?=$user_ds['surname']?></p>
                                 </div>
                              <? endwhile ?>
                           </div>
                        
                        </div>

                     </div>
                  </div>
               </div>
            </div>
         <? else: ?>

            <div class="bl_c">
               <div class="oko">
                  <div class="oko_s">
                     <div class="oko_s_name1">Құрметті клиент сізде қолдану уақыты аяқталған</div>
                  </div>
                  <div class="oko_sn">
                   
                  </div>
               </div>
            </div>

         <? endif ?>
      <? else: ?>
         <div class="ds_nr" Пустой список><p>Сіз қате кірдіңіз</p></div>
      <? endif ?>

	</div>

<? include "block/footer.php"; ?>

   <? if (@$comp): ?>
      <div class="pop_bl pop_bl2 loginq_block">
         <div class="pop_bl_a loginq_back"></div>
         <div class="pop_bl_c">
            <div class="head_c">
               <h4>Код-ты енгізіңіз</h4>
               <div class="btn btn_dd loginq_back"><i class="fal fa-times"></i></div>
            </div>
            <div class="pop_bl_cl">
               <div class="form_c">

                  <div class="form_im form_im_ps">
                     <i class="far fa-lock form_icon"></i>
                     <input type="phone" class="form_txt fr_code code" placeholder="Код" data-lenght="4" data-sel="0" data-eye="0" />
                     <i class="far fa-eye-slash form_icon_pass"></i>
                  </div>
                  <div class="form_im">
                     <button class="btn btn_sign">
                        <span>Кіру</span>
                        <i class="far fa-long-arrow-right"></i>
                     </button>
                  </div>
                  
                  <!-- <div class="form_im loginq_form">
                     <input type="phone" class="form_txt fr_code1 loginq_number1" placeholder="0" data-number="1">
                     <input type="phone" class="form_txt fr_code1 loginq_number2" placeholder="0" data-number="2">
                     <input type="phone" class="form_txt fr_code1 loginq_number3" placeholder="0" data-number="3">
                     <input type="phone" class="form_txt fr_code1 loginq_number4" placeholder="0" data-number="4">
                  </div> -->

               </div>
            </div>
         </div>
      </div>
   <? endif ?>