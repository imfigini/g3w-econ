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

            if (info.carrera !== ""){
                $("#formulario_filtro-carrera option[value="+ info.carrera +"]").attr("selected",true);
                $('#formulario_filtro-carrera').val(info.carrera);
            }
            
            if (info.mix !== ""){
                $("#formulario_filtro-mix option[value="+ info.mix +"]").attr("selected",true);
                $('#formulario_filtro-mix').val(info.mix);
            }
            
            buscarPeriodos($('#formulario_filtro-anio_academico').val());
            
			$(id).delegate(".link-js", "click", function() {
                        $(this).find('.toggle').toggleClass(function(){
                            //console.log($(this));
                                if ($(this).is('.icon-chevron-up')) {
                                        return 'icon-chevron-down';
                                } else {
                                        return 'icon-chevron-up';
                                }
                        });
                        $("#"+$(this).attr('data-link')).toggle();
                        return true;
                });
            
            inicio();
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
                            //console.log($elem_periodos);
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
        
        
    function inicio()
    {
		var materias_json = $('#materias').val();
        if (materias_json)
        {
            var materias = JSON.parse(materias_json);
			//console.log(materias);
			var cant = Object.keys(materias).length;
            for(var i=0; i<cant; i++)
            {
                var materia = materias[i];

                var comisiones = materia.COMISIONES;
                var cant_com = Object.keys(comisiones).length;
                for(var j=0; j<cant_com; j++)
                {
                    var comision = comisiones[j];
					var com = comision.COMISION;

					set_div_mensaje(com, 'promo1', comision.FECHAS_SOLICITADAS, comision.EVAL_ASIGNADAS);
					set_div_mensaje(com, 'promo2', comision.FECHAS_SOLICITADAS, comision.EVAL_ASIGNADAS);
					set_div_mensaje(com, 'recup', comision.FECHAS_SOLICITADAS, comision.EVAL_ASIGNADAS);
					set_div_mensaje(com, 'integ', comision.FECHAS_SOLICITADAS, comision.EVAL_ASIGNADAS);
                }
            }
        }
    }
        
});
    

function set_div_mensaje (comision, instancia, fechas_solicitadas, fechas_asignadas)
{
	if (fechas_solicitadas && fechas_solicitadas[instancia.toUpperCase()]) {
		estado = fechas_solicitadas[instancia.toUpperCase()].ESTADO;
	} else {
		estado = null;
	}
	if (fechas_asignadas && fechas_asignadas[instancia.toUpperCase()]) {
		fecha_asignada = fechas_asignadas[instancia.toUpperCase()].FECHA_HORA; 
	} else {
		fecha_asignada = null;
	}
	set_div_mensaje_aceptado(comision, instancia, estado, fecha_asignada);
}

function calendarioVisible(estado, comision, instancia)
{
    //console.log(estado.value);
	var div_date = 'div_date_'+instancia+'_'+comision;
	var div_time = 'div_time_'+instancia+'_'+comision;
    var div_aceptado = 'div_aceptado_'+instancia+'_'+comision;
    switch(estado.value)
    {
        case 'C': 
            setear_calendario_restringido(comision, instancia);
			$('#'+div_date).show();
			$('#'+div_time).hide();
            break;
        case 'R': 
            setear_calendario_abierto(comision, instancia);
			$('#'+div_date).show();
			$('#'+div_time).hide();
            break;
		case 'H': 
            setear_timepicker(comision, instancia);
			$('#'+div_date).hide();
			$('#'+div_time).show();
            break;
        default: 
			$('#'+div_date).hide();
			$('#'+div_time).hide();
	}
	$('#'+div_aceptado).show();
}

function setear_timepicker(comision, instancia)
{
	var timepick = 'timepicker_'+instancia+'_'+comision;
	var dias_clase = $('#dias_clase_'+comision).val();
	dias_clase = JSON.parse(dias_clase);

	var default_time = '08:00';
	if (dias_clase[0] && dias_clase[0].HS_COMIENZO_CLASE) {
		default_time = dias_clase[0].HS_COMIENZO_CLASE;
	}

	$('#'+timepick).timepicker( "destroy" );
    $('#'+timepick).timepicker({
			timeFormat: 'HH:mm',
			interval: 60,
			minTime: '08:00',
			maxTime: '20:00',
			defaultTime: default_time,
			//startTime: '08:00',
			dynamic: false,
			dropdown: true,
			scrollbar: true
		});
}

