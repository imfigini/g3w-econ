<?php
namespace econ\modelo\datos\db;
use kernel\kernel;
use kernel\util\db\db;
use siu\modelo\datos\catalogo;
use siu\modelo\datos\db\toba;


class coord_materia 
{
    /**
     * parametros: anio_academico, periodo
     * param_null: periodo
     * cache: no
     * filas: n
     */
    function get_materias_en_comisiones($parametros)
    {
        $anio_academico = $parametros['anio_academico'];
        if (isset($anio_academico))
        {
            $sql = "SELECT DISTINCT C.materia, 
                                    M.nombre_materia, 
                                    C.anio_academico, 
                                    C.periodo_lectivo, 
                                    U.coordinador,
                                    P.apellido || ', ' || P.nombres AS coordinador_nombre
                        FROM sga_comisiones C
                        JOIN sga_atrib_mat_plan M ON (M.materia = C.materia)
                        JOIN sga_planes PL ON (PL.unidad_academica = M.unidad_academica AND PL.carrera = M.carrera AND PL.plan = M.plan)
                        LEFT JOIN ufce_coordinadores_materias U ON (U.materia = M.materia AND U.anio_academico = C.anio_academico AND U.periodo_lectivo = C.periodo_lectivo)
                        LEFT JOIN sga_docentes D ON (D.legajo = U.coordinador)
                        LEFT JOIN sga_personas P ON (P.nro_inscripcion = D.nro_inscripcion)
                    WHERE   PL.estado = 'V'
			AND C.anio_academico = $anio_academico ";
            $periodo = $parametros['periodo'];
            if (!empty($periodo) && isset($periodo) && $periodo != "''")
            {
                $sql .= " AND C.periodo_lectivo = $periodo";
            }
            $sql .= " ORDER BY 2";
            
            $datos = kernel::db()->consultar($sql, db::FETCH_ASSOC);
            
            $nuevo = array();
            foreach($datos as $id => $dato) 
            {
                    $id = catalogo::generar_id($dato['MATERIA']);
                    $nuevo[$id][catalogo::id] = $id;
                    $nuevo[$id]['MATERIA'] = $dato['MATERIA'];
                    $nuevo[$id]['NOMBRE_MATERIA'] = $dato['NOMBRE_MATERIA']." (".$dato['MATERIA'].")";
                    $nuevo[$id]['PERIODO'] = $dato['PERIODO_LECTIVO'];
                    $nuevo[$id]['ANIO_ACADEMICO'] = $dato['ANIO_ACADEMICO'];
                    $nuevo[$id]['COORDINADOR'] = $dato['COORDINADOR'];
                    $nuevo[$id]['COORDINADOR_NOMBRE'] = $dato['COORDINADOR_NOMBRE'];
            }
            return $nuevo;
        }
        return null;
    }
    
    /**
     * parametros: materia, anio_academico, periodo
     * param_null: periodo
     * cache: no
     * filas: n
     */
    function get_docentes_de_materia($parametros)
    {
        $materia = $parametros['materia'];
        $anio_academico = $parametros['anio_academico'];
        $periodo = $parametros['periodo'];
        
        //$sql = "SELECT DISTINCT INITCAP(P.apellido) AS apellido, INITCAP(P.nombres) AS nombres, D.legajo
        $sql = "SELECT DISTINCT D.legajo, UPPER(P.apellido) || ', ' || UPPER(P.nombres) AS docente
                    FROM sga_docentes_com DC
                    JOIN sga_comisiones C ON (C.comision = DC.comision)
                    JOIN sga_docentes D ON (D.legajo = DC.legajo)
                    JOIN sga_personas P ON (P.nro_inscripcion = D.nro_inscripcion)
                    WHERE C.anio_academico = $anio_academico
                            AND C.materia = $materia ";
        if (!empty($periodo) && isset($periodo) && $periodo != "''")
        {
            $sql .= " AND C.periodo_lectivo = $periodo";
        }
        $sql .= " ORDER BY 2";
        
        $datos = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        $nuevo = array();
        foreach($datos as $id => $dato) 
        {
                $id = catalogo::generar_id($dato['LEGAJO']);
                $nuevo[$id][catalogo::id] = $id;
                $nuevo[$id]['LEGAJO'] = $dato['LEGAJO'];
                $nuevo[$id]['DOCENTE'] = $dato['DOCENTE'];
        }
        return $nuevo;

    }
    
    /**
    * parametros: materia, anio_academico, periodo
    * param_null: periodo
    * cache: no
    * filas: n
    */
    function get_coordinador($parametros)
    {
        if (!empty($parametros))
        {
            $materia = $parametros['materia'];
            $anio_academico = $parametros['anio_academico'];
            $periodo = $parametros['periodo'];
        
            $sql = "SELECT coordinador
                        FROM ufce_coordinadores_materias
                    WHERE   materia = $materia
                            AND anio_academico = $anio_academico ";
            if (!empty($periodo) && isset($periodo) && $periodo != "''")
            {
                $sql .= " AND periodo_lectivo = $periodo";
            }
            $datos = kernel::db()->consultar($sql, db::FETCH_ASSOC);
            return $datos[0]['COORDINADOR'];  
        }
        return null;
    }
    
