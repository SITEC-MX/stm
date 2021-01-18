<?php
/**
 * Sistemas Especializados e Innovación Tecnológica, SA de CV
 * Mpsoft.STM - Framework de Desarrollo Web para PHP
 *
 * v.1.0.0.0 - 2020-08-08
 */
namespace Mpsoft\STM\Sesion;

use \Mpsoft\STM\Dato\Configuraciones;
use \Mpsoft\STM\Dato\Configuracion;
use \Mpsoft\FDW\Dato\BdD;

use \Mpsoft\STM\Dato\GuardarConfiguracionException;

abstract class Usuario extends \Mpsoft\FDW\Sesion\Usuario
{
    /**
     * Verifica si se tiene permiso para realizar la acción sobre el Elemento
     * @param int $accion Acción a verificar
     * @return boolean
     */
    protected function PedirAutorizacion(int $accion):bool
    {
        global $SESION;

        return $SESION->PedirAutorizacion(Usuario::ObtenerNombrePermiso(), $accion);
    }







    // ********** Manejo de configuraciones **********
    private $configuraciones = NULL;
    private $configuraciones_sistema = NULL;
    private $configuraciones_usuario = NULL;

    private function CargarConfiguraciones():void
    {
        global $CONFIGURACIONES_DISPONIBLES;

        // Obtenemos las configuraciones disponibles para el usuario
        $modulo = new Configuraciones
            (
                array("id", "nombre", "forzar", "valor"), // Campos
                array // Filtros
                (
                    "usuario_id" => array( array("operador"=>FDW_DATO_BDD_OPERADOR_IGUAL, "operando"=>$this->ObtenerValor("id")))
                )
            );

        $configuraciones_existentes = array();
        while($configuracion = $modulo->ObtenerSiguienteRegistro()) // Para cada configuración
        {
            $nombre = $configuracion["nombre"];
            unset($configuracion["nombre"]);

            // Colocamos los parámetros de la configuración
            if(isset($CONFIGURACIONES_DISPONIBLES[$nombre])) // Si la configuración existe
            {
                $configuracion = array_merge($configuracion, $CONFIGURACIONES_DISPONIBLES[$nombre]);
            }
            else // Si la configuración no existe
            {
                $configuracion["tipo"] = FDW_DATO_STRING;
            }

            // Componemos el tipo de datos
            $configuracion["valor"] = BdD::ConvertirATipoDeDato($configuracion["valor"], $configuracion["tipo"]);

            $configuracion["sistema"] = FALSE;

            $configuraciones_existentes[$nombre] = $configuracion;
        }

        $this->configuraciones_sistema = Configuracion::ObtenerConfiguracionesDelSistema();
        $this->configuraciones_usuario = $configuraciones_existentes;

        $this->configuraciones = $this->GenerarConfiguracionFinalDeUsuario();
    }

    private function GenerarConfiguracionFinalDeUsuario() : array
    {
        global $CONFIGURACIONES_DISPONIBLES;

        $configuraciones = array();

        foreach($CONFIGURACIONES_DISPONIBLES as $nombre=>$parametros) // Para cada configuración disponible
        {
            if(isset($this->configuraciones_sistema[$nombre])) // Si hay configuración de sistema
            {
                if($this->configuraciones_sistema[$nombre]["forzar"]) // Si la configuración se debe forzar
                {
                    $configuraciones[$nombre] = $this->configuraciones_sistema[$nombre];
                }
                else // Si la configuración no se debe forzar
                {
                    if(isset($this->configuraciones_usuario[$nombre])) // Si hay configuración de usuario
                    {
                        $configuraciones[$nombre] = $this->configuraciones_usuario[$nombre];
                    }
                    else // Si no hay configuración de usuario
                    {
                        $configuraciones[$nombre] = $this->configuraciones_sistema[$nombre];
                    }
                }
            }
            else // Si no hay configuración de sistema
            {
                if(isset($this->configuraciones_usuario[$nombre])) // Si hay configuración de usuario
                {
                    $configuraciones[$nombre] = $this->configuraciones_usuario[$nombre];
                }
                else // Si tampoco hay configuración de usuario
                {
                    $parametros["valor"] = NULL;

                    $configuraciones[$nombre] = $parametros;
                }
            }
        }

        return $configuraciones;
    }

