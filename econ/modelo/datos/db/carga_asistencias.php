<?php
namespace econ\modelo\datos\db;

use kernel\kernel;
use kernel\util\db\db;
use siu\modelo\datos\catalogo;
//use siu\modelo\datos\db\carga_asistencias;
use siu\modelo\entidades\alumno_foto;

class carga_asistencias extends \siu\modelo\datos\db\carga_asistencias
{
    /**
    * parametros: clase
    * cache: no
    * filas: n
    */
    function tiene_cargadas_asistencias($parametros)
    {
        //Tengo cargadas asistencias?
        $sql = "SELECT COUNT(sga_inasistencias.legajo) AS cant
                    FROM sga_inasistencias,
                    sga_insc_cursadas
                WHERE   sga_inasistencias.clase            = {$parametros["clase"]}
                    AND sga_inasistencias.unidad_academica = sga_insc_cursadas.unidad_academica
                    AND sga_inasistencias.carrera          = sga_insc_cursadas.carrera
                    AND sga_inasistencias.legajo           = sga_insc_cursadas.legajo
                    AND sga_inasistencias.comision         = sga_insc_cursadas.comision";

        $cant = kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
        if ($cant['CANT'] > 0) {
            return true;
        }
        return false;
    }
    
    /**
    * parametros: clase
    * cache: no
    * filas: n
    */
    private function get_inscriptos ($parametros)
    {
        $sql = "SELECT  sga_comisiones.anio_academico,
                        sga_comisiones.periodo_lectivo,
                        sga_materias.materia,
                        sga_materias.nombre,
                        sga_comisiones.comision,
                        sga_comisiones.nombre,
                        0, 
                        '',
                        0,
                        '',
                        sga_calendcursada.clase,
                        sga_calendcursada.fecha,
                        sga_asignaciones.hs_comienzo_clase,
                        sga_asignaciones.hs_finaliz_clase,
                        sga_asign_clases.cantidad_horas,
                        sga_alumnos.carrera,
                        sga_alumnos.legajo,
                        sga_personas.apellido,
                        sga_personas.nombres,
                        1,
                        0,
                        sga_personas.id_imagen
                    FROM sga_calendcursada 
                    JOIN sga_insc_cursadas ON (sga_insc_cursadas.comision = sga_calendcursada.comision)
                    JOIN sga_comisiones ON (sga_comisiones.comision = sga_insc_cursadas.comision)
                    JOIN sga_materias ON (sga_materias.materia = sga_comisiones.materia)
                    JOIN sga_asignaciones ON (sga_asignaciones.asignacion = sga_calendcursada.asignacion)
                    JOIN sga_asign_clases ON (sga_asign_clases.comision = sga_calendcursada.comision AND sga_asign_clases.asignacion = sga_calendcursada.asignacion)
                    JOIN sga_alumnos ON (sga_alumnos.unidad_academica = sga_insc_cursadas.unidad_academica AND sga_alumnos.carrera = sga_insc_cursadas.carrera AND sga_alumnos.legajo = sga_insc_cursadas.legajo)
                    JOIN sga_personas ON (sga_personas.unidad_academica = sga_alumnos.unidad_academica AND sga_personas.nro_inscripcion = sga_alumnos.nro_inscripcion)
                        WHERE sga_calendcursada.clase = {$parametros["clase"]}
                ORDER BY sga_personas.apellido,
                        sga_personas.nombres ";
        return kernel::db()->consultar($sql, db::FETCH_NUM);
    }
    
    /**
    * parametros: clase
    * cache: no
    * filas: n
    */
    function recuperar_generar_asistencias($parametros)
    {
        $sql = "execute procedure sp_AsisAluClas({$parametros["clase"]})";
        //kernel::log()->add_debug('recuperar_generar_asistencias $sql: '.__FILE__.' - '.__LINE__, $sql);
        return kernel::db()->consultar($sql, db::FETCH_NUM);
    }
    
