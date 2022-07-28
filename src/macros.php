<?php
/**
 * Sistemas Especializados e Innovación Tecnológica, SA de CV
 * Mpsoft.STM - Framework de Desarrollo Web para PHP
 *
 * v.2.0.0.0 - 2020-09-13
 */

define("TOKEN_BLOQUEADO", -100);                          // El token está bloqueado

// API
define("API_ELEMENTO_BLOQUEADO", 200);                    // Se intentó modificar un Elemento bloqueado por otro usuario
define("API_ELEMENTO_ELIMINADO", 201);                    // Se intentó modificar un Elemento eliminado (inactivo)
define("API_ELEMENTO_INACTIVO", 202);                     // Ocurre cuando se intenta eliminar un Elemento que ya está inactivo.



// Permisos
define("STM_PERMISO_TOKEN_VER_BLOQUEANTE", 16);
define("STM_PERMISO_TOKEN_DESBLOQUEARELEMENTO", 32);






define("STM_ELEMENTO_SUBELEMENTO_NUEVO", 1);
define("STM_ELEMENTO_SUBELEMENTO_SINCAMBIOS", 2);
define("STM_ELEMENTO_SUBELEMENTO_CONCAMBIOS", 3);
define("STM_ELEMENTO_SUBELEMENTO_ELIMINADO", 4);






// Acciones
define("STM_HISTORIAL_ACCION_INICIARSESION", 0);
define("STM_HISTORIAL_ACCION_CERRARSESION", 1);
define("STM_HISTORIAL_ACCION_CAMBIARCONTRASENA", 2);

define("STM_HISTORIAL_ACCION_OBTENER", 3);
define("STM_HISTORIAL_ACCION_AGREGAR", 4);
define("STM_HISTORIAL_ACCION_MODIFICAR", 5);
define("STM_HISTORIAL_ACCION_ELIMINAR", 6);

define("STM_HISTORIAL_ACCION_USUARIO_REACTIVAR", 7);