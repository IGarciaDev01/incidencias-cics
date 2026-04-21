---
name: Sistema de Incidencias Universitario
description: Stack técnico, flujo de negocio, estructura de archivos generados y decisiones de arquitectura
type: project
---

## Stack
- Laravel 13 + PHP 8.3
- Inertia.js 3 + React + TypeScript (frontend aún por construir)
- Fortify (solo para 2FA y settings; rutas de auth desactivadas via Fortify::ignoreRoutes())
- SQLite (dev) / MySQL (prod)
- Pest (tests)

## Flujo de negocio
Todo reporte cae primero a Subdirección:
1. `abierta` → usuario reporta (público o con cuenta)
2. `en_revision` → subdirector toma la incidencia
3. `aprobada` → subdirector aprueba + asigna área + coordinador + calcula SLA
4. `rechazada` → subdirector rechaza con motivo
5. `en_proceso` → coordinador inicia atención
6. `resuelta` → coordinador provee resolución
7. `cerrada` → estado final
- Coordinador puede **escalar** (vuelve a `en_revision`)
- Reportante puede **reabrir** incidencias resueltas/cerradas

## Archivos generados (backend completo)

### Enums: `app/Enums/`
- RolUsuario, Prioridad, EstadoIncidencia, TipoAccionHistorial, TipoNotificacion

### Models: `app/Models/`
- User (modificado), Area, Categoria, Incidencia, HistorialIncidencia, ArchivoAdjunto, Notificacion, SlaConfiguracion
- HistorialIncidencia, ArchivoAdjunto, Notificacion: `const UPDATED_AT = null` (solo created_at)
- SlaConfiguracion: `const CREATED_AT = null` (solo updated_at)

### Services: `app/Services/`
- FolioService (genera INC-YYYY-NNNN con lockForUpdate en transacción)
- SlaService (calcula fecha_limite según prioridad desde sla_configuracion)
- HistorialService (registra entradas de historial)
- ArchivoService (almacena en disk 'public', directorio incidencias/{folio}/)
- NotificacionService (registra en DB + envía Mail, captura errores con Log)
- IncidenciaService (toda la lógica de negocio; inyecta los 5 servicios anteriores)

### Middleware: `app/Http/Middleware/CheckRole.php`
- Alias `role` registrado en bootstrap/app.php
- Desactiva sesión si usuario está inactivo

### Form Requests: `app/Http/Requests/`
- Raíz: LoginRequest, StoreIncidenciaPublicaRequest, BuscarSeguimientoRequest, ComentarSeguimientoRequest, AdjuntarArchivoRequest, ComentarIncidenciaRequest
- Subdireccion/: Aprobar, Rechazar, Asignar, Reasignar
- Coordinador/: Resolver, SolicitarInfo, Escalar
- Admin/: StoreUsuario, UpdateUsuario, StoreCategoria, UpdateCategoria, StoreArea, UpdateArea, UpdateSla

### Mail: `app/Mail/` + vistas en `resources/views/emails/incidencias/`
- Confirmada, Asignada, CambioEstado, SolicitudInfo, AlertaSLA

### Controllers:
- AuthController (login con redirect por rol, logout)
- DashboardController (stats por rol)
- IncidenciaPublicaController (create/store/confirmacion)
- SeguimientoController (buscar con sesión verificada)
- Panel/Subdireccion/IncidenciaController, ReporteController (CSV export)
- Panel/Coordinador/IncidenciaController
- Panel/Admin/: Usuario, Categoria, Area, Sla, Log

## Configuración modificada
- `AppServiceProvider`: Fortify::ignoreRoutes() para que AuthController tome /login
- `bootstrap/app.php`: alias 'role' → CheckRole
- `HandleInertiaRequests`: comparte auth.user con rol, y flash messages

## Pendiente (frontend)
Todas las vistas Inertia en `resources/js/Pages/`:
- Public/Incidencias/Create, Confirmacion
- Public/Seguimiento/Index, Show
- auth/login
- Panel/Dashboard
- Panel/Subdireccion/Incidencias/Index, Show + Reportes/Index
- Panel/Coordinador/Incidencias/Index, Show
- Panel/Admin/Usuarios, Categorias, Areas, Sla, Logs (Index, Create, Edit)

**Why:** Universitad necesita sistema de incidencias donde toda queja pasa por subdirección antes de asignarse a coordinadores de área.
**How to apply:** El flujo es estricto: no se puede asignar coordinador sin aprobar primero. El reabrir siempre vuelve a estado `abierta` para nueva revisión.
