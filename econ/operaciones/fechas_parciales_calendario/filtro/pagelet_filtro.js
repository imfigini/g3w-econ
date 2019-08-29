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
//                            console.log($(this));
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
    
    function inicio_calendario()
    {
        if (!$('#evaluaciones').val())
        {
            return;
        }
        
        var eventos = JSON.parse($('#evaluaciones').val());
        
        var feriados = get_feriados();

        eventos = eventos.concat(feriados);

        console.log(eventos);
        // eventos.forEach(function(element) {
        //         if (element['overlap'] == 'false')
        //         {
        //                 element['overlap'] = false;
        //         }
        //       });

        $('#calendar').fullCalendar({
                lang: 'es',
                plugins: [ 'interaction', 'dayGrid', 'timeGrid' ],
                header: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'month,agendaWeek,agendaDay'
                },

                start: '2019-08-01',
                end: '2019-12-31',

                editable: true,
                selectable: true,
                
                dayRender: function(date, cell) {
                        if (date._d.getDay() == 6) {
                                $(cell).addClass('fc-bgevent fc-nonbusiness');
                        }
                },
                

                events: eventos,
                eventRender: function(event, element) {
                    element.attr('title', event.tip);
                },

                eventOverlap: function(stillEvent, movingEvent) {
                        console.log('stillEvent ');
                        console.log(stillEvent);
                        console.log('movingEvent ');
                        console.log(movingEvent);
                      },

                eventDrop: function(info) { 
                        console.log(info);
                        //alert(info.event.title + " was dropped on " + info.event.start.toISOString()); 
                        //alert(info.title + " was dropped on " + info.start.toISOString());
                        var texto = "Está seguro de modificar la fecha para "+ info.title + '?. Tenga en consideración que si la fecha sólo estaba propuesta y sin aceptar, la misma será creada y se dará por aceptada con modificación. Si la instancia de evaluación ya estaba creada, sólo se le modificará la fecha que tenía asignada.';
                        if (!confirm(texto)) { 
                                info.revert(); 
                        }
                        //else : LLamar a ajax, y si falla (error/fail) que revierta
                       
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
        for(var key in feriados)
        {
                var evento =  new Object();
                evento['start'] = feriados[key]['FECHA'];
                evento['end'] = feriados[key]['FECHA'];
                evento['overlap'] = false;
                evento['rendering'] = 'background';
                evento['color'] = 'red';
                eventos[i] = evento;
                i++;

        }
        //console.log(eventos);
        return eventos;
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


   
});