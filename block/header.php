<? include __DIR__."/mobile_check.php"; ?><!DOCTYPE html>
<html lang="<?=$lang?>" id="html" class="html">
<head>

	<? include "head.php"; ?>

</head>
<body id="body" class="body<?=(!empty($posm) ? ' posm' : '')?>">
	<?=(!empty($user_id) && class_exists('plan', false)) ? plan::banner() : ''?>
	
	<? include "menu.php"; ?>

	<!-- body start -->
	<div class="app">