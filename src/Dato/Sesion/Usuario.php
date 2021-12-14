<?php
/**
 * Sistemas Especializados e Innovaci�n Tecnol�gica, SA de CV
 * Mpsoft.STM - Framework de Desarrollo Web para PHP
 *
 * v.1.0.0.0 - 2021-12-13
 */
namespace Mpsoft\STM\Dato\Sesion;

abstract class Usuario extends \Mpsoft\STM\Dato\Elemento
{
    /*
     * Obtiene el campo del Elemento que no es v�lido.
     * @return array Arreglo con la informaci�n del campo que no es v�lido. array( "campo" => campo, "error" => descripci�n del error ) o null si todos los campos son v�lidos.
     */
    public function ValidarDatos():?array
    {
        return null;
    }

    protected function GenerarAleatorio($ancho)
    {
        $semilla = rand(1000,9999) . time() . rand(1000,9999);

        $hash = hash("sha512", $semilla, TRUE);
        $base64 = base64_encode($hash);

        // Quitamos los caracteres de base64 que pueden crear problemas en una URL
        $base64 = str_replace("/", "-", $base64);

        return substr($base64, 0, $ancho);
    }







	public abstract function Desbloquear():void;







    /**
     * Obtiene el nombre de la tabla en la base de datos utilizada por el Elemento
     * @return string
     */
    public static function ObtenerNombreTabla():string
    {
        return "usuario";
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
        $campos = parent::ObtenerInformacionDeCampos();

        $campos["nombre"] = array("requerido" => true, "soloDeLectura" => false, "nombre" => "Nombre", "tipoDeDato" => FDW_DATO_STRING, "tamanoMaximo"=>200);
        $campos["fotografia_perfil_url"] = array("requerido" => false, "soloDeLectura" => true, "nombre" => "Fotograf�a de perfil", "tipoDeDato" => FDW_DATO_STRING, "tamanoMaximo"=>255);

        $campos["contrasena_fecha"] = array("requerido" => false, "soloDeLectura" => true, "nombre" => "Fecha de contrase�a", "tipoDeDato" => FDW_DATO_INT, "ignorarAlModificar" => true);
        $campos["intentos_login"] = array("requerido" => true, "soloDeLectura" => true, "nombre" => "Intentos de login fallidos", "tipoDeDato" => FDW_DATO_INT, "ignorarAlObtenerValores"=>true); // "ignorarAlModificar" => true -- no se puede ya que se modifica al aumentar intentos de login
        $campos["suspendido"] = array("requerido" => true, "soloDeLectura" => true, "nombre" => "Usuario suspendido", "tipoDeDato" => FDW_DATO_BOOL); // "ignorarAlModificar" => true -- no se puede ya que se modifica al aumentar intentos de login

        $campos["tfa_habilitado"] = array("requerido" => TRUE, "soloDeLectura" => TRUE, "nombre" => "�TFA Habilitado?", "tipoDeDato" => FDW_DATO_BOOL);
        $campos["tfa_secreto"] = array("requerido" => FALSE, "soloDeLectura" => TRUE, "nombre" => "TFA Secret", "tipoDeDato" => FDW_DATO_STRING, "tamanoMaximo"=>110, "ignorarAlObtenerValores"=>TRUE);

        return $campos;
    }

    /**
     * Obtiene el nombre del permiso del Modulo
     */
    public static function ObtenerNombrePermiso():string
    {
        return "Usuario";
    }
}