    public function ObtenerConfiguraciones() : array
    {
        if(!$this->configuraciones) // Si las configuraciones no se ha cargado
        {
            $this->CargarConfiguraciones();
        }

        $configuraciones = array();
        foreach($this->configuraciones as $nombre=>$configuracion)
        {
            $configuracion_a_agregar = array("valor" =>$configuracion["valor"], "forzar"=>$configuracion["forzar"], "tipo"=>$configuracion["tipo"]);

            if(isset($configuracion["catalogo"])) // Si el campo tiene un catálogo
            {
                $configuracion_a_agregar["catalogo"] = $configuracion["catalogo"];
            }

            $configuraciones[$nombre] = $configuracion_a_agregar;
        }

        return $configuraciones;
    }

    public function ObtenerConfiguracion(string $nombre)
    {
        if(!$this->configuraciones) // Si las configuraciones no se ha cargado
        {
            $this->CargarConfiguraciones();
        }

        return isset($this->configuraciones[$nombre]) ? $this->configuraciones[$nombre] : NULL;
    }

    public function GuardarConfiguraciones(array $configuraciones, $configuracion_sistema = FALSE) : void
    {
        global $CONFIGURACIONES_DISPONIBLES;

        if(!$this->configuraciones) // Si las configuraciones no se ha cargado
        {
            $this->CargarConfiguraciones();
        }

        $repositorio_configuraciones = $configuracion_sistema ? "configuraciones_sistema" : "configuraciones_usuario";

        // Guardamos las configuraciones recibidas
        foreach($configuraciones as $nombre=>$encapsulador) // Para cada configuración enviada
        {
            $valor = isset($encapsulador["valor"]) ? $encapsulador["valor"] : NULL;
            $forzar = isset($encapsulador["forzar"]) ? $encapsulador["forzar"] : FALSE;

            $this->GuardarConfiguracion($nombre, $valor, false, $configuracion_sistema, $forzar);
        }

        // Eliminamos las configuraciones que no existen en las configuraciones disponibles
        foreach($this->$repositorio_configuraciones as $nombre=>$parametros) // Para cada configuración del usuario
        {
            if(!isset($CONFIGURACIONES_DISPONIBLES[$nombre])) // Si la configuración no existe
            {
                $configuracion = new Configuracion($this->$repositorio_configuraciones[$nombre]["id"]);
                $configuracion->Eliminar();

                unset($this->$repositorio_configuraciones[$nombre]);
            }
        }

        $this->configuraciones = $this->GenerarConfiguracionFinalDeUsuario();
    }

