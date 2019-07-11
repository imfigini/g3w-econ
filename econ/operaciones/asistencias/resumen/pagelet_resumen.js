kernel.renderer.registrar_pagelet('resumen', function(info) {
	var id = '#' + info.id;

	return {
            onload: function() {
//              $('#boton_pdf').hide();

                $("#boton_pdf").on('click', function(){
                    var link = $(this).attr('href')+ '?comisiones='+info.comisiones;
                    console.log(link);
                    window.open(link);
                    return false;
                });

               // init();
            }
        };
        
    
//    function init()
//    {
//        var asistencias = $('#asistencias').val();
//        console.log(asistencias);
//    }

});