<?php
namespace econ\operaciones\fecha_examen\filtro;

use kernel\interfaz\componentes\forms\form_elemento_config;
use kernel\kernel;
use kernel\util\validador;
use \siu\extension_kernel\formularios\builder_formulario;
use \siu\extension_kernel\formularios\fabrica_formularios;
use \siu\extension_kernel\formularios\guarani_form;
use siu\modelo\datos\catalogo;
use siu\extension_kernel\formularios\guarani_form_elemento;
use siu\operaciones\fecha_examen\controlador;

class builder_form_filtro extends \siu\operaciones\fecha_examen\filtro\builder_form_filtro
{
    function get_planes()
    {
        $formulario_filtro = kernel::request()->get('formulario_filtro');
        $carrera = "";
        if($formulario_filtro['carrera'] != "") $carrera = $this->get_controlador()->decodificar_carrera($formulario_filtro['carrera']);

        if($carrera){
            $planes_todos = catalogo::consultar('plan_estudios', 'planes', array('carrera' => $carrera));
            //Iris: Se decartan planes 1992 y 2002 por pedido de Chelo
            $planes_activos = Array();
            foreach($planes_todos as $plan) {
                if ($plan['PLAN'] != '1992' && $plan['PLAN'] != '2002') {
                    $planes_activos[] = $plan;
                }
            }
            $datos = $planes_activos;
        
            return guarani_form_elemento::armar_combo_opciones($datos, '_ID_', 'PLAN', true, false, ucfirst(kernel::traductor()->trans('fecha_examen.filtro_todos')));
        }
        else{
            return array('' => ucfirst(kernel::traductor()->trans('fecha_examen.filtro_todos')));
        }

    }

}