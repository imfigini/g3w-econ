kernel.renderer.registrar_pagelet('filtro', function (info) {
	var id = '#' + info.id;

	return {
        onload: function () {
			$('#boton_buscar').on('click', function () {
                var $filtro  = $('#formulario_filtro');
                var action  = $filtro.attr('action');
                
				guarani.cambiar_op({
					href: action + '?' + $filtro.serialize()
				});
				return false;
			});
			
            $('#formulario_filtro-anio_academico').change(function(){
				buscarPeriodos($(this).val());
			});
			
			$('#formulario_filtro-periodo').change(function(){
				buscarMaterias($('#formulario_filtro-anio_academico').val(), $(this).val());
			});

            if (info.anio_academico_hash !== ""){
                $("#formulario_filtro-anio_academico option[value="+ info.anio_academico_hash +"]").attr("selected",true);
                $('#formulario_filtro-anio_academico').val(info.anio_academico_hash);
            }
			
			if (info.calidad !== ""){
                $("#formulario_filtro-calidad option[value="+ info.calidad +"]").attr("selected",true);
                $('#formulario_filtro-calidad').val(info.calidad);
			}

			if (info.materia !== ""){
                $("#formulario_filtro-materia option[value="+ info.materia +"]").attr("selected",true);
                $('#formulario_filtro-materia').val(info.materia);
			}
			buscarPeriodos($('#formulario_filtro-anio_academico').val());
			buscarMaterias(info.anio_academico_hash, info.periodo_hash);
		 
		 
			if (info.anio_academico_hash !== "" && info.periodo_hash !== ""){
				document.getElementById("btn_guardar").addEventListener("click", function(e) {
					//e.preventDefault();
					var anio_academico_hash = $('#anio_academico_hash').val();
					var periodo_hash = $('#periodo_hash').val();
					var checkboxs = $('[id*="checkbox_"]');
					var cant = checkboxs.length;
					var mensaje = '';
					for (var i=0; i<cant; i++)
					{
						var seleccionado = checkboxs[i].checked;
						if (seleccionado) {
							var id = checkboxs[i].id;
							var elems = id.split('_');
							var legajo = elems[1];
							var carrera = elems[2];
							var materia = elems[3];
							var comision = elems[4];
							var calidad_asignar = elems[5];
							$.ajax({
								async: false,
								url: info.url_grabar,
								dataType: 'json',
								data: {	anio_academico: anio_academico_hash, periodo: periodo_hash,
										legajo: legajo, carrera: carrera, materia: materia, comision: comision, calidad_asignar: calidad_asignar},
								type: 'get',
								success: function(data) {
									console.log(legajo);
									console.log(data.cont['resultado']);
									console.log(data.cont['mensaje']);
									kernel.ui.show_mensaje(data.cont['mensaje']+': '+legajo, {tipo: 'alert-info'});
									mensaje += '* '+data.cont['mensaje']+'\n\n';
								},
								error: function(response) {
									console.log('Fallo');
									console.log(response);
									kernel.ui.show_mensaje(response.msj, {tipo: 'alert-error'});
								},
							});
						}
					}
					alert(mensaje);
					location.reload();
				});
			}
		}
	};
	

	function buscarPeriodos(anio_academico){
		$.ajax({
			url: info.url_buscar_periodos,
			dataType: 'json',
			data: {anio_academico: anio_academico},
			type: 'get',
			success: function(data) {
				var $elem_periodos = $('#formulario_filtro-periodo');
				$elem_periodos.children().remove();
				$elem_periodos.append(
					$('<option></option>').val('').html('-- Seleccione --')
				);
				$.each(data, function(key, value) {
					
					if (value['ID'] === info.periodo_hash){
						$elem_periodos.append($('<option selected="selected"></option>').val(value['ID']).html(value['DESC']));
					} else {
						$elem_periodos.append($('<option></option>').val(value['ID']).html(value['DESC']));
					}
				});
			}
		});
		
	}

	function buscarMaterias(anio_academico, periodo){
		$.ajax({
			url: info.url_buscar_materias,
			dataType: 'json',
			data: {anio_academico: anio_academico, periodo: periodo},
			type: 'get',
			success: function(data) {
				var $elem_materias = $('#formulario_filtro-materia');
				$elem_materias.children().remove();
				$elem_materias.append(
					$('<option></option>').val('').html('-- Seleccione --')
				);
				$.each(data, function(key, value) {
					var $nombre_materia = value['NOMBRE_MATERIA'] + ' (' + value['MATERIA'] + ')';
					if (value['MATERIA'] === info.materia){
						$elem_materias.append($('<option selected="selected"></option>').val(value['MATERIA']).html($nombre_materia));
					} else {
						$elem_materias.append($('<option></option>').val(value['MATERIA']).html($nombre_materia));
					}
				});
			}
		});
	}
});

