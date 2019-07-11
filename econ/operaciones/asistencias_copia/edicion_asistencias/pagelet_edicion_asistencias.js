kernel.renderer.registrar_pagelet('edicion_asistencias', function(info) {
    var id = '#' + info.id;
	
    return {
        onload: function() {
			$('#js-guardar-asistencia').show().position({
				my: 'left',
				at: 'right center',
				offset: '5 0',
				of: $('#js-info-mesa')
			});
			
                        $(id).find('.box-asistencia').click(function() {
				var box = $(this);
                                console.log(box);
				if (box.parent().hasClass('asistio')) {
                                    box.find('.check-asistencia input').prop('checked',false);
                                    box.parent().find('.box-justific').prop('disabled',true);
                                    box.parent().find('.box-justific').removeClass('ocultar_select');
                                    $('#'+box.parent()[0].id + ' select' ).val(-1);
                                    box.parent().removeClass('asistio');
                                    box.parent().removeClass('ausente-justif');
                                    box.parent().addClass('ausente');
				} 
                                else 
                                {
                                    box.find('.check-asistencia input').prop('checked',true);
                                    box.parent().find('.box-justific').prop('disabled',false);
                                    box.parent().find('.box-justific').addClass('ocultar_select');
                                    box.parent().removeClass('ausente');
                                    box.parent().removeClass('ausente-justif');
                                    box.parent().addClass('asistio');
				}
			});
                        
                        
			$(id).find('.form-renglones').submit(function() {
				var $form = $(this);
				kernel.ajax.call($form.attr('action'), {
					type: 'POST',
					data: $form.serializeArray(),
					success: function(response) {
						kernel.ui.show_mensaje(response.cont);
					}
				});
				return false;
			});
        
    }
    }
    
    
})

    function actualizar_background(objeto, id_div)
    {
        var div = document.getElementById(id_div);
        if (objeto.value > 0)
        {
            div.classList.remove('ausente');    
            div.classList.add('ausente-justif');
        }
        else
        {
            div.classList.remove('ausente-justif');    
            div.classList.add('ausente');
       }
    }
    
    function verificar_visibilidad(objeto)
    {
        
        console.log(objeto);
    }