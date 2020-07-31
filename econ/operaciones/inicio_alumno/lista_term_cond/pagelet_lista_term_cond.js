kernel.renderer.registrar_pagelet('lista_term_cond', function(info) {
    var id = '#' + info.id;

    return {
        onload: function() {

            var term = $('#acepto_term_cond').val();
            seleccionar(term);

            $(id).delegate(".check-js", "click", function() {
                var val = $(id).context.activeElement.value;
                if (val == 'off') {
                    $('#check_terminos').val('on');
                    console.log('grabar aceptacion');
                    grabar_aceptacion();
                    seleccionar(true);
                }
            });

        }

    }
    function seleccionar(ya_acepto)
    {
        if (ya_acepto) {
            // console.log('entro por if');
            // console.log(ya_acepto);
            $('#terminos').hide();
            $('#ya_acepto').show(500);
        }
        else {
            //console.log('entro por else');
            $('#terminos').show(500);
            $('#ya_acepto').hide();
        }
    }


    function grabar_aceptacion() 
    {
        $.ajax({
            url: info.url_grabar_acept_term_cond,
            dataType: 'json',
            data: {},
            type: 'get',
            success: function(data) {
                console.log(data.cont['resultado']);
                console.log(data.cont['mensaje']);
                kernel.ui.show_mensaje('Fue registrada su aceptacion de terminos y condiciones', {tipo: 'alert-info'});
            },
            error: function(response) {
                console.log('Fallo');
                console.log(response);
                kernel.ui.show_mensaje(response.msj, {tipo: 'alert-error'});
            },
        });
    }
})