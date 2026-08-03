<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ContactoController;
use App\Http\Controllers\ComentarioController;
use App\Http\Controllers\DiscusionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\Nutriologo\DashboardController as NutriologoDashboard;
use App\Http\Controllers\Nutriologo\PacienteController as NutriologoPaciente;
use App\Http\Controllers\Nutriologo\EvaluacionController as NutriologoEvaluacion;
use App\Http\Controllers\Nutriologo\MenuController as NutriologoMenu;
use App\Http\Controllers\Nutriologo\ReporteController as NutriologoReporte;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\UsuarioController as AdminUsuario;
use App\Http\Controllers\Admin\ContenidoController as AdminContenido;
use App\Http\Controllers\Admin\ConfiguracionController as AdminConfiguracion;
use App\Http\Controllers\Admin\CitaController as AdminCitaController;
use App\Http\Controllers\Admin\NutriologoController as AdminNutriologoController;
use App\Http\Controllers\Admin\EstadisticasController as AdminEstadisticasController;
use App\Http\Controllers\Admin\AuditoriaController as AdminAuditoriaController;
use App\Http\Controllers\Admin\InstitucionController as AdminInstitucionController;
use App\Http\Controllers\Admin\RolController;
use App\Http\Controllers\Admin\PermisoController;
use App\Http\Controllers\Admin\BitacoraController;
use App\Http\Controllers\Admin\CatalogoController;
use App\Http\Controllers\Nutriologo\CitaController as NutriologoCitaController;
use App\Http\Controllers\Nutriologo\PerfilController as NutriologoPerfilController;
use App\Http\Controllers\Nutriologo\RecomendacionController as NutriologoRecomendacionController;

$flaskPublic = rtrim((string) env('FLASK_PUBLIC_URL', ''), '/');

// Con sitio Flask: /acceso solo redirige al login unificado (:5000/login). Sin Flask, pantalla Laravel.
if ($flaskPublic !== '') {
    Route::get('/acceso', function () use ($flaskPublic) {
        return redirect()->away($flaskPublic.'/login');
    })->name('acceso');
} else {
    Route::get('/acceso', [PageController::class, 'login'])->name('acceso');
}

// ============================================
// RUTAS PÚBLICAS - PÁGINAS
// Si FLASK_PUBLIC_URL está definido, las vistas de padres se sirven en Flask (puerto 5001 por defecto).
// Admin y nutriólogo siguen en Laravel.
// ============================================

if ($flaskPublic !== '') {
    // Raíz Laravel (p. ej. :8080 en Docker) → panel de acceso; el sitio público de padres vive en Flask (:5000).
    Route::redirect('/', '/acceso')->name('index');
    Route::redirect('/sitio-padres', $flaskPublic . '/')->name('flask.public');
    Route::redirect('/index', $flaskPublic . '/')->name('index.alias');
    Route::redirect('/Obesidad', $flaskPublic . '/Obesidad')->name('obesidad');
    Route::redirect('/calculadora', $flaskPublic . '/calculadora')->name('calculadora');
    Route::redirect('/nutriologos', $flaskPublic . '/nutriologos')->name('nutriologos');
    Route::redirect('/Comentarios', $flaskPublic . '/Comentarios')->name('comentarios');
    Route::redirect('/Foros', $flaskPublic . '/Foros')->name('foros');
    Route::redirect('/conocenos', $flaskPublic . '/conocenos')->name('conocenos');
    Route::redirect('/login', $flaskPublic . '/login')->name('login');
    Route::redirect('/dashboard', $flaskPublic . '/portal')->name('dashboard');
    Route::redirect('/Contacto', $flaskPublic . '/Contacto')->name('contacto');
} else {
    Route::get('/', [PageController::class, 'index'])->name('index');
    Route::get('/index', [PageController::class, 'index'])->name('index.alias');
    Route::get('/Obesidad', [PageController::class, 'obesidad'])->name('obesidad');
    Route::get('/calculadora', [PageController::class, 'calculadora'])->name('calculadora');
    Route::get('/nutriologos', [PageController::class, 'nutriologos'])->name('nutriologos');
    Route::get('/Comentarios', [PageController::class, 'comentarios'])->name('comentarios');
    Route::get('/Foros', [PageController::class, 'foros'])->name('foros');
    Route::get('/conocenos', [PageController::class, 'conocenos'])->name('conocenos');
    Route::get('/login', [PageController::class, 'login'])->name('login');
    Route::get('/dashboard', [PageController::class, 'dashboard'])->name('dashboard');
    Route::get('/Contacto', [PageController::class, 'contacto'])->name('contacto');
}

// ============================================
// RUTAS API - FORMULARIOS Y DATOS
// ============================================

// Contacto
Route::post('/InsertarContacto', [ContactoController::class, 'store'])->name('contacto.store');

