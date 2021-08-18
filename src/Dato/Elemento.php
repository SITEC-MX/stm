<?php
/**
 * Sistemas Especializados e Innovación Tecnológica, SA de CV
 * Mpsoft.STM - Framework de Desarrollo Web para PHP
 *
 * v.1.0.0.0 - 2017-04-22
 */
namespace Mpsoft\STM\Dato;

use Mpsoft\FDW\Dato\ElementoException;
use Exception;
use \Mpsoft\STM\Dato\Historial;

abstract class Elemento extends \Mpsoft\FDW\Dato\Elemento
{
    /**
     * Constructor del Elemento
     * @param ?int $id ID del Elemento o NULL para inicializar un Elemento nuevo.
     * @throws ElementoException
     */
    public function __construct(?int $id = NULL)
    {
        parent::__construct($id);

        $this->AgregarEvento("AlBloquearElemento");

        if ($id > 0) // Si es un Elemento existente
        {
        }
        else // Si es un Elemento nuevo
        {
            $tiempo = time();

            $this->AsignarValorSinValidacion("activo", true);
            $this->AsignarValorSinValidacion("creacion", $tiempo);
            $this->AsignarValorSinValidacion("modificacion", $tiempo);
        }

        $this->VincularEvento("AntesDeEliminar",    "ImpedirModificarEliminarInactivos", function(){ $this->ImpedirModificarEliminarInactivos(); });
        $this->VincularEvento("AntesDeModificar",   "ImpedirModificarEliminarInactivos", function(){ $this->ImpedirModificarEliminarInactivos(); });

        $this->VincularEvento("AntesDeModificar", "ActualizarTiempoModificar", function(){ $this->ActualizarTiempoModificar(); });

        $this->VincularEvento("AlBloquearElemento", "CrearHistorial", function(){ $this->CrearHistorialBloquear(); });
        $this->VincularEvento("DespuesDeAgregar", "CrearHistorial", function(){ $this->CrearHistorialAgregar(); });
        $this->VincularEvento("AntesDeModificar", "PrepararInformacionParaHistorial", function(){ $this->PrepararInformacionParaHistorial(); });
        $this->VincularEvento("DespuesDeModificar", "CrearHistorial", function(){ $this->CrearHistorialModificar(); });
        $this->VincularEvento("DespuesDeEliminar", "CrearHistorial", function(){ $this->CrearHistorialEliminar(); });
    }




    /**
     * Procedimiento que elimina el Elemento. Este método será invocado por el Elemento al ejecutar el método Eliminar.
     * _Eliminar se ejecuta después de ejecutar el evento AntesDeEliminar y antes de ejecutar el evento DespuesDeEliminar
     */
    protected function _Eliminar():void
    {
        Elemento::EliminarElementoSTM($this->_InicializarBaseDeDatos(), $this->_ObtenerNombreTabla(), $this->ObtenerValor("id"));

        $this->AsignarValorSinValidacion("activo", FALSE);
    }

    public function Reactivar()
    {
        if($this->ObtenerValor("activo")) // Si el campo está activo
        {
            throw new ElementoException("Se intentó reactivar un Elemento que está activo.", "STM_Dato_Elemento");
        }

        throw new Exception("VALIDAR PRIVILEGIOS");

        $this->AsignarValorSinValidacion("activo", true);
        $this->AplicarCambios();
    }

    protected function ImpedirModificarEliminarInactivos()
    {
        if(!$this->ObtenerValor("activo")) // Si el campo no está activo
        {
            throw new ElementoException("Se intentó alterar un Elemento que no está activo.", "STM_Dato_Elemento");
        }
    }

    protected function ActualizarTiempoModificar()
    {
        // Actualizamos la fecha de modificación
        $this->AsignarValorSinValidacion("modificacion", time());
    }

    public static function EliminarElementoSTM($base_de_datos, $tabla, $elemento_id)
    {
        $base_de_datos->EjecutarUPDATE
            (
                $tabla, // Tabla
                array("activo"), // Campos
                array(false), // Valores
                array // Filtros
                (
                    "id" => array(array("operador"=>FDW_DATO_BDD_OPERADOR_IGUAL, "operando"=>$elemento_id))
                )
            );
    }

