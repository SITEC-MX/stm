<?php
/**
 * Sistemas Especializados e Innovaci�n Tecnol�gica, SA de CV
 * Mpsoft.STM - Framework de Desarrollo Web para PHP
 *
 * v.1.0.0.0 - 2020-08-08
 */
namespace Mpsoft\STM\Sesion;

class Usuarios extends \Mpsoft\FDW\Sesion\Usuarios
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
        $campos = \Mpsoft\FDW\Sesion\Usuarios::ObtenerInformacionDeCampos();

        $campos["activo"] = array("requerido" => true, "soloDeLectura" => true, "nombre" => "Activo", "tipoDeDato" => FDW_DATO_BOOL, "ignorarAlModificar"=>true, "ignorarAlObtenerValores"=>true);
        $campos["bloqueo_tiempo"] = array("requerido" => FALSE, "soloDeLectura" => TRUE, "nombre" => "Tiempo de bloqueo", "tipoDeDato" => FDW_DATO_INT, "ignorarAlModificar"=>TRUE, "ignorarAlObtenerValores"=>TRUE);
        $campos["bloqueo_token_id"] = array("requerido" => FALSE, "soloDeLectura" => TRUE, "nombre" => "Usuario con el bloqueo", "tipoDeDato" => FDW_DATO_INT, "ignorarAlModificar"=>TRUE, "ignorarAlObtenerValores"=>TRUE);
        $campos["creacion"] = array("requerido" => true, "soloDeLectura" => true, "nombre" => "Creaci�n", "tipoDeDato" => FDW_DATO_INT, "ignorarAlModificar"=>true, "ignorarAlObtenerValores"=>true);
        $campos["modificacion"] = array("requerido" => true, "soloDeLectura" => true, "nombre" => "Modificaci�n", "tipoDeDato" => FDW_DATO_INT, "ignorarAlObtenerValores"=>true);

        $campos["nombre"] = array("requerido" => true, "soloDeLectura" => false, "nombre" => "Nombre", "tipoDeDato" => FDW_DATO_STRING, "tamanoMaximo"=>200);
        $campos["fotografia_perfil_url"] = array("requerido" => false, "soloDeLectura" => true, "nombre" => "Fotograf�a de perfil", "tipoDeDato" => FDW_DATO_STRING, "tamanoMaximo"=>255);

        $campos["contrasena_fecha"] = array("requerido" => false, "soloDeLectura" => true, "nombre" => "Fecha de contrase�a", "tipoDeDato" => FDW_DATO_INT, "ignorarAlModificar" => true);
        $campos["intentos_login"] = array("requerido" => true, "soloDeLectura" => true, "nombre" => "Intentos de login fallidos", "tipoDeDato" => FDW_DATO_INT, "ignorarAlObtenerValores"=>true); // "ignorarAlModificar" => true -- no se puede ya que se modifica al aumentar intentos de login
        $campos["suspendido"] = array("requerido" => true, "soloDeLectura" => true, "nombre" => "Usuario suspendido", "tipoDeDato" => FDW_DATO_BOOL); // "ignorarAlModificar" => true -- no se puede ya que se modifica al aumentar intentos de login

        $campos["tfa_habilitado"] = array("requerido" => FALSE, "soloDeLectura" => TRUE, "nombre" => "�TFA Habilitado?", "tipoDeDato" => FDW_DATO_BOOL);
        $campos["tfa_secreto"] = array("requerido" => FALSE, "soloDeLectura" => TRUE, "nombre" => "TFA Secret", "tipoDeDato" => FDW_DATO_STRING, "tamanoMaximo"=>110, "ignorarAlObtenerValores"=>TRUE);

        return $campos;
    }

    /**
     * Verifica si se tiene permiso para realizar la acci�n sobre el Elemento
     * @param int $accion Acci�n a verificar
     * @return boolean
     */
    protected function PedirAutorizacion(int $accion):bool
    {
        global $SESION;

        return $SESION->PedirAutorizacion(Usuario::ObtenerNombrePermiso(), $accion);
    }


    /**
     * Obtiene un arreglo de strings con los campos predeterminados del Elemento. Ser�n utilizado s�lo en caso que no se especifiquen.
     * @return array
     */
    public static function ObtenerCamposPredeterminados():array
    {
        return array("id");
    }

    /*
     * Obtiene un array asociativo de arrays asociativos con la informaci�n de los joins realizados en el M�dulo. array( identificador => array( "tipo", "tabla", "campoInterno", "campoExterno" )  )
     * @return array Retorna un array de arrays asociativos con la informaci�n de los joins realizados en el M�dulo. Retorna un array vac�o si no hay joins. array( identificador => array( "tipo", "tabla", "campoInterno", "campoExterno" )  )
     */
    public static function ObtenerJoins():?array
    {
        return NULL;
    }

    /**
     * Obtiene el nombre del permiso del Modulo
     */
    public static function ObtenerNombrePermiso():string
    {
        return "Usuario";
    }
}