function setear_calendario_restringido(comision, instancia)
{
	
    var datepick = 'datepicker_'+instancia+'_'+comision;
    var dias_no_validos = $('#dias_no_validos_'+comision).val();
    var dias_clase = $('#dias_clase_'+comision).val();
	setear_calendario(instancia, datepick, dias_clase, dias_no_validos);
}

function setear_calendario_abierto(comision, instancia)
{
    var datepick = 'datepicker_'+instancia+'_'+comision;
    var dias_no_validos = $('#dias_no_validos_'+comision).val();
    var dias_clase = null;

    setear_calendario(instancia, datepick, dias_clase, dias_no_validos);
}

function setear_calendario(instancia, datepick, dias_clase, dias_no_validos)
{
    var inicio_periodo = new Array( new Date ($('#inicio_periodo_'+1).val().replace(/-/g, '\/')),
                                    new Date ($('#inicio_periodo_'+2).val().replace(/-/g, '\/')),
                                    new Date ($('#inicio_periodo_'+3).val().replace(/-/g, '\/')));

    var fin_periodo = new Array(    new Date ($('#fin_periodo_'+1).val().replace(/-/g, '\/')),
                                    new Date ($('#fin_periodo_'+2).val().replace(/-/g, '\/')),
                                    new Date ($('#fin_periodo_'+3).val().replace(/-/g, '\/')));

    switch (instancia)
    {
        case 'promo1':
            get_fechas_and_set_values(datepick, inicio_periodo[0], fin_periodo[0], dias_clase, dias_no_validos);
            break;
        case 'promo2':
            get_fechas_and_set_values(datepick, inicio_periodo[1], fin_periodo[1], dias_clase, dias_no_validos);
            break;
        case 'recup':
            get_fechas_and_set_values(datepick, inicio_periodo[1], fin_periodo[2], dias_clase, dias_no_validos);
            break;
        case 'integ':
            get_fechas_and_set_values(datepick, inicio_periodo[1], fin_periodo[2], dias_clase, dias_no_validos);
            break;
        case 'regu1':
            get_fechas_and_set_values(datepick, inicio_periodo[0], fin_periodo[0], dias_clase, dias_no_validos);
            break;
        case 'recup1':
            get_fechas_and_set_values(datepick, inicio_periodo[1], fin_periodo[1], dias_clase, dias_no_validos);
            break;
        case 'recup2':
            get_fechas_and_set_values(datepick, inicio_periodo[1], fin_periodo[2], dias_clase, dias_no_validos);
            break;
    }
}

function get_fechas_and_set_values(objeto_id, inicio_periodo, fin_periodo, dias_clase, dias_no_validos)
{
    var posibles_fechas;
    if (dias_clase) {
        posibles_fechas = get_posibles_fechas(dias_clase, inicio_periodo, fin_periodo, dias_no_validos);
    }
    else {
        posibles_fechas = get_posibles_fechas_todas(inicio_periodo, fin_periodo, dias_no_validos);
    }
    set_values(objeto_id, posibles_fechas, inicio_periodo, fin_periodo);
}

function set_values(objeto_id, posibles_fechas, inicio_periodo, fin_periodo)
{
    $('#'+objeto_id).datepicker( "destroy" );
    $('#'+objeto_id).datepicker({

            format: 'YYYY-MM-DD',  
            regional: 'es',
            firstDay: 0,

            minDate: inicio_periodo,
            maxDate: fin_periodo,

            beforeShowDay: function (date) {
                        var diaFormateado = date.toISOString().substring(0, 10);
                        var habilitar = (posibles_fechas.includes(diaFormateado));
                        return [habilitar];
                    }
        });
}

function get_posibles_fechas(dias_semana, inicio_periodo, fin_periodo, dias_no_validos)
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
            var es_fecha_valida = fecha_valida(dias_no_validos, dia);
            if (feriados.includes(diaFormateado) == false && es_fecha_valida)
                posibles_fechas += diaFormateado + ',';
        }
        dia.setDate(dia.getDate() + 1);
    }
    return posibles_fechas.substring(0, posibles_fechas.length-1) + ']';
}


