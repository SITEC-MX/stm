<?php
/**
 * Sistemas Especializados e Innovaci�n Tecnol�gica, SA de CV
 * Mpsoft.STM - Framework de Desarrollo Web para PHP
 *
 * v.1.0.0.0 - 2020-07-28
 */
namespace Mpsoft\STM\Sesion;

/**
 * Encapsula la informaci�n de una aplicaci�n
 */
class Aplicacion extends \Mpsoft\STM\Dato\Elemento
{
    /*
     * Obtiene el campo del Elemento que no es v�lido.
     * @return array Arreglo con la informaci�n del campo que no es v�lido. array( "campo" => campo, "error" => descripci�n del error ) o null si todos los campos son v�lidos.
     */
    public function ValidarDatos()
    {
        return NULL;
    }

    /**
     * Nombre de la tabla del Elemento
     * @return string Nombre de la tabla del Elemento
     */
    protected function ObtenerNombreTabla()
    {
        return "aplicacion";
    }

    /**
     * Verifica si se tiene permiso para realizar la acci�n sobre el Elemento
     * @param string $accion Acci�n a verificar
     * @return boolean
     */
    protected function PedirAutorizacion(string $accion)
    {
        return TRUE;
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
    protected function ObtenerInformacionDeCampos()
    {
        $campos = parent::ObtenerInformacionDeCampos();

        $campos["nombre"] = array("requerido" => TRUE, "soloDeLectura" => TRUE, "nombre" => "Nombre", "tipoDeDato" => FDW_DATO_STRING, "tamanoMaximo"=>200);

        return $campos;
    }
}