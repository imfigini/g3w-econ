kernel.renderer.registrar_pagelet('filtro', function (info) {
	var id = '#' + info.id;
	var $filtro;

	return {
		onload: function () {

			$filtro = $('form').formulario();

            if(($("#formulario_filtro-materia_descr").val() != "") && ($("#formulario_filtro-materia").val() != "")){
                set_solo_lectura("formulario_filtro-materia_descr", true);
            }
            else{
                $("#formulario_filtro-materia_descr").val("");
                $("#formulario_filtro-materia").val("");
            }

            $("#formulario_filtro-carrera").on("change", function(){

                var $elem_planes = $('#formulario_filtro-plan');
                $elem_planes.children().remove();

                $elem_planes.append(
                    $('<option></option>').val("").html(info.mensajes.filtro_todos)
                );

                if($(this).val() != ""){

                    kernel.ajax.call(info.url_buscar_planes, {
                        type: 'GET',
                        dataType: 'json',
                        data: {
                            carrera: $(this).val()
                        },
                        success: function(data) {

                            $.each(data.planes, function(key, value) {
                                $elem_planes.append(
                                    $('<option></option>').val(value['_ID_']).html(value['PLAN'])
                                );
                            });

                            $elem_planes.focus();

                        }
                    });

                    limpiar_materia();

                }
            });   

            $("#formulario_filtro-plan").on("change", function(){

                if($(this).val() != ""){
                    limpiar_materia();
                    $('#formulario_filtro-anio_cursada').focus();
                }
                else if(($("#formulario_filtro-materia").val() == "") && ($("#formulario_filtro-materia_descr").val() == "")){
                    $('#formulario_filtro-anio_cursada').focus();
                }

            });

            $("#formulario_filtro-anio_cursada").on("change", function(){

                $('#formulario_filtro-materia_decr').focus();
            });

            $("#formulario_filtro-materia_descr").autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: info.url_buscar_materias,
                        dataType: "json",
                        data: {
                            term : request.term,
                            carrera : $('#formulario_filtro-carrera').val(),
                            plan : $('#formulario_filtro-plan').val(),
                            anio_cursada : $('#formulario_filtro-anio_cursada').val()
                            
                        },
                        success: function(data) {
                            response(data);
                        }
                    });
                },
                appendTo: id,
                minLength: 2,
                select: materia_seleccionada,
                focus: materia_foco,
                change: function( event, ui ) {
                    if($("#formulario_filtro-materia").val()==''){
                        $("#formulario_filtro-materia_descr").val("");
                    }
                },
                search: function( event, ui ) {
                    kernel.ui.show_loading();
                },
                response: function( event, ui ) {
                    kernel.ui.hide_loading();
                }

            });

            $('#limpiar_materia').on('click', function () {
                limpiar_materia();
                $('#formulario_filtro-materia_descr').focus();
            });

            $("#formulario_filtro").on("submit", function(event){
                event.preventDefault();
                filtar();
            });

            $(id).on('click', 'a.ver_mas_info', function(e){
                delegate_ver_mas_info($(this));
                e.preventDefault();
                return false;
            });

            $(id).on('keypress', 'a.ver_mas_info', function(e){
                if(e.keyCode == 13) {
                    $(this).trigger('click');
                }
            });

		}
	};

    function limpiar_materia(){
        $('#formulario_filtro-materia').val('');
        $('#formulario_filtro-materia_descr').val('');
        set_solo_lectura("formulario_filtro-materia_descr", false);
    }

    function materia_seleccionada(event, ui)
    {
        var id = ui.item.id;
        var label = ui.item.label;

        $("#formulario_filtro-materia").val(id);
        $("#formulario_filtro-materia_descr").val(label);
        set_solo_lectura("formulario_filtro-materia_descr", true);
        event.preventDefault();
    }

    function materia_foco(event)
    {
        event.preventDefault();
    }

    function set_solo_lectura(elemId, solo_lectura) {
        if (solo_lectura) {
            $('#'+elemId).attr('readonly', 'readonly');
        } else {
            $('#'+elemId).removeAttr("readonly");
        }
    }

    function delegate_ver_mas_info($link) {

        if ($link.data('estado') == 'v') {

            $('#'+$link.data('id')).slideDown('slow', function(){
                $link.text(info.ocultar);
                $link.data('estado', 'o');
            });

        } else {

            $('#'+$link.data('id')).slideUp('slow', function(){
                $link.text(info.ver);
                $link.data('estado', 'v');
            });

        }
    }

    function filtar(){

        kernel.ajax.load(info.url_buscar + '?' + $filtro.find("select, input[id!=formulario_filtro-materia_descr]").serialize(), id, {
            historia: true,
            type: 'get',
            show_loading: true,
            forzar_cambio_op: true
        });

        return false;
    }
});