<?php
/**
 * Sistemas Especializados e Innovación Tecnológica, SA de CV
 * Mpsoft.STM - Framework de Desarrollo Web para PHP
 *
 * v.2.0.0.0 - 2020-09-13
 */

function EstablecerCodigoDeEstadoHTTP(int $codigo)
{
    $SERVER_PROTOCOL = $_SERVER["SERVER_PROTOCOL"];

    $estados = array
    (
        100 => '100 Continue',
        101 => '101 Switching Protocols',
        200 => '200 OK',
        201 => '201 Created',
        202 => '202 Accepted',
        203 => '203 Non-Authoritative Information',
        204 => '204 No Content',
        205 => '205 Reset Content',
        206 => '206 Partial Content',
        300 => '300 Multiple Choices',
        301 => '301 Moved Permanently',
        302 => '302 Found',
        303 => '303 See Other',
        304 => '304 Not Modified',
        305 => '305 Use Proxy',
        307 => '307 Temporary Redirect',
        400 => '400 Bad Request',
        401 => '401 Unauthorized',
        402 => '402 Payment Required',
        403 => '403 Forbidden',
        404 => '404 Not Found',
        405 => '405 Method Not Allowed',
        406 => '406 Not Acceptable',
        407 => '407 Proxy Authentication Required',
        408 => '408 Request Time-out',
        409 => '409 Conflict',
        410 => '410 Gone',
        411 => '411 Length Required',
        412 => '412 Precondition Failed',
        413 => '413 Request Entity Too Large',
        414 => '414 Request-URI Too Large',
        415 => '415 Unsupported Media Type',
        416 => '416 Requested Range Not Satisfiable',
        417 => '417 Expectation Failed',
        429 => '429 Too Many Requests',
        500 => '500 Internal Server Error',
        501 => '501 Not Implemented',
        502 => '502 Bad Gateway',
        503 => '503 Service Unavailable',
        504 => '504 Gateway Time-out',
        505 => '505 HTTP Version Not Supported'
    );

    if(isset($estados[$codigo])) // Si el código existe
    {
        $estado = $estados[$codigo];

        header("{$SERVER_PROTOCOL} {$estado}");
    }
    else // Si el código no existe
    {
        throw new Exception("El código de estado HTTP no es válido.");
    }
}

function GenerarURLAvatar(string $nombre)
{
    // 20 colores obscuros
    $colores = array("283D3B","197278","9E6442","C44536","772E25","003049","D62828","F77F00","F1A204","8E7F29","A31420","254F17","2F7275","3E6F8E","1D3557","264653","2A9D8F","D5A220","D2660F","D7431D");

    // Generamos un número a partir del nombre
    $hash = md5($nombre, TRUE);
    $numero = ord($hash[5]) + ord($hash[10]); // Tomamos 2 letras del hash
    $indice_color = $numero%20;
    $color = $colores[$indice_color];

    // Iniciales
    $iniciales = "";
    if( strlen($nombre) == 0 ) // Si el nombre es una cadena vacía
    {
        $iniciales = "MP";
    }
    else // Si el nombre tiene caracteres
    {
        $elementos = explode(" ", $nombre, ); // Separamos el nombre por palabra

        foreach($elementos as $elemento) // Para cada palabra del nombre
        {
            if(strlen($elemento)>0) // Si hay palabra
            {
                $iniciales .= $elemento[0];
            }
        }
    }

    return "https://ui-avatars.com/api/?size=256&background={$color}&color=fff&name={$iniciales}";
}