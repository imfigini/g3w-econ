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
        
                        actualiza_vista();
        }
    }
    
    
})

    function actualiza_vista()
    {
        var alumnos_json = $('#alumnos').val();
        if (alumnos_json)
        {
            var alumnos  = JSON.parse(alumnos_json);
            for(var key in alumnos)
            {
                var alumno = alumnos[key];
                actualizar_cuadro(key, alumno);
            }
        }
    }
    
    function actualizar_cuadro(id, datos)
    {
        var contenedor = $('#'+id);
        var div_selector = $('#select_'+id);
        var selector = $('select[id="alumnos['+id+'][JUSTIFIC]"]');
        if (datos['CANT_INASIST'] == "0.00") {
            setear_presente(contenedor, div_selector, selector);
        }
        else {
            setear_ausente(datos, contenedor, div_selector, selector);
        }
    }
    
    function setear_presente(contenedor, div_selector, selector)
    {
        contenedor.addClass('asistio');
        contenedor.removeClass('ausente-justif');
        contenedor.removeClass('ausente');
        contenedor.find('.check-asistencia input').prop('checked',true);
        div_selector.addClass('ocultar_select');
        selector.val(-1); 
    }
    
    function setear_ausente(datos, contenedor, div_selector, selector)
    {
        contenedor.removeClass('asistio');
        contenedor.find('.check-asistencia input').prop('checked',false);
        div_selector.removeClass('ocultar_select');
        if (datos['MOTIVO_JUSTIFIC'])
        {
            contenedor.removeClass('ausente');
            selector.val(datos['MOTIVO_JUSTIFIC']); 
            contenedor.addClass('ausente-justif');
        }
        else
        {
            contenedor.removeClass('ausente-justif');
            contenedor.addClass('ausente');
            selector.val(-1); 
        }
    }
    
    function click_cuadro(contenedor)
    {
        var click_id = contenedor.id;
        var id = click_id.split('_')[1];
        var alumnos_json = $('#alumnos').val();
        var alumnos  = JSON.parse(alumnos_json);
        alumno = find_alumno(alumnos, id);    
        if (alumno['CANT_INASIST'] == "0.00")
        {
            alumno['CANT_INASIST'] = "1.00";
        }
        else
        {
            alumno['CANT_INASIST'] = "0.00";
            alumno['MOTIVO_JUSTIFIC'] = null;
        }
        $('#alumnos').val(JSON.stringify(alumnos));
        actualizar_cuadro(id, alumno);
    }
    

    function find_alumno(alumnos, id)
    {
        if (alumnos) {
            for(var key in alumnos) {
                if (key == id) {
                    return alumnos[key];
                }
            }
        }
        return null;
    }
    
    function actualizar_background(objeto, id)
    {
        if (id == null || id == undefined)  {
            return;
        }

        var valor_justif = objeto.value;

        var alumnos_json = $('#alumnos').val();
        var alumnos  = JSON.parse(alumnos_json);
        alumno = find_alumno(alumnos, id);      

        alumno['CANT_INASIST'] = "1.00";
        alumno['MOTIVO_JUSTIFIC'] = (valor_justif != -1) ? valor_justif : null;
        console.log(alumno);

        $('#alumnos').val(JSON.stringify(alumnos));
        actualizar_cuadro(id, alumno);
    }

