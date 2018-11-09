<?php
namespace econ\modelo\datos\db;
use kernel\kernel;
use kernel\util\db\db;

class cursos 
{
    /**
     * parametros: legajo
     * param_null: legajo
     * cache: no
     * filas: n
     */
    function get_materias_cincuentenario($parametros)
    {
        $sql = "SELECT DISTINCT M.materia, 
                                M.nombre AS materia_nombre
                        FROM sga_materias M
                        JOIN sga_atrib_mat_plan P ON (P.materia = M.materia)
                        WHERE P.plan LIKE '50%' ";

        if ($parametros['legajo'] != "''")
        {
            $legajo = $parametros['legajo'];
            $sql .= " AND M.materia IN (SELECT materia FROM ufce_coordinadores_materias WHERE coordinador = $legajo) ";
        }
        
        $sql .= " ORDER BY 2";
        $result = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        return $result;
    }
    
        /**
     * parametros: materia
     * cache: no
     * filas: n
     */
    function get_ciclo_de_materias($parametros)
    {
        $materia = $parametros['materia'];
        $sql = "SELECT DISTINCT 
                            CASE 
                                    WHEN lower(CIC.nombre) LIKE '%fundam%' THEN 'Fundamento'
                                    WHEN lower(CIC.nombre) LIKE '%prof%' THEN 'Profesional'
                                    ELSE ''
                            END AS ciclo_nombre
                    FROM sga_materias M
                    JOIN sga_materias_ciclo MC ON (MC.materia = M.materia)
                    JOIN sga_ciclos CIC ON (CIC.ciclo = MC.ciclo)
                    JOIN sga_ciclos_plan CP ON (CIC.ciclo = CP.ciclo)
                    WHERE CP.plan LIKE '50%'
                    AND M.materia = $materia ";
        $result = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        $es_fundamento = 0;
        $ciclo = $result[0]['CICLO_NOMBRE'];
        if ($result[0]['CICLO_NOMBRE'] == 'Fundamento')
        {
            $es_fundamento = 1;
        }
        if (isset($result[1]))
        {
            $ciclo .= ' y '.$result[1]['CICLO_NOMBRE'];
            if ($result[1]['CICLO_NOMBRE'] == 'Fundamento')
            {
                $es_fundamento = 1;
            }
        }
        return array('ciclo_nombre'=>$ciclo, 'es_ciclo_fundamento'=>$es_fundamento);
    }
                
    /**
     * parametros: anio_academico, periodo, materia
     * param_null: periodo
     * cache: no
     * filas: n
     */
    function get_comisiones_promo_de_materia($parametros)
    {
        $anio_academico = $parametros['anio_academico'];
        $periodo = $parametros['periodo'];
        $materia = $parametros['materia'];
        //Sólo retorna las comisiones que son promocionables
        $sql = "SELECT  C.comision, 
                        C.nombre AS comision_nombre, 
                        C.anio_academico, 
                        C.periodo_lectivo,
                        C.escala_notas, 
                        C.carrera, 
                        C.turno,
                        EN.nombre AS escala_notas_nombre,
                        UC.porc_parciales,
                        UC.porc_integrador,
                        UC.porc_trabajos
                FROM sga_comisiones C
                JOIN sga_escala_notas EN ON (EN.escala_notas = C.escala_notas)
                LEFT JOIN ufce_comisiones_porc_notas UC ON (UC.comision = C.comision)
                WHERE C.estado = 'A'
                        AND lower(EN.nombre) LIKE '%promo%'
                        AND C.anio_academico = $anio_academico
                        AND C.materia = $materia ";

        if ($periodo != "''")
        {
            $sql .= " AND C.periodo_lectivo = $periodo";
        }
        return kernel::db()->consultar($sql, db::FETCH_ASSOC);
    }
    
    /**
    * parametros: comision
    * cache: memoria
    * filas: n
    */
    function get_porcentajes_instancias($parametros)
    {
        $comision = $parametros['comision'];
        $sql = "SELECT porc_parciales, porc_integrador, porc_trabajos
                    FROM ufce_comisiones_porc_notas
                WHERE   comision = $comision";
        $datos = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        return $datos[0];  
    }
    