    /**
    * parametros: clase
    * cache: no
    * filas: n
    */
    function clase_detalle($parametros)
    {
        $datos = array();
        if ($this->tiene_cargadas_asistencias($parametros)) 
        {
            $datos = $this->recuperar_generar_asistencias($parametros);
        }
        else
        {
            $datos = $this->get_inscriptos($parametros);
        }
        
        $nuevo = array();
        foreach($datos as $id => $dato) 
        {
            $nuevo[$id][catalogo::id] = catalogo::generar_id($dato[15].$dato[16].$dato[4]);
            $nuevo[$id]['CARRERA'] = $dato[15];
            $nuevo[$id]['LEGAJO'] = $dato[16];
            $nuevo[$id]['COMISION'] = $dato[4];
            $nuevo[$id]['COMISION_NOMBRE'] = $dato[5];
            $calidad_insc = $this->get_calidad_inscripcion(array('legajo'=>$nuevo[$id]['LEGAJO'], 'comision'=>$nuevo[$id]['COMISION']));
            $nuevo[$id]['CALIDAD_INSC'] = $calidad_insc['CALIDAD_INSC'];
            $nuevo[$id]['CLASE'] = $dato[10];
            $nuevo[$id]['NOMBRE'] = "{$dato[17]}, {$dato[18]}"; 
            $nuevo[$id]['CANT_INASIST'] = $dato[19]; 
            //$nuevo[$id]['MOTIVO_JUSTIFIC'] = $dato[20]; 
            $motivo_justific = $this->get_justificacion_inasist(array('legajo'=>$nuevo[$id]['LEGAJO'], 'clase'=>$nuevo[$id]['CLASE']));
            $motivo_justific = (isset($motivo_justific['MOTIVO_JUSTIFIC']))? $motivo_justific['MOTIVO_JUSTIFIC'] : null;
            $nuevo[$id]['MOTIVO_JUSTIFIC'] = $motivo_justific;
//            kernel::log()->add_debug('clase_detalle $motivo_justific', $motivo_justific);
            
            $nuevo[$id]['ID_IMAGEN'] = $dato[21]; 
            if (!empty($nuevo[$id]['ID_IMAGEN'])) {
                    $nuevo[$id]['URL_IMAGEN'] = alumno_foto::url_imagen($nuevo[$id]['ID_IMAGEN']);
                    $nuevo[$id]['URL_IMAGEN_MEDIANA'] = alumno_foto::url_imagen($nuevo[$id]['ID_IMAGEN'], alumno_foto::TAMANIO_MEDIANA);
                    $nuevo[$id]['URL_IMAGEN_GRANDE'] = alumno_foto::url_imagen($nuevo[$id]['ID_IMAGEN'], alumno_foto::TAMANIO_GRANDE);
            } else {
                    $nuevo[$id]['URL_IMAGEN'] = kernel::vinculador()->vinculo_recurso('img/iconos/mm.png');
                    $nuevo[$id]['URL_IMAGEN_MEDIANA'] = kernel::vinculador()->vinculo_recurso('img/iconos/mm_grande.png');
                    $nuevo[$id]['URL_IMAGEN_GRANDE'] = kernel::vinculador()->vinculo_recurso('img/iconos/mm_grande.png');
            }			
        }
        //kernel::log()->add_debug('clase_detalle $nuevo', $nuevo);
        return $nuevo;
    }
    
    /**
    * parametros: legajo, comision
    * cache: no
    * filas: 1
    */
    private function get_calidad_inscripcion($parametros)
    {
        $sql = "SELECT calidad_insc 
                    FROM sga_insc_cursadas 
                    WHERE comision = '{$parametros['comision']}'
                    AND legajo = '{$parametros['legajo']}' ";
        return kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
    }
    
     /**
    * parametros: legajo, comision
    * cache: no
    * filas: 1
    */
    private function get_cant_inasistencias_justificadas($parametros)
    {
        $sql = "SELECT cant_justificadas::INT as cant_justificadas
                    FROM sga_inasis_acum
                    WHERE comision = '{$parametros['comision']}'
                    AND legajo = '{$parametros['legajo']}' ";
        return kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
    }
    
