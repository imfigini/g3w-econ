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
            
    
            inicio_calendario();
           
        }
    };
    
//    function get_eventos2()
//    {
//        var resultado =  [ { "title": "Prueba", "start": "2019-02-10", "end": "2019-02-10" } ];
//        console.log(resultado);
//        //resultado = JSON.parse(resultado);
//        //console.log(resultado);        
//        return resultado;
//    }
    
    function inicio_calendario()
    {
        if (!$('#evaluaciones').val())
        {
            return;
        }
        
        var eventos = JSON.parse($('#evaluaciones').val());
        
        $('#calendar').fullCalendar({
                lang: 'es',
                header: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'month,basicWeek,basicDay'
                },
                //editable: false,
                editable: true,
               // eventLimit: true, // allow "more" link when too many events
                //selectable: false,
                selectable: true,
                events: eventos,
                eventRender: function(event, element) {
                    element.attr('title', event.tip);
                },
                eventDrop: function(info) { 
                    console.log(info);
                        //alert(info.event.title + " was dropped on " + info.event.start.toISOString()); 
                        //alert(info.title + " was dropped on " + info.start.toISOString()); 
                        if (!confirm("Are you sure about this change?")) { info.revert(); }
                        //else : LLamar a ajax, y si falla (error/fail) que revierta
                        
                },
                monthNames: ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
                monthNamesShort: ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'],
                dayNames: ['Domingo','Lunes','Martes','Miercoles','Jueves','Viernes','Sabado'],
                dayNamesShort: ['Dom','Lun','Mar','Mie','Jue','Vie','Sab'],
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
        
//    function cargarCalendario()
//    {
//            console.log('entro por cargarCalendario');    
//    }
    
    
});