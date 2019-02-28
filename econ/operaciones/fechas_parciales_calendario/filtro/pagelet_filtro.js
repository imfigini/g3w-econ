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
            
//            cargarCalendario();
            
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
            
    
//            var eventos = get_eventos();
//            console.log(eventos);
            inicio_calendario();
            //set_values('datepicker_promo1_6879', '2018-01-01', '2018-12-31');
            
        }
    };
    
    function get_eventos()
    {
        console.log('entro por get_eventos');
        var evaluaciones_json = $('#evaluaciones').val();
        //var resultado = '[ ';
        if (evaluaciones_json)
        {
            var evaluaciones = JSON.parse(evaluaciones_json);
//            console.log(evaluaciones);
            var cant = Object.keys(evaluaciones).length;
            var cadena = '';
            for(var i=0; i<cant; i++)
            {
                var eval = evaluaciones[i];
  //              console.log(eval);
                var r = '{ ';
                r += ' "title": "'+eval['MATERIA_NOMBRE']+' '+eval['EVALUACION']+'"';
                r += ', "start": "'+eval['FECHA']+'"';
                r += ', "end": "'+eval['FECHA']+'"';
                cadena += r+'}, ';
            }
            var resultado = cadena.substring(0, cadena.length-2);
            console.log(resultado);
            resultado = JSON.parse(resultado);
            console.log(resultado);
            //return resultado;
            
            return resultado;
        }
        return null;
    }
    
    function get_eventos2()
    {
        var resultado =  [ { "title": "Prueba", "start": "2019-02-10", "end": "2019-02-10" } ];
        console.log(resultado);
        //resultado = JSON.parse(resultado);
        //console.log(resultado);        
        return resultado;
    }
    
    function inicio_calendario()
    {
//        var eventos = get_eventos();
//        console.log(eventos);
//var variable = [ { "title": "Prueba", "start": "2019-02-10", "end": "2019-02-10" } ];
        $('#calendar').fullCalendar({
                lang: 'es',
                header: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'month,basicWeek,basicDay'
                },
                editable: false,
                eventLimit: true, // allow "more" link when too many events
                selectable: false,
                events: function (start, end, timezone, callback) 
                        {
                            console.log(start);
                    var eventos = get_eventos2();
                            callback(eventos);
                        } ,
////                                './getdatos.php'	
//                    {
//                        title : 'Event Title1',
//                        start : '2019-02-20',
//                        end : '2019-02-20'
//                    }
                    
               //     {  title: "Analisis Macroeconomico Primer Parcial Promo", start: "2018-10-09", end: "2018-10-09"}, {  title: "Analisis Macroeconomico Segundo Parcial Promo", start: "2018-11-13", end: "2018-11-13"}, {  title: "Derecho Empresario II Parcial Regular", start: "2018-10-15", end: "2018-10-15"}, {  title: "Derecho Empresario II Primer Recuperatorio Regular", start: "2018-11-19", end: "2018-11-19"}, {  title: "Logistica y Organizacion Productiva Primer Parcial Promo", start: "2018-10-18", end: "2018-10-18"}, {  title: "Logistica y Organizacion Productiva Segundo Parcial Promo", start: "2018-11-22", end: "2018-11-22"}, {  title: "Marketing Primer Parcial Promo", start: "2018-10-18", end: "2018-10-18"}, {  title: "Marketing Segundo Parcial Promo", start: "2018-11-27", end: "2018-11-27"}, {  title: "Econometria y Modelizacion Primer Parcial Promo", start: "2018-10-19", end: "2018-10-19"}, {  title: "Econometria y Modelizacion Segundo Parcial Promo", start: "2018-11-23", end: "2018-11-23"}
//                    {
//                        'title' : 'Titulo del calendario',
//                        'start' : '2019-02-25T13:13:55.008',
//                        'textColor' : 'black',
//                        'tip' : 'este es un tip',
//                        'backgroundColor' : 'yellow',
//                        'borderColor' : 'red',
//                        'end' : '2019-02-25T13:13:55.008'
//                    }
                 //       ],
                eventRender: function(event, element) {
                    element.attr('title', event.tip);
                }
        });
    }
    
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
            });
    }
        
    function cargarCalendario()
    {
            console.log('entro por cargarCalendario');    
    }
    
});