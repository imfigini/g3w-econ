<?php
//	$dir = $_SERVER['TOBA_DIR'].'/php'; 
//	$separador = (substr(PHP_OS, 0, 3) == 'WIN') ? ';.;' : ':.:';
//	ini_set('include_path', ini_get('include_path'). $separador . $dir);
//	require_once('nucleo/toba_nucleo.php');
//	
//	toba_nucleo::instancia()->iniciar_contexto_desde_consola('desarrollo', 'Indicadores');
	
//	include_once("CalendarInfo.php");
//	
//        console.log('entro a getdatos');
//	$start = $_GET['start'];
//	$end = $_GET['end'];
//
//	$calendarInfo = new CalendarInfo($start, $end);
//
//	$resultado = $calendarInfo->get();
//	
console.log('entro por getdatos');
        $resultado = '[{ "title":"Integrador - Introduccion a la Economia",
                        "start":"2018-12-03",
                        "textColor":"black",
                        "tip":"Introduccion a la Economia",
                        "backgroundColor":"red",
                        "end":"2018-12-03"
                    },
                    {   "title":"Primer Parcial Promo - Introduccion a la Economia",
                        "start":"2018-10-08",
                        "textColor":"black",
                        "tip":"Introduccion a la Economia",
                        "backgroundColor":"blue",
                        "end":"2018-10-08"
                    },
                    {   
                        "title":"Recuperatorio Unico - Introduccion a la Economia",
                        "start":"2018-11-26",
                        "textColor":"black",
                        "tip":"Introduccion a la Economia",
                        "backgroundColor":"green",
                        "end":"2018-11-26"
                    }]';
        console.log($resultado);
	echo ($resultado);
?>
