<?php
/**
 * Sistemas Especializados e Innovación Tecnológica, SA de CV
 * Mpsoft.FDW - Framework de Desarrollo Web para PHP
 *
 * v.1.0.0.0 - 2020-08-24
 */
namespace Mpsoft\STM\Dato;

use Exception;

abstract class ElementoSoloLectura extends \Mpsoft\STM\Dato\Elemento
{
    /**
     * Constructor del Elemento
     * @param ?int $id ID del Elemento o NULL para inicializar un Elemento nuevo.
     */
    public function __construct(?int $id = NULL)
    {
        parent::__construct($id);

        if(!$id) // Si no se proporciona ID (Elemento nuevo)
        {
            throw new Exception("No es posible inicializar elementos sólo lectura nuevos.");
        }

        $this->VincularEvento("AntesDeAplicarCambios", "ImpedirModificarElemento", function(){ $this->ImpedirModificarElemento(); });
        $this->VincularEvento("AntesDeEliminar", "ImpedirEliminarElemento", function(){ $this->ImpedirEliminarElemento(); });
    }

    /**
     * Obtiene el número de segundos que se bloqueará el Elemento al ejecutar el método Bloquear()
     */
    protected function ObtenerSegundosDeBloqueo():int
    {
        throw new Exception("Los Elementos solo de lectura no tienen tiempo de bloqueo.");

        return 0; // No hay bloqueos en los elementos sólo de lectura
    }

    protected function InicializarTokenBloqueante():?\Mpsoft\STM\Sesion\Token
    {
        throw new Exception("Los Elementos solo de lectura no tienen tiempo de bloqueo.");

        return NULL;
    }


        /**
     * Verifica si se tiene permiso para realizar la acción sobre el Elemento
     * @param int $accion Acción a verificar
     * @return boolean
     */
    protected function PedirAutorizacion(int $accion):bool
    {
        global $SESION;

        return $SESION->PedirAutorizacion($this->clase_actual::ObtenerNombrePermiso(), $accion);
    }

    /**
     * Procedimiento que elimina el Elemento. Este método será invocado por el Elemento al ejecutar el método Eliminar.
     * _Eliminar se ejecuta después de ejecutar el evento AntesDeEliminar y antes de ejecutar el evento DespuesDeEliminar
     */
    protected function _Eliminar():void
    {
        throw new Exception("No implementado");
    }

    private function ImpedirModificarElemento()
    {
        throw new Exception("No es posible modificar elementos solo de lectura.");
    }

    private function ImpedirEliminarElemento()
    {
        throw new Exception("No es posible eliminar elementos solo de lectura.");
    }
}