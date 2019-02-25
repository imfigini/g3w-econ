<?php
//	$dir = $_SERVER['TOBA_DIR'].'/php'; 
//	$separador = (substr(PHP_OS, 0, 3) == 'WIN') ? ';.;' : ':.:';
//	ini_set('include_path', ini_get('include_path'). $separador . $dir);
//	require_once('nucleo/toba_nucleo.php');
//	
//	toba_nucleo::instancia()->iniciar_contexto_desde_consola('desarrollo', 'Indicadores');
	
	include_once("CalendarInfo.php");
	
        console.log('entro a getdatos');
	$start = $_GET['start'];
	$end = $_GET['end'];

	$calendarInfo = new CalendarInfo($start, $end);

	$resultado = $calendarInfo->get();
	
	echo ($resultado);
?>
