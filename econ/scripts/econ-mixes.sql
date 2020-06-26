------------------------------------------------------------------------------------------------------------------
------------ Crear tabla para administrar mix's ------------------------------------------------------------------
--DROP TABLE ufce_mixes;
CREATE TABLE ufce_mixes(
	unidad_academica VARCHAR(5) not null,
	carrera VARCHAR(5) 	not null,
	plan VARCHAR(5) 	not null,
	version VARCHAR(5) 	not null,
	materia VARCHAR(5) 	not null,
	anio_de_cursada		INTEGER,
	mix			VARCHAR(1),
	PRIMARY KEY (unidad_academica, carrera, plan, version, materia),
	FOREIGN KEY (unidad_academica, carrera, plan, version, materia) REFERENCES sga_atrib_mat_plan(unidad_academica, carrera, plan, version, materia)
);

{
SELECT * FROM sga_carreras;
--CA001	Contador Público
--CA002	Licenciatura en Administración
--CA003	Auxiliar Administrativo Contable --> pregrado
--CA004	Licenciatura en Economía Empresarial

SELECT * FROM sga_Atrib_mat_plan 
	WHERE plan LIKE '50%' 
	AND carrera = 'CA001'
ORDER BY anio_de_cursada, materia;
}

----------CA001	Contador Público--------------------------------------
---1º Año-------------------------------------------------------------
---MIX A--------------------------------------------------------------
--Contabilidad Básica
--Matemática I 
--Administración
INSERT INTO ufce_mixes VALUES ('FCE', 'CA001', '50º', 1, 'L0001', 1, 'A');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA001', '50º', 1, 'L0002', 1, 'A');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA001', '50º', 1, 'L0003', 1, 'A');
---MIX B--------------------------------------------------------------
--Introducción a la Economía
--Instituciones de Derecho Público
--Estadística
--Historia Económica
INSERT INTO ufce_mixes VALUES ('FCE', 'CA001', '50º', 1, 'L0005', 1, 'B');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA001', '50º', 1, 'L0006', 1, 'B');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA001', '50º', 1, 'L0007', 1, 'B');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA001', '50º', 1, 'L0008', 1, 'B');
---2º Año-------------------------------------------------------------
---MIX A--------------------------------------------------------------
--Contabilidad Intermedia
--Matemática II
--Sistemas de Información Gerencial
--Filosofía y Lógica
INSERT INTO ufce_mixes VALUES ('FCE', 'CA001', '50º', 1, 'L0009', 2, 'A');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA001', '50º', 1, 'L0010', 2, 'A');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA001', '50º', 1, 'L0011', 2, 'A');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA001', '50º', 1, 'L1000', 2, 'A');
---MIX B--------------------------------------------------------------
--Análisis Microeconómico
--Derecho Empresario I
--Técnicas Cuantitativas
--Sistemas Administrativos
INSERT INTO ufce_mixes VALUES ('FCE', 'CA001', '50º', 1, 'L0012', 2, 'B');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA001', '50º', 1, 'L0013', 2, 'B');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA001', '50º', 1, 'L0014', 2, 'B');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA001', '50º', 1, 'L0004', 2, 'B');
---3º Año-------------------------------------------------------------
---MIX A--------------------------------------------------------------
--Matemática Financiera
--Administración Pública
--Tecnologías de Información
--Costos
INSERT INTO ufce_mixes VALUES ('FCE', 'CA001', '50º', 1, 'L0015', 3, 'A');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA001', '50º', 1, 'L0020', 3, 'A');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA001', '50º', 1, 'L0021', 3, 'A');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA001', '50º', 1, 'L1003', 3, 'A');
---MIX B--------------------------------------------------------------
--Derecho Empresario II
--Análisis Macroeconómico
--Estados Contables
INSERT INTO ufce_mixes VALUES ('FCE', 'CA001', '50º', 1, 'L0017', 3, 'B');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA001', '50º', 1, 'L0016', 3, 'B');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA001', '50º', 1, 'L1001', 3, 'B');
---4º Año-------------------------------------------------------------
---MIX A--------------------------------------------------------------
--Finanzas Corporativas
--Gestión de Costos
--Contabilidad Superior
--Impuestos I
INSERT INTO ufce_mixes VALUES ('FCE', 'CA001', '50º', 1, 'L0022', 4, 'A');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA001', '50º', 1, 'L1018', 4, 'A');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA001', '50º', 1, 'L1004', 4, 'A');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA001', '50º', 1, 'L1007', 4, 'A');
---MIX B--------------------------------------------------------------
--Legislación Laboral
--Concursos y Quiebras
--Finanzas de Activos y Mercados Financieros
--Auditoría I
INSERT INTO ufce_mixes VALUES ('FCE', 'CA001', '50º', 1, 'L1005', 4, 'B');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA001', '50º', 1, 'L1009', 4, 'B');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA001', '50º', 1, 'L0024', 4, 'B');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA001', '50º', 1, 'L1006', 4, 'B');
---5º Año-------------------------------------------------------------
---MIX A--------------------------------------------------------------
--Metodología de la Investigación
--Auditoría II
--Impuestos II
--Práctica Profesional
INSERT INTO ufce_mixes VALUES ('FCE', 'CA001', '50º', 1, 'L0023', 5, 'A');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA001', '50º', 1, 'L1010', 5, 'A');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA001', '50º', 1, 'L1011', 5, 'A');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA001', '50º', 1, 'L1012', 5, 'A');


