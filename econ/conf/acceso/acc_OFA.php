<?php
return array(
    'id' => 'OFA',
    'parametros' => array(
        'index' => 'inicio_oficina_alumnos'
	),
	
    'operaciones' => array(

        'acceso' => array('activa' => true),
        'inicio_oficina_alumnos' => array('activa' => true),
        'mensajes' => array('activa' => true),
        'configuracion' => array('activa' => true),

        /* Recalcular la calidad de inscripción a las cursadas para los alumnos que lo hicieron regular
         */
        'recalcular_calidad_inscr' => array(
            'url' => 'recalcular_calidad_inscr',
            'activa' => true,
            'menu' => array(
                'submenu' => 'detalle_cursos',
                'visible' => true,
                'texto' => 'recalcular_calidad_inscr'
            )
		),        
    )
);
