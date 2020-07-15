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
        
                    if (info.anio_academico_hash !== ""){
                        setear_solicitud_fechas();
                        setear_calendarios();
                        setear_fecha_ctr_correlat();
                    }     
                }
                
    };
    
    function buscarPeriodos(anio_academico)
    {
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

    function set_values(objeto_id, fechaInicio, fechaFin, fechaMin, fechaMax, feriados)
    {
        //console.log(feriados);
        $('#'+objeto_id).daterangepicker({
                opens: 'right',
                drops: 'up',
                format: 'YYYY-MM-DD',  
                showDropdowns: true,
                autoUpdateInput: true,
                autoApply: true,
                locale: {
                    "format": "DD/MM/YYYY",
                    "separator": " - ",
                    "applyLabel": "Aplicar",
                    "cancelLabel": "Cancelar",
                    "fromLabel": "Desde",
                    "toLabel": "Hasta",
                    "customRangeLabel": "Custom",
                    "weekLabel": "W",
                    "daysOfWeek": [
                        "Do",
                        "Lu",
                        "Ma",
                        "Mi",
                        "Ju",
                        "Vr",
                        "Sa"
                    ],
                    "monthNames": [
                        "Enero",
                        "Febrero",
                        "Marzo",
                        "Abril",
                        "Mayo",
                        "Junio",
                        "Julio",
                        "Agosto",
                        "Septiembre",
                        "Octubre",
                        "Noviembre",
                        "Diciembre"
                    ],
                    firstDay: 0
                },
                
                minDate: new Date(fechaMin),
                maxDate: new Date(fechaMax),
                startDate: new Date(fechaInicio),
                endDate: new Date(fechaFin),
                isInvalidDate: function (momento) {
				var dia = momento.toDate();
				var diaSemana = dia.getDay();
				var formattedDate = moment(dia).format('YYYY-MM-DD');                            
				var bloquear =  (diaSemana == 0) ||
							(diaSemana == 6) ||
							(feriados.includes(formattedDate));

				return bloquear;
			}
        });
    }

    function setear_calendarios()
    {
        var i;
        var fecha_inicio;
        var fecha_fin;
		var feriados = armar_cadena_de_feriados();
		// var cant_periodos = $('#cant_periodos').val();
		// console.log(cant_periodos);
        for (i = 1; i <= 4; i++) 
        {
            fecha_inicio = new Date();
            fecha_fin = new Date();
            if ($('#fecha_inicio_'+i).length && $('#fecha_fin_'+i).length)
            {
                fecha_inicio = new Date ($('#fecha_inicio_'+i).val().replace(/-/g, '\/'));
                fecha_fin = new Date ($('#fecha_fin_'+i).val().replace(/-/g, '\/'));
            }
            else
            {
                if ($('#lectivo_fin'))
                {
                    fecha_inicio = new Date ($('#lectivo_fin').val().replace(/-/g, '\/'));
                    fecha_inicio.setDate(fecha_inicio.getDate() - 90/i);
                    fecha_fin = fecha_inicio;
                }
            }

            if ($('#daterange_'+i))
                set_values('daterange_'+i, fecha_inicio, fecha_fin, $('#lectivo_inicio').val(), $('#lectivo_fin').val(), feriados);
        }
    }
   
    function armar_cadena_de_feriados()
    {
        var i; 
        var cant = $('#cant_feriados').val();
        var feriados = '[';
        if (cant > 0)
        {
            feriados += "'"+$('#feriado_0').val()+"'";
        }
        for (i = 1; i< $('#cant_feriados').val(); i++)
        {
            feriados += ",'" + $('#feriado_'+i).val() + "'";
        }
        feriados += ']';
        return feriados;
    }

    function setear_solicitud_fechas()
    {
        var fecha_inicio;
        var fecha_fin;
        var feriados = armar_cadena_de_feriados();

        fecha_inicio = new Date();
        fecha_fin = new Date();
        if ($('#fecha_inicio_solicitud_fechas').length && $('#fecha_fin_solicitud_fechas').length)
        {
            fecha_inicio = new Date ($('#fecha_inicio_solicitud_fechas').val().replace(/-/g, '\/'));
            fecha_fin = new Date ($('#fecha_fin_solicitud_fechas').val().replace(/-/g, '\/'));
        }

        if ($('#daterange_solicitud_fechas'))
        {
            set_values('daterange_solicitud_fechas', fecha_inicio, fecha_fin, $('#lectivo_inicio').val(), $('#lectivo_fin').val(), feriados);
        }
    }

    function setear_fecha_ctr_correlat()
    {
        var feriados = armar_cadena_de_feriados();

        var fecha_inicio = new Date();
        var fecha_fin = new Date();
        if ($('#lectivo_inicio').length && $('#lectivo_fin').length)
        {
            fecha_inicio = new Date ($('#lectivo_inicio').val().replace(/-/g, '\/'));
            fecha_fin = new Date ($('#lectivo_fin').val().replace(/-/g, '\/'));
        }

        if ($('#fecha_ctr_correlat').val()) {
            var fecha_establecida = new Date ($('#fecha_ctr_correlat').val().replace(/-/g, '\/'));
            console.log(fecha_establecida);
        }
        else {
            var fecha_establecida = null;
        }

        $('#datepicker_ctr_correlat').datepicker({
                
            dateFormat: 'dd/mm/yy',  
            regional: 'es',
            minDate: fecha_inicio,
            maxDate: fecha_fin,
            firstDay: 0,
            setDate: fecha_establecida,

            beforeShowDay: function (date) {
                if (date.getDay() == 0) {
                    return false;
                }
                var dia = date.toISOString().substring(0, 10);
                if (feriados.includes(dia)) {
                    return false;
                }
                return dia;
            }
        }).datepicker("setDate", fecha_establecida);
    }


});