function get_posibles_fechas_todas(inicio_periodo, fin_periodo, dias_no_validos)
{
    var feriados = $('#feriados').val();
    var dia = new Date(inicio_periodo);
    var posibles_fechas = '[';
    while (dia <= fin_periodo)
    {
        var diaSemana = dia.getDay();
        if (diaSemana != 0)
        {
            var diaFormateado = dia.toISOString().substring(0, 10);
            var es_fecha_valida = fecha_valida(dias_no_validos, dia);
            if (feriados.includes(diaFormateado) == false && es_fecha_valida)
                posibles_fechas += diaFormateado + ',';
        }
        dia.setDate(dia.getDate() + 1);
    }
    return posibles_fechas.substring(0, posibles_fechas.length-1) + ']';
}

function contiene(dias_semana, dia)
{
	var dias = JSON.parse(dias_semana);
    for (var i in dias) {
        if (dias[i].DIA_SEMANA == dia) {
            return true;
        }
    }
    return false;
}

function fecha_valida(dias_no_validos, fecha)
{
    var fechas_no_validas = JSON.stringify(dias_no_validos);
    if (fechas_no_validas.includes(fecha)) {
		return false;
	}
    return true;
}

function set_div_mensaje_aceptado(comision, inst_nombre, estado, fecha_asignada)
{
	if (!fecha_asignada) {
		return;
	}

	if (!estado) {
		estado = calcular_estado(comision, fecha_asignada);
	}

	var div = $('#div_aceptado_'+inst_nombre+'_'+comision);
	var mensaje = $('#mensaje_estado_'+inst_nombre+'_'+comision);

	switch (estado.trim())
	{
		case 'A':		mensaje.text('Aceptada: '+fecha_asignada);
						mensaje.removeClass('resaltar_azul');
						mensaje.addClass('resaltar_verde');
						div.show();
						break;
		case 'C': 		mensaje.text('Asignada en día de cursada: '+fecha_asignada)
						mensaje.removeClass('resaltar_azul');
						mensaje.addClass('resaltar_verde');
						div.show();
						break;
		case 'R': 		mensaje.text('Asignada en otro día: '+fecha_asignada)
						mensaje.removeClass('resaltar_verde');
						mensaje.addClass('resaltar_azul');
						div.show();
						break;
		case 'AH':		mensaje.text('Aceptada en otro horario: '+fecha_asignada);
						mensaje.removeClass('resaltar_azul');
						mensaje.addClass('resaltar_verde');
						div.show();
						break;
		case 'CH': 		mensaje.text('Asignada en día de cursada y otro horario: '+fecha_asignada)
						mensaje.removeClass('resaltar_azul');
						mensaje.addClass('resaltar_verde');
						div.show();
						break;
		case 'RH': 		mensaje.text('Asignada en otro día y otro horario: '+fecha_asignada)
						mensaje.removeClass('resaltar_verde');
						mensaje.addClass('resaltar_azul');
						div.show();
						break;
		default: 		div.hide();
						break;
	}
}

function calcular_estado(comision, fecha_string)
{
	var dias_clase = $('#dias_clase_'+comision).val();
	var fecha_asign = fecha_string.substring(0,10);
	var fecha_split = fecha_asign.split('-');
	var fecha = new Date(fecha_split[0], parseInt(fecha_split[1])-1, fecha_split[2]);
	var diaSemana = fecha.getDay();

	var estado = '';
	if (contiene(dias_clase, diaSemana)) {
		estado = 'C';
	} else {
		estado = 'R';
	}

	var hora_asign = fecha_string.substr(11,8);
	var hora = get_hora_clase(dias_clase, diaSemana);
	if (hora_asign != hora) {
		estado = estado + 'H';	
	}
	return estado;
}

