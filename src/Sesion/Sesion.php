<?php
/**
 * Sistemas Especializados e Innovación Tecnológica, SA de CV
 * Mpsoft.STM - Framework de Desarrollo Web para PHP
 *
 * v.1.0.0.0 - 2021-01-15
 */
namespace Mpsoft\STM\Sesion;

use \Exception;

abstract class Sesion extends \Mpsoft\FDW\Sesion\Sesion
{
    public function __construct()
    {
        parent::__construct();

        $this->VincularEvento("sesion_iniciada_correctamente", "CargarPermisosDeUsuario", function(){ $this->CargarPermisos(); });
        $this->VincularEvento("sesion_reiniciada_correctamente", "CargarPermisosDeUsuario", function(){ $this->CargarPermisos(); });

        $this->VincularEvento("sesion_reiniciada_correctamente", "GuardarUltimaAccion", function(){ $this->GuardarUltimaAccion(); });
    }




    protected $permisos = array();

    private function CargarPermisos():void
    {
        // La sesión se acaba de iniciar y no tiene permisos. Le permitimos cargar los roles para inicializar los permisos.
        $rol_permiso_nombre = Rol::ObtenerNombrePermiso();
        $this->AgregarPermiso($rol_permiso_nombre, FDW_DATO_PERMISO_OBTENER);

        $permisos_del_usuario = $this->ObtenerPermisosDeUsuario($this->usuario);

        $this->QuitarPermiso($rol_permiso_nombre, FDW_DATO_PERMISO_OBTENER);

        $this->permisos = $permisos_del_usuario;
    }

    protected function ObtenerPermisosDeUsuario(Usuario $usuario):array
    {
        $roles = Rol::CargarRolesDeUsuario($this->usuario);

        $permisos = $this->CargarPermisosDeRoles($roles);

        return $permisos;
    }

    protected function CargarPermisosDeRoles(array $roles):array
    {
        $permisos = array();

        foreach($roles as $rol) // Para cada rol
        {
            $permisos_del_rol = $rol->ObtenerPermisos();

            foreach($permisos_del_rol as $permiso_nombre=>$permiso_valor) // Para cada permiso del rol
            {
                $permiso_actual = isset($permisos[$permiso_nombre]) ? $permisos[$permiso_nombre] : 0;

                $permisos[$permiso_nombre] = $permiso_actual | $permiso_valor;
            }
        }

        return $permisos;
    }

    public function ForzarPermiso(string $modulo, int $permiso):void
    {
        $this->permisos[$modulo] = $permiso;
    }

    public function AgregarPermiso(string $modulo, int $permiso):void
    {
        $permiso_final = isset($this->permisos[$modulo]) ? // Si el permiso ya existe
            $this->permisos[$modulo] | $permiso : // El permiso final es el permiso actual + nuevo permiso
            $permiso;

        $this->permisos[$modulo] = $permiso_final;
    }

    public function QuitarPermiso(string $modulo, int $permiso):void
    {
        if(isset($this->permisos[$modulo])) // Si el permiso está definido
        {
            $this->permisos[$modulo] = $this->permisos[$modulo] & ~$permiso; // El permiso final es el permiso actual - permiso a quitar
        }
    }

    public function ObtenerPermisos():array
    {
        return $this->permisos;
    }

    public function ObtenerPermiso(string $modulo):int
    {
        return isset($this->permisos[$modulo]) ? $this->permisos[$modulo] : 0;
    }

    public function PedirAutorizacion(string $modulo, int $permiso):bool
    {
        // Libre siempre es TRUE
        $tiene_permiso = $modulo == "Libre";

        if( isset($this->permisos[$modulo]) ) // Si el permiso existe
        {
            $tiene_permiso = ($this->permisos[$modulo] & $permiso) == $permiso;
        }

        return $tiene_permiso;
    }






