<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ComprobanteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IncidenciaPublicaController;
use App\Http\Controllers\Panel\Admin\AreaController;
use App\Http\Controllers\Panel\Admin\LogController;
use App\Http\Controllers\Panel\Admin\UsuarioController;
use App\Http\Controllers\Panel\CapitalHumano\IncidenciaController as CapitalHumanoIncidenciaController;
use App\Http\Controllers\Panel\EmpleadoController;
use App\Http\Controllers\Panel\JefeInmediato\IncidenciaController as JefeIncidenciaController;
use App\Http\Controllers\Panel\Sindicato\IncidenciaController as SindicatoIncidenciaController;
use App\Http\Controllers\Panel\Subdireccion\IncidenciaController as SubdirIncidenciaController;
use App\Http\Controllers\Panel\Subdireccion\ReporteController as SubdirReporteController;
use App\Http\Controllers\SeguimientoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas públicas
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => redirect()->route('incidencias.create'));

Route::get('/nueva-incidencia', [IncidenciaPublicaController::class, 'create'])
    ->name('incidencias.create');

Route::post('/nueva-incidencia', [IncidenciaPublicaController::class, 'store'])
    ->name('incidencias.store');

Route::get('/nueva-incidencia/enviada/{folio}', [IncidenciaPublicaController::class, 'confirmacion'])
    ->name('incidencias.confirmacion');

Route::get('/nueva-incidencia/buscar-empleado', [IncidenciaPublicaController::class, 'buscarEmpleado'])
    ->name('incidencias.buscar-empleado')
    ->middleware('throttle:30,1');

Route::get('/seguimiento', [SeguimientoController::class, 'index'])
    ->name('seguimiento.index');

Route::post('/seguimiento', [SeguimientoController::class, 'login'])
    ->name('seguimiento.login');

Route::get('/seguimiento/panel', [SeguimientoController::class, 'panel'])
    ->name('seguimiento.panel');

Route::post('/seguimiento/logout', [SeguimientoController::class, 'logout'])
    ->name('seguimiento.logout');

Route::get('/seguimiento/{folio}', [SeguimientoController::class, 'show'])
    ->name('seguimiento.show');

Route::get('/comprobante/{folio}', [ComprobanteController::class, 'descargar'])
    ->name('comprobante.descargar');

Route::get('/comprobante/{folio}/archivo/{archivoId}', [ComprobanteController::class, 'verArchivo'])
    ->name('comprobante.ver_archivo');

/*
|--------------------------------------------------------------------------
| Autenticación
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

/*
|--------------------------------------------------------------------------
| Panel interno
|--------------------------------------------------------------------------
*/

