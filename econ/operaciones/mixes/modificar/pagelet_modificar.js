kernel.renderer.registrar_pagelet('modificar', function(info) {
	var id = '#' + info.id;

	return {
		onload: function() {
			//Para que despliegue u oculte la información de las comisiones de cada materia. 
                        $(id).delegate(".link-js", "click", function() {
                                    $(this).find('.toggle').toggleClass(function(){
                                            if ($(this).is('.icon-chevron-up')) {
                                                    return 'icon-chevron-down';
                                            } else {
                                                    return 'icon-chevron-up';
                                            }
                                    });
                                    $("#"+$(this).attr('data-link')).toggle();
                                    return false;
                            });
		}
	};
});