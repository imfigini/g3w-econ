kernel.renderer.registrar_pagelet('autocalcular', function(info) {
    var id = '#' + info.id;
    
    function msj_error_mostrar(texto){
        $('#msj-autoerror').text(texto);
        $('#msj-autoerror').show();
    }
    
    function msj_error_ocultar(){
        $('#msj-autoerror').hide();
	}
	
    return {
        onload: function() {

			document.getElementById("btn_autocalcular").addEventListener("click", function(e) {
				e.preventDefault();
				var legajos = $("table .legajo");
				var notas = $("table .nota");
				var asistencias = $("table .asistencia");
				var fechas = $("table .fecha");
				var condiciones = $("table .condicion");
				var solo_vacio = $('#preset_solo_vacio_autocalcular').is(':checked');

				var cant = legajos.length;
				for (var i=0; i<cant; i++)
				{
					if (notas[i].value == '' || !solo_vacio)
					{
						actualizarNota(i, legajos, notas, asistencias, fechas, condiciones);		
					}
				}
			});
		 }
	}

	function actualizarNota(posicion, legajos, notas, asistencias, fechas, condiciones)
	{
		var legajo = legajos[posicion].innerHTML;
		var comision = $('#comision_id').val();

		$.ajax({
			url: info.url_autocalcular,
			dataType: 'json',
			data: {comision: comision, legajo: legajo},
			type: 'get',
			success: function(data) {
			
				var i = posicion + 1;
				var renglon = $('#renglon_'+i);
				var texto = ''; 
				
				if (data) {
					switch (data.estado) 
					{
						case 'listo': 		setear_datos(data, posicion, notas, asistencias, fechas, condiciones);
											renglon.addClass('listo'); 
											texto = decodeURIComponent(escape("El alumno no tiene mas instancias para rendir"));
											break;
						case 'abandono': 	setear_datos(data, posicion, notas, asistencias, fechas, condiciones);
											renglon.addClass('abandono'); 
											texto = decodeURIComponent(escape('El alumno abandono.'));
											break;
						case 'va_recup': 	vaciar_datos(posicion, notas, asistencias, fechas, condiciones);
											renglon.addClass("va-recup"); 
											texto = decodeURIComponent(escape('El alumno aun puede rendir el Recuperatorio Global'));
											break;
						case 'va_integ': 	vaciar_datos(posicion, notas, asistencias, fechas, condiciones);
											renglon.addClass("va-integ"); 
											texto = decodeURIComponent(escape('El alumno aun puede rendir el Integrador'));
											break;
						case 'falta_tp': 	vaciar_datos(posicion, notas, asistencias, fechas, condiciones);
											renglon.addClass("falta-tp"); 
											texto = decodeURIComponent(escape('Falta la nota de los TP para poder calcular la nota de cursada'));
											break;
						case 'falta_ponderacion': var msg = 'No se pueden autocalcular las notas debido a que no están definidas las ponderaciones para nota final de cierre de cursada. Por favor informe a la Dirección de Docentes.';
											kernel.ui.show_mensaje(msg, {tipo: 'alert-error'});
											break;
						default : 	texto = decodeURIComponent(escape('No se pudo calcular la nota.'));
					}
					renglon.prop('title', texto);
				}
			}
			
		});
	}

	function setear_datos(data, posicion, notas, asistencias, fechas, condiciones)
	{
		var hoy = new Date();
		var fecha = format_fecha(hoy);
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
		condiciones[posicion].value = data.condicion;

	}

	function vaciar_datos(posicion, notas, asistencias, fechas, condiciones)
	{
		notas[posicion].value = '';
		notas.change();
		asistencias[posicion].value = '';
		fechas[posicion].value = '';
		var condiciones = $("table .condicion");
		condiciones[posicion].value = '';
	}

	function format_fecha(fecha) 
	{
		var d = fecha.getDate();
		var m = fecha.getMonth()+1;
		var yyyy = fecha.getFullYear();
		var dd = (d < 10 ? '0':'' ) + d;
		var mm = (m < 10 ? '0':'' ) + m;
		return dd + "/" + mm + "/" + yyyy;
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
		return ($.isNumeric(val) && val == parseInt(val, 10) && val > 0);
	}

	function create_date(date_string) 
	{
		var fecha_split = date_string.split('/');                
		var parse = Date.parse(fecha_split[1]-1+'/'+fecha_split[0]+'/'+fecha_split[2]);
		if (isNaN(parse)){
			return false;
		}
		var fecha;
		fecha = new Date(fecha_split[2], fecha_split[1]-1, fecha_split[0]);        
		return fecha;
	}

})
