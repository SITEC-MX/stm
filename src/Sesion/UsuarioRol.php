<?php
/**
 * Sistemas Especializados e Innovaci�n Tecnol�gica, SA de CV
 * Mpsoft.STM - Framework de Desarrollo Web para PHP
 *
 * v.1.0.0.0 - 2017-05-17
 */
namespace Mpsoft\STM\Sesion;

class UsuarioRol extends \Mpsoft\STM\Dato\ElementoFDW
{
    protected function PuedeAccederAEsteElemento():void
    {
    }
    
    /*
     * Obtiene el campo del Elemento que no es v�lido.
     * @return array Arreglo con la informaci�n del campo que no es v�lido. array( "campo" => campo, "error" => descripci�n del error ) o null si todos los campos son v�lidos.
     */
    public function ValidarDatos():?array
    {
        return null;
    }


    /**
     * Verifica si se tiene permiso para realizar la acci�n sobre el Elemento
     * @param int $accion Acci�n a verificar
     * @return boolean
     */
    protected function PedirAutorizacion(int $accion):bool
    {
        global $SESION;

        return $SESION->PedirAutorizacion(UsuarioRol::ObtenerNombrePermiso(), $accion);
    }




    /**
     * Inicializa el gestor de base de datos utilizado por el Elemento
     * @return \Mpsoft\FDW\Dato\BdD
     */
    public static function InicializarBaseDeDatos():\Mpsoft\FDW\Dato\BdD
    {
        global $BDD;

        return $BDD;
    }

    /**
     * Obtiene el nombre de la tabla en la base de datos utilizada por el Elemento
     * @return string
     */
    public static function ObtenerNombreTabla():string
    {
        return "usuario_rol";
    }

    /**
     * Retorna un arreglo asociativo cuyo �ndice es el "nombreSQL" del campo (nombre del campo en la tabla del Elemento) y apunta a un arreglo asociativos con la informaci�n del campo del Elemento
     * @return array Arreglo asociativo de arreglos asociativos con los siguientes �ndices:
     * "requerido"               (Opcional) true si el campo es requerido,
     * "soloDeLectura"           (Opcional) true si campo de es de solo lectura,
     * "nombre"			        (Opcional) nombre del campo para el usuario. Si no se especifica ser� igual al nombreSQL
     * "tipoDeDato"              (Opcional) tipo de dato del campo, se asume FDW_DATO_STRING si no se especifica.
     * "tamanoMaximo"		    (Opcional) si no es especifica se asume 0 (ilimitado) tama�o m�ximo del campo (aplica s�lo para el tipo de datos FDW_DATO_STRING)
     *
     * "ignorarAlAgregar"	    (Opcional) si se especifica en true el campo no se incluir� al agregar el Elemento.
     * "ignorarAlModificar"      (Opcional) si se especifica en true el campo no se incluir� al modificar el Elemento.
     * "ignorarAlObtenerValores" (Opcional) si se especifica en true el campo no se incluir� al obtener los valores del Elemento.
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
            "usuario_id" => array("requerido" => true, "soloDeLectura" => true, "nombre" => "ID del Usuario", "tipoDeDato" => FDW_DATO_INT),
            "rol_id" => array("requerido" => true, "soloDeLectura" => true, "nombre" => "ID del Rol", "tipoDeDato" => FDW_DATO_INT)
        );
    }

    /**
     * Obtiene el nombre del permiso del Modulo
     */
    public static function ObtenerNombrePermiso():string
    {
        return "Rol";
    }
}