    public static function AsignarElementoSTM(\Mpsoft\FDW\Dato\Elemento $elemento_destino, string $campo_nombre, \Mpsoft\STM\Dato\Elemento $elemento_a_asignar = NULL)
    {
        // Validamos que el elemento a asignar esté activo
        if(!$elemento_a_asignar->ObtenerValor("activo")) // Si el Elemento no está activo
        {
            throw new ElementoException("El Elemento a asignar no está activo.", get_class($elemento_destino));
        }

        Elemento::AsignarElemento($elemento_destino, $campo_nombre, $elemento_a_asignar);
    }





    public function Bloquear():bool
    {
        global $SESION;

        $elemento_bloqueado = FALSE;

        $usuario = $SESION->ObtenerUsuario();
        if($usuario) // Si hay un usuario en sesión
        {
            // Obtenemos el token de la sesión
            $sesion_token_id = $SESION->ObtenerTokenID();
            $bloqueo_token_id = $this->ObtenerValor("bloqueo_token_id");

            if(!$bloqueo_token_id || $bloqueo_token_id === $sesion_token_id) // Si el token actual tiene el bloqueo (o si el Elemento no está bloqueado)
            {
                // Actualizamos el tiempo de bloqueo
                $this->_Bloquear();

                $elemento_bloqueado = TRUE;
            }
            else // Si el token actual no tiene el bloqueo
            {
                $segundos_de_bloqueo = $this->ObtenerSegundosDeBloqueo();

                // Verificamos si el tiempo de bloqueo sigue vigente. Si el tiempo ya expiró podemos bloquear
                $tiempo = time();
                $bloqueo_tiempo = $this->ObtenerValor("bloqueo_tiempo"); // Tiempo en el que se bloqueó el Elemento
                $bloqueo_tiempo_hasta = $bloqueo_tiempo + $segundos_de_bloqueo;

                if($tiempo>$bloqueo_tiempo_hasta) // Si el bloqueo anterior ya expiró
                {
                    // Actualizamos el tiempo de bloqueo
                    $this->_Bloquear();

                    $elemento_bloqueado = TRUE;
                }
                else // Si el bloqueo anterior no ha expirado
                {
                    // Verificamos si el token sigue vigente y si sigue bloqueando el Elemento
                    $token_bloqueante = $this->InicializarTokenBloqueante();

                    if(!$token_bloqueante) // Si el token bloqueante ya no es válido
                    {
                        // Actualizamos el tiempo de bloqueo
                        $this->_Bloquear();

                        $elemento_bloqueado = TRUE;
                    }
                    else // Si el token bloqueante sigue activo
                    {
                        $clase_bloqueada = $token_bloqueante->ObtenerValor("bloqueo_tipo");
                        $id_bloqueado = $token_bloqueante->ObtenerValor("bloqueo_id");
                        $id_actual = $this->ObtenerValor("id");

                        if($clase_bloqueada !== $this->clase_actual || $id_bloqueado !== $id_actual) // Si el bloqueo ya no es de este Elemento
                        {
                            // Actualizamos el tiempo de bloqueo
                            $this->_Bloquear();

                            $elemento_bloqueado = TRUE;
                        }                        
                    }
                }
            }
        }
        else // Si no hay un usuario en sesión
        {
            throw new Exception("Se requiere un usuario en sesión para bloquear el Elemento.");
        }

        return $elemento_bloqueado;
    }

