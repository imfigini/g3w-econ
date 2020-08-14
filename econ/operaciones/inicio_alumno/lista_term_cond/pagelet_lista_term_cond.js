kernel.renderer.registrar_pagelet('lista_term_cond', function(info) {
    var id = '#' + info.id;

    return {
        onload: function() {

            var term = $('#acepto_term_cond').val();
            seleccionar(term);

            $('#check_terminos').click(function() {
                var val = $('#check_terminos').val();
                console.log(val);
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
            $('#terminos').hide();
            $('#ya_acepto').show(500);
        }
        else {
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
                if (data.cont) {
                    kernel.ui.show_mensaje('Fue registrada su aceptacion de terminos y condiciones', {tipo: 'alert-info'});
                } else {
                    kernel.ui.show_mensaje('Ocurrio un error al grabar. Comuniquese con la Direccion de Alumnos', {tipo: 'alert-error'});
                }
            },
        });
    }
})