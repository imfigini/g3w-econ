kernel.renderer.registrar_pagelet('lista_materias', function(info) {
    var id = '#' + info.id;
	
	
	function cambiar_nombre_boton_comision(elemento)
	{
		var boton = elemento.parents('.clase').find('.mostrar');
		if (boton.hasClass('mostrando-clases')) {
			boton.text(info.mostrar_clases);
			boton.removeClass('mostrando-clases');
		} else {
			boton.text(info.ocultar_clases);
			boton.addClass('mostrando-clases');
		}
		
	}
	
	return {
        onload: function() {
						
            $("#filtro_form").submit(function( event ) {
                    var $form  = $("#filtro_form");
                    var url = $form.attr('action');
                    var params = 'filtro_per_lectivo='+$("#filtro_per_lectivo").val()+'&filtro_materia='+$("#filtro_materia").val()+'&filtro_docente_legajo='+$("#filtro_docente_legajo").val();

                    kernel.ajax.load(url + '?' + params, id, {
                            historia: true,
                            type: 'get',
                            show_loading: true,
                            forzar_cambio_op: true
                    });

                    return false;
            });


            $(id + ' .mostrar').click(function() {

                    var clase_id = $(this).parents('.clase').data('clase-id');
                    var comisiones = $('#comisiones_'+clase_id).val();
                    var elemento = $(this);

                    if (!elemento.parents('.clase').find('.js-clases-comision').hasClass('con_datos') ) {
                            kernel.ajax.call(info.url_mostrar_clases, {
                                    type: 'post',
                                    data: {comisiones: comisiones, cant: 3},
                                    success: function(paquete) {
                                            elemento.parents('.clase').find('.js-clases-comision').addClass('con_datos');
                                            elemento.parents('.clase').find('.js-clases-resumen').html(paquete.cont);
                                            elemento.parents('.clase').find('.js-clases-comision').toggle(400);
                                            cambiar_nombre_boton_comision(elemento);
                                    }

                            });
                    } else {
                            elemento.parents('.clase').find('.js-clases-comision').toggle(400);
                            cambiar_nombre_boton_comision(elemento);
                    }

            });


            $(id).on('click', ' .mostrar_todas', function() {

                    var clase_id = $(this).parents('.clase')[1].id;
                    var comisiones = $('#comisiones_'+clase_id).val();
                    var elemento = $(this);

                    if (!elemento.parents('.clase').find('.js-clases-completo').hasClass('completo') ) {
                                    kernel.ajax.call(info.url_mostrar_clases, {
                                            type: 'post',
                                            data: {comisiones: comisiones, cant: 0},
                                            success: function(paquete) {
                                                    elemento.parents('.clase').find('.js-clases-resumen').toggle();
                                                    elemento.parents('.clase').find('.js-clases-completo').html(paquete.cont);
                                                    elemento.parents('.clase').find('.js-clases-completo').addClass('completo');
                                                    elemento.parents('.clase').find('.js-clases-completo').find('.mostrar_todas').text(info.ver_ultimas);
                                            }

                                    });
                            } else {
                                    elemento.parents('.clase').find('.js-clases-resumen').toggle();
                                    elemento.parents('.clase').find('.js-clases-completo').toggle();
                            }		


            });
            
            $(id + ' .js-materia-cabecera').on('click', function () {
                            $(this).next('.js-materia-contenido').toggleClass('colapsado').slideToggle(500);
			}).on('keypress', function(e) {
                                if (e.which == 13) { $(this).trigger('click'); }
                        });
                                                
                        
            $(id + ' #js-toggle-all').on('click', function () {
                    var accion = $(this).toggleClass('toggle');
                    var texto_actual = accion.text();
                    var texto_nuevo = accion.data('toggle-texto');
                    accion.data('toggle-texto', texto_actual);
                    accion.text(texto_nuevo);

                    $('.js-materia-contenido').each(function() {
                            if ( accion.hasClass('toggle') ) {
                                    $(this).addClass('colapsado').slideUp(500);
                            } else {
                                    $(this).removeClass('colapsado').slideDown(500);
                            }

                    });
            });
			
			
            $(id + ' button.planilla').on('click',function() {
                //console.log(this);
                guarani.cambiar_op({
                        href: $(this).data('link')
                });
            });
            
            $(id + ' button.resumen').on('click',function() {
                console.log(this);
                guarani.cambiar_op({
                        href: $(this).data('link')
                });
            });
        }
    }
})