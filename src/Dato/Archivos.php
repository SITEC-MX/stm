<?php
/**
 * Sistemas Especializados e Innovación Tecnológica, SA de CV
 * Mpsoft.STM - Framework de Desarrollo Web para PHP
 *
 * v.1.0.0.0 - 2017-02-19
 */
namespace Mpsoft\STM\Dato;

use \Exception;

class Archivos extends \Mpsoft\STM\Dato\ModuloFDW
{
    /**
     * Obtiene el nombre de la tabla en la base de datos utilizada por el Elemento
     * @return string
     */
    public static function ObtenerNombreTabla():string
    {
        return "archivo";
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
            "ruta" => array("requerido" => true, "soloDeLectura" => true, "nombre" => "Ruta", "tipoDeDato" => FDW_DATO_STRING, "tamanoMaximo"=>1024),
            "tipo" => array("requerido" => true, "soloDeLectura" => true, "nombre" => "Tipo de Elemento", "tipoDeDato" => FDW_DATO_STRING, "tamanoMaximo"=>100),
            "tipo_id" => array("requerido" => true, "soloDeLectura" => true, "nombre" => "ID de Elemento", "tipoDeDato" => FDW_DATO_INT),
            "nombre" => array("requerido" => true, "soloDeLectura" => true, "nombre" => "Nombre", "tipoDeDato" => FDW_DATO_STRING, "tamanoMaximo"=>50)
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
        return array("id", "ruta", "nombre");
    }

    /**
     * Obtiene el nombre del permiso del Modulo
     */
    public static function ObtenerNombrePermiso():string
    {
        return "Libre";
    }




    public static function _ObtenerArchivosInseguro($elemento_clase, $elemento_id, $nombre = NULL)
    {
        $archivos = array();

        $filtros = array
            (
                "tipo" => array( array("operador"=>FDW_DATO_BDD_OPERADOR_IGUAL, "operando"=>$elemento_clase) ),
                "tipo_id" => array( array("operador"=>FDW_DATO_BDD_OPERADOR_IGUAL, "operando"=>$elemento_id) )
            );

        if($nombre) // Si especifica el nombre
        {
            $filtros["nombre"] = array( array("operador"=>FDW_DATO_BDD_OPERADOR_IGUAL, "operando"=>$nombre) );
        }

        $modulo = new Archivos
            (
                array("id", "ruta", "nombre"), // Campos
                $filtros
            );

        while($elemento = $modulo->ObtenerSiguienteRegistro() ) // Mientras haya archivos
        {
            $ruta = $elemento["ruta"];

            $archivos[ $elemento["nombre"] ] = array("ruta"=>"{$ruta}", "id"=>$elemento["id"]);
        }

        return $archivos;
    }

    public static function ObtenerArchivos($elemento, $nombre = NULL)
    {
        global $CFG;

        $archivos = array();

        if(is_a($elemento, "STM_Dato_Elemento"))  // Si es un STM_Dato_Elemento
        {
            $archivos = Archivos::_ObtenerArchivosInseguro(get_class($elemento), $elemento->ObtenerValor("id"), $nombre);
        }
        else // Si no es un STM_Dato_Elemento
        {
            throw new Exception("Se necesita un elemento de tipo STM_Dato_Elemento para crear el archivo.");
        }

        return $archivos;
    }
}
