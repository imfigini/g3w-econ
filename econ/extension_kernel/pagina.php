<?php
namespace econ\extension_kernel;
use kernel\kernel;
class pagina extends \siu\extension_kernel\pagina
{

    /**
     * @return menu
     */
    protected function get_menu_desde_conf()
    {
        /**
         * @var $menu menu
         */
        $menu = kernel::persona()->perfil()->get_menu();
        
        switch (kernel::persona()->perfil()->get_id()) { // reordenamos en base al perfil activo
            case 'DOC':
                $menu->set_orden('agenda_cursadas', 0);
                $menu->set_orden('inscriptos_cursadas', 1);
                $menu->set_orden('asistencias', 2);
                $menu->set_orden('notas_parciales', 3);
				$menu->set_orden('notas_cursada', 4);
				$menu->set_orden('resumen_cursada', 5);
                $menu->set_orden('acta_cursadas', 6);
                $menu->set_orden('acta_promociones', 7);
                $menu->set_orden('agenda_examenes', 0);
                $menu->set_orden('inscriptos_examenes', 1);
                $menu->set_orden('notas_examen', 2);
                $menu->set_orden('acta_examenes', 3);
                break;
        }
        $menu->reordenar();
        return $menu;
    }


}
?>
