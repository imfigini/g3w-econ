<?php
return array(
    'id' => 'DOC',
    'parametros' => array(
        'index' => 'inicio_docente'
    ),
        'operaciones' => array (
              'notas_parciales' => array(
                  'activa' => true,
                  'url' => 'parciales',
                  'menu' => array(
                      'submenu' => 'parciales',
                      'visible' => true,
                      'texto' => 'parciales'
                  )
              ),

                'definicion_cursos' => array(
                  'activa' => true,
                  'url' => 'definicion_cursos',
                  'menu' => array(
                      'submenu' => 'parciales',
                      'visible' => true,
                      'texto' => 'definicion_cursos'
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
            ),
);