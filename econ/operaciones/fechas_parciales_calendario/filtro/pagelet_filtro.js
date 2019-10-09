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
                        return true;
                });
    
                inicio_calendario();
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
        })  
    }
    
    function inicio_calendario()
    {
        if (!$('#evaluaciones').val()) {
            return;
        }
	   
        var inicio_periodo = $('#inicio_periodo').val();
		var fin_periodo = $('#fin_periodo').val();
		
		var periodos_evaluacion = JSON.parse($('#priodos_evaluacion').val());
		var primer_dia_visible = periodos_evaluacion[0]['FECHA_INICIO'];

        var eventos = JSON.parse($('#evaluaciones').val());
        var feriados = get_feriados();
		var domingos = get_domingos(inicio_periodo, fin_periodo);
		var periodos_validos = get_periodos_validos();

        eventos = eventos.concat(feriados);
		eventos = eventos.concat(domingos);
		eventos = eventos.concat(periodos_validos);

//        console.log(eventos);

        $('#calendar').fullCalendar({
                lang: 'es',
                header: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'month,agendaWeek,agendaDay'
                },

                defaultView: 'month',
                editable: true,
                selectable: true,

                validRange: {
                        start: inicio_periodo,
                        end: fin_periodo
                },
                defaultDate: primer_dia_visible,           
                
                events: eventos,

                eventRender: function(event, element) {
                    element.attr('title', event.tip);
                },

                eventDrop: function(event, delta, revertFunc) { 
                        //console.log(event);
                        //console.log(delta);
						//console.log(revertFunc);
						console.log(event);
                        var texto = "Est� seguro de modificar la fecha solicitada para "+ event.title + '?. Priorice modificar a d�as de cursada.';
                        if (!confirm(texto)) {
                                revertFunc();
                        } else {
                                mover_evaluacion(event, delta, revertFunc);
                        }
               },

               eventClick: function(info) {
                        if (info.estado != 'A')
                        {
							var texto = 'Desea confirmar la fecha solicitada para ' + info.title + '?.';
							if (confirm(texto)) {
								confirmar_evaluacion(info);
							}
                        }
                        else
                        {
							kernel.ui.show_mensaje('La evaluaci�n ya se encuentra creada y aceptada en esta fecha. ', {tipo: 'alert-info'});
                        }
                },
            
                monthNames: ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
                monthNamesShort: ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'],
                dayNames: ['Domingo','Lunes','Martes','Miercoles','Jueves','Viernes','Sabado'],
                dayNamesShort: ['Dom','Lun','Mar','Mie','Jue','Vie','Sab'],
        });
    }
    

    function get_feriados()
    {
        var feriados = JSON.parse($('#dias_no_laborales').val());
        //console.log(feriados);
        var eventos =  new Array();
        var i = 0;
        var key;
        for(key in feriados)
        {
                var evento =  new Object();
                evento['id'] = key;
                evento['start'] = feriados[key]['FECHA'];
                evento['overlap'] = false;
                evento['rendering'] = 'background';
                evento['color'] = 'LightSalmon';
                eventos[i] = evento;
                i++;
        }
        //console.log(eventos);
        return eventos;
    }

    function get_domingos(start, end)
    {
        var inicio = new Date(start.replace(/-/g, '\/'));
        var fin = new Date(end.replace(/-/g, '\/'));
        var domingos =  new Array();
        var i = 0;
        for (dia = inicio; dia <= fin; dia.setDate(dia.getDate() + 1)) 
        {
                if (dia.getDay() == 0) //Domingo
                {
                        var evento =  new Object();
                        evento['_id'] = 'dom-'+i;
                        fecha = dia.toISOString().substring(0, 10);
                        evento['start'] = fecha;
                        evento['overlap'] = false;
                        evento['rendering'] = 'background';
                        evento['color'] = 'LightGray';
                        domingos[i] = evento;
                        i++;
                }
        };
        //console.log(domingos);
        return domingos;
	}
	
	function get_periodos_validos()
	{
		var periodos = JSON.parse($('#priodos_evaluacion').val());
        var eventos =  new Array();
        var i = 0;
        var key;
        for(key in periodos)
        {
                 var evento =  new Object();
				 evento['start'] = periodos[key]['FECHA_INICIO'];
				 evento['end'] = periodos[key]['FECHA_FIN'];
                 evento['rendering'] = 'background';
                 evento['color'] = 'PaleGreen';
                 eventos[i] = evento;
                 i++;
        }
        return eventos;
	}

    function mover_evaluacion(event, delta, revertFunc)
    {
        var fecha_orig = event.start._i;
        var fecha = new Date(fecha_orig.replace(/-/g, '\/'));
        var dif = parseInt(event.delta) + delta._days;
        fecha.setDate(fecha.getDate() + dif);
        var fecha_dest = fecha.toISOString().substring(0, 10);
        var anio_academico_hash = $('#formulario_filtro-anio_academico').val();
        var periodo_hash = $('#formulario_filtro-periodo').val();

        $.ajax({
                url: info.url_correr_evaluacion,
                dataType: 'json',
                data: { materia: event.materia, evaluacion: event.evaluacion, 
                        fecha_orig: fecha_orig, fecha_dest: fecha_dest,
                        color_acep: event.color_acep,
                        anio_academico_hash: anio_academico_hash, periodo_hash: periodo_hash },
                type: 'post',
                success: function(data) {
					console.log(data);
                        if (data.cod < 0) {
                                revertFunc();
                                var msg = data.titulo + data.mensajes[2];
                                kernel.ui.show_mensaje(msg, {tipo: 'alert-error'});
                        } else {
                                event.backgroundColor = data.backgroundColor;
                                event.borderColor = 'grey';
                                event.delta = dif;
                                $('#calendar').fullCalendar('updateEvent', event);
                                kernel.ui.show_mensaje('Se modific� la fecha de la evualuci�n correctamente. ');
                        }
                }
        });
    }

    function confirmar_evaluacion(event)
    {
        var anio_academico_hash = $('#formulario_filtro-anio_academico').val();
        var periodo_hash = $('#formulario_filtro-periodo').val();

//        console.log(event);
        $.ajax({
                url: info.url_confirmar_evaluacion,
                dataType: 'json',
                data: { materia: event.materia, evaluacion: event.evaluacion, fecha: event.start._i,
                        anio_academico_hash: anio_academico_hash, periodo_hash: periodo_hash },
                type: 'post',
                success: function(data) {
                        if (data.cod < 0) {
                                var msg = data.titulo + data.mensajes[2];
                                kernel.ui.show_mensaje(msg, {tipo: 'alert-error'});
                        } else {
                                event.backgroundColor = event.color_acep;
                                event.borderColor = 'grey';
                                event.estado = 'A';
                                $('#calendar').fullCalendar('updateEvent', event);
                                kernel.ui.show_mensaje(data.mensaje);
                        }
                }
        });
    }
   
});