// Comentarios
Route::get('/ObtenerComentarios', [ComentarioController::class, 'index'])->name('comentarios.index');
Route::post('/InsertarComentario', [ComentarioController::class, 'store'])->name('comentarios.store')->middleware('auth');

// Discusiones
Route::get('/ObtenerDiscusiones', [DiscusionController::class, 'index'])->name('discusiones.index');
Route::post('/InsertarDiscusion', [DiscusionController::class, 'store'])->name('discusiones.store')->middleware('auth');
Route::post('/ActualizarDiscusion/{id}', [DiscusionController::class, 'update'])->name('discusiones.update')->middleware('auth');
Route::post('/EliminarDiscusion/{id}', [DiscusionController::class, 'destroy'])->name('discusiones.destroy')->middleware('auth');

// Autenticación
Route::match(['post', 'options'], '/IniciarSesion', [AuthController::class, 'login'])->name('auth.login');

// Usuarios
Route::post('/RegistrarUsuario', [UsuarioController::class, 'store'])->name('usuarios.store');

// ============================================
// RUTAS PARA NUTRIÓLOGOS
// ============================================
Route::middleware(['auth', 'role:nutriologo'])->prefix('nutriologo')->name('nutriologo.')->group(function () {
    Route::get('/dashboard', [NutriologoDashboard::class, 'index'])->name('dashboard');

    Route::get('/citas', [NutriologoCitaController::class, 'index'])->name('citas.index');
    Route::get('/agenda', [NutriologoCitaController::class, 'agenda'])->name('citas.agenda');
    Route::post('/citas/{cita}/tomar', [NutriologoCitaController::class, 'tomar'])->name('citas.tomar');
    Route::post('/citas/{cita}/confirmar', [NutriologoCitaController::class, 'confirmar'])->name('citas.confirmar');

    Route::get('/perfil', [NutriologoPerfilController::class, 'index'])->name('perfil.index');
    Route::put('/perfil', [NutriologoPerfilController::class, 'update'])->name('perfil.update');

    Route::get('/recomendaciones', [NutriologoRecomendacionController::class, 'index'])->name('recomendaciones.index');
    Route::post('/recomendaciones', [NutriologoRecomendacionController::class, 'store'])->name('recomendaciones.store');
    
    // Pacientes
    Route::get('/pacientes', [NutriologoPaciente::class, 'index'])->name('pacientes.index');
    Route::get('/pacientes/crear', [NutriologoPaciente::class, 'create'])->name('pacientes.create');
    Route::post('/pacientes', [NutriologoPaciente::class, 'store'])->name('pacientes.store');
    Route::get('/pacientes/{id}', [NutriologoPaciente::class, 'show'])->name('pacientes.show');
    Route::get('/pacientes/{id}/editar', [NutriologoPaciente::class, 'edit'])->name('pacientes.edit');
    Route::put('/pacientes/{id}', [NutriologoPaciente::class, 'update'])->name('pacientes.update');
    
    // Evaluaciones
    Route::get('/evaluaciones', [NutriologoEvaluacion::class, 'index'])->name('evaluaciones.index');
    Route::get('/evaluaciones/crear', [NutriologoEvaluacion::class, 'create'])->name('evaluaciones.create');
    Route::post('/evaluaciones', [NutriologoEvaluacion::class, 'store'])->name('evaluaciones.store');
    Route::get('/evaluaciones/{id}/editar', [NutriologoEvaluacion::class, 'edit'])->name('evaluaciones.edit');
    Route::put('/evaluaciones/{id}', [NutriologoEvaluacion::class, 'update'])->name('evaluaciones.update');
    
    // Menús
    Route::get('/menus', [NutriologoMenu::class, 'index'])->name('menus.index');
    Route::get('/menus/crear', [NutriologoMenu::class, 'create'])->name('menus.create');
    Route::post('/menus', [NutriologoMenu::class, 'store'])->name('menus.store');
    Route::get('/menus/{id}/editar', [NutriologoMenu::class, 'edit'])->name('menus.edit');
    Route::put('/menus/{id}', [NutriologoMenu::class, 'update'])->name('menus.update');
    Route::post('/menus/{id}/duplicar', [NutriologoMenu::class, 'duplicate'])->name('menus.duplicate');
    
    // Reportes
    Route::get('/reportes', [NutriologoReporte::class, 'index'])->name('reportes.index');
    Route::post('/reportes', [NutriologoReporte::class, 'store'])->name('reportes.store');
    Route::get('/reportes/{reporte}', [NutriologoReporte::class, 'show'])->name('reportes.show');
    Route::get('/reportes/{reporte}/pdf', [NutriologoReporte::class, 'exportPdf'])->name('reportes.pdf');
});

