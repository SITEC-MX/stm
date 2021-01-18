<?php
/**
 * Sistemas Especializados e Innovaci�n Tecnol�gica, SA de CV
 * Mpsoft.STM - Framework de Desarrollo Web para PHP
 *
 * v.2.0.0.0 - 2017-05-17
 */
namespace Mpsoft\STM\Sesion;

class UsuarioRoles extends \Mpsoft\FDW\Dato\Modulo
{
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
            "rol_id" => array("requerido" => true, "soloDeLectura" => true, "nombre" => "ID del Rol", "tipoDeDato" => FDW_DATO_INT),

            "usuario_nombre" => array("requerido" => true, "soloDeLectura" => true, "nombre" => "Nombre del Usuario", "tipoDeDato" => FDW_DATO_STRING, "tamanoMaximo"=>200, "identificadorJoin"=>"u", "campoExterno"=>"nombre"),
            "rol_nombre" => array("requerido" => true, "soloDeLectura" => true, "nombre" => "Nombre del Rol", "tipoDeDato" => FDW_DATO_STRING, "tamanoMaximo"=>200, "identificadorJoin"=>"r", "campoExterno"=>"nombre")
        );
    }

    /*
     * Obtiene un array asociativo de arrays asociativos con la informaci�n de los joins realizados en el M�dulo. array( identificador => array( "tipo", "tabla", "campoInterno", "campoExterno" )  )
     * @return array Retorna un array de arrays asociativos con la informaci�n de los joins realizados en el M�dulo. Retorna un array vac�o si no hay joins. array( identificador => array( "tipo", "tabla", "campoInterno", "campoExterno" )  )
     */
    public static function ObtenerJoins():?array
    {
        return array
            (
                "u" => array("tipo"=>"INNER", "tabla"=>Usuario::ObtenerNombreTabla(), "campoInterno" => "usuario_id", "campoExterno"=>"id"),
                "r" => array("tipo"=>"INNER", "tabla"=>Rol::ObtenerNombreTabla(), "campoInterno" => "rol_id", "campoExterno"=>"id")
            );
    }

    /**
     * Obtiene un arreglo de strings con los campos predeterminados del Elemento. Ser�n utilizado s�lo en caso que no se especifiquen.
     * @return array
     */
    public static function ObtenerCamposPredeterminados():array
    {
        return array("id");
    }

    /**
     * Obtiene el nombre del permiso del Modulo
     */
    public static function ObtenerNombrePermiso():string
    {
        return "Rol";
    }
}