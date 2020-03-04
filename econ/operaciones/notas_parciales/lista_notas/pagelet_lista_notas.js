kernel.renderer.registrar_pagelet('lista_notas', function(info) {
    var id = '#' + info.id;
    return {
        onload: function() {
			$("#boton_pdf").on('click', function(){
				//console.log(info);
				var link = $(this).attr('href')+ '?eval_id='+info.evaluacion_id;
				console.log(link);
				window.open(link);
				return false;
			});

			$("#boton_excel").on('click', function(){
                    var link = $(this).attr('href')+ '?eval_id='+info.evaluacion_id;
                    console.log(link);
                    window.open(link);
                    return false;
                });
        }
    }
})
