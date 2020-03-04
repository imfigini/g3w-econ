kernel.renderer.registrar_pagelet('comision', function(info) {
    var id = '#' + info.id;
    return {
        onload: function() {
			$("#boton_pdf").on('click', function(){
				//console.log(info);
				var link = $(this).attr('href')+ '?comision_hash='+info.comision_hash
                                            + '&anio_academico_hash='+info.anio_academico_hash
                                            + '&periodo_hash='+info.periodo_hash;
                                            
				console.log(link);
				window.open(link);
				return false;
			});

			$("#boton_excel").on('click', function(){
					var link = $(this).attr('href')+ '?comision_hash='+info.comision_hash
											+ '&anio_academico_hash='+info.anio_academico_hash
											+ '&periodo_hash='+info.periodo_hash;
                    console.log(link);
                    window.open(link);
                    return false;
                });
        }
    }
})
