<?php

namespace App\Enums;

enum AuditAction: string
{
    case IncidenciaCreada = 'incidencia_creada';
    case IncidenciaAprobada = 'incidencia_aprobada';
    case IncidenciaRechazada = 'incidencia_rechazada';
    case IncidenciaEnviadaSindicato = 'incidencia_enviada_sindicato';
    case IncidenciaComentada = 'incidencia_comentada';
    case IncidenciaArchivoAdjuntado = 'incidencia_archivo_adjuntado';
    case UsuarioCreado = 'usuario_creado';
    case UsuarioActualizado = 'usuario_actualizado';
    case UsuarioEliminado = 'usuario_eliminado';
    case UsuarioActivado = 'usuario_activado';
    case UsuarioDesactivado = 'usuario_desactivado';
    case AreaCreada = 'area_creada';
    case AreaActualizada = 'area_actualizada';
    case AreaEliminada = 'area_eliminada';
    case EmpleadoCreado = 'empleado_creado';
    case EmpleadoActualizado = 'empleado_actualizado';
    case EmpleadosImportados = 'empleados_importados';
    case Login = 'login';
    case Logout = 'logout';
    case SeguimientoLogin = 'seguimiento_login';
    case SeguimientoLogout = 'seguimiento_logout';
    case ReporteExportado = 'reporte_exportado';
    case ComprobanteDescargado = 'comprobante_descargado';
    case ArchivoConsultado = 'archivo_consultado';

    public function label(): string
    {
        return match ($this) {
            self::IncidenciaCreada => 'Incidencia creada',
            self::IncidenciaAprobada => 'Incidencia aprobada',
            self::IncidenciaRechazada => 'Incidencia rechazada',
            self::IncidenciaEnviadaSindicato => 'Incidencia enviada a Sindicato',
            self::IncidenciaComentada => 'Comentario en incidencia',
            self::IncidenciaArchivoAdjuntado => 'Archivo adjuntado a incidencia',
            self::UsuarioCreado => 'Usuario creado',
            self::UsuarioActualizado => 'Usuario actualizado',
            self::UsuarioEliminado => 'Usuario eliminado',
            self::UsuarioActivado => 'Usuario activado',
            self::UsuarioDesactivado => 'Usuario desactivado',
            self::AreaCreada => 'Área creada',
            self::AreaActualizada => 'Área actualizada',
            self::AreaEliminada => 'Área eliminada',
            self::EmpleadoCreado => 'Empleado creado',
            self::EmpleadoActualizado => 'Empleado actualizado',
            self::EmpleadosImportados => 'Empleados importados',
            self::Login => 'Inicio de sesión interno',
            self::Logout => 'Cierre de sesión interno',
            self::SeguimientoLogin => 'Inicio de sesión en seguimiento',
            self::SeguimientoLogout => 'Cierre de sesión en seguimiento',
            self::ReporteExportado => 'Reporte exportado',
            self::ComprobanteDescargado => 'Comprobante descargado',
            self::ArchivoConsultado => 'Archivo consultado',
        };
    }

    public function category(): string
    {
        return match ($this) {
            self::IncidenciaCreada,
            self::IncidenciaAprobada,
            self::IncidenciaRechazada,
            self::IncidenciaEnviadaSindicato,
            self::IncidenciaComentada,
            self::IncidenciaArchivoAdjuntado => 'Incidencias',
            self::UsuarioCreado,
            self::UsuarioActualizado,
            self::UsuarioEliminado,
            self::UsuarioActivado,
            self::UsuarioDesactivado => 'Usuarios',
            self::AreaCreada,
            self::AreaActualizada,
            self::AreaEliminada => 'Áreas',
            self::EmpleadoCreado,
            self::EmpleadoActualizado,
            self::EmpleadosImportados => 'Empleados',
            self::Login,
            self::Logout,
            self::SeguimientoLogin,
            self::SeguimientoLogout => 'Autenticación',
            self::ReporteExportado => 'Reportes',
            self::ComprobanteDescargado,
            self::ArchivoConsultado => 'Documentos',
        };
    }
}