    /**
    * parametros: _ua, carrera, legajo, comision, clase, cant_inasist, justific
    * cache: no
    * filas: 1
    */
   function guardar($parametros) 
   {
//        kernel::log()->add_debug('guardar iris', $parametros);

        if (!$this->tiene_cargadas_asistencias($parametros)) 
        {
            $sql = "execute procedure sp_AsisAluClas({$parametros["clase"]})";
            //kernel::db()->consultar($sql, db::FETCH_NUM);
        }
        
		if ( ($parametros["cant_inasist"] == "'1'" && $parametros["justific"] <> "'-1'") //Ausencia justificada
			or ($parametros["cant_inasist"] == '1' && $parametros["justific"] <> '-1') )
        {
            $sql1 = "EXECUTE PROCEDURE sp_u_justsaluclas(" .
                                 $parametros["_ua"] . ",".
                                 $parametros["carrera"] . "," .
                                 $parametros["legajo"] . "," .
                                 $parametros["comision"] . "," .
                                 $parametros["clase"] . "," .
                                 $parametros["cant_inasist"] . ",".
                                 $parametros["justific"] .");";   
            
            $sql2 = "EXECUTE PROCEDURE sp_u_asisaluclas(" .
                                 $parametros["_ua"] . ",".
                                 $parametros["carrera"] . "," .
                                 $parametros["legajo"] . "," .
                                 $parametros["comision"] . "," .
                                 $parametros["clase"] . "," .
                                 $parametros["cant_inasist"] . ",".
                                 "NULL);";
        }
        else
        {
            $sql1 = "EXECUTE PROCEDURE sp_u_asisaluclas(" .
                             $parametros["_ua"] . ",".
                             $parametros["carrera"] . "," .
                             $parametros["legajo"] . "," .
                             $parametros["comision"] . "," .
                             $parametros["clase"] . "," .
                             $parametros["cant_inasist"] . ",".
                             "NULL);";
            $sql2 = "EXECUTE PROCEDURE sp_u_justsaluclas(" .
                                 $parametros["_ua"] . ",".
                                 $parametros["carrera"] . "," .
                                 $parametros["legajo"] . "," .
                                 $parametros["comision"] . "," .
                                 $parametros["clase"] . "," .
                                 0 . ",".
                                 "NULL" .");";   
        }
        kernel::db()->consultar_fila($sql2, db::FETCH_NUM);
        return kernel::db()->consultar_fila($sql1, db::FETCH_NUM);
    }	
    
    
    /**
     * parametros: 
     * cache: no
     * filas: n
     */
    function get_motivos_inasistencia($parametros)
    {
        // Solo recupera los motivos cuyo tipo son "Justificados"
        $sql = "SELECT motivo, descripcion FROM sga_inasis_motivos WHERE tipo = 1";
        return kernel::db()->consultar($sql, db::FETCH_ASSOC);        
    }
    
