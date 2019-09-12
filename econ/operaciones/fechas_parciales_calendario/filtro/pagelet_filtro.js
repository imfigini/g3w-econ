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

        var eventos = JSON.parse($('#evaluaciones').val());
        var feriados = get_feriados();
        var domingos = get_domingos(inicio_periodo, fin_periodo);

        eventos = eventos.concat(feriados);
        eventos = eventos.concat(domingos);

        console.log(eventos);

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
                defaultDate: inicio_periodo,           
                
                events: eventos,

                eventRender: function(event, element) {
                    element.attr('title', event.tip);
                },

                eventDrop: function(event, delta, revertFunc) { 
                        //console.log(event);
                        //console.log(delta);
                        //console.log(revertFunc);
                        var texto = "Está seguro de modificar la fecha para "+ info.title + '?. Tenga en consideración que si la fecha sólo estaba propuesta y sin aceptar, la misma será creada y se dará por aceptada con modificación. Si la instancia de evaluación ya estaba creada, sólo se le modificará la fecha que tenía asignada.';

                        //alert(event.title + " was dropped on " + event.start.format());
                            
                        if (!confirm(texto)) {
                                revertFunc();
                        }
                        else
                        {
                                var datos = new Object();
                                datos['evaluacion'] = event.evaluacion;
                                datos['materia'] = event.materia;
                                datos['fecha_orig'] = event.start._i;
                                var fecha = new Date(datos['fecha_orig'].replace(/-/g, '\/'));
                                var dif = parseInt(event.delta) + delta._days;
                                fecha.setDate(fecha.getDate() + dif);
                                datos['fecha_dest'] = fecha.toISOString().substring(0, 10);
                                datos['color_acep'] = event.color_acep;
                                datos['anio_academico_hash'] = $('#formulario_filtro-anio_academico').val();
                                datos['periodo_hash'] = $('#formulario_filtro-periodo').val();
                                datos['delta'] = dif;
                                mover_evaluacion(datos, event, revertFunc);
                        }
                       
               },

               eventClick: function(info) {
                        console.log(info);
                        alert('Desea confirmar la fecha para ' + info.title + '?. En caso afirmativo la instancia de evaluación será creada si no lo estaba, se dará por aceptada sin modificaciones y cambiará el color a más oscuro. Si ya estaba creada no hará nada. ');
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

    function mover_evaluacion(datos, event, revertFunc)
    {
        console.log(event);
        console.log(datos);
        $.ajax({
                url: info.url_correr_evaluacion,
                dataType: 'json',
                data: { materia: datos['materia'], evaluacion: datos['evaluacion'], 
                        fecha_orig: datos['fecha_orig'], fecha_dest: datos['fecha_dest'],
                        color_acep: datos['color_acep'],
                        anio_academico_hash: datos['anio_academico_hash'], periodo_hash: datos['periodo_hash']},
                type: 'post',
                success: function(data) {
                        if (data.cod < 0) {
                                var msg = data.titulo + data.mensajes[2];
                                console.log(msg);
                                revertFunc();
                                kernel.ui.show_mensaje(msg, {tipo: 'alert-error'});
                        } else {
                                event.backgroundColor = data.backgroundColor;
                                event.delta = datos.delta;
                                $('#calendar').fullCalendar('updateEvent', event);
                                kernel.ui.show_mensaje(data.titulo);
                        }
                }
        });
    }
   
});