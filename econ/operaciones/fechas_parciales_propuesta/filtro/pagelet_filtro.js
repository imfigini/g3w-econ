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
            
            //Para que despliegue u oculte la informaci�n de las comisiones de cada materia. 
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
            
			setear_calendarios();  
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
        
    function setear_calendarios()
    {
        var materias_json = $('#materias').val();
        if (materias_json)
        {
            var materias = JSON.parse(materias_json);
            
            var inicio_periodo = new Array( new Date ($('#inicio_periodo_'+1).val().replace(/-/g, '\/')),
                                            new Date ($('#inicio_periodo_'+2).val().replace(/-/g, '\/')),
                                            new Date ($('#inicio_periodo_'+3).val().replace(/-/g, '\/')));
			
			var fin_periodo = new Array(    new Date ($('#fin_periodo_'+1).val().replace(/-/g, '\/')),
                                            new Date ($('#fin_periodo_'+2).val().replace(/-/g, '\/')),
                                            new Date ($('#fin_periodo_'+3).val().replace(/-/g, '\/')));

            var cant = Object.keys(materias).length;
            for(var i=0; i<cant; i++)
            {
                var materia = materias[i];
				set_values_materias(materia, inicio_periodo, fin_periodo);

				//Carga el detalle de las comisiones de abajo
				if (materia.COMISIONES) {
                    set_values_comisiones(materia);
                }
            }
        }
    }

    function set_values_materias(materia, inicio_periodo, fin_periodo)
    {
        var m = materia.MATERIA;
		var dias_semana = materia.DIAS;
		var dias_ocupados = materia.FECHAS_OCUPADAS;
		var dias_no_validos = materia.FECHAS_NO_VALIDAS;
		
		var dp_parcial1 = 'datepicker_materia_parcial1_'+m;
		var posibles_fechas_parcial1 = get_posibles_fechas(dias_semana, inicio_periodo[0], fin_periodo[0], dias_ocupados, dias_no_validos);
        set_values(dp_parcial1, inicio_periodo[0], fin_periodo[0], posibles_fechas_parcial1);

        var dp_parcial2 = 'datepicker_materia_parcial2_'+m;
        var posibles_fechas_parcial2 = get_posibles_fechas(dias_semana, inicio_periodo[1], fin_periodo[1], dias_ocupados, dias_no_validos);
        set_values(dp_parcial2, inicio_periodo[1], fin_periodo[1], posibles_fechas_parcial2);

        var dp_integ = 'datepicker_materia_integ_'+m;
        // console.log(inicio_periodo);
        // console.log(fin_periodo);
        var posibles_fechas_integ = get_posibles_fechas(dias_semana, inicio_periodo[1], fin_periodo[2], dias_ocupados, dias_no_validos);
		set_values(dp_integ, inicio_periodo[1], fin_periodo[2], posibles_fechas_integ);
    }
    
    function set_values_comisiones(materia)
    {
        var comisiones = materia.COMISIONES;
        var cant_com = Object.keys(comisiones).length;
        for(var j=0; j<cant_com; j++)
        {
			var comision = comisiones[j];
			var fechas_evaluacion = comision.FECHAS_EVAL;
			var parcial1 = $('#parcial1_'+comision.COMISION);
			var parcial2 = $('#parcial2_'+comision.COMISION);
			var recup = $('#recup_'+comision.COMISION);
			var integ = $('#integ_'+comision.COMISION);

			var cant_eval = Object.keys(fechas_evaluacion).length;
			for(var i=0; i<cant_eval; i++)
			{
				if (fechas_evaluacion[i]['EVAL_NOMBRE'])
				{
					estado = (fechas_evaluacion[i]['ESTADO']).trim();
					fecha = fechas_evaluacion[i]['FECHA'];
					switch( (fechas_evaluacion[i]['EVAL_NOMBRE']).trim() )
					{
						case 'PARCIAL1': 	set_value_instancia(parcial1, fecha, estado)
											break;
						case 'PARCIAL2': 	set_value_instancia(parcial2, fecha, estado)
											break;
						case 'RECUP': 	set_value_instancia(recup, fecha, estado)
										break;
						case 'INTEG': 	set_value_instancia(integ, fecha, estado)
										break;
					}
				}
			}
		
        }
	}
	
	function set_value_instancia(instancia, fecha, estado)
	{
		instancia.val(fecha);
		if ( estado.trim() == 'P' )
		{
			instancia.removeClass('no_editable_confirmada');
			instancia.addClass('no_editable_pendiente');
		}
		else
		{
			instancia.removeClass('no_editable_pendiente');
			instancia.addClass('no_editable_confirmada');
		}
	}

    function get_posibles_fechas(dias_semana, inicio_periodo, fin_periodo, dias_ocupados, dias_no_validos)
    {
		var feriados = $('#feriados').val();
        var dia = new Date(inicio_periodo);
        var posibles_fechas = '[';
        while (dia <= fin_periodo)
        {
			var diaSemana = dia.getDay();
            if (contiene(dias_semana, diaSemana))
            {
				var diaFormateado = dia.toISOString().substring(0, 10);
                var es_fecha_libre = fecha_disponible_mix(dias_ocupados, dia);
				var es_fecha_valida = fecha_disponible(dias_no_validos, dia);

				if (feriados.includes(diaFormateado) == false && es_fecha_libre && es_fecha_valida)
					posibles_fechas += diaFormateado + ',';
            }
            dia.setDate(dia.getDate() + 1);
        }
        return posibles_fechas.substring(0, posibles_fechas.length-1) + ']';
    }
    
    //  Verifica que ese mismo d�a no este ocupado por otra materia del mismo mix, y tampoco el d�a anterior o posterior consecutivo. 
    function fecha_disponible_mix(fechas_no_disponibles, fecha)
    {
        var cant = Object.keys(fechas_no_disponibles).length;
        var fecha_formateada = fecha.toISOString().substring(0, 10);

        var dia_anterior = new Date(fecha);
        dia_anterior.setDate(dia_anterior.getDate() - 1);
        var dia_anterior_formateado = dia_anterior.toISOString().substring(0, 10);
        
        var dia_siguiente = new Date(fecha);
        dia_siguiente.setDate(dia_siguiente.getDate() + 1);
        var dia_siguiente_formateado = dia_siguiente.toISOString().substring(0, 10);

		for (var i=0; i<cant; i++)
        {
            if ( fechas_no_disponibles[i]['FECHA'] == fecha_formateada
				|| fechas_no_disponibles[i]['FECHA'] == dia_anterior_formateado
				|| fechas_no_disponibles[i]['FECHA'] == dia_siguiente_formateado )
			{
				return false;
			}
        };
        return true;
	}
	
	//  Verifica que ese mismo d�a no este invalidado
    function fecha_disponible(fechas_no_disponibles, fecha)
    {
        var cant = Object.keys(fechas_no_disponibles).length;
        var fecha_formateada = fecha.toISOString().substring(0, 10);

		for (var i=0; i<cant; i++) {
            if ( fechas_no_disponibles[i]['FECHA'] == fecha_formateada ) {
				return false;
			}
        };
        return true;
    }

    function contiene(dias_semana, dia)
    {
        var cant = Object.keys(dias_semana).length;
        for (var i=0; i<cant; i++)
        {
            if (dias_semana[i].DIA_SEMANA == dia)
            {
                return true;
            }
        }
        return false;
    }
    
    
    function set_values(objeto_id, inicio_periodo, fin_periodo, posibles_fechas)
    {
        $('#'+objeto_id).attr( 'readOnly' , 'true' ).datepicker({
 
                dateFormat: 'yy-mm-dd',  
                regional: 'es',
                firstDay: 0,
                minDate: inicio_periodo,
                maxDate: fin_periodo,

                beforeShowDay: function (date) {
                            var posiblesDias = posibles_fechas;
                            var diaFormateado = date.toISOString().substring(0, 10);
                            var habilitar =   (posiblesDias.includes(diaFormateado));
//                            console.log(habilitar);
                            return [habilitar];
                        }
            });
    }

});


