--Para inscribir algunos alumnos automáticamente a comisiones: 
select * from sga_comisiones 
where materia = 'L0015' and anio_academico = 2020
and comision in (select comision from sga_docentes_com where legajo = '2449');
7712
7713


select first 10 "EXECUTE PROCEDURE sp_i_inscCursadas('FCE', '" || carrera || "', '" || legajo || "', '" || nro_inscripcion ||
			"' , 7713, 'P',  1 , 'L0015', NULL, '" || plan || "', '2', NULL, NULL, NULL, NULL, 'N');"
from sga_alumnos 
where regular = 'S'
and calidad = 'A'
and carrera in ('CA001', 'CA002', 'CA004')
and plan like '50%'
and legajo not in (select legajo from sga_cursadas 
						where materia = 'L0015'
						and resultado in ('A','P')
						and carrera = sga_alumnos.carrera)
and legajo not in  (select legajo from vw_hist_academica 
						where materia = 'L0015'
						and resultado = 'A'
						and carrera = sga_alumnos.carrera)
and legajo not in (select legajo from sga_insc_cursadas 
						where comision = 7712)
and legajo in (select legajo from sga_reinscripcion where carrera = sga_alumnos.carrera and anio_academico = 2020);


select first 10 "EXECUTE PROCEDURE sp_i_inscCursadas('FCE', '" || ALU.carrera || "', '" || legajo || "', '" || nro_inscripcion ||
			"' , 7713, 'P',  1 , 'L0015', NULL, '" || plan || "', '2', NULL, NULL, NULL, NULL, 'N');"
from sga_alumnos ALU
where regular = 'S'
and calidad = 'A'
and carrera in ('CA001', 'CA002', 'CA004')
and plan like '50%'
and legajo not in (select legajo from sga_cursadas 
						where materia = 'L0015'
						and resultado in ('A','P')
						and carrera = ALU.carrera)
and legajo not in  (select legajo from vw_hist_academica 
						where materia = 'L0015'
						and resultado = 'A'
						and carrera = ALU.carrera)
and legajo not in (select legajo from sga_insc_cursadas 
						where comision in (7712, 7713))
and legajo in (select legajo from sga_alumnos where carrera = ALU.carrera and year(fecha_ingreso) = 2020);



EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-833921', 'FCE-8339' , 7712, 'P',  1 , 'L0015', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA002', 'FCE-840356', 'FCE-8403' , 7712, 'P',  1 , 'L0015', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA002', 'FCE-836640', 'FCE-8366' , 7712, 'P',  1 , 'L0015', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-846316', 'FCE-8463' , 7712, 'P',  1 , 'L0015', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-846434', 'FCE-8464' , 7712, 'P',  1 , 'L0015', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-846547', 'FCE-8465' , 7712, 'P',  1 , 'L0015', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-846751', 'FCE-8467' , 7712, 'P',  1 , 'L0015', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA002', 'FCE-846841', 'FCE-8468' , 7712, 'P',  1 , 'L0015', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA002', 'FCE-846956', 'FCE-8469' , 7712, 'P',  1 , 'L0015', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA002', 'FCE-847012', 'FCE-8470' , 7712, 'P',  1 , 'L0015', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');

EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-183915', 'FCE-1839' , 7712, 'P',  1 , 'L0015', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-196815', 'FCE-1968' , 7712, 'P',  1 , 'L0015', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-299220', 'FCE-2992' , 7712, 'P',  1 , 'L0015', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-341340', 'FCE-3413' , 7712, 'P',  1 , 'L0015', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-478103', 'FCE-4781' , 7712, 'P',  1 , 'L0015', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-495808', 'FCE-4958' , 7712, 'P',  1 , 'L0015', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-499451', 'FCE-4994' , 7712, 'P',  1 , 'L0015', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-526018', 'FCE-5260' , 7712, 'P',  1 , 'L0015', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-533630', 'FCE-5336' , 7712, 'P',  1 , 'L0015', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-535207', 'FCE-5352' , 7712, 'P',  1 , 'L0015', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');

EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-183915', 'FCE-1839' , 7713, 'P',  1 , 'L0015', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-196815', 'FCE-1968' , 7713, 'P',  1 , 'L0015', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-299220', 'FCE-2992' , 7713, 'P',  1 , 'L0015', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-341340', 'FCE-3413' , 7713, 'P',  1 , 'L0015', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-478103', 'FCE-4781' , 7713, 'P',  1 , 'L0015', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-495808', 'FCE-4958' , 7713, 'P',  1 , 'L0015', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-499451', 'FCE-4994' , 7713, 'P',  1 , 'L0015', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-526018', 'FCE-5260' , 7713, 'P',  1 , 'L0015', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-533630', 'FCE-5336' , 7713, 'P',  1 , 'L0015', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-535207', 'FCE-5352' , 7713, 'P',  1 , 'L0015', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');

EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-371651', 'FCE-3716' , 7713, 'P',  1 , 'L0015', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA004', 'FCE-507534', 'FCE-5075' , 7713, 'P',  1 , 'L0015', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA002', 'FCE-612856', 'FCE-6128' , 7713, 'P',  1 , 'L0015', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA002', 'FCE-709003', 'FCE-7090' , 7713, 'P',  1 , 'L0015', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-723555', 'FCE-7235' , 7713, 'P',  1 , 'L0015', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA002', 'FCE-744910', 'FCE-7449' , 7713, 'P',  1 , 'L0015', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA002', 'FCE-746912', 'FCE-7469' , 7713, 'P',  1 , 'L0015', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA002', 'FCE-762628', 'FCE-7626' , 7713, 'P',  1 , 'L0015', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA002', 'FCE-766710', 'FCE-7667' , 7713, 'P',  1 , 'L0015', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA002', 'FCE-772757', 'FCE-7727' , 7713, 'P',  1 , 'L0015', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');



-----------------------------------------------------------------------------------------------
----Práctica Profesional ('L1012') Carrera: CA001----------------------------------------------
--Malaga = 29754929
INSERT INTO aca_tipos_usuar_ag VALUES ('FCE', 'FCE-98', 'OFD', 'A');

EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-31615', 'FCE-316' , 7752, 'P',  1 , 'L1012', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-66154', 'FCE-661' , 7752, 'P',  1 , 'L1012', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-3215', 'FCE-32' , 7752, 'P',  1 , 'L1012', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-142615', 'FCE-1426' , 7752, 'P',  1 , 'L1012', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-183915', 'FCE-1839' , 7752, 'P',  1 , 'L1012', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-189215', 'FCE-1892' , 7752, 'P',  1 , 'L1012', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-196815', 'FCE-1968' , 7752, 'P',  1 , 'L1012', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');

-----------------------------------------------------------------------------------------------
----Taller de Desarrollo de Habilidades I ('LT001') Carrera: CA001-----------------------------
--Casco	Miriam María Rosa = 14416192 (legajo: 2023)
INSERT INTO aca_tipos_usuar_ag VALUES ('FCE', 'FCE-7419', 'OFD', 'A');

EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-66154', 'FCE-661' , 7663, 'P',  1 , 'LT001', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-142615', 'FCE-1426' , 7663, 'P',  1 , 'LT001', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-189215', 'FCE-1892' , 7663, 'P',  1 , 'LT001', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-495808', 'FCE-4958' , 7663, 'P',  1 , 'LT001', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-592044', 'FCE-5920' , 7663, 'P',  1 , 'LT001', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-574330', 'FCE-5743' , 7663, 'P',  1 , 'LT001', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-605750', 'FCE-6057' , 7663, 'P',  1 , 'LT001', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA002', 'FCE-600906', 'FCE-6009' , 7663, 'P',  1 , 'LT001', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-626142', 'FCE-6261' , 7663, 'P',  1 , 'LT001', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');
EXECUTE PROCEDURE sp_i_inscCursadas('FCE', 'CA001', 'FCE-620344', 'FCE-6203' , 7663, 'P',  1 , 'LT001', NULL, '50º', '2', NULL, NULL, NULL, NULL, 'N');

