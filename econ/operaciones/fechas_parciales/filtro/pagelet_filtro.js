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
                        return false;
                });
            
            //setear_calendarios();  
            
            iniciar();
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
        
        
    function iniciar()
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
//            console.log(inicio_periodo);
//            console.log(fin_periodo);

            var cant = Object.keys(materias).length;
            for(var i=0; i<cant; i++)
            {
                var materia = materias[i];
                var dias_no_disponibles = materia.FECHAS_OCUPADAS;
                
                if (materia.DIAS_PROMO)
                {
                    set_values_materias_promo(materia, inicio_periodo, fin_periodo, dias_no_disponibles);
                }
                if (materia.DIAS_REGU)
                {
                    set_values_materias_regu(materia, inicio_periodo, fin_periodo, dias_no_disponibles);
                }
                if (materia.COMISIONES)
                {
                    set_values_comisiones(materia, inicio_periodo, fin_periodo, dias_no_disponibles);
                }
            }
        }
    }

    function set_values_materias_promo(materia, inicio_periodo, fin_periodo, dias_no_disponibles)
    {
        var m = materia.MATERIA;
        var dias_semana = materia.DIAS_PROMO;

        var dp_promo1 = 'datepicker_materia_promo1_'+m;
        var posibles_fechas_promo1 = get_posibles_fechas(dias_semana, inicio_periodo[0], fin_periodo[0], dias_no_disponibles, null);
        set_values(dp_promo1, inicio_periodo[0], fin_periodo[0], posibles_fechas_promo1);

        var dp_promo2 = 'datepicker_materia_promo2_'+m;
        var posibles_fechas_promo2 = get_posibles_fechas(dias_semana, inicio_periodo[1], fin_periodo[1], dias_no_disponibles, null);
        set_values(dp_promo2, inicio_periodo[1], fin_periodo[1], posibles_fechas_promo2);

        if (materia.CICLO == 'F' ||  materia.CICLO == 'FyP')
        {
            var dp_recup = 'datepicker_materia_promo_recup_'+m;
            var posibles_fechas_recup = get_posibles_fechas(dias_semana, inicio_periodo[1], fin_periodo[1], dias_no_disponibles, null);
            set_values(dp_recup, inicio_periodo[1], fin_periodo[1], posibles_fechas_recup);

            var dp_integ = 'datepicker_materia_promo_integ_'+m;
            var posibles_fechas_integ = get_posibles_fechas(dias_semana, inicio_periodo[2], fin_periodo[2], dias_no_disponibles, null);
            set_values(dp_integ, inicio_periodo[2], fin_periodo[2], posibles_fechas_integ);
        }
        else
        {
            var dp_integ = 'datepicker_materia_promo_integ_'+m;
            var posibles_fechas_integ = get_posibles_fechas(dias_semana, inicio_periodo[1], fin_periodo[2], dias_no_disponibles, null);
            set_values(dp_integ, inicio_periodo[1], fin_periodo[2], posibles_fechas_integ);
        }
    }
    
    function set_values_materias_regu(materia, inicio_periodo, fin_periodo, dias_no_disponibles)    
    {
        var m = materia.MATERIA;
        var dias_semana = materia.DIAS_REGU;

        var dp_regu1 = 'datepicker_materia_regu1_'+m;
        if (materia.CICLO == 'F' || materia.CICLO == 'FyP' )
        {
            var posibles_fechas_regu1 = get_posibles_fechas(dias_semana, inicio_periodo[0], fin_periodo[0], dias_no_disponibles, null);
            set_values(dp_regu1, inicio_periodo[0], fin_periodo[0], posibles_fechas_regu1);
        }
        else
        {
            var posibles_fechas_regu1 = get_posibles_fechas(dias_semana, inicio_periodo[0], fin_periodo[1], dias_no_disponibles, null);
            set_values(dp_regu1, inicio_periodo[0], fin_periodo[1], posibles_fechas_regu1);
        }

        var dp_recup1 = 'datepicker_materia_regu_recup1_'+m;
        var posibles_fechas_recup1 = get_posibles_fechas(dias_semana, inicio_periodo[0], fin_periodo[1], dias_no_disponibles, null);
        set_values(dp_recup1, inicio_periodo[0], fin_periodo[1], posibles_fechas_recup1);

        var dp_recup2 = 'datepicker_materia_regu_recup2_'+m;
        if (materia.CICLO == 'F' || materia.CICLO == 'FyP' )
        {
            var posibles_fechas_recup2 = get_posibles_fechas(dias_semana, inicio_periodo[1], fin_periodo[2], dias_no_disponibles, null);
            set_values(dp_recup2, inicio_periodo[1], fin_periodo[2], posibles_fechas_recup2);
        }
        else
        {
            var posibles_fechas_recup2 = get_posibles_fechas(dias_semana, inicio_periodo[2], fin_periodo[2], dias_no_disponibles, null);
            set_values(dp_recup2, inicio_periodo[2], fin_periodo[2], posibles_fechas_recup2);
        }
    }

    function set_values_comisiones(materia, inicio_periodo, fin_periodo, dias_no_disponibles)
    {
        var comisiones = materia.COMISIONES;
        var cant_com = Object.keys(comisiones).length;
        for(var j=0; j<cant_com; j++)
        {
            var comision = comisiones[j];

            var dias_no_validos = comision.DIAS_NO_VALIDOS;
            
            if (comision.ESCALA == 'P  ' || comision.ESCALA == 'PyR')
            {
                set_values_comisiones_promo(comision, materia.CICLO, inicio_periodo, fin_periodo, dias_no_disponibles, dias_no_validos);
            }
            
            if (comision.ESCALA == 'R  ' || comision.ESCALA == 'PyR')
            {
                set_values_comisiones_regu(comision, materia.CICLO, inicio_periodo, fin_periodo, dias_no_disponibles, dias_no_validos);
            }
        }
    }

    function set_values_comisiones_promo(comision, ciclo, inicio_periodo, fin_periodo, dias_no_disponibles, dias_no_validos)
    {
        var c = comision.COMISION;
        var dias_semana = comision['DIAS_CLASE'];
                
        var dp_com_promo1 = 'datepicker_comision_promo1_'+c;
        var posibles_fechas_promo1_com = get_posibles_fechas(dias_semana, inicio_periodo[0], fin_periodo[0], dias_no_disponibles, dias_no_validos);
        set_values(dp_com_promo1, inicio_periodo[0], fin_periodo[0], posibles_fechas_promo1_com);

        var dp_com_promo2 = 'datepicker_comision_promo2_'+c;
        var posibles_fechas_promo2_com = get_posibles_fechas(dias_semana, inicio_periodo[1], fin_periodo[1], dias_no_disponibles, dias_no_validos);
        set_values(dp_com_promo2, inicio_periodo[1], fin_periodo[1], posibles_fechas_promo2_com);

        if (ciclo == 'F' ||  ciclo == 'FyP')
        {
            var dp_com_recup = 'datepicker_comision_promo_recup_'+c;
            var posibles_fechas_recup_com = get_posibles_fechas(dias_semana, inicio_periodo[1], fin_periodo[1], dias_no_disponibles, dias_no_validos);
            set_values(dp_com_recup, inicio_periodo[1], fin_periodo[1], posibles_fechas_recup_com);

            var dp_com_integ = 'datepicker_comision_promo_integ_'+c;
            var posibles_fechas_integ_com = get_posibles_fechas(dias_semana, inicio_periodo[2], fin_periodo[2], dias_no_disponibles, dias_no_validos);
            set_values(dp_com_integ, inicio_periodo[2], fin_periodo[2], posibles_fechas_integ_com);
        }
        else
        {
            var dp_com_integ = 'datepicker_comision_promo_integ_'+c;
            var posibles_fechas_integ_com = get_posibles_fechas(dias_semana, inicio_periodo[1], fin_periodo[2], dias_no_disponibles, dias_no_validos);
            set_values(dp_com_integ, inicio_periodo[1], fin_periodo[2], posibles_fechas_integ_com);
        }
    }

    function set_values_comisiones_regu(comision, ciclo, inicio_periodo, fin_periodo, dias_no_disponibles, dias_no_validos)
    {
        var c = comision.COMISION;
        var dias_semana = comision['DIAS_CLASE'];
        
        var dp_com_regu1 = 'datepicker_comision_regu1_'+c;
        if (ciclo == 'F' || ciclo == 'FyP' )
        {
            var posibles_fechas_regu1_com = get_posibles_fechas(dias_semana, inicio_periodo[0], fin_periodo[0], dias_no_disponibles, dias_semana, dias_no_validos);
            set_values(dp_com_regu1, inicio_periodo[0], fin_periodo[0], posibles_fechas_regu1_com);
        }
        else
        {
            var posibles_fechas_regu1_com = get_posibles_fechas(dias_semana, inicio_periodo[0], fin_periodo[1], dias_no_disponibles, dias_semana, dias_no_validos);
            set_values(dp_com_regu1, inicio_periodo[0], fin_periodo[1], posibles_fechas_regu1_com);
        }

        var dp_com_recup1 = 'datepicker_comision_regu_recup1_'+c;
        var posibles_fechas_recup1_com = get_posibles_fechas(dias_semana, inicio_periodo[0], fin_periodo[1], dias_no_disponibles, dias_semana, dias_no_validos);
        set_values(dp_com_recup1, inicio_periodo[0], fin_periodo[1], posibles_fechas_recup1_com);

        var dp_com_recup2 = 'datepicker_comision_regu_recup2_'+c;
        if (ciclo == 'F' || ciclo == 'FyP' )
        {
            var posibles_fechas_recup2_com = get_posibles_fechas(dias_semana, inicio_periodo[1], fin_periodo[2], dias_no_disponibles, dias_semana, dias_no_validos);
            set_values(dp_com_recup2, inicio_periodo[1], fin_periodo[2], posibles_fechas_recup2_com);
        }
        else
        {
            var posibles_fechas_recup2_com = get_posibles_fechas(dias_semana, inicio_periodo[2], fin_periodo[2], dias_no_disponibles, dias_no_validos);
            set_values(dp_com_recup2, inicio_periodo[2], fin_periodo[2], posibles_fechas_recup2_com);
        }
    }

    function get_posibles_fechas(dias_semana, inicio_periodo, fin_periodo, dias_no_disponibles, dias_no_validos)
    {
        var feriados = $('#feriados').val();
        var dia = new Date(inicio_periodo);
        var posibles_fechas = '[';
        while (dia <= fin_periodo)
        {
            
            var diaSemana = dia.getDay();
            //if (dias_semana.includes(String(diaSemana+1)))
            if (contiene(dias_semana, diaSemana))
            {
                var diaFormateado = dia.toISOString().substring(0, 10);
                var es_fecha_disponible = fecha_disponible(dias_no_disponibles, dia);
                var es_fecha_valida = fecha_valida(dias_no_validos, dia);
                if (feriados.includes(diaFormateado) == false && es_fecha_disponible && es_fecha_valida)
                    posibles_fechas += diaFormateado + ',';
            }
            dia.setDate(dia.getDate() + 1);
        }
        return posibles_fechas.substring(0, posibles_fechas.length-1) + ']';
    }
    
    //  Verifica que ese mismo día no esté ocupado por otra materia del mismo mix, y tampoco el día anterior o posterior consecutivo. 
    function fecha_disponible(fechas_no_disponibles, fecha)
    {
        var dias_ocupados = JSON.stringify(fechas_no_disponibles);
        if (dias_ocupados.includes(fecha))
            return false;

        var dia_anterior = new Date(fecha);
        dia_anterior.setDate(dia_anterior.getDate() + 1);
        if (dias_ocupados.includes(dia_anterior))
            return false;
        
        var dia_siguiente = new Date(fecha);
        dia_siguiente.setDate(dia_siguiente.getDate() + 1);
        if (dias_ocupados.includes(dia_siguiente))
            return false;
        
        return true;
    }
    
    function fecha_valida(dias_no_validos, fecha)
    {
        var fechas_no_validas = JSON.stringify(dias_no_validos);
        if (fechas_no_validas.includes(fecha))
            return false;
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
//        console.log(inicio_periodo);
//        console.log(fin_periodo);

        $('#'+objeto_id).datepicker({
                
                format: 'YYYY-MM-DD',  
                regional: 'es',
                firstDay: 0,
                
                //date: new Date(defaultDay),
                //date: new Date('2018-05-03'),
                
                minDate: inicio_periodo,
                maxDate: fin_periodo,

                beforeShowDay: function (date) {
//                            diaSemana = date.getDay();
                            posiblesDias = posibles_fechas;
                            diaFormateado = date.toISOString().substring(0, 10);
                            habilitar =   (posiblesDias.includes(diaFormateado));
//                            console.log(habilitar);
                            return [habilitar];
                        }
            });
    }