----------CA002	Licenciatura en Administración------------------------
---1º Año-------------------------------------------------------------
---MIX A--------------------------------------------------------------
--Contabilidad Básica
--Matemática I 
--Administración
INSERT INTO ufce_mixes VALUES ('FCE', 'CA002', '50º', 1, 'L0001', 1, 'A');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA002', '50º', 1, 'L0002', 1, 'A');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA002', '50º', 1, 'L0003', 1, 'A');
---MIX B--------------------------------------------------------------
--Introducción a la Economía 
--Instituciones de Derecho Público
--Estadística
--Historia Económica
INSERT INTO ufce_mixes VALUES ('FCE', 'CA002', '50º', 1, 'L0005', 1, 'B');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA002', '50º', 1, 'L0006', 1, 'B');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA002', '50º', 1, 'L0007', 1, 'B');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA002', '50º', 1, 'L0008', 1, 'B');
---2º Año-------------------------------------------------------------
---MIX A--------------------------------------------------------------
--Comportamiento Organizacional
--Matemática II
--Sistemas de Información Gerencial
--Filosofía y Lógica
INSERT INTO ufce_mixes VALUES ('FCE', 'CA002', '50º', 1, 'L2000', 2, 'A');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA002', '50º', 1, 'L0009', 2, 'A');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA002', '50º', 1, 'L0010', 2, 'A');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA002', '50º', 1, 'L0011', 2, 'A');
---MIX B--------------------------------------------------------------
--Análisis Microeconómico
--Derecho Empresario I
--Técnicas Cuantitativas
--Sistemas Administrativos
INSERT INTO ufce_mixes VALUES ('FCE', 'CA002', '50º', 1, 'L0012', 2, 'B');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA002', '50º', 1, 'L0013', 2, 'B');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA002', '50º', 1, 'L0014', 2, 'B');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA002', '50º', 1, 'L0004', 2, 'B');
---3º Año-------------------------------------------------------------
---MIX A--------------------------------------------------------------
--Matemática Financiera
--Recursos Humanos
--Análisis e Interpretación de Estados Contables
--Administración Pública
INSERT INTO ufce_mixes VALUES ('FCE', 'CA002', '50º', 1, 'L0015', 3, 'A');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA002', '50º', 1, 'L2001', 3, 'A');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA002', '50º', 1, 'L0019', 3, 'A');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA002', '50º', 1, 'L0020', 3, 'A');
---MIX B--------------------------------------------------------------
--Derecho Empresario II
--Análisis Macroeconómico
--Marketing
--Logística y Organización Productiva
INSERT INTO ufce_mixes VALUES ('FCE', 'CA002', '50º', 1, 'L0017', 3, 'B');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA002', '50º', 1, 'L0016', 3, 'B');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA002', '50º', 1, 'L2002', 3, 'B');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA002', '50º', 1, 'L0025', 3, 'B');
---4º Año-------------------------------------------------------------
---MIX A--------------------------------------------------------------
--Finanzas Corporativas
--Derecho Empresario III
--Dirección Estratégica I
--Innovación y Desarrollo Regional
INSERT INTO ufce_mixes VALUES ('FCE', 'CA002', '50º', 1, 'L0022', 4, 'A');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA002', '50º', 1, 'L2003', 4, 'A');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA002', '50º', 1, 'L2004', 4, 'A');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA002', '50º', 1, 'L0026', 4, 'A');
---MIX B--------------------------------------------------------------
--Introducción a la Tributación
--Costos para la Toma de Decisiones
--Emprendedorismo y Empresa Familiar
--Gestión Comercial 
INSERT INTO ufce_mixes VALUES ('FCE', 'CA002', '50º', 1, 'L0018', 4, 'B');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA002', '50º', 1, 'L0027', 4, 'B');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA002', '50º', 1, 'L2006', 4, 'B');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA002', '50º', 1, 'L2005', 4, 'B');
---5º Año-------------------------------------------------------------
---MIX A--------------------------------------------------------------
--Metodología de la Investigación
--Dirección Estratégica II
--Práctica Profesional
--Tecnologías de Información
INSERT INTO ufce_mixes VALUES ('FCE', 'CA002', '50º', 1, 'L0023', 5, 'A');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA002', '50º', 1, 'L2007', 5, 'A');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA002', '50º', 1, 'L2008', 5, 'A');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA002', '50º', 1, 'L0021', 5, 'A');


