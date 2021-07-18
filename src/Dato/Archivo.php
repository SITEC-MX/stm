<?php
/**
 * Sistemas Especializados e Innovación Tecnológica, SA de CV
 * Mpsoft.STM - Framework de Desarrollo Web para PHP
 *
 * v.1.0.0.0 - 2018-02-19
 */
namespace Mpsoft\STM\Dato;

use \Exception;

abstract class Archivo extends \Mpsoft\STM\Dato\ElementoFDW
{
    /*
     * Obtiene el campo del Elemento que no es válido.
     * @return array Arreglo con la información del campo que no es válido. array( "campo" => campo, "error" => descripción del error ) o null si todos los campos son válidos.
     */
    public function ValidarDatos():?array
    {
        return null;
    }

    /**
     * Procedimiento que elimina el Elemento. Este método será invocado por el Elemento al ejecutar el método Eliminar.
     * _Eliminar se ejecuta después de ejecutar el evento AntesDeEliminar y antes de ejecutar el evento DespuesDeEliminar
     */
    protected function _Eliminar():void
    {
        global $CFG;

        $clase_actual = get_class($this);

        $ruta = $this->ObtenerValor("ruta");

        parent::_Eliminar();

        $clase_actual::BorrarArchivoDeDisco($ruta);
    }



    public function ObtenerURLDeRepositorio()
    {
        global $CFG;

        $ruta = $this->ObtenerValor("ruta");

        return "{$CFG->repositorio_www}/{$ruta}";
    }