    public function GuardarConfiguracion(string $nombre, $valor, $generar_configuracion = true, $guardar_configuracion_sistema = false, $forzar_configuracion_sistema = false) : void
    {
        global $CONFIGURACIONES_DISPONIBLES;

        $repositorio_configuraciones = $guardar_configuracion_sistema ? "configuraciones_sistema" : "configuraciones_usuario";

        if(!$this->configuraciones) // Si las configuraciones no se ha cargado
        {
            $this->CargarConfiguraciones();
        }

        if(isset($CONFIGURACIONES_DISPONIBLES[$nombre])) // Si la configuración existe
        {
            // Obtenemos la configuración del sistema (si es que existe)
            $configuracion_sistema_valor = isset($this->configuraciones_sistema[$nombre]) ? $this->configuraciones_sistema[$nombre]["valor"] : NULL;

            $configuracion_id = NULL;

            switch($repositorio_configuraciones)
            {
                case "configuraciones_usuario":
                    if(isset($this->configuraciones_usuario[$nombre]))
                    {
                        $configuracion_id = $this->configuraciones_usuario[$nombre]["id"];
                    }
                    break;

                case "configuraciones_sistema":
                    if(isset($this->configuraciones_sistema[$nombre]))
                    {
                        $configuracion_id = $this->configuraciones_sistema[$nombre]["id"];
                    }
                    break;
            }

            $configuracion = new Configuracion($configuracion_id);

            // Si la nueva configuracion es diferente a la del sistema
            if($configuracion_sistema_valor != $valor || $guardar_configuracion_sistema) // Si la configuración es diferente a la del sistema  ó es una configuración de sistema
            {
                switch($repositorio_configuraciones)
                {
                    case "configuraciones_usuario":
                        $this->configuraciones_usuario[$nombre]["valor"] = $valor;
                        break;

                    case "configuraciones_sistema":
                        $this->configuraciones_sistema[$nombre]["valor"] = $valor;
                        break;
                }

                if($valor) // Si se proporciona un valor para la configuración
                {
                    $configuracion->AsignarValorConfiguracion($valor);
                    $configuracion->AsignarValor("forzar", $forzar_configuracion_sistema);

                    if(!$configuracion_id) // Si la configuración es nueva
                    {
                        if(!$guardar_configuracion_sistema) // Si no es configuración del sistema, le agregamos el usuario
                        {
                            $configuracion->AsignarUsuario($this);
                        }
                        $configuracion->AsignarValor("nombre", $nombre);
                    }

                    $configuracion->AplicarCambios();
                }
                else // Si no se proporciona un valor para la configuración
                {
                    $configuracion->Eliminar();
                }
            }
            else // Si la configuración es igual a la del sistema
            {
                if($configuracion_id) // Si la configuración existe; y no estamos aquí porque NULL == NULL
                {
                    $configuracion->Eliminar();
                }
            }
        }
        else // Si la configuracion no existe
        {
            throw new GuardarConfiguracionException("La configuración '{$nombre}' no existe.", $nombre);
        }

        if($generar_configuracion) // Si se debe generar la configuración final
        {
            $this->configuraciones = $this->GenerarConfiguracionFinalDeUsuario();
        }
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
        return "usuario";
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
        $campos = \Mpsoft\FDW\Sesion\Usuarios::ObtenerInformacionDeCampos();

        $campos["activo"] = array("requerido" => true, "soloDeLectura" => true, "nombre" => "Activo", "tipoDeDato" => FDW_DATO_BOOL, "ignorarAlModificar"=>true, "ignorarAlObtenerValores"=>true);
        $campos["bloqueo_tiempo"] = array("requerido" => FALSE, "soloDeLectura" => TRUE, "nombre" => "Tiempo de bloqueo", "tipoDeDato" => FDW_DATO_INT, "ignorarAlModificar"=>TRUE, "ignorarAlObtenerValores"=>TRUE);
        $campos["bloqueo_usuario_id"] = array("requerido" => FALSE, "soloDeLectura" => TRUE, "nombre" => "Usuario con el bloqueo", "tipoDeDato" => FDW_DATO_INT, "ignorarAlModificar"=>TRUE, "ignorarAlObtenerValores"=>TRUE);
        $campos["creacion"] = array("requerido" => true, "soloDeLectura" => true, "nombre" => "Creación", "tipoDeDato" => FDW_DATO_INT, "ignorarAlModificar"=>true, "ignorarAlObtenerValores"=>true);
        $campos["modificacion"] = array("requerido" => true, "soloDeLectura" => true, "nombre" => "Modificación", "tipoDeDato" => FDW_DATO_INT, "ignorarAlObtenerValores"=>true);


        $campos["nombre"] = array("requerido" => true, "soloDeLectura" => false, "nombre" => "Nombre", "tipoDeDato" => FDW_DATO_STRING, "tamanoMaximo"=>200);
        $campos["fotografia_perfil_url"] = array("requerido" => false, "soloDeLectura" => true, "nombre" => "Fotografía de perfil", "tipoDeDato" => FDW_DATO_STRING, "tamanoMaximo"=>255);

        $campos["contrasena_fecha"] = array("requerido" => false, "soloDeLectura" => true, "nombre" => "Fecha de contraseña", "tipoDeDato" => FDW_DATO_INT, "ignorarAlModificar" => true);
        $campos["intentos_login"] = array("requerido" => true, "soloDeLectura" => true, "nombre" => "Intentos de login fallidos", "tipoDeDato" => FDW_DATO_INT, "ignorarAlObtenerValores"=>true); // "ignorarAlModificar" => true -- no se puede ya que se modifica al aumentar intentos de login
        $campos["suspendido"] = array("requerido" => true, "soloDeLectura" => true, "nombre" => "Usuario suspendido", "tipoDeDato" => FDW_DATO_BOOL); // "ignorarAlModificar" => true -- no se puede ya que se modifica al aumentar intentos de login

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