    private function _Bloquear():void
    {
        global $SESION;
        $sesion_token_id = $SESION->ObtenerTokenID();
        $bloqueo_token_id = $this->ObtenerValor("bloqueo_token_id");
        $tiempo = time();

        // Actualizamos la fecha de bloqueo del Elemento actual
        $base_de_datos = $this->_InicializarBaseDeDatos();

        $campos = array("bloqueo_tiempo");
        $valores = array($tiempo);

        if($sesion_token_id !== $bloqueo_token_id)
        {
            $campos[] = "bloqueo_token_id";
            $valores[] = $sesion_token_id;
        }

        $base_de_datos->EjecutarUPDATE
        (
            $this->_ObtenerNombreTabla(), // Tabla
            $campos, // Campos
            $valores, // Valores
            array // Filtros
            (
                "id" => array(array("operador"=>FDW_DATO_BDD_OPERADOR_IGUAL, "operando"=>$this->ObtenerValor("id")))
            )
        );

        $this->AsignarSinValidarNiNotificar("bloqueo_token_id", $sesion_token_id);
        $this->AsignarSinValidarNiNotificar("bloqueo_tiempo", $tiempo);



        // Actualizamos los datos de bloqueo del token
        $id = $this->ObtenerValor("id");
        $SESION->ActualizarElementoBloqueado($this->clase_actual, $id);



        $this->DispararEvento("AlBloquearElemento");
    }

    private function _ActualizarTiempoDeBloqueo():void
    {
        $tiempo = time();

        $base_de_datos = $this->_InicializarBaseDeDatos();
        $base_de_datos->EjecutarUPDATE
            (
                $this->_ObtenerNombreTabla(), // Tabla
                array("bloqueo_tiempo"), // Campos
                array($tiempo), // Valores
                array // Filtros
                (
                    "id" => array(array("operador"=>FDW_DATO_BDD_OPERADOR_IGUAL, "operando"=>$this->ObtenerValor("id")))
                )
            );

        $this->AsignarSinValidarNiNotificar("bloqueo_tiempo", $tiempo);
    }

    public function BloqueadoPorUsuarioEnSesion():bool
    {
        global $SESION;

        $bloqueado_por_usuario_en_sesion = FALSE;

        $usuario = $SESION->ObtenerUsuario();
        if($usuario) // Si hay un usuario en sesión
        {
            $sesion_usuario_id = $usuario->ObtenerValor("id");

            $bloqueado_por_usuario_en_sesion = $this->ObtenerValor("bloqueo_usuario_id") == $sesion_usuario_id;
        }

        return $bloqueado_por_usuario_en_sesion;
    }

    /**
     * Obtiene el número de segundos que se bloqueará el Elemento al ejecutar el método Bloquear()
     */
    protected abstract function ObtenerSegundosDeBloqueo():int;









    private function CrearHistorialBloquear():void
    {
        $this->GuardarHistorial(STM_HISTORIAL_ACCION_OBTENER);
    }

    private function CrearHistorialAgregar():void
    {
        $this->GuardarHistorial(STM_HISTORIAL_ACCION_AGREGAR);
    }

    protected $historial_info = NULL;
    protected function PrepararInformacionParaHistorial():void
    {
        $historial_info = $this->ObtenerValoresOriginalesModificados();
        unset($historial_info["modificacion"]);

        $this->historial_info = $historial_info;
    }

    private function CrearHistorialModificar():void
    {
        $this->GuardarHistorial(STM_HISTORIAL_ACCION_MODIFICAR, json_encode($this->historial_info));

        $this->historial_info = NULL;
    }

    private function CrearHistorialEliminar():void
    {
        $this->GuardarHistorial(STM_HISTORIAL_ACCION_ELIMINAR);
    }

    protected function GuardarHistorial(int $accion, string $info=NULL):void
    {
        $elemento = $this->clase_actual::ObtenerTipoHistorial();
        $elemento_id = $this->ObtenerValor("id");

        Historial::CrearEntrada($elemento, $accion, $elemento_id, $info);
    }






    protected abstract function InicializarTokenBloqueante():?\Mpsoft\STM\Sesion\Token;









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

    public abstract static function ObtenerTipoHistorial();
}


define("STM_ELEMENTO_SUBELEMENTO_NUEVO", 1);
define("STM_ELEMENTO_SUBELEMENTO_SINCAMBIOS", 2);
define("STM_ELEMENTO_SUBELEMENTO_CONCAMBIOS", 3);
define("STM_ELEMENTO_SUBELEMENTO_ELIMINADO", 4);