----------CA004	Licenciatura en Economía Empresarial------------------
---1º Año-------------------------------------------------------------
---MIX A--------------------------------------------------------------
--Contabilidad Básica
--Matemática I 
--Administración
INSERT INTO ufce_mixes VALUES ('FCE', 'CA004', '50º', 1, 'L0001', 1, 'A');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA004', '50º', 1, 'L0002', 1, 'A');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA004', '50º', 1, 'L0003', 1, 'A');
---MIX B--------------------------------------------------------------
--Introducción a la Economía 
--Instituciones de Derecho Público
--Estadística
--Historia Económica
INSERT INTO ufce_mixes VALUES ('FCE', 'CA004', '50º', 1, 'L0005', 1, 'B');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA004', '50º', 1, 'L0006', 1, 'B');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA004', '50º', 1, 'L0007', 1, 'B');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA004', '50º', 1, 'L0008', 1, 'B');
---2º Año-------------------------------------------------------------
---MIX A--------------------------------------------------------------
--Matemática II
--Sistemas de Información Gerencial
--Filosofía y Lógica
INSERT INTO ufce_mixes VALUES ('FCE', 'CA004', '50º', 1, 'L0009', 2, 'A');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA004', '50º', 1, 'L0010', 2, 'A');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA004', '50º', 1, 'L0011', 2, 'A');
---MIX B--------------------------------------------------------------
--Análisis Microeconómico
--Derecho Empresario I
--Técnicas Cuantitativas
--Sistemas Administrativos
INSERT INTO ufce_mixes VALUES ('FCE', 'CA004', '50º', 1, 'L0012', 2, 'B');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA004', '50º', 1, 'L0013', 2, 'B');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA004', '50º', 1, 'L0014', 2, 'B');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA004', '50º', 1, 'L0004', 2, 'B');
---3º Año-------------------------------------------------------------
---MIX A--------------------------------------------------------------
--Matemática Financiera
--Administración Pública  
--Análisis e Interpretación de Estados Contables 
--Organización Industrial
INSERT INTO ufce_mixes VALUES ('FCE', 'CA004', '50º', 1, 'L0015', 3, 'A');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA004', '50º', 1, 'L0020', 3, 'A');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA004', '50º', 1, 'L0019', 3, 'A');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA004', '50º', 1, 'L3000', 3, 'A');
---MIX B--------------------------------------------------------------
--Derecho Empresario II
--Análisis Macroeconómico 
--Logística y Organización Productiva 
--Econometría y Modelización
INSERT INTO ufce_mixes VALUES ('FCE', 'CA004', '50º', 1, 'L0017', 3, 'B');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA004', '50º', 1, 'L0016', 3, 'B');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA004', '50º', 1, 'L0025', 3, 'B');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA004', '50º', 1, 'L3001', 3, 'B');
---4º Año-------------------------------------------------------------
---MIX A--------------------------------------------------------------
--Finanzas Corporativas 
--Innovación y Desarrollo Regional 
--Introducción al Análisis de Datos y Datamining
--Tecnologías de Información
INSERT INTO ufce_mixes VALUES ('FCE', 'CA004', '50º', 1, 'L0022', 4, 'A');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA004', '50º', 1, 'L0026', 4, 'A');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA004', '50º', 1, 'L3004', 4, 'A');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA004', '50º', 1, 'L0021', 4, 'A');
---MIX B--------------------------------------------------------------
--Derecho Financiero y de Mercado de Capitales
--Introducción a la Tributación
--Costos para la Toma de Decisiones
--Finanzas de Activos y Mercados Financieros
INSERT INTO ufce_mixes VALUES ('FCE', 'CA004', '50º', 1, 'L3002', 4, 'B');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA004', '50º', 1, 'L0018', 4, 'B');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA004', '50º', 1, 'L0027', 4, 'B');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA004', '50º', 1, 'L0024', 4, 'B');
---5º Año-------------------------------------------------------------
---MIX A--------------------------------------------------------------
--Metodología de la Investigación
--Economía Gerencial
--Práctica Profesional 
--Política Económica
INSERT INTO ufce_mixes VALUES ('FCE', 'CA004', '50º', 1, 'L0023', 5, 'A');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA004', '50º', 1, 'L3005', 5, 'A');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA004', '50º', 1, 'L3006', 5, 'A');
INSERT INTO ufce_mixes VALUES ('FCE', 'CA004', '50º', 1, 'L3003', 5, 'A');

{
SELECT C.nombre, A.plan, A.nombre_materia, A.anio_de_cursada 
	FROM sga_atrib_mat_plan A
	JOIN sga_carreras C ON (C.carrera = A.carrera)
WHERE A.plan LIKE '50%' AND A.obligatoria = 'S'
ORDER BY A.carrera, A.anio_de_cursada, A.nombre_materia;

SELECT * FROM sga_atrib_mat_plan WHERE carrera = 'CA004' AND plan LIKE '50%' AND obligatoria = 'S' AND anio_de_cursada = 5;

SELECT sga_materias.nombre, ufce_mixes.* 
	FROM ufce_mixes
	JOIN sga_materias ON (ufce_mixes.materia = sga_materias.materia)
WHERE ufce_mixes.carrera = 'CA001' 
AND ufce_mixes.anio_de_cursada = 2 AND ufce_mixes.mix = 'B';

SELECT * FROM ufce_mixes;
}