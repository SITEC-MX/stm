<?php
/**
 * Sistemas Especializados e Innovaci�n Tecnol�gica, SA de CV
 * Mpsoft.STM - Framework de Desarrollo Web para PHP
 *
 * v.2.0.0.0 - 2018-03-02
 */
namespace Mpsoft\STM\Dato;

class Configuracion extends \Mpsoft\STM\Dato\ElementoFDW
{
    /**
     * Constructor del Elemento
     * @param ?int $id ID del Elemento o NULL para inicializar un Elemento nuevo.
     */
    public function __construct(?int $id = NULL)
    {
        parent::__construct($id);

        if ($id > 0) // Si es un Elemento existente
        {
        }
        else // Si es un Elemento nuevo
        {
            $this->AsignarValorSinValidacion("forzar", false);
        }
    }

    protected function PuedeAccederAEsteElemento():void
    {
    }





















    public static function ObtenerConfiguracionesDelSistema()
    {
        global $CONFIGURACIONES_DISPONIBLES;

        // Obtenemos la configuraci�n del sistema
        $modulo = new Configuraciones
            (
                array("id", "nombre", "valor", "forzar"), // Campos
                array // Filtros
                (
                    "usuario_id" => array( array("operador" => FDW_DATO_BDD_OPERADOR_IGUAL, "operando"=>NULL)  )
                )
            );

        $configuraciones_existentes = array();
        while($configuracion = $modulo->ObtenerSiguienteRegistro()) // Para cada configuraci�n
        {
            $nombre = $configuracion["nombre"];
            unset($configuracion["nombre"]);

            // Colocamos los par�metros de la configuraci�n
            if(isset($CONFIGURACIONES_DISPONIBLES[$nombre])) // Si la configuraci�n existe
            {
                $configuracion = array_merge($configuracion, $CONFIGURACIONES_DISPONIBLES[$nombre]);
            }
            else // Si la configuraci�n no existe
            {
                $configuracion["tipo"] = FDW_DATO_STRING;
            }

            // Componemos el tipo de datos
            $configuracion["valor"] = \Mpsoft\FDW\Dato\BdD::ConvertirATipoDeDato($configuracion["valor"], $configuracion["tipo"]);

            $configuracion["sistema"] = TRUE;

            $configuraciones_existentes[$nombre] = $configuracion;
        }

        return $configuraciones_existentes;
    }

    public static function ObtenerConfiguracionDelSistema(string $nombre_configuracion)
    {
        $valor = NULL;

        $configuracion = Configuracion::ObtenerConfiguracionesDelSistema();
        if(isset($configuracion[$nombre_configuracion])) // Si la configuraci�n existe
        {
            $valor = $configuracion[$nombre_configuracion]["valor"];
        }

        return $valor;
    }
















    /**
     * Obtiene el nombre de la tabla en la base de datos utilizada por el Elemento
     * @return string
     */
    public static function ObtenerNombreTabla():string
    {
        return "configuracion";
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
        return array
        (
            "id" => array("requerido" => true, "soloDeLectura" => true, "nombre" => "ID", "tipoDeDato" => FDW_DATO_INT),
            "usuario_id" => array("requerido" => false, "soloDeLectura" => true, "nombre" => "ID del Usuario", "tipoDeDato" => FDW_DATO_INT),
            "nombre" => array("requerido" => true, "soloDeLectura" => false, "nombre" => "Nombre de la configuraci�n", "tipoDeDato" => FDW_DATO_STRING, "tamanoMaximo"=>50),
            "valor" => array("requerido" => true, "soloDeLectura" => true, "nombre" => "Valor de la configuraci�n", "tipoDeDato" => FDW_DATO_STRING, "tamanoMaximo"=>65535),
            "forzar" => array("requerido" => true, "soloDeLectura" => false, "nombre" => "Forzar", "tipoDeDato" => FDW_DATO_BOOL)
        );
    }

    /**
     * Obtiene el nombre del permiso del Elemento
     */
    public static function ObtenerNombrePermiso():string
    {
        return "Libre";
    }




    /*
     * Obtiene el campo del Elemento que no es v�lido.
     * @return array Arreglo con la informaci�n del campo que no es v�lido. array( "campo" => campo, "error" => descripci�n del error ) o null si todos los campos son v�lidos.
     */
    public function ValidarDatos():?array
    {
        $errores = NULL;


        $es_sistema = $this->ObtenerValor("usuario_id") == NULL;
        $forzar = $this->ObtenerValor("forzar");

        if(!$es_sistema && $forzar) // No se puede forzar una configuraci�n de usuario
        {
            $errores =  array( "campo" => "forzar", "error" => "Una configuraci�n de usuario no se puede forzar.");
        }

        return $errores;
    }










    public function AsignarValorConfiguracion($valor):void
    {
        $valor_string = \Mpsoft\FDW\Dato\BdD::ConvertirATipoDeDato($valor, FDW_DATO_STRING);

        $this->AsignarValorSinValidacion("valor", $valor_string);
    }



    public function AsignarUsuario(\Mpsoft\STM\Sesion\Usuario $usuario):void
    {
        \Mpsoft\STM\Dato\Elemento::AsignarElemento($this, "usuario_id", $usuario);
    }
}
