<?php
/**
 * Sistemas Especializados e Innovación Tecnológica, SA de CV
 * Mpsoft.STM - Framework de Desarrollo Web para PHP
 *
 * v.2.0.0.0 - 2020-10-04
 */
namespace Mpsoft\STM\Dato;

class GuardarConfiguracionException extends \Exception
{
    private $configuracion_nombre = NULL;

    function __construct (string $message = "", string $configuracion_nombre = "", int $code = 0 , Throwable $previous = NULL)
    {
        $this->configuracion_nombre = $configuracion_nombre;
    }

    public function ObtenerConfiguracionNombre()
    {
        return $this->configuracion_nombre;
    }
}