Route::prefix('panel')->name('panel.')->middleware(['auth', 'active'])->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    /*
    |----------------------------------------------------------------------
    | Jefe Inmediato
    |----------------------------------------------------------------------
    */
    Route::prefix('jefe-inmediato')
        ->name('jefe_inmediato.')
        ->middleware('role:jefe_inmediato')
        ->group(function () {

            Route::prefix('incidencias')->name('incidencias.')->group(function () {
                Route::get('/', [JefeIncidenciaController::class, 'index'])->name('index');
                Route::get('/{incidencia}', [JefeIncidenciaController::class, 'show'])->name('show');
                Route::post('/{incidencia}/aprobar', [JefeIncidenciaController::class, 'aprobar'])->name('aprobar');
                Route::post('/{incidencia}/rechazar', [JefeIncidenciaController::class, 'rechazar'])->name('rechazar');
                Route::post('/{incidencia}/comentar', [JefeIncidenciaController::class, 'comentar'])->name('comentar');
            });

            Route::prefix('empleados')->name('empleados.')->group(function () {
                Route::get('/', [EmpleadoController::class, 'index'])->name('index');
                Route::get('/{numeroEmpleado}', [EmpleadoController::class, 'show'])->name('show');
                Route::get('/{numeroEmpleado}/editar', [EmpleadoController::class, 'edit'])->name('edit');
                Route::patch('/{numeroEmpleado}', [EmpleadoController::class, 'update'])->name('update');
            });
        });

    /*
    |----------------------------------------------------------------------
    | Capital Humano
    |----------------------------------------------------------------------
    */
    Route::prefix('capital-humano')
        ->name('capital_humano.')
        ->middleware('role:capital_humano')
        ->group(function () {

            Route::prefix('incidencias')->name('incidencias.')->group(function () {
                Route::get('/', [CapitalHumanoIncidenciaController::class, 'index'])->name('index');
                Route::get('/{incidencia}', [CapitalHumanoIncidenciaController::class, 'show'])->name('show');
                Route::post('/{incidencia}/aprobar', [CapitalHumanoIncidenciaController::class, 'aprobar'])->name('aprobar');
                Route::post('/{incidencia}/rechazar', [CapitalHumanoIncidenciaController::class, 'rechazar'])->name('rechazar');
            });

            Route::prefix('empleados')->name('empleados.')->group(function () {
                Route::get('/', [EmpleadoController::class, 'index'])->name('index');
                Route::get('/crear', [EmpleadoController::class, 'create'])->name('create');
                Route::post('/crear', [EmpleadoController::class, 'store'])->name('store');
                Route::get('/plantilla', [EmpleadoController::class, 'descargarPlantilla'])->name('plantilla');
                Route::post('/importar', [EmpleadoController::class, 'importarExcel'])->name('importar');
                Route::get('/{numeroEmpleado}', [EmpleadoController::class, 'show'])->name('show');
                Route::get('/{numeroEmpleado}/editar', [EmpleadoController::class, 'edit'])->name('edit');
                Route::patch('/{numeroEmpleado}', [EmpleadoController::class, 'update'])->name('update');
            });

            Route::prefix('reportes')->name('reportes.')->group(function () {
                Route::get('/', [SubdirReporteController::class, 'index'])->name('index');
                Route::get('/exportar', [SubdirReporteController::class, 'exportar'])->name('exportar');
            });

            Route::prefix('areas')->name('areas.')->group(function () {
                Route::get('/', [AreaController::class, 'index'])->name('index');
            });
        });

    /*
    |----------------------------------------------------------------------
    | Sindicato
    |----------------------------------------------------------------------
    */
    Route::prefix('sindicato')
        ->name('sindicato.')
        ->middleware('role:sindicato')
        ->group(function () {

            Route::prefix('incidencias')->name('incidencias.')->group(function () {
                Route::get('/', [SindicatoIncidenciaController::class, 'index'])->name('index');
                Route::get('/{incidencia}', [SindicatoIncidenciaController::class, 'show'])->name('show');
                Route::post('/{incidencia}/aprobar', [SindicatoIncidenciaController::class, 'aprobar'])->name('aprobar');
                Route::post('/{incidencia}/rechazar', [SindicatoIncidenciaController::class, 'rechazar'])->name('rechazar');
            });

            Route::prefix('empleados')->name('empleados.')->group(function () {
                Route::get('/', [EmpleadoController::class, 'index'])->name('index');
                Route::get('/crear', [EmpleadoController::class, 'create'])->name('create');
                Route::post('/crear', [EmpleadoController::class, 'store'])->name('store');
                Route::get('/plantilla', [EmpleadoController::class, 'descargarPlantilla'])->name('plantilla');
                Route::post('/importar', [EmpleadoController::class, 'importarExcel'])->name('importar');
                Route::get('/{numeroEmpleado}', [EmpleadoController::class, 'show'])->name('show');
                Route::get('/{numeroEmpleado}/editar', [EmpleadoController::class, 'edit'])->name('edit');
                Route::patch('/{numeroEmpleado}', [EmpleadoController::class, 'update'])->name('update');
            });
        });

    /*
    |----------------------------------------------------------------------
    | Subdirección
    |----------------------------------------------------------------------
    */
    Route::prefix('subdireccion')
        ->name('subdireccion.')
        ->middleware('role:subdirector')
        ->group(function () {

            Route::prefix('incidencias')->name('incidencias.')->group(function () {
                Route::get('/', [SubdirIncidenciaController::class, 'index'])->name('index');
                Route::get('/{incidencia}', [SubdirIncidenciaController::class, 'show'])->name('show');
                Route::post('/{incidencia}/aprobar', [SubdirIncidenciaController::class, 'aprobar'])->name('aprobar');
                Route::post('/{incidencia}/rechazar', [SubdirIncidenciaController::class, 'rechazar'])->name('rechazar');
            });

            Route::prefix('reportes')->name('reportes.')->group(function () {
                Route::get('/', [SubdirReporteController::class, 'index'])->name('index');
                Route::get('/exportar', [SubdirReporteController::class, 'exportar'])->name('exportar');
            });

            Route::prefix('empleados')->name('empleados.')->group(function () {
                Route::get('/', [EmpleadoController::class, 'index'])->name('index');
                Route::get('/crear', [EmpleadoController::class, 'create'])->name('create');
                Route::post('/crear', [EmpleadoController::class, 'store'])->name('store');
                Route::get('/plantilla', [EmpleadoController::class, 'descargarPlantilla'])->name('plantilla');
                Route::post('/importar', [EmpleadoController::class, 'importarExcel'])->name('importar');
                Route::get('/{numeroEmpleado}', [EmpleadoController::class, 'show'])->name('show');
                Route::get('/{numeroEmpleado}/editar', [EmpleadoController::class, 'edit'])->name('edit');
                Route::patch('/{numeroEmpleado}', [EmpleadoController::class, 'update'])->name('update');
            });

            Route::prefix('admin')->name('admin.')->group(function () {
                Route::resource('usuarios', UsuarioController::class)->except(['show']);
                Route::patch('usuarios/{usuario}/toggle-activo', [UsuarioController::class, 'toggleActivo'])
                    ->name('usuarios.toggleActivo');

                Route::resource('areas', AreaController::class)->except(['show']);

                Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
            });
        });
});
