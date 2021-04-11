<?php
/**
 * Sistemas Especializados e Innovación Tecnológica, SA de CV
 * Mpsoft.STM - Framework de Desarrollo Web para PHP
 *
 * v.1.0.0.0 - 2018-12-01
 */
namespace Mpsoft\STM\Core;

use \Mpsoft\FDW\Core\Parametro;
use \Mpsoft\FDW\Dato\Elemento;
use \Exception;

abstract class Formato
{
    static function RemplazarVariables($texto_con_variables, $datos)
    {
        global $CFG;

        preg_match_all("/{{(\w+(\.\w+){0,1})}}/", $texto_con_variables, $variables_encontradas); // Obtenemos las etiquetas a remplazar
        $variables_a_remplazar = array_flip($variables_encontradas[1]); // Quitamos las variables repetidas

        foreach ($variables_a_remplazar as $variable => $tmp) // Para cada variable a remplazar
        {
            $valor = NULL;

            $indice_punto = strpos($variable, "."); // Obtenemos la posición del separador (si es que existe)
            if($indice_punto !== false) // Si hay un separador (xx . xx) [Es una variable de un elemento o de un arreglo]
            {
                $elemento = substr($variable, 0, $indice_punto);
                $campo = substr($variable, $indice_punto + 1);

                if(isset($datos[$elemento])) // Si el Elemento (o arreglo) se envía
                {
                    if(is_array($datos[$elemento])) // Si el objeto es un arreglo
                    {
                        if(array_key_exists($campo, $datos[$elemento])) // Si el campo existe dentro del arreglo
                        {
                            if($valor = $datos[$elemento][$campo]) // Si hay un valor
                            {
                                $valor = Parametro::LimpiarValor($valor, FDW_DATO_STRING);
                                $valor = addslashes($valor);
                            }
                            else // Si no hay valor
                            {
                                $valor = "";
                            }
                        }
                    }
                    else // Si el objeto no es un arreglo
                    {
                        if(is_a($datos[$elemento], Elemento::class )) // Si es un Elemento
                        {
                            if($datos[$elemento]->TieneElCampo($campo))
                            {
                                $valor = $datos[$elemento]->ObtenerValor($campo);
                                $valor = Parametro::LimpiarValor($valor, FDW_DATO_STRING);

                                if(!$valor) // Si el valor obtenido es nulo
                                {
                                    $valor = " ";
                                }
                            }
                        }
                        else // Si no es un Elemento (ni un arreglo)
                        {
                            throw new Exception("El objeto de datos proporcionado en '{$elemento}' no es válido.");
                        }
                    }
                }
            }
            else // Si no hay separador (xx . xx) [Es una variable de $CFG]
            {
                if(isset($CFG->$variable)) // Si el campo especificado existe en $CFG
                {
                    $valor =  $CFG->$variable;
                }
            }

            if($valor === "") {$valor = " ";} // El generador de PDF no permite cadenas vacías.

            if($valor !== NULL) // Si hay un valor a remplazar
            {
                $texto_con_variables = str_replace("{{" . $variable . "}}", $valor, $texto_con_variables);
            }
        }

        return $texto_con_variables;
    }
}