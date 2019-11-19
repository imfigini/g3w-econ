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
            
            buscarPeriodos($('#formulario_filtro-anio_academico').val());
            
            //Para que despliegue u oculte la información de las comisiones de cada materia. 
            $(id).delegate(".link-js", "click", function() {
                        $(this).find('.toggle').toggleClass(function(){
                                if ($(this).is('.icon-chevron-up')) {
                                        return 'icon-chevron-down';
                                } else {
                                        return 'icon-chevron-up';
                                }
                        });
                        $("#"+$(this).attr('data-link')).toggle();
                        return false;
                });
                                
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

function actualiza_porcentaje_con_integrador(porc)
{
	x = porc.id.split('_');
	indice = x[3];
	A = $("#"+'porc_parciales_P_'+indice).val();
	B = $("#"+'porc_integrador_P_'+indice).val();
	total = 100-A-B;
	$("#"+'porc_trabajos_P_'+indice).val(total);
	C = $("#"+'porc_trabajos_P_'+indice).val();
	if (C < 0) {
		alert("No se puede asignar un número negativo. Verifique los porcentajes ingresados.");
	}
}

function actualiza_porcentaje_regular(porc)
{
	x = porc.id.split('_');
	console.log(x);
	indice = x[3];
	A = $("#"+'porc_parciales_R_'+indice).val();
	total = 100-A;
	$("#"+'porc_trabajos_R_'+indice).val(total);
	C = $("#"+'porc_trabajos_R_'+indice).val();
	if (C < 0) {
		alert("No se puede asignar un número negativo. Verifique los porcentajes ingresados.");
	}
}

function actualiza_porcentaje_prom_directa(porc)
{
	x = porc.id.split('_');
	console.log(x);
	indice = x[3];
	A = $("#"+'porc_parciales_D_'+indice).val();
	total = 100-A;
	$("#"+'porc_trabajos_D_'+indice).val(total);
	C = $("#"+'porc_trabajos_D_'+indice).val();
	if (C < 0) {
		alert("No se puede asignar un número negativo. Verifique los porcentajes ingresados.");
	}
}

function validar_datos(porc)
{
	x = porc.id.split('_');
	indice = x[2];
	CP = parseInt ( $("#"+'porc_trabajos_P_'+indice).val() );
	CR = parseInt ( $("#"+'porc_trabajos_R_'+indice).val() );
	if (CP < 0 || CR < 0)
	{
		alert("Los trabajos prácticos no pueden tener una ponderación negativa. Verifique los porcentajes ingresados.");
		return false;
	}

	return tiene_todos_los_valores(porc);
}

function tiene_todos_los_valores(porc)
{
	x = porc.id.split('_');
	indice = x[2];
	PP = parseInt ( $("#"+'porc_parciales_P_'+indice).val() );
	IP = parseInt ( $("#"+'porc_integrador_P_'+indice).val() );
	PR = parseInt ( $("#"+'porc_parciales_R_'+indice).val() );
	if (!PP)
	{
		alert("Debe asignar al menos un valor para la ponderación de Parciales.");
		return false;
	}
	if (!IP)
	{
		alert("Debe asignar al menos un valor para la ponderación de Integrador.");
		return false;
	}
	if (!PR)
	{
		alert("Debe asignar al menos un valor para la ponderación de Parciales (sin instancia de integrador).");
		return false;
	}
	return true;
}