    /**
    * parametros: comision, porc_parciales, porc_integrador, porc_trabajos
    * cache: memoria
    * filas: n
    */
    function update_porcentajes_instancias($parametros)
    {
        $comision = $parametros['comision'];
        $porc_parciales = $parametros['porc_parciales'];
        $porc_integrador = $parametros['porc_integrador'];
        $porc_trabajos = $parametros['porc_trabajos'];

        $sql = "UPDATE ufce_comisiones_porc_notas 
                    SET porc_parciales = $porc_parciales,
                        porc_integrador = $porc_integrador,
                        porc_trabajos = $porc_trabajos
                    WHERE comision = $comision";
        $datos = kernel::db()->ejecutar($sql);
        return $datos;
    }
    
    /**
    * parametros: comision, porc_parciales, porc_integrador, porc_trabajos
    * cache: memoria
    * filas: n
    */
    function set_porcentajes_instancias($parametros)
    {
        $existe = $this->get_porcentajes_instancias($parametros);
        if ($existe)
        {
            return $this->update_porcentajes_instancias($parametros);
        }
        else
        {
            $comision = $parametros['comision'];
            $porc_parciales = $parametros['porc_parciales'];
            $porc_integrador = $parametros['porc_integrador'];
            $porc_trabajos = $parametros['porc_trabajos'];

            $sql = "INSERT INTO ufce_comisiones_porc_notas (comision, porc_parciales, porc_integrador, porc_trabajos) VALUES
                        ($comision, $porc_parciales, $porc_integrador, $porc_trabajos)";
            $datos = kernel::db()->ejecutar($sql);
            return $datos;
        }
    }
    
    
    /**
    * parametros: comision
    * cache: memoria
    * filas: n
    */
    function get_nombre_comision($parametros)
    {
        $comision = $parametros['comision'];

        $sql = "SELECT nombre
                    FROM sga_comisiones
                WHERE   comision = $comision";
        $datos = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        return $datos[0]['NOMBRE'];  
    }

    /**
    * parametros: materia
    * cache: memoria
    * filas: n
    */
    function get_nombre_materia($parametros)
    {
        if (isset($parametros['materia']))
        {
            $materia = $parametros['materia'];
        
            $sql = "SELECT nombre
                        FROM sga_materias
                    WHERE   materia = $materia";
            $datos = kernel::db()->consultar($sql, db::FETCH_ASSOC);
            return $datos[0]['NOMBRE'];  
        }
        if (isset($parametros['comision']))
        {
            $comision = $parametros['comision'];
        
            $sql = "SELECT nombre
                        FROM sga_materias
                    WHERE   materia IN (
                                SELECT materia FROM sga_comisiones WHERE comision = $comision)";
            $datos = kernel::db()->consultar($sql, db::FETCH_ASSOC);
            return $datos[0]['NOMBRE'];  
        }
        return '';
    }
    
    /**
    * parametros: comision
    * cache: memoria
    * filas: n
    */
    function get_nombre_materia_de_comision($parametros)
    {
        $comision = $parametros['comision'];

        $sql = "SELECT nombre
                    FROM sga_materias
                WHERE   materia IN (
                            SELECT materia FROM sga_comisiones WHERE comision = $comision)";
        $datos = kernel::db()->consultar($sql, db::FETCH_ASSOC);
        return $datos[0]['NOMBRE'];  
    }

    /**
     * parametros: anio_academico, periodo, materia
     * param_null: periodo
     * cache: no
     * filas: n
     */
    function get_comisiones_de_materia($parametros)
    {
        $anio_academico = $parametros['anio_academico'];
        $periodo = $parametros['periodo'];
        $materia = $parametros['materia'];
        //Retorna todas las comisiones de la materia, junto con su escala de notas
        $sql = "SELECT  C.comision, 
                        C.nombre AS comision_nombre, 
                        C.anio_academico, 
                        C.periodo_lectivo,
                        C.escala_notas, 
                        C.turno, 
                        C.carrera, 
                        EN.nombre AS escala_notas_nombre
                FROM sga_comisiones C
                JOIN sga_escala_notas EN ON (EN.escala_notas = C.escala_notas)
                WHERE C.estado = 'A'
                        AND C.anio_academico = $anio_academico
                        AND C.materia = $materia ";

        if ($periodo != "''")
        {
            $sql .= " AND C.periodo_lectivo = $periodo";
        }
        return kernel::db()->consultar($sql, db::FETCH_ASSOC);
    }
    
}
