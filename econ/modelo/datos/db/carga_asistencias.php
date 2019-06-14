<?php
namespace econ\modelo\datos\db;

use kernel\kernel;
use kernel\util\db\db;
use siu\modelo\datos\catalogo;
use siu\modelo\entidades\alumno_foto;

class carga_asistencias extends \siu\modelo\datos\db\carga_asistencias
{
    /**
    * parametros: _ua, legajo
    * cache: no
    * filas: n
    */
    function listado_dias_clases_docente($parametros)
    {
        $sql = "SELECT DISTINCT sga_materias.materia,
                                sga_materias.nombre as materia_nombre,
                                sga_asignaciones.dia_semana, 
                                CASE 
                                    when sga_asignaciones.dia_semana = 1 then 'Domingo'
                                    when sga_asignaciones.dia_semana = 2 then 'Lunes'
                                    when sga_asignaciones.dia_semana = 3 then 'Martes'
                                    when sga_asignaciones.dia_semana = 4 then 'Miércoles'
                                    when sga_asignaciones.dia_semana = 5 then 'Jueves'
                                    when sga_asignaciones.dia_semana = 6 then 'Viernes'
                                    when sga_asignaciones.dia_semana = 7 then 'Sábado'
                                END AS dia_nombre,
                                to_char(sga_asignaciones.hs_comienzo_clase,'%H:%M') as hs_comienzo_clase, 
                                to_char(sga_asignaciones.hs_finaliz_clase ,'%H:%M') as hs_finaliz_clase,
                                sga_periodos_lect.anio_academico,
                                sga_periodos_lect.periodo_lectivo,
                                (SELECT COUNT(DISTINCT sga_insc_cursadas.legajo) FROM sga_insc_cursadas WHERE comision = sga_docentes_com.comision AND estado in ('A','P','E')) as cant_inscriptos,
                                (SELECT COUNT(DISTINCT sga_inasis_acum.legajo) FROM sga_inasis_acum WHERE sga_inasis_acum.comision = sga_docentes_com.comision AND sga_inasis_acum.estado = 'L') as cant_libres
                    FROM sga_docentes_com
                    JOIN sga_comisiones ON (sga_comisiones.comision = sga_docentes_com.comision)
                    JOIN sga_materias ON (sga_materias.materia = sga_comisiones.materia)
                    JOIN sga_asign_clases ON (sga_asign_clases.comision = sga_docentes_com.comision)
                    JOIN sga_asignaciones ON (sga_asignaciones.asignacion = sga_asign_clases.asignacion)
                    JOIN sga_periodos_lect ON (sga_periodos_lect.anio_academico = sga_comisiones.anio_academico AND sga_periodos_lect.periodo_lectivo = sga_comisiones.periodo_lectivo)
                    WHERE sga_docentes_com.legajo = {$parametros['legajo']}
                    AND sga_periodos_lect.fecha_inactivacion >= TODAY
                    ORDER BY 	sga_materias.materia, 
                                    sga_asignaciones.dia_semana, 
                                    to_char(sga_asignaciones.hs_comienzo_clase,'%H:%M') ";
				
        $datos = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        $resultado = array();
        foreach(array_keys($datos) as $id) {
            $resultado[$id] = $datos[$id];
            $resultado[$id][catalogo::id] = catalogo::generar_id($datos[$id]['MATERIA'].$datos[$id]['DIA_SEMANA'].$datos[$id]['HS_COMIENZO_CLASE']);
        }
        return $resultado;
    }
    
