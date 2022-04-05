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
        $clase_actual::BorrarArchivoDeDisco($this);

        parent::_Eliminar();
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
            "video/mp4" => "mp4",
            "application/zip" => "zip"
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
        $str = self::QuitarAcentos($string);

		// Remove any '-' from the string since they will be used as concatenaters
		$str = str_replace('-', ' ', $str);

		// Trim white spaces at beginning and end of alias and make lowercase
		$str = trim(strtolower($str));

		// Remove any duplicate whitespace, and ensure all characters are alphanumeric
		$str = preg_replace('/(\s|[^A-Za-z0-9\-])+/', '-', $str);

		// Trim dashes at beginning and end of alias
		$str = trim($str, '-');

		return $str;
    }

    public static function QuitarAcentos($string)
    {
        if ( !preg_match('/[\x80-\xff]/', $string) )
            return $string;

        $chars = array(
        // Decompositions for Latin-1 Supplement
        chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
        chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
        chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
        chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
        chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
        chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
        chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
        chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
        chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
        chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
        chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
        chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
        chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
        chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
        chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
        chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
        chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
        chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
        chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
        chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
        chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
        chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
        chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
        chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
        chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
        chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
        chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
        chr(195).chr(191) => 'y',
        // Decompositions for Latin Extended-A
        chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
        chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
        chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
        chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
        chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
        chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
        chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
        chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
        chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
        chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
        chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
        chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
        chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
        chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
        chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
        chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
        chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
        chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
        chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
        chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
        chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
        chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
        chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
        chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
        chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
        chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
        chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
        chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
        chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
        chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
        chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
        chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
        chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
        chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
        chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
        chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
        chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
        chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
        chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
        chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
        chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
        chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
        chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
        chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
        chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
        chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
        chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
        chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
        chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
        chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
        chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
        chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
        chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
        chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
        chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
        chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
        chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
        chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
        chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
        chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
        chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
        chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
        chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
        chr(197).chr(190) => 'z', chr(197).chr(191) => 's'
        );
        $string = strtr($string, $chars);
        return $string;
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

    public static function BorrarArchivoDeDisco(Archivo $archivo):void
    {
        global $CFG;

        $ruta_archivo = $archivo->ObtenerValor("ruta");

        unlink("{$CFG->repositorio_ruta}/{$ruta_archivo}");
    }
}

define("STM_ARCHIVO_CREAR_ELEMENTONOVALIDO", 1);
define("STM_ARCHIVO_CREAR_ELEMENTOINACTIVO", 2);
define("STM_ARCHIVO_CREAR_EXTENSIONNOSOPORTADA", 3);