    /**
     * parametros: legajo, clase
     * cache: no
     * filas: 1
     */
    private function get_justificacion_inasist($parametros)
    {
        $legajo = $parametros['legajo'];
        $clase = $parametros['clase'];
        $sql = "SELECT motivo_justific
                    FROM sga_inasistencias 
                    WHERE clase = '$clase'
                        AND legajo = '$legajo'";
        return kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);        
    }
    
    /**
    * parametros: comision, fecha, cantidad, tipo, tipo_clase
    * param_null: fecha, cantidad, tipo, tipo_clase
    * cache: no
    * filas: n
    */
    function get_planilla($parametros)
    {
        $subcomision = "NULL";

        $sql = "EXECUTE PROCEDURE sp_datos_planilla(" .
                          $parametros["comision"] . "," .
                          $subcomision . "," .
                          $parametros["fecha"] . "," .
                          $parametros["cantidad"] . "," .
                          $parametros["tipo"] . "," .
                          $parametros["tipo_clase"] . 
                          ");";	
        
        $datos = kernel::db()->consultar($sql, db::FETCH_NUM);
        $corte = array();
        $i = 0;
        foreach ($datos as $dato)
        {
            $i += 1;
            $corte[$dato[4]]['COMISION'] = $dato[0];
            $corte[$dato[4]]['FECHA'] = $dato[1];
            $corte[$dato[4]]['CARRERA_COM'] = $dato[2];
            $corte[$dato[4]]['CARRERA_NOMBRE'] = $dato[3];
            $corte[$dato[4]]['MATERIA'] = $dato[4];
            $corte[$dato[4]]['MATERIA_NOMBRE'] = $dato[5];
            $corte[$dato[4]]['COMISION_NOMBRE'] = $dato[6];
            $corte[$dato[4]]['PERIODO_LECTIVO'] = $dato[7];
            $corte[$dato[4]]['TURNO'] = $dato[8];
            $corte[$dato[4]]['ANIO_ACADEMICO'] = $dato[9];
            $corte[$dato[4]]['CATEDRA'] = $dato[10];
            $corte[$dato[4]]['DOCENTES'] = $dato[11];
            $corte[$dato[4]]['AULA'] = $dato[12];
            $corte[$dato[4]]['SUBCOMISION'] = $dato[23];
            $corte[$dato[4]]['SUB_NOMBRE'] = $dato[24];
            $corte[$dato[4]]['FECHAS'][1] = (!empty($dato[17]) ? date("d/m/y", strtotime($dato[17])) : '  /  /   '); 
            $corte[$dato[4]]['FECHAS'][2] = (!empty($dato[18]) ? date("d/m/y", strtotime($dato[18])) : '  /  /   '); 
            $corte[$dato[4]]['FECHAS'][3] = (!empty($dato[19]) ? date("d/m/y", strtotime($dato[19])) : '  /  /   '); 
            $corte[$dato[4]]['FECHAS'][4] = (!empty($dato[20]) ? date("d/m/y", strtotime($dato[20])) : '  /  /   '); 
            $corte[$dato[4]]['FECHAS'][5] = (!empty($dato[21]) ? date("d/m/y", strtotime($dato[21])) : '  /  /   '); 

            $alumno['NRO'] = $i; 
            $alumno['ALUMNO'] = $dato [13]; 
            $alumno['UA'] = $dato [14]; 
            $alumno['CARRERA'] = $dato [15]; 
            $alumno['LEGAJO'] = $dato [16]; 
            $calidad_insc = $this->get_calidad_inscripcion(array('legajo'=>$alumno['LEGAJO'], 'comision'=>$dato[0]));                
            $alumno['CALIDAD_INSC'] = $calidad_insc ['CALIDAD_INSC']; 
            $alumno['ACUMULADAS'] = (int)$dato [22]; 
            $cant_justificadas = $this->get_cant_inasistencias_justificadas(array('legajo'=>$alumno['LEGAJO'], 'comision'=>$dato[0]));                
            $alumno['JUSTIFICADAS'] = (isset($cant_justificadas ['CANT_JUSTIFICADAS'])) ? (int) $cant_justificadas ['CANT_JUSTIFICADAS'] : 0; 
//                kernel::log()->add_debug('get_planilla $alumno', $alumno);
            $corte[$dato[4]]['ALUMNOS'][] = $alumno;
            unset($alumno);
        } 
        return $corte;
    }
    
    /**
    * parametros: _ua, legajo
    * cache: no
    * filas: n
    */
    function get_materias_y_dias_clases($parametros)
    {
        $legajo = $parametros['legajo'];
        $sql = "SELECT DISTINCT
                    sga_comisiones.materia,
                    sga_materias.nombre as materia_nombre, 
                    sga_periodos_lect.anio_academico, 
                    sga_periodos_lect.periodo_lectivo,
                    sga_asign_clases.tipo_clase,
                    sga_asignaciones.dia_semana, 
                    to_char(sga_asignaciones.hs_comienzo_clase,'%H:%M') AS hs_comienzo_clase,
                    to_char(sga_asignaciones.hs_finaliz_clase,'%H:%M') AS hs_finaliz_clase
                        FROM sga_docentes_com
                        JOIN sga_comisiones ON (sga_comisiones.comision = sga_docentes_com.comision)
                        JOIN sga_materias ON (sga_materias.materia = sga_comisiones.materia)
                        JOIN sga_asign_clases ON (sga_asign_clases.comision = sga_docentes_com.comision)
                        JOIN sga_asignaciones ON (sga_asignaciones.asignacion = sga_asign_clases.asignacion)
                        JOIN sga_periodos_lect ON (sga_periodos_lect.anio_academico = sga_comisiones.anio_academico AND sga_periodos_lect.periodo_lectivo = sga_comisiones.periodo_lectivo)
                            WHERE sga_docentes_com.legajo = $legajo
                            AND sga_periodos_lect.fecha_inactivacion >= TODAY
                    ORDER BY    sga_comisiones.materia,
                                sga_asignaciones.dia_semana, 
                                to_char(sga_asignaciones.hs_finaliz_clase,'%H:%M') ";
        return kernel::db()->consultar($sql, db::FETCH_ASSOC);
    }
    
    /**
    * parametros: _ua, materia, tipo_clase, dia_semana, hs_comienzo_clase
    * cache: no
    * filas: n
    */
    function get_comisiones_en_clase($parametros)
    {
        $materia = $parametros['materia'];
        $tipo_clase = $parametros['tipo_clase'];
        $dia_semana = $parametros['dia_semana'];
        $hs_comienzo_clase = $parametros['hs_comienzo_clase'];
        $sql = "SELECT  sga_comisiones.comision,
                        sga_comisiones.nombre AS comision_nombre,
                        (SELECT COUNT(DISTINCT sga_insc_cursadas.legajo) FROM sga_insc_cursadas WHERE comision = sga_comisiones.comision AND estado in ('A','P','E')) as cant_inscriptos
		FROM sga_comisiones
        	JOIN sga_asign_clases ON (sga_asign_clases.comision = sga_comisiones.comision)
	        JOIN sga_asignaciones ON (sga_asignaciones.asignacion = sga_asign_clases.asignacion)
        	JOIN sga_periodos_lect ON (sga_periodos_lect.anio_academico = sga_comisiones.anio_academico AND sga_periodos_lect.periodo_lectivo = sga_comisiones.periodo_lectivo)
        		WHERE 	sga_comisiones.materia = $materia
				AND sga_asign_clases.tipo_clase = $tipo_clase
				AND sga_asignaciones.dia_semana = $dia_semana
				AND to_char(sga_asignaciones.hs_comienzo_clase,'%H:%M') = $hs_comienzo_clase
	                	AND sga_periodos_lect.fecha_inactivacion >= TODAY";
        return kernel::db()->consultar($sql, db::FETCH_ASSOC);
    }
    
    /**
    * parametros: comision
    * cache: no
    * filas: n
    */
    function get_horarios_comision($parametros)
    {
        $comision = $parametros['comision'];
        $sql = "SELECT  sga_asign_clases.tipo_clase,
                        sga_tipo_clase.descripcion AS tipo_clase_nombre,
                        sga_asignaciones.dia_semana,
                        CASE when sga_asignaciones.dia_semana = 1 then 'Domingo'
                            WHEN sga_asignaciones.dia_semana = 2 THEN 'Lunes'
                            WHEN sga_asignaciones.dia_semana = 3 THEN 'Martes'
                            WHEN sga_asignaciones.dia_semana = 4 THEN 'Miércoles'
                            WHEN sga_asignaciones.dia_semana = 5 THEN 'Jueves'
                            WHEN sga_asignaciones.dia_semana = 6 THEN 'Viernes'
                            WHEN sga_asignaciones.dia_semana = 7 THEN 'Sábado'
                        END AS dia_nombre,
                        to_char(sga_asignaciones.hs_comienzo_clase,'%H:%M') as hs_comienzo_clase,
                        to_char(sga_asignaciones.hs_finaliz_clase ,'%H:%M') as hs_finaliz_clase
                    FROM sga_comisiones
                    JOIN sga_asign_clases ON (sga_asign_clases.comision = sga_comisiones.comision)
                    JOIN sga_asignaciones ON (sga_asignaciones.asignacion = sga_asign_clases.asignacion)
                    JOIN sga_tipo_clase ON (sga_tipo_clase.tipo_clase = sga_asign_clases.tipo_clase)
                        WHERE sga_comisiones.comision = $comision
                ORDER BY sga_asignaciones.dia_semana, to_char(sga_asignaciones.hs_comienzo_clase,'%H:%M')";
        return kernel::db()->consultar($sql, db::FETCH_ASSOC);
    }
    
    /**
    * parametros: _ua, comision, filas
    * cache: no
    * filas: n
    */
    function get_clases_comision($parametros)
    {
    // cache: memoria
    // cache_expiracion: 3600

        $first = '';
        $filas = filter_var($parametros['filas'], FILTER_SANITIZE_NUMBER_INT);
        if ($filas > 0) {
                $first = "FIRST $filas";
        }		

        $sql = "SELECT	$first
                            sga_asignaciones.dia_semana as dia_semana,
                            to_char(sga_calendcursada.fecha, '%d/%m/%Y') as fecha_clase,
                            sga_tipo_clase.descripcion as tipo_clase,
                            to_char(sga_asignaciones.hs_comienzo_clase,'%H:%M') as hs_comienzo_clase,
                            to_char(sga_asignaciones.hs_finaliz_clase ,'%H:%M') as hs_finaliz_clase,
                            sga_asign_clases.cantidad_horas,
                            sga_calendcursada.fecha

                        FROM	sga_comisiones,
                                        sga_calendcursada,   
                                        sga_asignaciones,
                                        sga_asign_clases,
                                        OUTER sga_tipo_clase

                        WHERE	sga_comisiones.comision         = {$parametros['comision']}
                        AND		sga_comisiones.comision			= sga_calendcursada.comision
                        AND		sga_calendcursada.asignacion	= sga_asignaciones.asignacion 
                        AND		sga_calendcursada.valido		= 'S'
                        AND		sga_calendcursada.comision      = sga_asign_clases.comision
                        AND		sga_calendcursada.asignacion    = sga_asign_clases.asignacion
                        AND		sga_calendcursada.fecha        <= TODAY
                        AND     sga_tipo_clase.tipo_clase = sga_asign_clases.tipo_clase
                        ORDER BY sga_calendcursada.fecha DESC;";

        return kernel::db()->consultar($sql, db::FETCH_ASSOC);
    }	
    
    /**
    * parametros: comision, fecha, hs_comienzo_clase
    * cache: no
    * filas: 1
    */
    function get_datos_comision_enviada($parametros)
    {
        $sql = "SELECT      sga_materias.materia, 
                            sga_materias.nombre AS materia_nombre,
                            sga_asignaciones.dia_semana as dia_semana,
                            to_char(sga_calendcursada.fecha, '%d/%m/%Y') as fecha_clase,
                            sga_tipo_clase.descripcion as tipo_clase,
                            to_char(sga_asignaciones.hs_comienzo_clase,'%H:%M') as hs_comienzo_clase,
                            to_char(sga_asignaciones.hs_finaliz_clase ,'%H:%M') as hs_finaliz_clase,
                            sga_asign_clases.cantidad_horas,
                            sga_calendcursada.fecha

                        FROM	sga_comisiones,
                                sga_calendcursada,   
                                sga_asignaciones,
                                sga_asign_clases,
                                sga_materias, 
                                OUTER sga_tipo_clase

                        WHERE	sga_comisiones.comision = {$parametros['comision']}
                        AND	sga_comisiones.comision	= sga_calendcursada.comision
                        AND	sga_calendcursada.asignacion = sga_asignaciones.asignacion 
                        AND	sga_calendcursada.valido = 'S'
                        AND	sga_calendcursada.comision = sga_asign_clases.comision
                        AND	sga_calendcursada.asignacion = sga_asign_clases.asignacion
                        AND	to_char(sga_calendcursada.fecha, '%d/%m/%Y') = {$parametros['fecha']}
			AND 	to_char(sga_asignaciones.hs_comienzo_clase,'%H:%M') = {$parametros['hs_comienzo_clase']}
                        AND     sga_tipo_clase.tipo_clase = sga_asign_clases.tipo_clase 
                        AND     sga_materias.materia = sga_comisiones.materia ";
        return kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
    }
    
    /**
    * parametros: comision
    * cache: no
    * filas: 1
    */
    function get_nombre_comision($parametros)
    {
        $sql = "SELECT nombre FROM sga_comisiones
                    WHERE comision = {$parametros['comision']}";
        return kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);          
    }
    
    /**
    * parametros: comision, fecha, hs_comienzo_clase
    * cache: no
    * filas: 1
    */
    function get_clase_comision($parametros)
    {
        $sql = "SELECT clase 
                     FROM sga_calendcursada, sga_asignaciones
                     WHERE comision = {$parametros['comision']}
                         AND	to_char(sga_calendcursada.fecha, '%d/%m/%Y') = {$parametros['fecha']}
                         AND	sga_calendcursada.asignacion = sga_asignaciones.asignacion 
                         AND to_char(sga_asignaciones.hs_comienzo_clase,'%H:%M') = {$parametros['hs_comienzo_clase']}";
        return kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);
    }

    
    /**
    * parametros: comision
    * cache: no
    * filas: 1
    */
    function get_datos_comision($parametros)
    {
        $sql = "SELECT      sga_comisiones.comision,
                            sga_comisiones.nombre as comision_nombre,
                            sga_materias.materia, 
                            sga_materias.nombre AS materia_nombre,
                            sga_comisiones.anio_academico,
                            sga_comisiones.periodo_lectivo,
                            sga_comisiones.turno,
                            to_char(sga_calendcursada.fecha, '%d/%m/%Y') as fecha_clase,
                            CASE when sga_asignaciones.dia_semana = 1 then 'Dom'
                                WHEN sga_asignaciones.dia_semana = 2 THEN 'Lun'
                                WHEN sga_asignaciones.dia_semana = 3 THEN 'Mar'
                                WHEN sga_asignaciones.dia_semana = 4 THEN 'Mie'
                                WHEN sga_asignaciones.dia_semana = 5 THEN 'Jue'
                                WHEN sga_asignaciones.dia_semana = 6 THEN 'Vie'
                                WHEN sga_asignaciones.dia_semana = 7 THEN 'Sab'
                            END AS dia_nombre,
                            sga_tipo_clase.descripcion as tipo_clase,
                            sga_calendcursada.fecha
                        FROM	sga_comisiones,
                                sga_calendcursada,   
                                sga_materias, 
                                sga_asign_clases,
                                sga_asignaciones,
                                OUTER sga_tipo_clase
                        WHERE	sga_comisiones.comision = {$parametros['comision']}
                        AND	sga_comisiones.comision	= sga_calendcursada.comision
                        AND	sga_calendcursada.valido = 'S'
                        AND	sga_calendcursada.comision = sga_asign_clases.comision
                        AND	sga_calendcursada.asignacion = sga_asign_clases.asignacion
                        AND     sga_tipo_clase.tipo_clase = sga_asign_clases.tipo_clase 
                        AND     sga_materias.materia = sga_comisiones.materia
                        AND     sga_asignaciones.asignacion = sga_calendcursada.asignacion
                ORDER BY sga_calendcursada.fecha";
        return kernel::db()->consultar($sql, db::FETCH_ASSOC);
    }
    
    /**
    * parametros: comision
    * cache: no
    * filas: 1
    */
    function get_alumnos_inscriptos_comision($parametros)
    {
        $sql = "SELECT TRIM(dba.sga_personas.apellido) || ', ' || TRIM(dba.sga_personas.nombres) AS alumno,
                    dba.sga_alumnos.carrera,
                    dba.sga_alumnos.legajo,
                    sga_insc_cursadas.calidad_insc,
                    dba.sga_inasis_acum.cant_acumuladas::INT as cant_acumuladas,
                    dba.sga_inasis_acum.cant_justificadas::INT as cant_justificadas,
                    dba.sga_inasis_acum.estado
            FROM    dba.sga_insc_cursadas,   
                    dba.sga_alumnos,   
                    dba.sga_personas,   
                    OUTER dba.sga_inasis_acum   
            WHERE   dba.sga_insc_cursadas.comision = {$parametros['comision']} AND
                    dba.sga_alumnos.unidad_academica = dba.sga_insc_cursadas.unidad_academica AND
                    dba.sga_alumnos.carrera = dba.sga_insc_cursadas.carrera AND
                    dba.sga_alumnos.legajo  = dba.sga_insc_cursadas.legajo AND
                    dba.sga_personas.unidad_academica = dba.sga_alumnos.unidad_academica AND
                    dba.sga_personas.nro_inscripcion  = dba.sga_alumnos.nro_inscripcion AND
                    dba.sga_insc_cursadas.unidad_academica = dba.sga_inasis_acum.unidad_academica  AND
                    dba.sga_insc_cursadas.carrera = dba.sga_inasis_acum.carrera  AND
                    dba.sga_insc_cursadas.legajo = dba.sga_inasis_acum.legajo  AND
                    dba.sga_insc_cursadas.comision = dba.sga_inasis_acum.comision
            ORDER BY 1";
        return kernel::db()->consultar($sql, db::FETCH_ASSOC);
    }
    
    /**
    * parametros: comision, legajo
    * cache: no
    * filas: 1
    */
    function get_inasistencias_alumno($parametros)
    {
        $sql = "SELECT  sga_calendcursada.clase,
                        sga_calendcursada.fecha,
                        to_char(sga_calendcursada.fecha, '%d/%m/%y') as fecha_clase,
                        sga_inasistencias.legajo, 
                        sga_inasistencias.cant_inasistencias::INT as cant_inasistencias,
                        sga_inasistencias.cant_justificadas::INT as cant_justificadas
                FROM	sga_calendcursada 
                LEFT JOIN sga_inasistencias ON (sga_inasistencias.comision = sga_calendcursada.comision 
                                                AND sga_inasistencias.clase = sga_calendcursada.clase 
                                                AND sga_inasistencias.legajo = {$parametros['legajo']})
                    WHERE sga_calendcursada.comision = {$parametros['comision']}
                    AND	sga_calendcursada.valido = 'S'
                ORDER BY sga_calendcursada.fecha";
        return kernel::db()->consultar($sql, db::FETCH_ASSOC);           
    }
    
    /**
    * parametros: comision
    * cache: no
    * filas: 1
    */
    function get_docentes_com($parametros)
    {
        $comision = $parametros['comision'];
        $sql = "EXECUTE PROCEDURE sp_docentes_com($comision)";
        return kernel::db()->consultar_fila($sql, db::FETCH_NUM);
    }
    
    /**
    * parametros: comision
    * cache: no
    * filas: 1
    */
    function get_asignac_com($parametros)
    {
        $comision = $parametros['comision'];
        $sql = "EXECUTE PROCEDURE sp_asignac_com($comision)";
        return kernel::db()->consultar_fila($sql, db::FETCH_NUM);
    }

}
?>
