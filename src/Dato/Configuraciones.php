<?php
/**
 * Sistemas Especializados e Innovación Tecnológica, SA de CV
 * Mpsoft.STM - Framework de Desarrollo Web para PHP
 *
 * v.2.0.0.0 - 2018-03-02
 */
namespace Mpsoft\STM\Dato;

class Configuraciones extends \Mpsoft\STM\Dato\ModuloFDW
{
    /**
     * Obtiene el nombre de la tabla en la base de datos utilizada por el Elemento
     * @return string
     */
    public static function ObtenerNombreTabla():string
    {
        return "configuracion";
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
            "usuario_id" => array("requerido" => false, "soloDeLectura" => true, "nombre" => "ID del Usuario", "tipoDeDato" => FDW_DATO_INT),
            "nombre" => array("requerido" => true, "soloDeLectura" => false, "nombre" => "Nombre de la configuración", "tipoDeDato" => FDW_DATO_STRING, "tamanoMaximo"=>50),
            "valor" => array("requerido" => true, "soloDeLectura" => true, "nombre" => "Valor de la configuración", "tipoDeDato" => FDW_DATO_STRING, "tamanoMaximo"=>65535),
            "forzar" => array("requerido" => true, "soloDeLectura" => false, "nombre" => "Forzar", "tipoDeDato" => FDW_DATO_BOOL)
        );
    }

    /*
     * Obtiene un array asociativo de arrays asociativos con la información de los joins realizados en el Módulo. array( identificador => array( "tipo", "tabla", "campoInterno", "campoExterno" )  )
     * @return array Retorna un array de arrays asociativos con la información de los joins realizados en el Módulo. Retorna un array vacío si no hay joins. array( identificador => array( "tipo", "tabla", "campoInterno", "campoExterno" )  )
     */
    public static function ObtenerJoins():?array
    {
        return array();
    }

    /**
     * Obtiene un arreglo de strings con los campos predeterminados del Elemento. Serán utilizado sólo en caso que no se especifiquen.
     * @return array
     */
    public static function ObtenerCamposPredeterminados():array
    {
        return array("id", "nombre", "valor");
    }

    /**
     * Obtiene el nombre del permiso del Elemento
     */
    public static function ObtenerNombrePermiso():string
    {
        return "Libre";
    }
}
