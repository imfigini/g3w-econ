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
        
//            'definicion_cursos' => array(
//                  'activa' => true,
//                  'url' => 'definicion_cursos',
//                  'menu' => array(
//                      'submenu' => 'parciales',
//                      'visible' => true,
//                      'texto' => 'definicion_cursos'
//                  )
//            ),
            
//            'notas_parciales' => array(
//                  'activa' => true,
//                  'url' => 'parciales',
//                  'menu' => array(
//                      'submenu' => 'parciales',
//                      'visible' => true,
//                      'texto' => 'parciales'
//                  )
//            ),

        ),
);