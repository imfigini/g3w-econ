<?php
return array(
    'id' => 'OFD',
    'parametros' => array(
        'index' => 'inicio_oficina_docentes'
    ),
    'operaciones' => array(

        'acceso' => array('activa' => true),
        'inicio_oficina_docentes' => array('activa' => true),
        'mensajes' => array('activa' => true),
        'configuracion' => array('activa' => true),

        /* ASIGNACION DE COORDINADORES A MATERIAS
         */
        'asignacion_coord_materias' => array(
            'url' => 'asignacion_coord_materias',
            'activa' => true,
            'menu' => array(
                'submenu' => 'coord_materias',
                'visible' => true,
                'texto' => 'asignacion_coord_materias'
            )
        ),
        
        /* Cursadas
         */
        'definicion_cursos' => array(
            'url' => 'definicion_cursos',
            'activa' => true,
            'menu' => array(
                'submenu' => 'definicion_cursos',
                'visible' => true,
                'texto' => 'definicion_cursos'
            )
        ),
        
//        'definicion_cursos' => array ( // nueva operación
//            'menu' => array(
//                'visible' => true,
//                'submenu' => 'listados'
//            ),
//        ),
    )
);