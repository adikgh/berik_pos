   
<? include "../config/core.php"; ?>

<? if (isset($_GET['catalog'])): ?>
    <? 
        $catalog_id = @strip_tags($_POST['catalog_id']); 
        $_SESSION['catalog_id'] = $catalog_id;
    ?>
    <? $cashbox_id = @strip_tags($_POST['cashbox_id']); ?>

    <? $product = db::query("select * from product where catalog_id = '$catalog_id' and arh = 0 order by name_kz asc"); ?>
    <? while ($product_d = mysqli_fetch_assoc($product)): ?>
        <div class="hup_rcbi cashbox_add" data-oid="<?=$cashbox_id?>" data-id="<?=$product_d['id']?>">
            <div class=""><?=$product_d['name_kz']?></div>
            <div class=""><?=$product_d['price']?> тг</div>
        </div>
    <? endwhile ?>

    <? exit(); ?>
<? endif ?>


<? // Кассадағы тауар іздеу (атауы бойынша, тек өз компаниясының тауарлары)
if (isset($_GET['search'])):
    if (!$user_id) exit();
    $q = trim(strip_tags(@$_POST['q']));
    $cashbox_id = intval(@$_POST['cashbox_id']);
    if ($q === '') exit();
    $q_sql = mysqli_real_escape_string(db::$connect, addcslashes($q, '%_'));
    $company_sql = mysqli_real_escape_string(db::$connect, $company);
    // Бағасы бойынша іздеу: жазылған мәтіндегі сандар (бос орын, «тг» есепке алынбайды)
    $q_digits = preg_match('/^[\d\s]+(тг|₸)?$/u', $q) ? preg_replace('/\D+/', '', $q) : '';
    $price_sql = $q_digits !== '' ? " or p.price like '%$q_digits%'" : '';

    $product = db::query("select p.* from product p
        inner join product_catalog c on c.id = p.catalog_id
        where c.company_id = '$company_sql' and c.arh is null and p.arh = 0
        and (p.name_kz like '%$q_sql%' or p.name_ru like '%$q_sql%'$price_sql)
        order by p.name_kz asc limit 300");

    // Атауда HTML (мысалы <b class="cl_red">КОЛА</b>) болуы мүмкін: тегтерді алып тастап, мәтін бойынша сүзгілейміз
    $found = [];
    while ($product && ($row = mysqli_fetch_assoc($product))) {
        $plain = strip_tags($row['name_kz']).' '.strip_tags($row['name_ru']);
        $hit = function_exists('mb_stripos') ? mb_stripos($plain, $q, 0, 'UTF-8') : stripos($plain, $q);
        if ($hit === false && $q_digits !== '' && strpos((string)$row['price'], $q_digits) !== false) $hit = 0;
        if ($hit !== false) $found[] = $row;
        if (count($found) >= 40) break;
    }
?>
    <? if (count($found)): ?>
        <? foreach ($found as $product_d): ?>
            <div class="hup_rcbi cashbox_add" data-oid="<?=$cashbox_id?>" data-id="<?=$product_d['id']?>">
                <div class=""><?=$product_d['name_kz']?></div>
                <div class=""><?=$product_d['price']?> тг</div>
            </div>
        <? endforeach ?>
    <? else: ?>
        <div class="hup_search_empty"><?=sprintf(t::w('Nothing found', '«%s» бойынша тауар табылмады'), htmlspecialchars($q))?></div>
    <? endif ?>
    <? exit(); ?>
<? endif ?>
