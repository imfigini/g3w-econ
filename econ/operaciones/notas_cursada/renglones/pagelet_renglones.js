kernel.renderer.registrar_pagelet('renglones', function(info) {
	var id = '#' + info.id;
	var fecha_inicio, fecha_fin;
	
	/*************************************************************************/
	// UTIL
	/*************************************************************************/
	function get_nota(row) {
		return $.trim(row.find('.nota').val());
	}

	function get_cond_nota(row) {
		var nota = get_nota(row);
		var cond = get_cond(row);

		if (nota == '') {
			if (cond == '1') {
				return 'L';
			}
			return '';
		}
//		var escala = _escalas.get_escala(id_escala)
//		escala.validar(nota);
		return info.escala[nota].r;
	}
	
	function get_res_nota(row) {
		var nota = get_nota(row);
		if (nota == '') {
			return '';
		}
		
		var result = info.escala[nota].r;
		
		if (result == "P" && (!info.prom_abierta || !row.hasClass('promocional'))){			
			result = "A";
		}
		
		return _resultados[result];
	}

	function get_cond(row) {
		return row.find('.condicion').find(':selected').val();
	}

	function get_res_cond(row) {
		var cond = get_cond(row);
		var esta_seteada = cond != '' && (typeof _condiciones[cond].resultado !== "undefined");
		var result = _condiciones[cond].resultado;
		if (result == "P" && (!info.prom_abierta || !row.hasClass('promocional'))){
			result = "A";
		}
		if (esta_seteada) {
			return _resultados[result];
		} else {
			return '';
		}
	}
	/*************************************************************************/
	// CHEQUEO DE ERRORES
	/*************************************************************************/
	/**
	 * Setea una condición válida para la nota actual
	 */
	function update_condicion(row) {
		var cond_nota = get_cond_nota(row);
		var condicion = false;
		//MOISES: actualización de resultado permite Regular de mas de 6 (limite de la promoción)
		var res_cond = row.find('.condicion').find(':selected').data('resultado');
		//var res_cond = get_cond(row);
	
		
		 if (cond_nota == "P" && (!info.prom_abierta || !row.hasClass('promocional') || res_cond == "A")){ 
		/* if (cond_nota == "P" && (!info.prom_abierta || !row.hasClass('promocional') )){ */
			//console.log('update_condicion: '+row.hasClass('promocional'));
			cond_nota = "A";
		}


		$.each(_condiciones, function() {
			if (this.resultado == cond_nota) {
				condicion = this;
				return false;
			}
		});

		if (condicion === false) { // no hay condición que matchee con la nota
			//console.log('error de matcheo de resultado de condición con resultado de nota');
		}

		row.find('.condicion').val(condicion.cond);
		
	}
	
	// Setea el resultado a partir de la nota
	function update_resultado_nota(row) {
		var res = get_res_nota(row);
		row.find('.resultado').html(res);
	}
	
	// Setea el resultado a partir de la condición
	function update_resultado_cond(row) {
        update_condicion(row);
		var res = get_res_cond(row);
		row.find('.resultado').html(res);
	}
	
	/**
	 * Una nota es válida si es vacía o si existe en el mapeo de la escala
	 */
	function chequeo_nota_valida(row) {
		var nota = get_nota(row);
		var valida = (nota == '') || (typeof info.escala[nota] !== "undefined");
		
		return valida;
	}

	function chequeo_consistencia(row) {
		var res_cond = row.find('.condicion').find(':selected').data('resultado');
		
		// Si la nota es promocionable la condición puede ser promoción o aprobado
		if (get_cond_nota(row) == "P" && (res_cond == "P" || res_cond == "A")){ 
			return true;
		} else {
			return res_cond == get_cond_nota(row);
		}
	}

	function chequeo_porcentaje_valido(row) {
		var valor = row.find('.asistencia').val();
		if ((valor == '')) {
			return true;
		}
		
		var porcentaje = parseFloat(valor);
		return (! isNaN(valor) && porcentaje >= 0 && porcentaje <= 100);
	}

	/**
	 * si la nota no es válida o no pasa el chequeo de consistencia (comparar 
	 * los resultados de la condición y la nota) se limpia el campo nota
	 */
	function control_condicion(row) {
		if (! chequeo_nota_valida(row) || ! chequeo_consistencia(row)) {
			var nota = row.find('.nota');
			//nota.val('');
			//$(id).plugin_renglones('remove_error', nota);
			//$(id).plugin_renglones('set_error', nota, info.mensajes.nota_no_coincide_condicion_promocion);
            if(nota.val() != ''){
                $("tr#"+id_renglon_before_change+" select.condicion").val(val_condicion_before_change);
                kernel.ui.show_mensaje(info.mensajes.nota_no_coincide_condicion_seleccionada, {tipo: 'alert-error'});
            }
		}
        else{
		    update_resultado_cond(row);
        }
	}

	function validar_renglon(row, tipo) {
		var valido = true;
		if (! chequeo_porcentaje_valido(row)) {
			$(id).plugin_renglones('set_error', row.find('.asistencia'), info.mensajes.asistencia_invalida);
			valido = false;
		} else {
			$(id).plugin_renglones('remove_error', row.find('.asistencia'));
		}

		if (! chequeo_nota_valida(row)) {
			$(id).plugin_renglones('set_error', row.find('.nota'), info.mensajes.nota_invalida);
			valido = false;
		} else { // si es válido hay que setear el resultado
			if (get_nota(row) != '') { // sólo cambia el resultado si la nota no es vacía
				//update_resultado_nota(row);
				update_resultado_cond(row);
			} //MOISES
			else{
				//nota vacia puede ser libre
				if (row.find('.condicion').find(':selected').data('resultado') == "L")
				{
					row.find('.resultado').html("Libre");
				}
				else {
					if (row.find('.condicion').find(':selected').data('resultado') == "U"){
						row.find('.resultado').html("Ausente");
					}else{
						row.find('.resultado').html("Sin nota");
					}
				}
			}
			//MOISES
			$(id).plugin_renglones('remove_error', row.find('.nota'));
		}

		// no coincide el resultado de la nota con el resultado de la condición
		if (valido && ! chequeo_consistencia(row)) {
			if (get_nota(row) != '') { // se actualiza la condición sólo si la nota no es vacía
				update_condicion(row);
			} //else{//MOISES
				// la nota es vacia puede ser LIBRE
			//	update_condicion(row);
			//}
		}

		return valido;
	}
	
	function validar_fecha(row) {
		var fecha = create_date(row.find('.fecha').val());
		//console.log(fecha);
		if (fecha.getTime() < fecha_inicio.getTime() || fecha_fin.getTime() < fecha.getTime()) {
			$(id).plugin_renglones('set_error', row.find('.fecha'), info.mensajes.fecha_invalida);	
		} else {
			$(id).plugin_renglones('remove_error', row.find('.fecha'));
		}
	}
	
	function validar_inicial(row) {
		if (get_nota(row) != '') { // si la nota no es vacía entonces se actualiza la condición
			//update_resultado_nota(row);
			//update_condicion(row);
		} else {
			//update_resultado_cond(row);
		}
	}
	
	function hay_cambios() {
		var cambiaron_datos = false;
		
		$(id).find('tbody tr').each(function() {
			$(this).find('input, select').each(function(){
				var campo = $(this);
				if (campo.val() != campo.attr('prev-value')){
					cambiaron_datos = true;
				}
			});
		});
		
		return cambiaron_datos;
	}
	
	function setup_autocomplete() {
		$('#notas_cursada_query').autocomplete({
			source: info.url_autocomplete,
			minLength: 1,
			search: function() {
				kernel.ui.show_loading();
			},
			open: function() {

			},
			response: function(event, ui) {
				kernel.ui.hide_loading();
				if (ui.content.length === 0) {
					kernel.ui.show_mensaje(info.mensajes.no_se_encontraron_alumnos, {tipo: 'alert-error'});
				}
			},
			select: function( event, ui ) {
				guarani.cambiar_op({
					href: ui.item.id
				});
			}
		});
			
		$('#notas_cursada_query').keypress(function(event){
			if(event.keyCode == 13){
				event.preventDefault();
			}
		})
	}
	
	function create_date(date_string) {
		var fecha_split = date_string.split('/');
		return new Date(fecha_split[2], fecha_split[1]-1, fecha_split[0]);
	}
	
	return {
		onload: function() {
			guarani.set_condicion_antes_de_navegar(hay_cambios, info.msj_navegacion);
			
			$(id).plugin_renglones({
				validadores: [{
						tipo: 'inicial',
						validar: validar_inicial
					},{
						tipo: ["fecha"],
						validar: validar_fecha
					},{
						tipo: ["nota", "asistencia", "condicion"],
						validar: validar_renglon
					}, {
						tipo: ["condicion"],
						validar: control_condicion
					}
				],
				
				highlight_renglon: info.highlight_renglon,
				
				success: function() {
					kernel.ui.show_mensaje(info.guardado_exitoso, {until_interaction: true});
				},
				error: function() {
					kernel.ui.show_mensaje(info.guardado_error, {tipo: 'alert-error'});
				}
			});
			
			fecha_inicio = create_date(info.fecha_inicio);
			fecha_fin = create_date(info.fecha_fin);
			setup_autocomplete();
			$('.btn-primary').focus();

            $(id).on('focus', 'select.condicion', function(){
                val_condicion_before_change = $(this).val();
                id_renglon_before_change = $(this).parents('tr').attr('id');
            });

		}
	}

})

function autocalcular()
{
	console.log('aaaaautocalcular');
	console.log(this);
}
