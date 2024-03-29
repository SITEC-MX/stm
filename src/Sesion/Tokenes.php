<?php
/**
 * Sistemas Especializados e Innovaci�n Tecnol�gica, SA de CV
 * Mpsoft.STM - Framework de Desarrollo Web para PHP
 *
 * v.2.0.0.0 - 2021-01-14
 */
namespace Mpsoft\STM\Sesion;

abstract class Tokenes extends \Mpsoft\FDW\Sesion\Tokenes
{
    /**
     * Verifica si se tiene permiso para realizar la acci�n sobre el Elemento
     * @param int $accion Acci�n a verificar
     * @return boolean
     */
    protected function PedirAutorizacion(int $accion):bool
    {
        return TRUE;
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
        $campos = \Mpsoft\FDW\Sesion\Tokenes::ObtenerInformacionDeCampos();

        $campos["bloqueado"] = array("requerido" => FALSE, "soloDeLectura" => TRUE, "nombre" => "�Token bloqueado?", "tipoDeDato" => FDW_DATO_BOOL); // Es requerido pero se asigna antes de agregar
        $campos["ultimaaccion_tiempo"] = array("requerido" => FALSE, "soloDeLectura" => TRUE, "nombre" => "�ltima acci�n - Tiempo", "tipoDeDato" => FDW_DATO_INT);

        $campos["bloqueo_tipo"] = array("requerido" => FALSE, "soloDeLectura" => TRUE, "nombre" => "Bloqueo (tipo Elemento)", "tipoDeDato" => FDW_DATO_STRING, "tamanoMaximo"=>250);
        $campos["bloqueo_id"] = array("requerido" => FALSE, "soloDeLectura" => TRUE, "nombre" => "Bloqueo (ID Elemento)", "tipoDeDato" => FDW_DATO_INT);

        return $campos;
    }
}