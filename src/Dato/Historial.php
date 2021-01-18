<?php
/**
 * Sistemas Especializados e Innovación Tecnológica, SA de CV
 * Mpsoft.STM - Framework de Desarrollo Web para PHP
 *
 * v.1.0.0.0 - 2017-03-21
 */
namespace Mpsoft\STM\Dato;

use Mpsoft\FDW\Dato\ElementoException;

class Historial extends \Mpsoft\STM\Dato\ElementoSoloLectura
{
    /**
     * Constructor del Elemento
     * @param ?int $id ID del Elemento o NULL para inicializar un Elemento nuevo.
     */
    public function __construct(?int $id = NULL)
    {
        global $SESION;

        if(!$SESION->SesionIniciada()) // Si la sesión de usuario no está iniciada
        {
            throw new ElementoException("Se requiere de una sesión válida para poder inicializar STM_Dato_Historial", "STM_Dato_Historial");
        }

        parent::__construct($id);

        $this->VincularEvento("AntesDeAgregar", "AntesDeAgregar", function(){ $this->AntesDeAgregar(); });
        $this->VincularEvento("AntesDeModificar", "AntesDeModificar", function(){ $this->AntesDeModificar(); });
    }

    /**
     * Verifica si se tiene permiso para realizar la acción sobre el Elemento
     * @param int $accion Acción a verificar
     * @return boolean
     */
    protected function PedirAutorizacion(int $accion):bool
    {
        return TRUE;
    }

    protected function PuedeAccederAEsteElemento():void
    {
        throw new Exception("No es posible acceder al historial.");
    }

    /*
     * Obtiene el campo del Elemento que no es válido.
     * @return array Arreglo con la información del campo que no es válido. array( "campo" => campo, "error" => descripción del error ) o null si todos los campos son válidos.
     */
    public function ValidarDatos()
    {
        return null;
    }

    /**
     * Procedimiento que elimina el Elemento. Este método será invocado por el Elemento al ejecutar el método Eliminar.
     * _Eliminar se ejecuta después de ejecutar el evento AntesDeEliminar y antes de ejecutar el evento DespuesDeEliminar
     */
    protected function _Eliminar()
    {
        throw new ElementoException("No implementado");
    }

    private function AntesDeAgregar()
    {
        global $SESION;

        $this->AsignarValorSinValidacion("tiempo", time());
        $this->AsignarValorSinValidacion("usuario_id", $SESION->ObtenerUsuario()->ObtenerValor("id"));
    }

    private function AntesDeModificar()
    {
        throw new ElementoException("No implementado");
    }





    /**
     * Obtiene el nombre de la tabla en la base de datos utilizada por el Elemento
     * @return string
     */
    public static function ObtenerNombreTabla():string
    {
        return "historial";
    }

    /**
     * Retorna un arreglo asociativo cuyo índice es el "nombreSQL" del campo (nombre del campo en la tabla del Elemento) y apunta a un arreglo asociativos con la información del campo del Elemento
     * @return array Arreglo asociativo de arreglos asociativos con los siguientes índices:
     * "requerido"               (Opcional) true si el campo es requerido,
     * "soloDeLectura"           (Opcional) true si campo de es de solo lectura,
     * "nombre"			        (Opcional) nombre del campo para el usuario. Si no se especifica será igual al nombreSQL
     * "tipoDeDato"              (Opcional) tipo de dato del campo, se asume FDW_DATO_STRING si no se especifica.
     * "tamanoMaximo"		    (Opcional) si no es especifica se asume 0 (ilimitado) tamaño máximo del campo (aplica sólo para el tipo de datos FDW_DATO_STRING)
     *
     * "ignorarAlAgregar"	    (Opcional) si se especifica en true el campo no se incluirá al agregar el Elemento.
     * "ignorarAlModificar"      (Opcional) si se especifica en true el campo no se incluirá al modificar el Elemento.
     * "ignorarAlObtenerValores" (Opcional) si se especifica en true el campo no se incluirá al obtener los valores del Elemento.
     *
     *  Ejemplo "id" => array("requerido" => true, "soloDeLectura" => true, "nombre" => "ID", "tipoDeDato" => FDW_DATO_INT)
     *
     * NOTA: El Elemento debe contener el campo "id"
     */
    public static function ObtenerInformacionDeCampos():array
    {
        return array
            (
            "id" => array("requerido" => true, "soloDeLectura" => true, "nombre" => "ID", "tipoDeDato" => FDW_DATO_INT),
            "usuario_id" => array("requerido" => false, "soloDeLectura" => true, "nombre" => "ID de Usuario", "tipoDeDato" => FDW_DATO_INT), // Requeridos pero se auto-asignan
            "tiempo" => array("requerido" => false, "soloDeLectura" => true, "nombre" => "Tiempo", "tipoDeDato" => FDW_DATO_INT), // Requeridos pero se auto-asignan
            "elemento" => array("requerido" => true, "soloDeLectura" => false, "nombre" => "Elemento", "tipoDeDato" => FDW_DATO_INT),
            "accion" => array("requerido" => true, "soloDeLectura" => false, "nombre" => "Acción", "tipoDeDato" => FDW_DATO_INT),
            "info" => array("requerido" => false, "soloDeLectura" => false, "nombre" => "Información", "tipoDeDato" => FDW_DATO_STRING, "tamanoMaximo"=>25)
        );
    }

    public static function CrearEntrada($elemento, $accion, $info = NULL)
    {
        $historial = new Historial();
        $historial->AsignarValor("elemento", $elemento);
        $historial->AsignarValor("accion", $accion);
        $historial->AsignarValor("info", $info);

        $historial->AplicarCambios();
    }
}