function grabar_comision(comision)
{
	var promo1 = grabar_instancia_evaluacion(comision, 'promo1', 1);
	var promo2 = grabar_instancia_evaluacion(comision, 'promo2', 2);
	var recup = grabar_instancia_evaluacion(comision, 'recup', 7);
	var integ = grabar_instancia_evaluacion(comision, 'integ', 14);

	var regu1 = grabar_instancia_evaluacion(comision, 'regu1', 21);
	var recup1 = grabar_instancia_evaluacion(comision, 'recup1', 4);
	var recup2 = grabar_instancia_evaluacion(comision, 'recup2', 5);

	var mensaje = armar_mensaje(promo1, promo2, recup, integ, regu1, recup1, recup2);
	kernel.ui.show_mensaje(mensaje);
}

function grabar_instancia_evaluacion(comision, instancia, evaluacion)
{
	var datos = get_datos_instancia_evaluacion(comision, instancia, evaluacion);

	if (datos)
	{
//		console.log(datos);
		var formulario = $('#comision_seleccionada_'+comision);
		var resultado = '';
		kernel.ajax.call(formulario.attr('action'), {
				async: false,
				type: "post",
				dataType: 'json',
				data: datos,
				success: function(data) { 
					$('#aceptar_'+instancia+'_'+comision).val('P');
					$('#div_date_'+instancia+'_'+comision).hide();
					$('#div_time_'+instancia+'_'+comision).hide();

					if (data.cont[0].success == -1) {
							alert(data.cont[0].mensaje);
					} else {
						var estado_new = data.cont[0].estado;
						var fecha_hora_asign = data.cont[0].fecha_hora;
						set_div_mensaje_aceptado(comision, instancia, estado_new, fecha_hora_asign);
						resultado = data.cont[0].mensaje;
					}
				}, 
				error: function(response) {
					console.log('Falló');
					console.log(response);
					kernel.ui.show_mensaje(response.msj, {tipo: 'alert-error'});
				}
		});
		return resultado;
	}

}

function get_datos_instancia_evaluacion(comision, instancia, evaluacion)
{
	var opcion = $('#aceptar_'+instancia+'_'+comision).val();
	if (!opcion || opcion == 'P') {
		return null;
	}
	console.log('opcion: '+opcion);
	var fecha_hora = $('#fecha_hora_solic_'+instancia+'_'+comision).val();
	console.log(fecha_hora);
	var estado;

	if (opcion == 'A') {
		estado = 'A';
	}
	if (opcion == 'C' || opcion == 'R')
	{
		var dias_clase = $('#dias_clase_'+comision).val();

		var datepicker = $('#datepicker_'+instancia+'_'+comision).val();
		var fecha_slpit = datepicker.split('/');
		var fecha_string = fecha_slpit[2] + '-' + fecha_slpit[1] + '-' + fecha_slpit[0];
		var fecha = new Date(fecha_slpit[2], parseInt(fecha_slpit[1])-1, fecha_slpit[0]);
		var diaSemana = fecha.getDay();

		estado = 'R';
		if (contiene(dias_clase, diaSemana))
		{
			if (fecha_hora.substring(0,10) == fecha_string) {
				estado = 'A';
			} else {
				estado = 'C';
			}
		}
		var hora = get_hora_clase(dias_clase, diaSemana);
		fecha_hora = fecha_string + ' ' + hora;
	}
	if (opcion == 'H')
	{
		var timepicker = $('#timepicker_'+instancia+'_'+comision).val();
		fecha_hora = timepicker + ':00'; //solo envío la hora
		estado = 'H';
	}
	
	return {comision: comision, evaluacion: evaluacion, fecha_hora: fecha_hora, estado: estado};
}

function get_hora_clase(dias_clase, diaSemana)
{
	var dias = JSON.parse(dias_clase);
    for (var i in dias) {
        if (dias[i].DIA_SEMANA == diaSemana) {
            return dias[i].HS_COMIENZO_CLASE;
        }
    }
    return dias[0].HS_COMIENZO_CLASE;
}

function armar_mensaje(promo1, promo2, recup, integ, regu1, recup1, recup2)
{
	var mensaje = '';
	if (promo1)	{
		mensaje += promo1;
	}
	if (promo2)	{
		mensaje += promo2;
	}
	if (recup)	{
		mensaje += recup;
	}
	if (integ)	{
		mensaje += integ;
	}
	if (regu1)	{
		mensaje += regu1;
	}
	if (recup1)	{
		mensaje += recup1;
	}
	if (recup2)	{
		mensaje += recup2;
	}
	return mensaje;
}
