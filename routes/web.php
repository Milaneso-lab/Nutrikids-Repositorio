<?php

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

// ============================================
// RUTAS PÚBLICAS - PÁGINAS
// ============================================

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
Route::post('/IniciarSesion', [AuthController::class, 'login'])->name('auth.login');

// Usuarios
Route::post('/RegistrarUsuario', [UsuarioController::class, 'store'])->name('usuarios.store');

// ============================================
// RUTAS PARA NUTRIÓLOGOS
// ============================================
Route::middleware(['auth', 'role:nutriologo'])->prefix('nutriologo')->name('nutriologo.')->group(function () {
    Route::get('/dashboard', [NutriologoDashboard::class, 'index'])->name('dashboard');
    
    // Pacientes
    Route::get('/pacientes', [NutriologoPaciente::class, 'index'])->name('pacientes.index');
    Route::get('/pacientes/crear', [NutriologoPaciente::class, 'create'])->name('pacientes.create');
    Route::get('/pacientes/{id}', [NutriologoPaciente::class, 'show'])->name('pacientes.show');
    Route::get('/pacientes/{id}/editar', [NutriologoPaciente::class, 'edit'])->name('pacientes.edit');
    
    // Evaluaciones
    Route::get('/evaluaciones', [NutriologoEvaluacion::class, 'index'])->name('evaluaciones.index');
    Route::get('/evaluaciones/crear', [NutriologoEvaluacion::class, 'create'])->name('evaluaciones.create');
    Route::get('/evaluaciones/{id}/editar', [NutriologoEvaluacion::class, 'edit'])->name('evaluaciones.edit');
    
    // Menús
    Route::get('/menus', [NutriologoMenu::class, 'index'])->name('menus.index');
    Route::get('/menus/crear', [NutriologoMenu::class, 'create'])->name('menus.create');
    Route::get('/menus/{id}/editar', [NutriologoMenu::class, 'edit'])->name('menus.edit');
    
    // Reportes
    Route::get('/reportes', [NutriologoReporte::class, 'index'])->name('reportes.index');
    Route::get('/reportes/{id}', [NutriologoReporte::class, 'show'])->name('reportes.show');
});

// ============================================
// RUTAS PARA ADMINISTRADORES
// ============================================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');
    
    // Usuarios
    Route::get('/usuarios', [AdminUsuario::class, 'index'])->name('usuarios.index');
    Route::get('/usuarios/crear', [AdminUsuario::class, 'create'])->name('usuarios.create');
    Route::post('/usuarios', [AdminUsuario::class, 'store'])->name('usuarios.store');
    Route::get('/usuarios/{id}/editar', [AdminUsuario::class, 'edit'])->name('usuarios.edit');
    Route::put('/usuarios/{id}', [AdminUsuario::class, 'update'])->name('usuarios.update');
    Route::delete('/usuarios/{id}', [AdminUsuario::class, 'destroy'])->name('usuarios.destroy');
    
    // Contenido
    Route::get('/contenido', [AdminContenido::class, 'index'])->name('contenido.index');
    Route::get('/contenido/alimentos', [AdminContenido::class, 'alimentos'])->name('contenido.alimentos');
    Route::get('/contenido/recetas', [AdminContenido::class, 'recetas'])->name('contenido.recetas');
    Route::get('/contenido/menus', [AdminContenido::class, 'menus'])->name('contenido.menus');
    Route::post('/contenido/contactos/{id}/responder', [AdminContenido::class, 'responderContacto'])->name('contenido.contactos.responder');
    Route::post('/contenido/contactos/{id}/eliminar', [AdminContenido::class, 'destroyContacto'])->name('contenido.contactos.destroy');
    Route::post('/contenido/comentarios/{id}/eliminar', [AdminContenido::class, 'destroyComentario'])->name('contenido.comentarios.destroy');
    Route::post('/contenido/discusiones/{id}/eliminar', [AdminContenido::class, 'destroyDiscusion'])->name('contenido.discusiones.destroy');
    
    // Configuración
    Route::get('/configuracion', [AdminConfiguracion::class, 'index'])->name('configuracion.index');
    Route::post('/configuracion', [AdminConfiguracion::class, 'update'])->name('configuracion.update');
    Route::post('/configuracion/logo', [AdminConfiguracion::class, 'uploadLogo'])->name('configuracion.uploadLogo');
});

// Logout
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login')->with('success', 'Sesión cerrada correctamente');
})->name('logout')->middleware('auth');
