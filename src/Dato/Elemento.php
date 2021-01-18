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
            $segundos_de_bloqueo = $this->ObtenerSegundosDeBloqueo();

            $sesion_usuario_id = $usuario->ObtenerValor("id");

            $bloqueo_usuario_id = $this->ObtenerValor("bloqueo_usuario_id");
            $bloqueo_tiempo_hasta = $this->ObtenerValor("bloqueo_tiempo") + $segundos_de_bloqueo; // El Elemento está bloqueado hasta este tiempo

            $tiempo = time();

            if($bloqueo_usuario_id == $sesion_usuario_id || $tiempo > $bloqueo_tiempo_hasta) // Si el bloqueo expiró
            {
                $base_de_datos = $this->_InicializarBaseDeDatos();

                $base_de_datos->EjecutarUPDATE
                    (
                        $this->_ObtenerNombreTabla(), // Tabla
                        array("bloqueo_usuario_id", "bloqueo_tiempo"), // Campos
                        array($sesion_usuario_id, $tiempo), // Valores
                        array // Filtros
                        (
                            "id" => array(array("operador"=>FDW_DATO_BDD_OPERADOR_IGUAL, "operando"=>$this->ObtenerValor("id")))
                        )
                    );

                $elemento_bloqueado = TRUE;

                $this->AsignarSinValidarNiNotificar("bloqueo_usuario_id", $sesion_usuario_id);
                $this->AsignarSinValidarNiNotificar("bloqueo_tiempo", $tiempo);
            }
        }
        else // Si no hay un usuario en sesión
        {
            throw new Exception("Se requiere un usuario en sesión para bloquear el Elemento.");
        }

        return $elemento_bloqueado;
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

    public function ActualizarTiempoDeBloqueo():void
    {
        global $SESION;

        $usuario = $SESION->ObtenerUsuario();
        if($usuario) // Si hay un usuario en sesión
        {
            $sesion_usuario_id = $usuario->ObtenerValor("id");
            $bloqueo_usuario_id = $this->ObtenerValor("bloqueo_usuario_id");
            $tiempo = time();

            if($bloqueo_usuario_id == $sesion_usuario_id || $this->EsNuevo()) // Si el usuario actual tiene el bloqueo
            {
                $this->AsignarValorSinValidacion("bloqueo_usuario_id", $sesion_usuario_id);
                $this->AsignarValorSinValidacion("bloqueo_tiempo", $tiempo);
            }
            else
            {
                throw new Exception("El bloqueo del Elemento pertenece a otro usuario.");
            }
        }
        else // Si no hay un usuario en sesión
        {
            throw new Exception("Se requiere un usuario en sesión para actualizar el tiempo de bloqueo del Elemento.");
        }
    }

    /**
     * Obtiene el número de segundos que se bloqueará el Elemento al ejecutar el método Bloquear()
     */
    protected abstract function ObtenerSegundosDeBloqueo():int;





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
                "activo" => array("requerido" => TRUE, "soloDeLectura" => TRUE, "nombre" => "Activo", "tipoDeDato" => FDW_DATO_BOOL, "ignorarAlModificar"=>TRUE, "ignorarAlObtenerValores"=>TRUE),
                "bloqueo_tiempo" => array("requerido" => FALSE, "soloDeLectura" => TRUE, "nombre" => "Tiempo de bloqueo", "tipoDeDato" => FDW_DATO_INT),
                "bloqueo_usuario_id" => array("requerido" => FALSE, "soloDeLectura" => TRUE, "nombre" => "Usuario con el bloqueo", "tipoDeDato" => FDW_DATO_INT),
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