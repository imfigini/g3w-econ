
--SELECT DISTINCT plan FROM ufce_mixes;
--SELECT * FROM ufce_mixes;
UPDATE ufce_mixes
    SET plan = REPLACE(plan, '50Âº', '50º')
    WHERE 1=1;

--SELECT * FROM ufce_cron_eval_parc_estados;
UPDATE ufce_cron_eval_parc_estados
    SET descripcion = REPLACE(descripcion, 'Ã³', 'ó')
    WHERE 1=1;
UPDATE ufce_cron_eval_parc_estados
    SET descripcion = REPLACE(descripcion, 'Ã­', 'í')
    WHERE 1=1;

--SELECT * FROM ufce_parametros;
UPDATE ufce_parametros
    SET descripcion = REPLACE(descripcion, 'Ã³', 'ó')
    WHERE 1=1;
UPDATE ufce_parametros
    SET descripcion = REPLACE(descripcion, 'Ã­', 'í')
    WHERE 1=1;
UPDATE ufce_parametros
    SET descripcion = REPLACE(descripcion, 'Ã¡', 'á')
    WHERE 1=1;

--SELECT * FROM ufce_periodos_tipo;
UPDATE ufce_periodos_tipo
    SET descripcion = REPLACE(descripcion, 'Ã³', 'ó')
    WHERE 1=1;
UPDATE ufce_periodos_tipo
    SET descripcion = REPLACE(descripcion, 'Ã­', 'í')
    WHERE 1=1;

