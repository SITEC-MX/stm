<?php
/**
 * Sistemas Especializados e Innovación Tecnológica, SA de CV
 * Mpsoft.STM - Framework de Desarrollo Web para PHP
 *
 * v.1.0.0.0 - 2017-04-25
 */
namespace Mpsoft\STM\Sesion;

use \Mpsoft\STM\Dato\Modulo;

class Rol extends \Mpsoft\STM\Dato\ElementoSoloLectura
{
    private $permisos = NULL;

    /**
     * Constructor del Elemento
     * @param ?int $id ID del Elemento o NULL para inicializar un Elemento nuevo.
     */
    public function __construct(?int $id = NULL)
    {
        parent::__construct($id);
    }








    private function CargarPermisos()
    {
        global $PERMISOS_DISPONIBLES;

        $rol_id = $this->ObtenerValor("id");

        $modulo = new Permisos
            (
                array("nombre", "valor"), // Campos
                array //  Filtros
                (
                    "rol_id" => array( array("operador"=>FDW_DATO_BDD_OPERADOR_IGUAL, "operando"=> $rol_id) )
                )
            );

        $permisos = array();
        foreach($PERMISOS_DISPONIBLES as $permiso_nombre) // Para cada permiso disponible
        {
            $permisos[$permiso_nombre] = 0;
        }

        while($elemento = $modulo->ObtenerSiguienteRegistro()) // Para cada permiso del rol
        {
            $permiso_nombre = $elemento["nombre"];

            $permisos[$permiso_nombre] = $elemento["valor"];
        }

        $this->permisos = $permisos;
    }

    public function ObtenerPermisos()
    {
        if($this->permisos === NULL) // Si no se han cargado los permisos
        {
            $this->CargarPermisos();
        }

        return $this->permisos;
    }

    public function ObtenerPermiso($permiso_nombre)
    {
        if($this->permisos === NULL) // Si no se han cargado los permisos
        {
            $this->CargarPermisos();
        }

        return isset($this->permisos[$permiso_nombre]) ? $this->permisos[$permiso_nombre] : 0;
    }







    /**
     * Verifica si se tiene permiso para realizar la acción sobre el Elemento
     * @param int $accion Acción a verificar
     * @return boolean
     */
    protected function PedirAutorizacion(int $accion):bool
    {
        global $SESION;

        return $SESION->PedirAutorizacion(Rol::ObtenerNombrePermiso(), $accion);
    }







    /*
     * Obtiene el campo del Elemento que no es válido.
     * @return array Arreglo con la información del campo que no es válido. array( "campo" => campo, "error" => descripción del error ) o null si todos los campos son válidos.
     */
    public function ValidarDatos():?array
    {
        return null;
    }

    public function ObtenerValores():array
    {
        $valores = parent::ObtenerValores();

        $permisos = array();

        // Llenamos los permisos con 0
        $permisos_disponibles = array();
        foreach($permisos_disponibles as $indice=>$permiso)
        {
            $permisos[$permiso] = 0;
        }

        // Colocamos los permisos disponibles
        $valores["permiso"] = array();
        foreach($this->permisos as $nombre=>$permiso) // Para cada permiso del rol
        {
            $permisos[$nombre] = $permiso["valor"];
        }

        $valores["permiso"] = $permisos;

        return $valores;
    }








    public static function CargarRolesDeUsuario(Usuario $usuario):array
    {
        $usuario_id = $usuario->ObtenerValor("id");
        $modulo = new UsuarioRoles
            (
                array("rol_id"), // Campos
                array // Filtros
                (
                    "usuario_id" => array( array("operador"=>FDW_DATO_BDD_OPERADOR_IGUAL, "operando"=>$usuario_id) )
                )
            );

        $roles = array();
        while($elemento = $modulo->ObtenerSiguienteRegistro()) // Para cada rol del usuario
        {
            $roles[] = new Rol( $elemento["rol_id"] );
        }


        return $roles;
    }








    /**
     * Obtiene el nombre de la tabla en la base de datos utilizada por el Elemento
     * @return string
     */
    public static function ObtenerNombreTabla():string
    {
        return "rol";
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
        $campos = Modulo::ObtenerInformacionDeCampos();

        $campos["nombre"] = array("requerido" => true, "soloDeLectura" => false, "nombre" => "Rol", "tipoDeDato" => FDW_DATO_STRING, "tamanoMaximo"=>200);

        return $campos;
    }

    /**
     * Obtiene el nombre del permiso del Modulo
     */
    public static function ObtenerNombrePermiso():string
    {
        return "Rol";
    }

    public static function ObtenerTipoHistorial()
    {
        return 0; // Los elementos solo de lectura no tienen historial porque no se pueden modificar.
    }
}
