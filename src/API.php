<?php
/**
 * Sistemas Especializados e Innovación Tecnológica, SA de CV
 * Mpsoft.STM - Framework de Desarrollo Web para PHP
 *
 * v.2.0.0.0 - 2021-02-04
 */

use \Mpsoft\STM\Sesion\Token;

function STM_GET_Elemento(string $elemento_clase, ?int $elemento_id = NULL, ?callable $preparar_elemento_inicializado = NULL, ?callable $obtener_resultado=NULL):array
{
    return FDW_GET_Elemento($elemento_clase,  $elemento_id, "STM_Bloquear_Elemento", "STM_Obtener_Resultado");
}

function STM_POST_Elemento(array $OPENAPI_REQUEST, string $elemento_clase, ?int $elemento_id = NULL, ?callable $guardar_informacion_adicional = NULL, ?callable $procesar_exception_aplicarcambios=NULL, &$elemento = NULL):array
{
    $campos_a_ignorar = array("activo", "bloqueo_tiempo", "bloqueo_token_id", "creacion", "modificacion");

    return FDW_POST_Elemento($OPENAPI_REQUEST, $elemento_clase, $elemento_id, "STM_Verificar_Elemento_Inicializado", $campos_a_ignorar, $guardar_informacion_adicional, "STM_Obtener_Resultado", $procesar_exception_aplicarcambios, $elemento);
}

function STM_DELETE_Elemento(string $elemento_clase, ?int $elemento_id = NULL, ?callable $preparar_elemento_inicializado = NULL):array
{
    return FDW_DELETE_Elemento($elemento_clase, $elemento_id, "STM_Puede_Eliminar");
}

function STM_Verificar_Elemento_Inicializado($elemento):?array
{
    $estado = NULL;

    if(!$elemento->EsNuevo()) // Si es un Elemento existente
    {
        // Verificamos que el usuario actual tenga el bloqueo
        if( !$elemento->BloqueadoPorTokenEnSesion() ) // Si el usuario no tiene el bloqueo
        {
            $estado = array();
            $estado["estado"] = API_ELEMENTO_BLOQUEADO;
            $estado["mensaje"] = utf8_encode("El Elmento se encuentra bloqueado por otro usuario.");
        }

        // Verificamos que el Elemento esté activo
        if(!$elemento->ObtenerValor("activo") ) // Si el Elemento no está activo
        {
            $estado = array();
            $estado["estado"] = API_ELEMENTO_ELIMINADO;
            $estado["mensaje"] = utf8_encode("El Elmento se encuentra inactivo.");
        }
    }

    return $estado;
}

function STM_Obtener_Resultado($elemento):?array
{
    global $SESION; // En sistemas STM se asume la existencia de la variable global $SESION

    $puede_desbloquear = $SESION->PedirAutorizacion( Token::ObtenerNombrePermiso(), STM_PERMISO_TOKEN_DESBLOQUEARELEMENTO);
    $puede_ver_bloqueante = $SESION->PedirAutorizacion( Token::ObtenerNombrePermiso(), STM_PERMISO_TOKEN_VER_BLOQUEANTE);

    $resultado = array();
    $resultado["valores"] = $elemento->ObtenerValores();

    $bloqueado = $elemento->Bloquear();

    // Obtenemos el token bloqueante sólo si tiene permisos para desbloquer, si no no tiene caso mandarlo
    $token_id = NULL;
    if($puede_desbloquear) // Si tiene permiso para desbloquear el Elemento
    {
        $token_id = $elemento->ObtenerValor("bloqueo_token_id");
    }

    $bloqueo_nombre = NULL;
    if(!$bloqueado && $puede_ver_bloqueante) // Si no está bloqueado y podemos ver el nombre del usuario bloqueante
    {
        $bloqueo_nombre = $elemento->ObtenerTokenBloqueanteNombre();
    }

    $resultado["bloqueo"] = array("bloqueado"=>$bloqueado, "nombre"=>$bloqueo_nombre, "token_id"=>$token_id);

    return $resultado;
}

function STM_Bloquear_Elemento($elemento):?array
{
    $elemento->Bloquear();

    return NULL;
}

function STM_Puede_Eliminar($elemento)
{
    $estado = NULL;

    // Verificamos que el Elemento esté activo
    if(!$elemento->ObtenerValor("activo") ) // Si el Elemento no está activo
    {
        $estado = array();
        $estado["estado"] = API_ELEMENTO_INACTIVO;
        $estado["mensaje"] = utf8_encode("El Elmento se encuentra inactivo.");
    }

    return $estado;
}