//Verifica la cronolog�a de las fechas
function verifica_fechas_materia(componente)
{
    var x = componente.id.split('_');
    var comision = x[3];
    
	var parcial1 = $("#"+'datepicker_materia_parcial1_'+comision).val();
	var parcial2 = $("#"+'datepicker_materia_parcial2_'+comision).val();
    var integ = $("#"+'datepicker_materia_integ_'+comision).val();

	verifica_fechas(componente, parcial1, parcial2, integ);
}
    

function verifica_fechas(componente, parcial1, parcial2, integ)
{
  
    if (!fechas_en_orden(parcial1, parcial2))
    {
        alert ('La fecha para el 1er Parcial debe ser anterior a la del 2� Parcial');
        componente.value = null;
    }

	if (!fechas_en_orden(parcial2, integ))
    {
        alert ('La fecha para el 2do Parcial debe ser anterior a la del Integrador / Recuperatorio Global');
        componente.value = null;
	}
	
	if (!fechas_en_orden(parcial1, integ))
    {
        alert ('La fecha para el 1er Parcial debe ser anterior a la del Integrador / Recuperatorio Global');
        componente.value = null;
    }
}

function fechas_en_orden(fecha1, fecha2)
{
    if ((fecha1) && (fecha2))
    {
        if (fecha1 >= fecha2)
        {
            return false;
        }
    }
    return true;
}