    /**
    * parametros: _ua, legajo, materia, dia_semana, hs_comienzo, filas
    * cache: no
    * filas: n
    */
    function get_clases_especificas_docente($parametros)
    {
        $first = '';
        $filas = filter_var($parametros['filas'], FILTER_SANITIZE_NUMBER_INT);
        if ($filas > 0) {
            $first = "FIRST $filas";
        }	
        $legajo = $parametros['legajo'];
        $materia = $parametros['materia'];
        $dia_semana = $parametros['dia_semana'];
        $hs_comienzo = $parametros['hs_comienzo'];
        
        $sql = "SELECT $first DISTINCT
                                sga_materias.materia as materia,
                                sga_materias.nombre  as materia_nombre,
                                sga_asignaciones.dia_semana as dia_semana,
                                CASE 
                                    when sga_asignaciones.dia_semana = 1 then 'Domingo'
                                    when sga_asignaciones.dia_semana = 2 then 'Lunes'
                                    when sga_asignaciones.dia_semana = 3 then 'Martes'
                                    when sga_asignaciones.dia_semana = 4 then 'Miércoles'
                                    when sga_asignaciones.dia_semana = 5 then 'Jueves'
                                    when sga_asignaciones.dia_semana = 6 then 'Viernes'
                                    when sga_asignaciones.dia_semana = 7 then 'Sábado'
                                END AS dia_nombre,
                                to_char(sga_calendcursada.fecha, '%d/%m/%Y') as fecha_clase,
                                sga_tipo_clase.descripcion as tipo_clase,
                                to_char(sga_asignaciones.hs_comienzo_clase,'%H:%M') as hs_comienzo_clase,
                                to_char(sga_asignaciones.hs_finaliz_clase ,'%H:%M') as hs_finaliz_clase,
                                sga_asign_clases.cantidad_horas,
                                sga_calendcursada.fecha

                    FROM    sga_comisiones,
                            sga_docentes_com,
                            sga_materias,   
                            sga_calendcursada,   
                            sga_asignaciones,
                            sga_asign_clases,
                            sga_periodos_lect,
                            OUTER sga_tipo_clase

                    WHERE   sga_asignaciones.dia_semana = $dia_semana
                            AND to_char(sga_asignaciones.hs_comienzo_clase,'%H:%M') = $hs_comienzo
                            AND sga_materias.materia = $materia
                            AND sga_docentes_com.legajo = $legajo
                            AND	sga_materias.unidad_academica = sga_comisiones.unidad_academica
                            AND	sga_materias.materia = sga_comisiones.materia
                            AND sga_docentes_com.comision = sga_comisiones.comision
                            AND	sga_comisiones.comision = sga_calendcursada.comision
                            AND	sga_calendcursada.asignacion = sga_asignaciones.asignacion 
                            AND sga_calendcursada.valido = 'S'
                            AND	sga_calendcursada.comision = sga_asign_clases.comision
                            AND	sga_calendcursada.asignacion = sga_asign_clases.asignacion
                            AND	sga_calendcursada.fecha <= TODAY
                            AND sga_tipo_clase.tipo_clase = sga_asign_clases.tipo_clase
                            AND sga_periodos_lect.anio_academico = sga_comisiones.anio_academico 
                            AND sga_periodos_lect.periodo_lectivo = sga_comisiones.periodo_lectivo
                            AND sga_periodos_lect.fecha_inactivacion >= TODAY
                    ORDER BY sga_calendcursada.fecha DESC ";
				
        $datos = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        $resultado = array();
        foreach(array_keys($datos) as $id) {
            $resultado[$id] = $datos[$id];
            $resultado[$id][catalogo::id] = catalogo::generar_id($datos[$id]['MATERIA'].$datos[$id]['FECHA'].$datos[$id]['HS_COMIENZO_CLASE']);
        }
        return $resultado;
    }

    
    /**
    * parametros: materia, fecha_clase, hs_comienzo_clase
    * cache: no
    * filas: n
    */
    function get_clases_materia_dia_hs($parametros)
    {
        $materia = $parametros['materia'];
        $fecha_clase = $parametros['fecha_clase'];
        $hs_comienzo_clase = $parametros['hs_comienzo_clase'];
        
        $sql = "SELECT clase 
                        FROM sga_calendcursada
                        JOIN sga_comisiones ON (sga_comisiones.comision = sga_calendcursada.comision)
                        JOIN sga_asignaciones ON (sga_asignaciones.asignacion = sga_calendcursada.asignacion)
                WHERE sga_comisiones.materia = $materia
                AND sga_calendcursada.fecha = $fecha_clase
                AND to_char(sga_asignaciones.hs_comienzo_clase,'%H:%M') = $hs_comienzo_clase ";
        
        return kernel::db()->consultar($sql, db::FETCH_ASSOC);
    }
    
    /**
    * parametros: clase
    * cache: no
    * filas: n
    */
    function clase_detalle($parametros)
    {
        $sql = "execute procedure sp_AsisAluClas({$parametros["clase"]})";
        $datos = kernel::db()->consultar($sql, db::FETCH_NUM);
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
            $nuevo[$id]['INASISTENCIA'] = $dato[19]; 
            $nuevo[$id]['MOTIVO_INASIST'] = $dato[20]; 
            $motivo_justific = $this->get_justificacion_inasist(array('legajo'=>$nuevo[$id]['LEGAJO'], 'clase'=>$nuevo[$id]['CLASE']));
            $nuevo[$id]['MOTIVO_JUSTIFIC'] = $motivo_justific['MOTIVO_JUSTIFIC'];
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
    * parametros: _ua, carrera, legajo, comision, clase, cant_inasist, motivo
    * param_null: motivo
    * cache: no
    * filas: 1
    */
   function guardar($parametros) 
   {
        if (!isset($parametros["motivo"]))
        {
            $sql = "EXECUTE PROCEDURE sp_u_asisaluclas(" .
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
            $tipo = $this->get_tipo_inasistencia($parametros);
            if ($tipo['TIPO'] == 1) //Justificada
            {
                $sql = "EXECUTE PROCEDURE sp_u_justsaluclas(" .
                                 $parametros["_ua"] . ",".
                                 $parametros["carrera"] . "," .
                                 $parametros["legajo"] . "," .
                                 $parametros["comision"] . "," .
                                 $parametros["clase"] . "," .
                                 $parametros["cant_inasist"] . ",".
                                 $parametros["motivo"] .");";   
            }
            else    //Injustificada
            {
                $sql = "EXECUTE PROCEDURE sp_u_asisaluclas(" .
                                 $parametros["_ua"] . ",".
                                 $parametros["carrera"] . "," .
                                 $parametros["legajo"] . "," .
                                 $parametros["comision"] . "," .
                                 $parametros["clase"] . "," .
                                 $parametros["cant_inasist"] . ",".
                                 $parametros["motivo"] .");";
            }
        }
        return kernel::db()->consultar_fila($sql, db::FETCH_NUM);
    }	
    
    /**
     * parametros: 
     * cache: no
     * filas: n
     */
    function get_motivos_inasistencia($parametros)
    {
        $sql = "SELECT motivo, descripcion, tipo FROM sga_inasis_motivos";
        return kernel::db()->consultar($sql, db::FETCH_ASSOC);        
    }
    
    /**
     * parametros: motivo
     * cache: no
     * filas: 1
     */
    function get_tipo_inasistencia($parametros)
    {
        $motivo = $parametros['motivo'];
        $sql = "SELECT tipo FROM sga_inasis_motivos WHERE motivo = $motivo";
        return kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);        
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
        $sql = "SELECT motivo_justific FROM sga_inasistencias WHERE clase = '$clase' AND legajo = '$legajo'";
        return kernel::db()->consultar_fila($sql, db::FETCH_ASSOC);        
    }
}
?>