<?php
/**
 * Sistemas Especializados e Innovaci�n Tecnol�gica, SA de CV
 * Mpsoft.STM - Framework de Desarrollo Web para PHP
 *
 * v.2.0.0.0 - 2021-01-14
 */
namespace Mpsoft\STM\Sesion;

abstract class Token extends \Mpsoft\FDW\Sesion\Token
{
    /**
     * Constructor del Elemento
     * @param ?int $id ID del Elemento o NULL para inicializar un Elemento nuevo.
     */
    public function __construct(?int $id = NULL)
    {
        parent::__construct($id);

        if($id) // Si el Elemento existe
        {
        }
        else // Si el Elemento es nuevo
        {
            $this->VincularEvento("AntesDeAgregar", "BloquearTokenSiUsuarioTieneTFA", function(){ $this->BloquearTokenSiUsuarioTieneTFA(); });
        }
    }






    private function BloquearTokenSiUsuarioTieneTFA()
    {
        $usuario = $this->ObtenerUsuario();

        $bloqueado = $usuario->ObtenerValor("tfa_habilitado");

        $this->AsignarValorSinValidacion("bloqueado", $bloqueado);
    }

    public function Bloquear()
    {
        $this->AsignarValorSinValidacion("bloqueado", TRUE);
        $this->AplicarCambios();
    }

    public function Desbloquear()
    {
        $this->AsignarValorSinValidacion("bloqueado", FALSE);
        $this->AplicarCambios();
    }







    protected function GenerarTokenAleatorio():string
    {
        global $CFG;

        $semilla = time() . rand(100000,999999) . $CFG->token_semilla;

        $sha512 = hash("sha512", $semilla, TRUE);

        $token = base64_encode($sha512);

        return substr($token, 0, 86);
    }





    public function ActualizarUltimaAccion()
    {
        $tiempo = time();

        $this->AsignarValorSinValidacion("ultimaaccion_tiempo", $tiempo);
        $this->AplicarCambios();
    }





    /**
     * Procedimiento que elimina el Elemento. Este m�todo ser� invocado por el Elemento al ejecutar el m�todo Eliminar.
     * _Eliminar se ejecuta despu�s de ejecutar el evento AntesDeEliminar y antes de ejecutar el evento DespuesDeEliminar
     */
    protected function _Eliminar():void
    {
        \Mpsoft\FDW\Dato\Elemento::EliminarElemento($this);
    }






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
        $campos = \Mpsoft\FDW\Sesion\Token::ObtenerInformacionDeCampos();

        $campos["bloqueado"] = array("requerido" => FALSE, "soloDeLectura" => TRUE, "nombre" => "�Token bloqueado?", "tipoDeDato" => FDW_DATO_BOOL); // Es requerido pero se asigna antes de agregar
        $campos["ultimaaccion_tiempo"] = array("requerido" => FALSE, "soloDeLectura" => TRUE, "nombre" => "�ltima acci�n - Tiempo", "tipoDeDato" => FDW_DATO_INT);

        return $campos;
    }
}