    /**
     * Obtiene el nombre de la tabla en la base de datos utilizada por el Elemento
     * @return string
     */
    public static function ObtenerNombreTabla():string
    {
        return "archivo";
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
            "id" => array("requerido" => true, "soloDeLectura" => true, "nombre" => "ID", "tipoDeDato" => FDW_DATO_INT),
            "ruta" => array("requerido" => true, "soloDeLectura" => true, "nombre" => "Ruta", "tipoDeDato" => FDW_DATO_STRING, "tamanoMaximo"=>1024),
            "tipo" => array("requerido" => true, "soloDeLectura" => true, "nombre" => "Tipo de Elemento", "tipoDeDato" => FDW_DATO_STRING, "tamanoMaximo"=>100),
            "tipo_id" => array("requerido" => true, "soloDeLectura" => true, "nombre" => "ID de Elemento", "tipoDeDato" => FDW_DATO_INT),
            "nombre" => array("requerido" => true, "soloDeLectura" => true, "nombre" => "Nombre", "tipoDeDato" => FDW_DATO_STRING, "tamanoMaximo"=>50)
        );
    }

    /**
     * Obtiene el nombre del permiso del Modulo
     */
    public static function ObtenerNombrePermiso():string
    {
        return "Libre";
    }



    private static $extension = array
        (
            "image/jpeg" => "jpg",
            "image/png" => "png",
            "image/svg+xml" => "svg",
            "image/webp" => "webp",
            "application/pdf" => "pdf",
            "text/plain" => "txt",
            "text/html" => "html",
            "text/xml" => "xml",
            "audio/mpeg" => "mpg",
            "audio/ogg" => "ogg",
            "video/mp4" => "mp4"
        );

    private static $mimetype = NULL;

    protected static function _CrearArchivoSinAplicarCambiosInseguro($archivo_clase, $elemento_clase, $elemento_id, $nombre, $ruta_directorio, $contenido_archivo, $tiempo = NULL)
    {
        global $CFG;

        $archivo = NULL;

        $parte = explode(";", $contenido_archivo);

        $mimetype = substr($parte[0], 5); // Quitamos data:
        $contenido = substr($parte[1], 7); // Quitamos base64,

        if(isset( Archivo::$extension[$mimetype] )) // Si la extensión está soportada
        {
            // Borramos el archivo actual; si es que existe
            $archivos_existentes = Archivos::_ObtenerArchivosInseguro($elemento_clase, $elemento_id, $nombre);
            if(isset($archivos_existentes[$nombre])) // Si el archivo ya existe
            {
                $archivo_existete_id = $archivos_existentes[$nombre]["id"];

                $archivo_existente = new $archivo_clase($archivo_existete_id);

                $archivo_existente->Eliminar();
            }


            $extension = Archivo::$extension[$mimetype];


            // Escribimos el archivo en el disco
            $nombre_seguro = Archivo::ObtenerURLSegura($nombre);
            $time = $tiempo ? $tiempo : time();
            $nombre_archivo = "{$nombre_seguro}-{$time}.{$extension}";

            $archivo = new $archivo_clase();

            $archivo_ruta = $archivo_clase::EscribirArchivoEnDisco($archivo, $ruta_directorio, $nombre_archivo, base64_decode($contenido), $mimetype);
                        
            $archivo->AsignarValorSinValidacion("ruta",$archivo_ruta);
            $archivo->AsignarValorSinValidacion("tipo", $elemento_clase);
            $archivo->AsignarValorSinValidacion("tipo_id", $elemento_id);
            $archivo->AsignarValorSinValidacion("nombre",$nombre);
            $archivo->AplicarCambios();
        }
        else // Si la extensión no está soportada
        {
            throw new Exception("El archivo proporcionado no es válido.", STM_ARCHIVO_CREAR_EXTENSIONNOSOPORTADA);
        }

        return $archivo;
    }

    protected static function _CrearArchivoSinAplicarCambios($archivo_clase, \Mpsoft\FDW\Dato\Elemento $elemento, $nombre, $ruta_directorio, $contenido_archivo, $tiempo = NULL)
    {
        $archivo = null;

        if(!$elemento->TieneElCampo("activo") || $elemento->ObtenerValor("activo")) // Si el elemento está activo (o no tiene estado)
        {
            $archivo = Archivo::_CrearArchivoSinAplicarCambiosInseguro($archivo_clase, get_class($elemento), $elemento->ObtenerValor("id"), $nombre, $ruta_directorio, $contenido_archivo, $tiempo);
        }
        else // Si el elemento no está activo
        {
            throw new Exception("Se necesita un elemento activo para crear el archivo.", STM_ARCHIVO_CREAR_ELEMENTOINACTIVO);
        }

        return $archivo;
    }





    public static function ObtenerURLSegura($string)
    {
		// Remove any '-' from the string since they will be used as concatenaters
		$str = str_replace('-', ' ', $string);

		// Trim white spaces at beginning and end of alias and make lowercase
		$str = trim(strtolower($str));

		// Remove any duplicate whitespace, and ensure all characters are alphanumeric
		$str = preg_replace('/(\s|[^A-Za-z0-9\-])+/', '-', $str);

		// Trim dashes at beginning and end of alias
		$str = trim($str, '-');

		return $str;
    }

    public static function ObtenerMimeType($extension)
    {
        if(!Archivo::$mimetype) // Si el arreglo no se ha inicializado
        {
            Archivo::$mimetype = array_flip(Archivo::$extension);
        }

        return isset(Archivo::$mimetype[$extension]) ? Archivo::$mimetype[$extension] : NULL;
    }

    public static function EscribirArchivoEnDisco(Archivo $archivo, string $ruta_directorio, string $nombre_archivo, string $contenido_archivo, string $mimetype):string
    {
        global $CFG;

        // Creamos la ruta en el repositorio
        $partes_ruta_directorio = explode("/", $ruta_directorio);
        foreach($partes_ruta_directorio as $indice=>$parte) // Para cada parte de la URL
        {
            $partes_ruta_directorio[$indice] = Archivo::ObtenerURLSegura($parte);
        }

        $directorio_ruta = implode("/", $partes_ruta_directorio);
        mkdir("{$CFG->repositorio_ruta}/{$directorio_ruta}", 0777, true);

        // Colocamos el archivo
        $archivo_ruta  = "{$directorio_ruta}/{$nombre_archivo}";
        file_put_contents("{$CFG->repositorio_ruta}/{$archivo_ruta}", $contenido_archivo);

        return $archivo_ruta;
    }

    public static function BorrarArchivoDeDisco($ruta_archivo)
    {
        global $CFG;

        unlink("{$CFG->repositorio_ruta}/{$ruta_archivo}");
    }
}

define("STM_ARCHIVO_CREAR_ELEMENTONOVALIDO", 1);
define("STM_ARCHIVO_CREAR_ELEMENTOINACTIVO", 2);
define("STM_ARCHIVO_CREAR_EXTENSIONNOSOPORTADA", 3);

