SELECT *
FROM sga_alumnos al
WHERE al.plan IN ('2001',
                  '2002',
                  '2008')
  AND al.legajo IN
    (SELECT legajo
     FROM sga_reinscripcion
     WHERE anio_academico = 2020)
  AND al.nro_inscripcion NOT IN
    (SELECT nro_inscripcion
     FROM u808_req_ingl_cump u8
     WHERE u8.cumplido = 'S' )
  AND al.nro_inscripcion NOT IN
    (SELECT nro_inscripcion
     FROM sga_req_cumplidos
     WHERE requisito = '2'
       AND fecha_presentacion IS NOT  NULL )
  AND al.legajo NOT IN
    (SELECT legajo
     FROM vw_hist_academica
     WHERE materia = 'LT004'
       AND resultado = 'A'
       AND legajo = al.legajo
       AND legajo IN
         (SELECT legajo
          FROM vw_hist_academica
          WHERE materia = 'LT005'
            AND resultado = 'A' ) )
  AND ((28 =
          (SELECT count(materia)
           FROM vw_hist_academica
           WHERE legajo = al.legajo
             AND resultado = 'A'
             AND carrera = 'CA002'
             AND PLAN IN ('2001',
                          '2002',
                          '2008')
             AND materia NOT IN ('AAC01',
                                 'AAC02',
                                 'AAC51',
                                 'AAC57',
                                 'LT004',
                                 'LT005',
                                 'LT010',
                                 'M0062'))
        AND al.carrera = 'CA002')
       OR (31 =
             (SELECT count(materia)
              FROM vw_hist_academica
              WHERE legajo = al.legajo
                AND resultado = 'A'
                AND carrera = 'CA001'
                AND PLAN IN ('2001',
                             '2002',
                             '2008')
                AND materia NOT IN ('AAC01',
                                    'AAC02',
                                    'AAC51',
                                    'AAC57',
                                    'LT004',
                                    'LT005',
                                    'LT010'))
           AND al.carrera = 'CA001'));