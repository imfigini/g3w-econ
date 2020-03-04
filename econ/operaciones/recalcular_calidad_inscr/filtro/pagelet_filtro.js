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
            
            if (info.anio_academico_hash !== ""){
                $("#formulario_filtro-anio_academico option[value="+ info.anio_academico_hash +"]").attr("selected",true);
                $('#formulario_filtro-anio_academico').val(info.anio_academico_hash);
            }
			
			if (info.calidad !== ""){
                $("#formulario_filtro-calidad option[value="+ info.calidad +"]").attr("selected",true);
                $('#formulario_filtro-calidad').val(info.calidad);
			}

            buscarPeriodos($('#formulario_filtro-anio_academico').val());
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
});

// function grabar_cambio_calidad()
// {
// 	var alumnos = $('[name*="alumno_"]');
// 	console.log(alumnos);
// // 	var datos = get_datos_instancia_evaluacion(comision, instancia, evaluacion);

// // 	if (datos)
// // 	{
// // //		console.log(datos);
// // 		var formulario = $('#comision_seleccionada_'+comision);
// // 		var resultado = '';
// // 		kernel.ajax.call(formulario.attr('action'), {
// // 				async: false,
// // 				type: "post",
// // 				dataType: 'json',
// // 				data: datos,
// // 				success: function(data) { 
// // 					$('#aceptar_'+instancia+'_'+comision).val('P');
// // 					$('#div_date_'+instancia+'_'+comision).hide();
// // 					$('#div_time_'+instancia+'_'+comision).hide();

// // 					if (data.cont[0].success == -1) {
// // 							alert(data.cont[0].mensaje);
// // 					} else {
// // 						var estado_new = data.cont[0].estado;
// // 						var fecha_hora_asign = data.cont[0].fecha_hora;
// // 						set_div_mensaje_aceptado(comision, instancia, estado_new, fecha_hora_asign);
// // 						resultado = data.cont[0].mensaje;
// // 					}
// // 				}, 
// // 				error: function(response) {
// // 					console.log('Falló');
// // 					console.log(response);
// // 					kernel.ui.show_mensaje(response.msj, {tipo: 'alert-error'});
// // 				}
// // 		});
// // 		return resultado;
// //	}

// }
