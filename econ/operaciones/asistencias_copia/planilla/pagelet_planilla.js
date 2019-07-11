kernel.renderer.registrar_pagelet('planilla', function(info) {
	var id = '#' + info.id;

	function registrar_disparadores() {
	}

	function registrar_listeners() {
	}

	return {
            onload: function() {
//                    $('#boton_pdf').hide();
                    $filtro = $('form').formulario();                
                    $("#boton_pdf").on('click', function(){
                        var link = $(this).attr('href')+ '?ID='+info.clase_id + '&MATERIA='+info.materia + '&DIA_SEMANA='+info.dia_semana + '&HS_COMIENZO_CLASE='+info.hs_comienzo_clase
                                                       + '&cantidad='+$('#formulario_filtro-cantidad').val() + '&fecha='+$('#formulario_filtro-fecha').val();
                        console.log(link);
                        window.open(link);
			return false;
                    });
                    
                    $('#boton_buscar').on('click', function(){ 
                        $('#formulario_filtro').submit();                       
                    });
                    
                    $('#formulario_filtro').on('submit', function(){
                        var fecha_seleccionada = $filtro.find('#formulario_filtro-fecha').val();
                        kernel.ajax.call($(this).attr('action')+ '?ID='+info.clase_id + '&MATERIA='+info.materia + '&DIA_SEMANA='+info.dia_semana + '&HS_COMIENZO_CLASE='+info.hs_comienzo_clase, {
                            data: $filtro.serialize(),
                            type: 'POST',
                            success: function(paquete) {
				$(id).html(paquete.cont);
                                $('#formulario_filtro-fecha').datepicker("setDate", fecha_seleccionada);
			    }
                        });
                        return false;
                    });
                                                      
                    $( "#formulario_filtro-fecha" ).datepicker( "destroy" );
                    $('#formulario_filtro-fecha').datepicker({
                            changeMonth: true,
                            changeYear: true,
                    });
                    $('#formulario_filtro-fecha').datepicker("setDate", new Date());
            }
        };
});