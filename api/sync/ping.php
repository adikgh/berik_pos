<?php
	// Байланыс сапасын тексеру: программа бірнеше рет шақырып, жауап уақытын өлшейді
	header('Content-Type: application/json; charset=utf-8');
	header('Cache-Control: no-store');
	echo json_encode(['ok' => true, 't' => time(), 'ver' => 1]);
