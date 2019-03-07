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

        /* MIXes
         */
        'mixes' => array(
            'url' => 'mixes',
            'activa' => true,
            'menu' => array(
                'submenu' => 'mixes',
                'visible' => true,
                'texto' => 'definicion_mixes'
            )
        ),        

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
        
                
        /* Per�odos de evaluaci�n
         */
        'periodos_evaluacion' => array(
            'url' => 'periodos_evaluacion',
            'activa' => true,
            'menu' => array(
                'submenu' => 'detalle_cursos',
                'visible' => true,
                'texto' => 'periodos_evaluacion'
            )
        ),
        
        /* Visualizacion fechas de evaluaci�n (con calendario)
         */
        'fechas_parciales_calendario' => array(
            'url' => 'fechas_parciales_calendario',
            'activa' => true,
            'menu' => array(
                'submenu' => 'detalle_cursos',
                'visible' => true,
                'texto' => 'fechas_parciales_calendario'
            )
        ),
        
        /* Aceptaci�n fechas de evaluaci�n
         */
        'fechas_parciales_aceptacion' => array(
            'url' => 'fechas_parciales_aceptacion',
            'activa' => true,
            'menu' => array(
                'submenu' => 'detalle_cursos',
                'visible' => true,
                'texto' => 'fechas_parciales_aceptacion'
            )
        ),
        
        /* Listado de Cursadas con sus %
         */
        'detalle_cursos' => array(
            'url' => 'detalle_cursos',
            'activa' => true,
            'menu' => array(
                'submenu' => 'detalle_cursos',
                'visible' => true,
                'texto' => 'detalle_cursos'
            )
        ),
        
    )
);