// ============================================
// RUTAS PARA ADMINISTRADORES
// ============================================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

    Route::get('/citas', [AdminCitaController::class, 'index'])->name('citas.index');
    Route::post('/citas/{cita}/asignar', [AdminCitaController::class, 'asignar'])->name('citas.asignar');
    Route::post('/citas/{cita}/estado', [AdminCitaController::class, 'estado'])->name('citas.estado');
    
    // Usuarios
    Route::get('/usuarios', [AdminUsuario::class, 'index'])->name('usuarios.index');
    Route::get('/usuarios/crear', [AdminUsuario::class, 'create'])->name('usuarios.create');
    Route::post('/usuarios', [AdminUsuario::class, 'store'])->name('usuarios.store');
    Route::get('/usuarios/{id}/editar', [AdminUsuario::class, 'edit'])->name('usuarios.edit');
    Route::put('/usuarios/{id}', [AdminUsuario::class, 'update'])->name('usuarios.update');
    Route::delete('/usuarios/{id}', [AdminUsuario::class, 'destroy'])->name('usuarios.destroy');

    // Nutriólogos (vistas dedicadas)
    Route::get('/nutriologos', [AdminNutriologoController::class, 'index'])->name('nutriologos.index');
    Route::get('/nutriologos/crear', [AdminNutriologoController::class, 'create'])->name('nutriologos.create');
    Route::post('/nutriologos', [AdminNutriologoController::class, 'store'])->name('nutriologos.store');
    Route::get('/nutriologos/{id}/editar', [AdminNutriologoController::class, 'edit'])->name('nutriologos.edit');
    Route::put('/nutriologos/{id}', [AdminNutriologoController::class, 'update'])->name('nutriologos.update');

    // Estadísticas, auditoría e instituciones
    Route::get('/estadisticas', [AdminEstadisticasController::class, 'index'])->name('estadisticas.index');
    Route::get('/auditoria', [AdminAuditoriaController::class, 'index'])->name('auditoria.index');
    Route::get('/bitacora', [BitacoraController::class, 'index'])->name('bitacora.index');
    Route::get('/instituciones', [AdminInstitucionController::class, 'index'])->name('instituciones.index');
    Route::post('/instituciones', [AdminInstitucionController::class, 'store'])->name('instituciones.store');
    Route::post('/instituciones/{id}/toggle', [AdminInstitucionController::class, 'toggle'])->name('instituciones.toggle');

    Route::get('/roles', [RolController::class, 'index'])->name('roles.index');
    Route::put('/roles/{role}', [RolController::class, 'update'])->name('roles.update');
    Route::get('/permisos', [PermisoController::class, 'index'])->name('permisos.index');
    Route::post('/permisos/{role}/sync', [PermisoController::class, 'sync'])->name('permisos.sync');
    Route::get('/catalogos', [CatalogoController::class, 'index'])->name('catalogos.index');
    
    // Contenido
    Route::get('/contenido', [AdminContenido::class, 'index'])->name('contenido.index');
    Route::get('/contenido/alimentos', [AdminContenido::class, 'alimentos'])->name('contenido.alimentos');
    Route::get('/contenido/recetas', [AdminContenido::class, 'recetas'])->name('contenido.recetas');
    Route::get('/contenido/menus', [AdminContenido::class, 'menus'])->name('contenido.menus');
    Route::post('/contenido/menus/{id}/eliminar', [AdminContenido::class, 'destroyMenu'])->name('contenido.menus.destroy');
    Route::post('/contenido/contactos/{id}/responder', [AdminContenido::class, 'responderContacto'])->name('contenido.contactos.responder');
    Route::post('/contenido/contactos/{id}/eliminar', [AdminContenido::class, 'destroyContacto'])->name('contenido.contactos.destroy');
    Route::post('/contenido/comentarios/{id}/eliminar', [AdminContenido::class, 'destroyComentario'])->name('contenido.comentarios.destroy');
    Route::post('/contenido/discusiones/{id}/eliminar', [AdminContenido::class, 'destroyDiscusion'])->name('contenido.discusiones.destroy');
    
    // Configuración
    Route::get('/configuracion', [AdminConfiguracion::class, 'index'])->name('configuracion.index');
    Route::post('/configuracion', [AdminConfiguracion::class, 'update'])->name('configuracion.update');
    Route::post('/configuracion/logo', [AdminConfiguracion::class, 'uploadLogo'])->name('configuracion.uploadLogo');
});

// Logout: si padres usan Flask, tras cerrar sesión volver al login del sitio principal
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    $flask = rtrim((string) env('FLASK_PUBLIC_URL', ''), '/');
    if ($flask !== '') {
        return redirect()->away($flask . '/login');
    }
    return redirect()->route('acceso')->with('success', 'Sesión cerrada correctamente');
})->name('logout')->middleware('auth');

