<?php
/**
 * Sistemas Especializados e Innovación Tecnológica, SA de CV
 * Mpsoft.STM - Framework de Desarrollo Web para PHP
 *
 * v.1.0.0.0 - 2016-11-08
 */
namespace Mpsoft\STM\Dato;

use \Mpsoft\FDW\Dato\ModuloException;

class Historiales extends \Mpsoft\STM\Dato\ModuloFDW
{
    /**
     * Construye un Módulo.
     * @param array $campos Arreglo con el listado de campos del Módulo que se seleccionarán. array("Campo1", "Campo2")
     * @param array $filtros Arreglo asociativo de arreglo de arreglos con la información de los filtro a aplicar. array( "Campo1" => array( array("tipo"=> ** Ver lógica ** (opcional, se asume FDW_DATO_BDD_LOGICA_Y), "operador"=> ** Ver operadores **, "operando"=>Valor), array("operador"=> ** Ver operadores **, "operando"=>Valor) ) )
     * @param array $ordenamiento Arreglo con los campos que se utilizarán para ordenar la consulta. array("Campo1", "Campo2 DESC")
     * @param int $inicioONumeroDeRegistros Si se especifica $numeroDeRegistros es el registro inicial que se seleccionará, de lo contrario es el total de registros a seleccionar.
     * @param int $numeroDeRegistros Total de registros a seleccionar.
     */
    public function __construct($campos = NULL, $filtros = NULL, $ordenamiento = NULL, $inicioONumeroDeRegistros = 0, $numeroDeRegistros = 0)
    {
        global $SESION;

        if($SESION->SesionIniciada()) // Si la sesión está iniciada
        {
            $usuario = $SESION->ObtenerUsuario();

            if(!$filtros) // Si los filtros no se han inicializado
            {
                $filtros = array();
            }

            $filtros["usuario_id"] = array( array("tipo"=>FDW_DATO_BDD_LOGICA_Y, "operador"=>FDW_DATO_BDD_OPERADOR_IGUAL, "operando"=>$usuario->ObtenerValor("id")) );
        }
        else // Si la sesión no está iniciada
        {
            throw new ModuloException("Es necesario una sesión iniciada para inicializar el Módulo.", "STM_Dato_Historiales");
        }

        parent::__construct($campos, $filtros, $ordenamiento, $inicioONumeroDeRegistros, $numeroDeRegistros);
    }



    /**
     * Verifica si se tiene permiso para realizar la acción sobre el Elemento
     * @param int $accion Acción a verificar
     * @return boolean
     */
    protected function PedirAutorizacion(int $accion):bool
    {
        return TRUE;
    }




    /**
     * Obtiene el nombre de la tabla en la base de datos utilizada por el Elemento
     * @return string
     */
    public static function ObtenerNombreTabla():string
    {
        return "historial";
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
            "id" => array("requerido" => true, "soloDeLectura" => TRUE, "nombre" => "ID", "tipoDeDato" => FDW_DATO_INT),
            "usuario_id" => array("requerido" => false, "soloDeLectura" => TRUE, "nombre" => "ID de Usuario", "tipoDeDato" => FDW_DATO_INT), // Requeridos pero se auto-asignan
            "tiempo" => array("requerido" => false, "soloDeLectura" => TRUE, "nombre" => "Tiempo", "tipoDeDato" => FDW_DATO_INT), // Requeridos pero se auto-asignan
            "elemento" => array("requerido" => true, "soloDeLectura" => TRUE, "nombre" => "Elemento", "tipoDeDato" => FDW_DATO_INT),
            "accion" => array("requerido" => true, "soloDeLectura" => TRUE, "nombre" => "Acción", "tipoDeDato" => FDW_DATO_INT),
            "elemento_id" => array("requerido" => FALSE, "soloDeLectura" => TRUE, "nombre" => "ID del Elemento", "tipoDeDato" => FDW_DATO_INT),
            "info" => array("requerido" => false, "soloDeLectura" => TRUE, "nombre" => "Información", "tipoDeDato" => FDW_DATO_STRING, "tamanoMaximo"=>65535),

            "usuario_usuario" => array("requerido" => false, "soloDeLectura" => false, "nombre" => "Usuario", "tipoDeDato" => FDW_DATO_STRING, "tamanoMaximo"=>255, "identificadorJoin"=>"u", "campoExterno"=>"usuario"),
            "usuario_nombre" => array("requerido" => false, "soloDeLectura" => false, "nombre" => "Usuario", "tipoDeDato" => FDW_DATO_STRING, "tamanoMaximo"=>200, "identificadorJoin"=>"u", "campoExterno"=>"usuario")
        );
    }

    /*
     * Obtiene un array asociativo de arrays asociativos con la información de los joins realizados en el Módulo. array( identificador => array( "tipo", "tabla", "campoInterno", "campoExterno" )  )
     * @return array Retorna un array de arrays asociativos con la información de los joins realizados en el Módulo. Retorna un array vacío si no hay joins. array( identificador => array( "tipo", "tabla", "campoInterno", "campoExterno" )  )
     */
    public static function ObtenerJoins():?array
    {
        return array("u" => array("tipo"=>"INNER", "tabla"=>\Mpsoft\STM\Sesion\Usuario::ObtenerNombreTabla(), "campoInterno" => "usuario_id", "campoExterno"=>"id"));
    }

    /**
     * Obtiene un arreglo de strings con los campos predeterminados del Elemento. Serán utilizado sólo en caso que no se especifiquen.
     * @return array
     */
    public static function ObtenerCamposPredeterminados():array
    {
        return array("id", "usuario_nombre", "tiempo", "elemento", "accion", "info");
    }

    /**
     * Obtiene el nombre del permiso del Modulo
     */
    public static function ObtenerNombrePermiso():string
    {
        return "Libre";
    }
}
