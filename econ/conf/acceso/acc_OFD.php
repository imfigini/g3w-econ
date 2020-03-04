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
        
                
        /* Períodos de evaluación
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
        
		/* Visualizacion fechas de evaluación (con calendario)
         */
        'fechas_parciales_calend' => array(
            'url' => 'fechas_parciales_calend',
            'activa' => true,
            'menu' => array(
                'submenu' => 'detalle_cursos',
                'visible' => true,
                'texto' => 'fechas_parciales_calend'
            )
        ),
        
        /* Aceptación fechas de evaluación
         */
        'fechas_parciales_acept' => array(
            'url' => 'fechas_parciales_acept',
            'activa' => true,
            'menu' => array(
                'submenu' => 'detalle_cursos',
                'visible' => true,
                'texto' => 'fechas_parciales_acept'
            )
		),
		
		/* Aceptación fechas de evaluación - 2019
         */
        'fechas_parciales_acept_2019' => array(
            'url' => 'fechas_parciales_acept_2019',
            'activa' => true,
            'menu' => array(
                'submenu' => 'detalle_cursos',
                'visible' => true,
                'texto' => 'fechas_parciales_acept_2019'
            )
        ),
		
		/* Definición de materias por promoción directa
         */
        'asignacion_mat_prom_dir' => array(
            'url' => 'asignacion_mat_prom_dir',
            'activa' => true,
            'menu' => array(
                'submenu' => 'detalle_cursos',
                'visible' => true,
                'texto' => 'asignacion_mat_prom_dir'
            )
		),
		
        /* Listado de Cursadas con sus %
         */
        'ponder_notas_detalle' => array(
            'url' => 'ponder_notas_detalle',
            'activa' => true,
            'menu' => array(
                'submenu' => 'detalle_cursos',
                'visible' => true,
                'texto' => 'ponder_notas_detalle'
            )
        ),

		/* Definición de materias por promoción directa
         */
        'asignacion_mat_prom_dir' => array(
            'url' => 'asignacion_mat_prom_dir',
            'activa' => true,
            'menu' => array(
                'submenu' => 'detalle_cursos',
                'visible' => true,
                'texto' => 'asignacion_mat_prom_dir'
            )
		),
        
    )
);
