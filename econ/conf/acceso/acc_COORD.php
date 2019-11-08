<?php
return array(
    'id' => 'COORD',
    'parametros' => array(
        'index' => 'inicio_coordinador'
	),
	
    'operaciones' => array(

        'acceso' => array('activa' => true),
        'inicio_coordinador' => array('activa' => true),
        'mensajes' => array('activa' => true),
        'configuracion' => array('activa' => true),

			'ponderacion_notas' => array(
				'activa' => true,
				'url' => 'ponderacion_notas',
				'menu' => array(
					'submenu' => 'parciales',
					'visible' => true,
					'texto' => 'ponderacion_notas'
				)
			),
	
		  'fechas_parciales_propuesta' => array(
				'activa' => true,
				'url' => 'fechas_parciales_propuesta',
				'menu' => array(
					'submenu' => 'parciales',
					'visible' => true,
					'texto' => 'fechas_parciales_propuesta'
				)
		  	),

			'fechas_parciales_prop_2019' => array(
				'activa' => true,
				'url' => 'fechas_parciales_prop_2019',
				'menu' => array(
					'submenu' => 'parciales',
					'visible' => true,
					'texto' => 'fechas_parciales_prop_2019'
				)
			),
		  
           
        //    'notas_parciales' => array(
        //          'activa' => true,
        //          'url' => 'parciales',
        //          'menu' => array(
        //              'submenu' => 'parciales',
        //              'visible' => true,
        //              'texto' => 'parciales'
        //          )
        //    ),

        ),
);