    /**
    * parametros: materia, anio_academico, periodo, coordinador
    * cache: no
    * filas: n
    */
    function update_coordinador($parametros)
    {
        if (!empty($parametros))
        {
            $materia = $parametros['materia'];
            $anio_academico = $parametros['anio_academico'];
            $periodo = $parametros['periodo'];
            $coordinador = $parametros['coordinador'];
        
            $sql = "UPDATE ufce_coordinadores_materias 
                        SET coordinador = $coordinador
                        WHERE materia = $materia
                            AND anio_academico = $anio_academico
                            AND periodo_lectivo = $periodo";
            $datos = kernel::db()->ejecutar($sql);
            return $datos;
        }
        return null;
    }
    
    /**
    * parametros: materia, anio_academico, periodo, coordinador
    * cache: no
    * filas: n
    */
    function set_coordinador($parametros)
    {
        if (!empty($parametros))
        {
            $existe = $this->get_coordinador($parametros);
            if ($existe)
            {
               $this->update_coordinador($parametros);
               $this->del_coordinador_anterior(array('coord_anterior'=>$existe));
            }
            else
            {
                $materia = $parametros['materia'];
                $anio_academico = $parametros['anio_academico'];
                $periodo = $parametros['periodo'];
                $coordinador = $parametros['coordinador'];

                $sql = "INSERT INTO ufce_coordinadores_materias (unidad_academica, materia, anio_academico, periodo_lectivo, coordinador) VALUES
                            ('FCE', $materia, $anio_academico, $periodo, $coordinador)";
                $datos = kernel::db()->ejecutar($sql);
            }
            if (!$this->is_usuario_coord($parametros))
            {
                $this->set_usuario_coord($parametros);
            }
        }
    }
    
    /**
    * parametros: coordinador
    * cache: no
    * filas: n
    */
    function is_usuario_coord($parametros)
    {
        $coordinador = $parametros['coordinador'];
        $sql = "SELECT * FROM aca_tipos_usuar_ag 
                    WHERE nro_inscripcion IN (
                                SELECT nro_inscripcion FROM sga_docentes WHERE legajo = $coordinador
                            )
                        AND  tipo_usuario = 'COORD'";
        
//        kernel::log()->add_info("EJECUTANDO ACCION IRIS is_usuario_coord", '');
//        kernel::log()->add_info($sql, '');
        
        $resultado = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        
//        kernel::log()->add_info($resultado, '');
        
        if (count($resultado)>0 && isset($resultado[0]))
        {
            return true;
        }
        return false;
    }

    /**
    * parametros: coordinador
    * cache: no
    * filas: n
    */
    function set_usuario_coord($parametros)
    {
        $coordinador = $parametros['coordinador'];
        $sql = "SELECT nro_inscripcion FROM sga_docentes
                    WHERE legajo = $coordinador";

        $nro_inscripcion = kernel::db()->consultar($sql, db::FETCH_ASSOC);
                $nro_inscripcion = $nro_inscripcion[0]['NRO_INSCRIPCION'];
        
        $sql = "INSERT INTO aca_tipos_usuar_ag VALUES ('FCE', '$nro_inscripcion', 'COORD', 'A')";
        kernel::db()->ejecutar($sql);
    }
    
    /**
    * parametros: coord_anterior
    * cache: no
    * filas: n
    */
    function del_coordinador_anterior($parametros)
    {
        $coord_anterior = $parametros['coord_anterior'];
        $sql = "SELECT coordinador
                    FROM ufce_coordinadores_materias
                    WHERE coordinador = '$coord_anterior' ";
        $existe = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        if (empty($existe) || !isset($existe[0]))
        {
            $sql = "SELECT nro_inscripcion FROM sga_docentes WHERE legajo = '$coord_anterior'";
            $nro_inscripcion = kernel::db()->consultar($sql, db::FETCH_ASSOC);
            $nro_inscripcion = $nro_inscripcion[0]['NRO_INSCRIPCION'];
            $sql = "DELETE FROM aca_tipos_usuar_ag
                        WHERE nro_inscripcion = '$nro_inscripcion'
                        AND tipo_usuario = 'COORD'";
            kernel::db()->ejecutar($sql);
        }
    }
    
    /**
    * parametros: anio_academico, periodo, anio_academico_anterior, periodo_anterior
    * cache: no
    * filas: n
    */
    function replicar_coordinador($parametros)
    {
        //Borra los del cuatrimestre actual
        $anio_academico = $parametros['anio_academico'];
        $periodo = $parametros['periodo'];
        $sql = "DELETE FROM ufce_coordinadores_materias
                    WHERE anio_academico = $anio_academico
                        AND periodo_lectivo = $periodo";
        kernel::db()->ejecutar($sql);
        
        //Recupera los del cuatrimestre anterior
        $anio_academico_anterior = $parametros['anio_academico_anterior'];
        $periodo_anterior = $parametros['periodo_anterior'];
        $sql = "SELECT unidad_academica, materia, anio_academico, periodo_lectivo, coordinador
                    FROM ufce_coordinadores_materias
                    WHERE anio_academico = $anio_academico_anterior
                        AND periodo_lectivo = $periodo_anterior";
        $resultado = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        
        //Inserta los del cuatrimestre anterior en el actual
        foreach($resultado as $res)
        {
            $ua = $res['UNIDAD_ACADEMICA'];
            $materia = $res['MATERIA'];
            $coord = $res['COORDINADOR'];
            $sql = "INSERT INTO ufce_coordinadores_materias 
                        VALUES ('$ua', '$materia', $anio_academico, $periodo, '$coord')";
            kernel::db()->ejecutar($sql);

            if (!$this->is_usuario_coord(array('coordinador'=>"'$coord'")))
            {
                $this->set_usuario_coord(array('coordinador'=>"'$coord'"));
            }
        }
    }
}

