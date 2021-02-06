<?php
/**
 * Sistemas Especializados e Innovaci�n Tecnol�gica, SA de CV
 * Mpsoft.STM - Framework de Desarrollo Web para PHP
 *
 * v.2.0.0.0 - 2020-09-13
 */

define("TOKEN_BLOQUEADO", -100);                          // El token est� bloqueado

// API
define("API_ELEMENTO_BLOQUEADO", 200);                    // Se intent� modificar un Elemento bloqueado por otro usuario
define("API_ELEMENTO_ELIMINADO", 201);                    // Se intent� modificar un Elemento eliminado (inactivo)
define("API_ELEMENTO_INACTIVO", 202);                     // Ocurre cuando se intenta eliminar un Elemento que ya est� inactivo.