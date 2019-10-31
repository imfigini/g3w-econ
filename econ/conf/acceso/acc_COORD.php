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

            'fechas_parciales_propuesta' => array(
                  'activa' => true,
                  'url' => 'fechas_parciales_propuesta',
                  'menu' => array(
                      'submenu' => 'parciales',
                      'visible' => true,
                      'texto' => 'fechas_parciales_propuesta'
                  )
            ),
        
           'ponderacion_notas' => array(
                 'activa' => true,
                 'url' => 'ponderacion_notas',
                 'menu' => array(
                     'submenu' => 'parciales',
                     'visible' => true,
                     'texto' => 'ponderacion_notas'
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
