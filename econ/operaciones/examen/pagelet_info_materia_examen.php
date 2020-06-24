<?php

namespace econ\operaciones\examen;

use kernel\kernel;
use \siu\modelo\entidades\parametro;
use \siu\modelo\datos\catalogo;

class pagelet_info_materia_examen extends \siu\operaciones\examen\pagelet_info_materia_examen 
{
    protected function prepare_materia_seleccionada() {
        $materia = $this->get_materia();

        $this->data['llamados'] = $this->modelo()->info__lista_mesas($materia);
        
        foreach ($this->data['llamados'] as $key => $mesa) {
            if ($mesa['INSCRIBIR_COMO'] == 'T') {
                unset($this->data['llamados'][$key]);
                continue;
            }
            $this->data['llamados'][$key]['MATERIA'] = $materia;
            $this->data['llamados'][$key]['TIPO_INSCRIPCION_DESC'] = $this->get_desc_condicion($mesa['INSCRIBIR_COMO']);
            if ($this->existe_insc_mesa($mesa[catalogo::id])) {
                $this->data['llamados'][$key]['INSCRIPTO'] = true;
                $this->data['llamados'][$key]['ORDEN_INSC'] = $this->orden_inscripcion($mesa[catalogo::id]);
                $transaccion = $this->get_nro_transaccion($mesa[catalogo::id]);
                $this->data['llamados'][$key]['URL_COMP'] = kernel::vinculador()->crear('examen', 'generar_comprobante', array(
                    'materia' => $materia,
                    'transaccion' => $transaccion
                ));

                $this->data['llamados'][$key]['URL_MAIL_COMP'] = kernel::vinculador()->crear('examen', 'enviar_comprobante', array(
                    'materia' => $materia,
                    'transaccion' => $transaccion
                ));
            } else {
                $this->data['llamados'][$key]['INSCRIPTO'] = false;
            }
            $fecha = date_parse_from_format('Y-m-d', $mesa['FECHA_PRESTAMO_AULA']);
            $this->data['llamados'][$key]['FECHA_PRESTAMO_AULA'] = $fecha['day'] . '/' . $fecha['month'] . '/' . $fecha['year'];
        }
 
        $this->data['materia_nombre'] = $this->get_materia_nombre($materia);
        $this->data['materia'] = $materia;
        $this->data['url_baja'] = kernel::vinculador()->crear('examen', 'baja');
        $this->data['url_alta'] = kernel::vinculador()->crear('examen', 'inscribir');
        $this->data['param_insc_examen_seleccion_auto_tipo_insc'] = parametro::insc_examen_seleccion_auto_tipo_insc();
        $this->data['csrf'] = $this->generar_csrf();

        //Para la seccion de Terminos y Condiciones
        $path = dirname(__FILE__);
        $this->data['terminos_y_condiciones'] = file_get_contents($path.'/terminosYcondiciones.html');
    }

}

?>
