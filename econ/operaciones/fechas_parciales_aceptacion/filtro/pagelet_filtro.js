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
                            console.log($(this));
                                if ($(this).is('.icon-chevron-up')) {
                                        return 'icon-chevron-down';
                                } else {
                                        return 'icon-chevron-up';
                                }
                        });
                        $("#"+$(this).attr('data-link')).toggle();
                        return true;
                });
            
    
            //setear_calendarios();  
            inicio();
            //set_values('datepicker_promo1_6879', '2018-01-01', '2018-12-31');
            
        }
    };
    
    
    function buscarPeriodos(anio_academico){
            //console.log(anio_academico.valueOf());    
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
                                    $('<option></option>').val('').html(info.mensaje_seleccione)
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
                    if (comision.ESCALA == 'P  ' || comision.ESCALA == 'PyR')
                    {
                        setear_estado('aceptar_promo1_'+com, comision.EVAL_PROMO1);
                        setear_estado('aceptar_promo2_'+com, comision.EVAL_PROMO2);
                        if (materia.CICLO == 'F' || materia.CICLO == 'FyP' )
                        {
                            setear_estado('aceptar_recup_'+com, comision.EVAL_RECUP);
                        }
                        setear_estado('aceptar_integ_'+com, comision.EVAL_INTEG);
                    }

                    if (comision.ESCALA == 'R  ' || comision.ESCALA == 'PyR')
                    {
                        setear_estado('aceptar_regu1_'+com, comision.EVAL_REGU1);
                        setear_estado('aceptar_recup1_'+com, comision.EVAL_RECUP1);
                        setear_estado('aceptar_recup2_'+com, comision.EVAL_RECUP2);
                    }
                }
            }
        }
    }

    function setear_estado(boton, eval)
    {
        var estado = 'P'
        if (eval.hasOwnProperty("ESTADO"))
        {
             estado = eval.ESTADO;
        }                            
        $('#'+boton).val(estado);
    }
        
});
    

function calendarioVisible(estado, comision, instancia)
{
//    console.log(estado.value);
    var div = 'div_'+instancia+'_'+comision;
    switch(estado.value)
    {
        case 'R': 
            $('#'+div).show();
            setear_calendario_restringido(comision, instancia);
            break;
        case 'N': 
            $('#'+div).show();
            //setear_calendario_abierto(comision, instancia);
            break;
        default: 
            $('#'+div).hide();
    }
}

function setear_calendario_restringido(comision, instancia)
{
    var datepick = 'datepicker_'+instancia+'_'+comision;
    var dias_no_validos = $('#dias_no_validos_'+comision).val();
    var dias_clase = $('#dias_clase_'+comision).val();
//    console.log(datepick);
//    console.log(dias_no_validos);
//    console.log(escala);
//    console.log(dias_clase);

    var inicio_periodo = new Array( new Date ($('#inicio_periodo_'+1).val().replace(/-/g, '\/')),
                                    new Date ($('#inicio_periodo_'+2).val().replace(/-/g, '\/')),
                                    new Date ($('#inicio_periodo_'+3).val().replace(/-/g, '\/')));

    var fin_periodo = new Array(    new Date ($('#fin_periodo_'+1).val().replace(/-/g, '\/')),
                                    new Date ($('#fin_periodo_'+2).val().replace(/-/g, '\/')),
                                    new Date ($('#fin_periodo_'+3).val().replace(/-/g, '\/')));
//    console.log(inicio_periodo);
//    console.log(fin_periodo);

    switch (instancia)
    {
        case 'promo1':
            set_values(datepick, inicio_periodo[0], fin_periodo[0], dias_clase, dias_no_validos);
            break;
        case 'promo2':
            set_values(datepick, inicio_periodo[1], fin_periodo[1], dias_clase, dias_no_validos);
            break;
        case 'recup':
            set_values(datepick, inicio_periodo[1], fin_periodo[2], dias_clase, dias_no_validos);
            break;
        case 'integ':
            set_values(datepick, inicio_periodo[1], fin_periodo[2], dias_clase, dias_no_validos);
            break;
    }
}

function set_values(objeto_id, inicio_periodo, fin_periodo, dias_clase, dias_no_validos)
{
//    console.log(inicio_periodo);
//    console.log(fin_periodo);
    var posibles_fechas = get_posibles_fechas(dias_clase, inicio_periodo, fin_periodo, dias_no_validos);

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

function contiene(dias_semana, dia)
{
    var dias = JSON.parse(dias_semana);
    for (var i in dias)
    {
        if (dias[i].DIA_SEMANA == dia)
        {
            return true;
        }
    }
    return false;
}

function fecha_valida(dias_no_validos, fecha)
{
    var fechas_no_validas = JSON.stringify(dias_no_validos);
    if (fechas_no_validas.includes(fecha))
        return false;
    return true;
}