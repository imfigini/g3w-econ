kernel.renderer.registrar_pagelet('autocalcular', function(info) {
    var id = '#' + info.id;
	// var campos_undo = {
	// 	tipo: '',
	// 	campos: null
	// };
	
	// function set_undo(tipo, campos) {
	// 	campos_undo.tipo = tipo;
	// 	campos_undo.campos = campos.clone();
	// 	$('#preset_undo').removeAttr('disabled');
	// }
	
    
    function msj_error_mostrar(texto){
        $('#msj-autoerror').text(texto);
        $('#msj-autoerror').show();
    }
    
    function msj_error_ocultar(){
        $('#msj-autoerror').hide();
    }
    
    return {
        onload: function() {
			// $('#precalcular_form #autofecha').datepicker();
			
			// $('#precalcular_form .preset_valor').focus(function(){
			// 	msj_error_ocultar();
			// });
			
			$('#precalcular_form').submit(function(e) {
				e.preventDefault();
				//autocalcular = true;
				console.log('Inicia autocalcular...');
				let legajos = $( "table .legajo" );
				let notas = $("table .nota");
				let asistencias = $("table .asistencia");
				let fechas = $("table .fecha");
				var solo_vacio = $('#preset_solo_vacio_autocalcular').is(':checked');

				let cant = legajos.length;
				for (var i=0; i<cant; i++)
				{
					if (notas[i].value == '' || !solo_vacio)
					{
						actualizarNota(i, legajos, notas, asistencias, fechas);		
					}
				}
				
				//autocalcular = false;
				//actualizarNota(2, legajos, notas, condiciones, resultados, asistencias);
				
				// var val = $.trim($(this).find('.preset_valor:visible').val());
				// if (val === '') {
				// 	return false;
				// }
				// $(this).find('.preset_valor:visible').val('');
				
				// // chequeo si el valor es una nota valida
				// if (campo === 'nota' && typeof info.escala[val] === "undefined"){
				// 	return false;
				// }
				
				// // chequeo si el valor es una asistencia valida				
				// if (campo === 'asistencia' && !asistencia_valida(val)){
				// 	return false;
				// }
                
				// // chequeo si el valor es una fecha valida
				// if (campo === 'fecha' && !fecha_valida(val)){
				// 	return false;
				// }
				
                
				// var campos;
				// campos = $('#renglones').find('.' + campo+'[disabled!="disabled"]');
				// console.log(campos);
				// set_undo(campo, campos);
				
				// var solo_vacio = $('#preset_solo_vacio').is(':checked');
				// campos.filter(function() {
				// 	return (solo_vacio) ? $(this).val() === '' : true;
				// }).val(val).change().keyup();
			});
			
			
            
			
            
			// $('#preset_campo').change(function() {
            //     msj_error_ocultar();
			// 	$('#precalcular_form').find('.preset_valor').val('');
            //     $('#precalcular_form').find('.preset_valor').hide();
            //     $('#precalcular_form').find('.preset_label').hide();
                
            //     if ($(this).val() === 'fecha') {
			// 		$('#precalcular_form').find('#autofecha').show();
			// 		$('#precalcular_form').find('#lfecha').show();
			// 	} else if ($(this).val() === 'nota') {
            //             $('#precalcular_form').find('#autonota').show();
            //             $('#precalcular_form').find('#lnota').show();
            //     } else if ($(this).val() === 'asistencia') {
            //             $('#precalcular_form').find('#autonota').show();
            //             $('#precalcular_form').find('#lasistencia').show();
            //     } else if ($(this).val() === 'condicion') {
            //             $('#precalcular_form').find('#autocondicion').show();
            //             $('#precalcular_form').find('#lcondicion').show();
            //     }
			// });
			
			// $('#preset_undo').click(function() {
            //     msj_error_ocultar();
			// 	$('#renglones').find('.' + campos_undo.tipo).each(function(i) {
			// 		$(this).val($(campos_undo.campos[i]).val()).keyup().change();
			// 	});
			// 	$('#renglones').attr('disabled', 'disabled');
			// });
			
// 			$('#precalcular_form .preset_valor').on('change',function(){
// 				var campo = $('#preset_campo').val();
// 				var val = $(this).val();
                
//                 var msg_arriba;
// 				if (campo === 'nota' && typeof info.escala[val] === "undefined"){
// 					$(this).val('');
//                     msj_error_mostrar(info.nota_invalida);
                    
// //                    msg_arriba = $('<div role="alert" class="alert alert alert-error" style="width: auto; float:left; margin:0;">'+info.nota_invalida+'<button type="button" class="close" data-dismiss="alert">x</button></div>');
// //					$(id).append(msg_arriba);
// //					msg_arriba.position({
// //						my: 'top',
// //						at: 'top center',
// //						offset: '0 0',
// //						of: $(id)
// //					}).show();
// 				}
				
// 				if (campo === 'asistencia' && !asistencia_valida(val)){
// 					$(this).val('');
                    
//                     msj_error_mostrar(info.asistencia_invalida);
// //                    msg_arriba = $('<div role="alert" class="alert alert alert-error" style="width: auto; float:left; margin:0;">'+info.asistencia_invalida+'<button type="button" class="close" data-dismiss="alert">x</button></div>');
// //					$(id).append(msg_arriba);
// //                    msg_arriba.position({
// //						my: 'top',
// //						at: 'top center',
// //						offset: '0 0',
// //						of: $(id)
// //					}).show();
// 				}
                
// 				if (campo === 'fecha' && !fecha_valida(val)){
// 					$(this).val('');
                    
//                     msj_error_mostrar(info.fecha_invalida);
// //                    msg_arriba = $('<div role="alert" class="alert alert alert-error" style="width: auto; float:left; margin:0;">'+info.fecha_invalida+'<button type="button" class="close" data-dismiss="alert">x</button></div>');
// //					$(id).append(msg_arriba);
// //                    msg_arriba.position({
// //						my: 'top',
// //						at: 'top center',
// //						offset: '0 0',
// //						of: $(id)
// //					}).show();
// 				}
// 			});
 		}
	}

	function actualizarNota(posicion, legajos, notas, asistencias, fechas)
	{
		let legajo = legajos[posicion].innerHTML;
		let comision = $('#comision_id').val();

		$.ajax({
			url: info.url_autocalcular,
			dataType: 'json',
			data: {comision: comision, legajo: legajo},
			type: 'get',
			success: function(data) {
			
				var i = posicion + 1;
				var renglon = $('#renglon_'+i);
				var texto = ''; 
				
				switch (data.estado) 
				{
					case 'listo': 		setear_datos(data, posicion, notas, asistencias, fechas);
										renglon.addClass('listo'); 
										//texto = decodeURIComponent(escape("El alumno no tiene más instancias para rendir"));
										texto = "El alumno no tiene más instancias para rendir";
										break;
					case 'abandono': 	setear_datos(data, posicion, notas, asistencias, fechas);
										renglon.addClass('abandono'); 
										//texto = decodeURIComponent(escape('El alumno abandonó.'));
										texto = "El alumno abandonó.";
										break;
					case 'va_recup': 	renglon.addClass("va-recup"); 
										//texto = decodeURIComponent(escape('El alumno aún puede rendir el Recuperatorio Global'));
										texto = "El alumno aún puede rendir el Recuperatorio Global";
										break;
					case 'va_integ': 	renglon.addClass("va-integ"); 
										//texto = decodeURIComponent(escape('El alumno aún puede rendir el Integrador'));
										texto = "El alumno aún puede rendir el Integrador";
										break;
				}
				renglon.prop('title', texto);
			}
		});

		function setear_datos(data, posicion, notas, asistencias, fechas)
		{
			var hoy = new Date();
			var fecha = hoy.getDate() + "/" + (hoy.getMonth() +1) + "/" + hoy.getFullYear();
			if (!fecha_valida(fecha)) {
				msj_error_mostrar(info.fecha_invalida);
			}
			if (!asistencia_valida(data.asistencia)){
				msj_error_mostrar(info.asistencia_invalida);
			}
			notas[posicion].value = data.nota;
			notas.change();
			asistencias[posicion].value = data.asistencia;
			fechas[posicion].value = fecha;
		}

		function fecha_valida(fecha)
		{
			fecha_inicio = create_date(info.fecha_inicio);
			fecha_fin = create_date(info.fecha_fin);
			actual = create_date(fecha);
			if (actual != false && actual >= fecha_inicio && actual <= fecha_fin){
				return true;
			}
			return false;
		}
		
		function asistencia_valida(val)
		{
			return true;
			//return ($.isNumeric(val) && val == parseInt(val, 10) && val > 0);
		}

		function create_date(date_string) 
		{
			var fecha_split = date_string.split('/');                
			//var parse = Date.parse(fecha_split[1]-1+'/'+fecha_split[0]+'/'+fecha_split[2]);
			// if (isNaN(parse)){
			// 	return false;
			// }
			var fecha;
			fecha = new Date(fecha_split[2], fecha_split[1]-1, fecha_split[0]);        
			return fecha;
		}

	}

})



//var autocalcular = false; 