//    function set_values(objeto_id, fechaInicio, fechaFin, fechaMin, fechaMax, feriados)
//    {
//        $('#'+objeto_id).daterangepicker({
//                opens: 'left',
//                format: 'YYYY-MM-DD',  
//                showDropdowns: false,
//                locale: {
//                    "format": "DD/MM/YYYY",
//                    "separator": " - ",
//                    "applyLabel": "Aplicar",
//                    "cancelLabel": "Cancelar",
//                    "fromLabel": "Desde",
//                    "toLabel": "Hasta",
//                    "customRangeLabel": "Custom",
//                    "weekLabel": "W",
//                    "daysOfWeek": [
//                        "Do",
//                        "Lu",
//                        "Ma",
//                        "Mi",
//                        "Ju",
//                        "Vr",
//                        "Sa"
//                    ],
//                    "monthNames": [
//                        "Enero",
//                        "Febrero",
//                        "Marzo",
//                        "Abril",
//                        "Mayo",
//                        "Junio",
//                        "Julio",
//                        "Agosto",
//                        "Septiembre",
//                        "Octubre",
//                        "Noviembre",
//                        "Diciembre"
//                    ],
//                    firstDay: 0
//                },
//                
//                minDate: new Date(fechaMin),
//                maxDate: new Date(fechaMax),
//                startDate: new Date(fechaInicio),
//                endDate: new Date(fechaFin),
//                isInvalidDate: function (momento) {
//                            dia = momento.toDate();
//                            diaSemana = dia.getDay();
//                            diaFormateado = dia.toISOString().substring(0, 10);
//
//                            bloquear =  (diaSemana == 0) ||
//                                        (diaSemana == 6) ||
//                                        (feriados.includes(diaFormateado));
//
//                            return bloquear;
//                        }
//        });
//    }

