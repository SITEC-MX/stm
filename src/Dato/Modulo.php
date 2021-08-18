<?php
/**
 * Sistemas Especializados e Innovación Tecnológica, SA de CV
 * Mpsoft.STM - Framework de Desarrollo Web para PHP
 *
 * v.1.0.0.0 - 2017-04-22
 */
namespace Mpsoft\STM\Dato;

abstract class Modulo extends \Mpsoft\FDW\Dato\Modulo
{
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
                "id" => array("requerido" => TRUE, "soloDeLectura" => TRUE, "nombre" => "ID", "tipoDeDato" => FDW_DATO_INT, "ignorarAlAgregar"=>TRUE, "ignorarAlModificar"=>TRUE),
                "activo" => array("requerido" => TRUE, "soloDeLectura" => TRUE, "nombre" => "Activo", "tipoDeDato" => FDW_DATO_BOOL, "ignorarAlModificar"=>TRUE),
                "bloqueo_tiempo" => array("requerido" => FALSE, "soloDeLectura" => TRUE, "nombre" => "Tiempo de bloqueo", "tipoDeDato" => FDW_DATO_INT, "ignorarAlObtenerValores"=>TRUE),
                "bloqueo_token_id" => array("requerido" => FALSE, "soloDeLectura" => TRUE, "nombre" => "Token con el bloqueo", "tipoDeDato" => FDW_DATO_INT, "ignorarAlObtenerValores"=>TRUE),
                "creacion" => array("requerido" => TRUE, "soloDeLectura" => TRUE, "nombre" => "Creación", "tipoDeDato" => FDW_DATO_INT, "ignorarAlModificar"=>TRUE, "ignorarAlObtenerValores"=>TRUE),
                "modificacion" => array("requerido" => TRUE, "soloDeLectura" => TRUE, "nombre" => "Modificación", "tipoDeDato" => FDW_DATO_INT, "ignorarAlObtenerValores"=>TRUE)
            );
    }
}