    private function GuardarUltimaAccion():void
    {
        if( !$this->TokenBloqueado() ) // Si el token no está bloqueado
        {
            $this->token->ActualizarUltimaAccion();
        }
    }







    public function AsignarTFASecretoAUsuario():string
    {
        $secreto = NULL;

        if(!$this->usuario->ObtenerValor("tfa_habilitado")) // Si el usuario no tiene TFA habilitado
        {
            $clase_sesion = get_class($this);

            $secreto = $clase_sesion::_GenerarTFASecreto();

            $this->usuario->AsignarTFASecreto($secreto);
        }
        else // Si el usuario tiene TFA habilitado
        {
            throw new Exception("El usuario ya tiene TFA habilitado.");
        }

        return $secreto;
    }

    public function HabilitarTFAUsuario(string $codigo):bool
    {
        $exito = FALSE;

        if(!$this->usuario->ObtenerValor("tfa_habilitado")) // Si el usuario no tiene TFA habilitado
        {
            $secreto = $this->usuario->ObtenerValor("tfa_secreto");

            if($secreto) // Si el usuario ya tiene secreto TFA
            {
                $clase_sesion = get_class($this);

                if($clase_sesion::_ValidarTFA($codigo, $secreto)) // Si el código TFA es válido
                {
                    $this->usuario->HabilitarTFA();

                    $exito = TRUE;
                }
            }
            else // Si el usuario no tiene secreto TFA
            {
                throw new Exception("El usuario no tiene secreto TFA.");
            }
        }
        else // Si el usuario tiene TFA habilitado
        {
            throw new Exception("El usuario ya tiene TFA habilitado.");
        }

        return $exito;
    }

    public function TokenBloqueado():bool
    {
        return $this->token->ObtenerValor("bloqueado");
    }

    public function DesbloquearToken(string $codigo):bool
    {
        $exito = FALSE;

        if( $this->SesionIniciada() ) // Si la sesión está iniciada
        {
            if( $this->TokenBloqueado() ) // Si el token está bloqueado
            {
                $secreto = $this->usuario->ObtenerValor("tfa_secreto");

                $clase_sesion = get_class($this);

                if($clase_sesion::_ValidarTFA($codigo, $secreto)) // Si el código TFA es válido
                {
                    $this->token->Desbloquear();

                    $exito = TRUE;
                }
            }
            else // Si el token no está bloqueado
            {
                throw new Exception("El token no está bloqueado.");
            }
        }
        else // Si la sesión no está iniciada
        {
            throw new Exception("La sesión no está iniciada.");
        }

        return $exito;
    }









    protected function UsuarioPuedeIniciarSesion(\Mpsoft\FDW\Sesion\Usuario $usuario):bool
    {
        if(!$usuario->ObtenerValor("activo"))
        {
            throw new Exception("El usuario no está activo.");
        }

        if($usuario->ObtenerValor("suspendido"))
        {
            throw new Exception("El usuario está suspendido.");
        }

        return TRUE;
    }







    public function ObtenerConfiguraciones():array
    {
        $usuario = $this->ObtenerUsuario();

        $configuraciones = NULL;

        if($usuario) // Si hay un usuario en sesión
        {
            $configuraciones = $usuario->ObtenerConfiguraciones();
        }
        else // Si no hay un usuario en sesion
        {
            throw new Exception("Sesión sin usuario - No implementado");
        }

        return $configuraciones;
    }

    public function ObtenerConfiguracion(string $nombre):?array
    {
        $usuario = $this->ObtenerUsuario();

        $configuracion = NULL;
        if($usuario) // Si hay un usuario en sesión
        {
            $configuracion = $usuario->ObtenerConfiguracion($nombre);
        }
        else // Si no hay un usuario en sesion
        {
            throw new Exception("Sesión sin usuario - No implementado");
        }

        return $configuracion;
    }




    public abstract static function _GenerarTFASecreto():string;

    public abstract static function _ValidarTFA(string $codigo, string $secreto):bool;
}