//    function setear_calendarios()
//    {
//        
//        //var posiblesDias = ["2018-11-12", "2018-11-14", "2018-11-19", "2018-11-21"];
//        //console.log($('#fechas_promo1_6864'));
//        //var posiblesDias = $('#fechas_promo1_7098').val();
//        //var defaultDay = posiblesDias.substring(1, 11);
//        //console.log(defaultDay);
//        //console.log(posiblesDias);
//        //console.log($('#inicio_periodo_'+1).val());
//        
//        var inicio_1 = new Date ($('#inicio_periodo_'+1).val().replace(/-/g, '\/'));
//        var fin_1 = new Date ($('#fin_periodo_'+1).val().replace(/-/g, '\/'));
//        var inicio_2 = new Date ($('#inicio_periodo_'+2).val().replace(/-/g, '\/'));
//        var fin_2 = new Date ($('#fin_periodo_'+2).val().replace(/-/g, '\/'));
//        var inicio_3 = new Date ($('#inicio_periodo_'+3).val().replace(/-/g, '\/'));
//        var fin_3 = new Date ($('#fin_periodo_'+3).val().replace(/-/g, '\/'));
//        console.log($(this));
//        console.log($(id));
//            $('[id*="datepicker_comision_promo1_"]').datepicker({
//                
//                format: 'YYYY-MM-DD',  
//                regional: 'es',
//                firstDay: 0,
//                
//                //date: new Date(defaultDay),
//                //date: new Date('2018-05-03'),
//                
//                minDate: new Date(inicio_1),
//                maxDate: new Date(fin_1),
//
//                beforeShowDay: function (date) {
////                            diaSemana = date.getDay();
//                            posiblesDias = $('[id*="fechas_promo1_"]').val();
//                            diaFormateado = date.toISOString().substring(0, 10);
//                            habilitar =   (posiblesDias.includes(diaFormateado));
////                            console.log(habilitar);
//                            return [habilitar];
//                        }
//            });
            
        
        
