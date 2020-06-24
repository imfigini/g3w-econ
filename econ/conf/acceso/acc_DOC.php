<?php
return array(
    'operaciones' => array(

        /*************************************
         * CURSADAS
         */

        'asistencias' => array(
              'activa' => true,
              'menu' => array(
                  'submenu' => 'cursadas',
                  'visible' => true,
                  'texto' => 'header.menu.asistencias',
              )
        ),

        'notas_parciales' => array(
            'activa' => true,
            'url' => 'parciales',
            'menu' => array(
                'submenu' => 'cursadas',
                'visible' => true,
                'texto' => 'parciales'
            )
        ),

        /*************************************
         * SIN SUBMENU
         */
        'ficha_alumno' => array(
            'activa' => true,
            'menu' => array(
                'visible' => true
            )
        ),

    )
);