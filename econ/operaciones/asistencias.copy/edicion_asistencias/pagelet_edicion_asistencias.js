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
                                var sel = box.find('.select-inasist select').val();
                                switch (sel) 
                                {
                                    case '-1' : box.addClass('ausente');
                                                box.removeClass('asistio');
                                                box.removeClass('ausente-justif');
                                                break;
                                    case '0':   box.removeClass('ausente');
                                                box.addClass('asistio');
                                                box.removeClass('ausente-justif');
                                                break;
                                    default:    box.removeClass('ausente');
                                                box.removeClass('asistio');
                                                box.addClass('ausente-justif');
                                                break;
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