//        var i;
//        var fecha_inicio;
//        var fecha_fin;
//        var feriados = armar_cadena_de_feriados();
//        for (i = 1; i <= 3; i++) 
//        {
//            fecha_inicio = new Date();
//            fecha_fin = new Date();
//            if ($('#fecha_inicio_'+i).length && $('#fecha_fin_'+i).length)
//            {
//                fecha_inicio = new Date ($('#fecha_inicio_'+i).val().replace(/-/g, '\/'));
//                fecha_fin = new Date ($('#fecha_fin_'+i).val().replace(/-/g, '\/'));
//            }
//            else
//            {
//                if ($('#lectivo_fin'))
//                {
//                    fecha_inicio = new Date ($('#lectivo_fin').val().replace(/-/g, '\/'));
//                    fecha_inicio.setDate(fecha_inicio.getDate() - 90/i);
//                    fecha_fin = fecha_inicio;
//                }
//            }
//
//            if ($('#daterange_'+i))
//                set_values('daterange_'+i, fecha_inicio, fecha_fin, $('#lectivo_inicio').val(), $('#lectivo_fin').val(), feriados);
//        }
//    }
   
//    function armar_cadena_de_feriados()
//    {
//        var i; 
//        var cant = $('#cant_feriados').val();
//        var feriados = '[';
//        if (cant > 0)
//        {
//            feriados += "'"+$('#feriado_0').val()+"'";
//        }
//        for (i = 1; i< $('#cant_feriados').val(); i++)
//        {
//            feriados += ",'" + $('#feriado_'+i).val() + "'";
//        }
//        feriados += ']';
//        return feriados;
//    }
//
});

