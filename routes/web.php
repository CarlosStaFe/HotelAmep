<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('index');
});

Route::post('/contacto', function (\Illuminate\Http\Request $request) {
    $data = $request->validate([
        'name' => ['required', 'string', 'max:100'],
        'email' => ['required', 'email', 'max:255'],
        'subject' => ['required', 'string', 'max:150'],
        'message' => ['required', 'string', 'max:5000'],
    ]);

    \Illuminate\Support\Facades\Log::info('Mensaje recibido desde el formulario de contacto.', $data);

    return back()->with('success', 'Su mensaje ha sido recibido. Nos pondremos en contacto pronto.');
})->name('contact.send');

Auth::routes();

Route::get('/home', [App\Http\Controllers\AdminController::class, 'index'])
->name('home')
->middleware('auth');

//RUTA PARA EL ADMIN
Route::get('/admin', [App\Http\Controllers\AdminController::class, 'index'])
->name('admin.index')
->middleware('auth');

//RUTA PARA USUARIOS
Route::get('/admin/usuarios/index', [App\Http\Controllers\UsuarioController::class, 'index'])
->name('usuarios.index')
->middleware('auth');
Route::get('/admin/usuarios/create', [App\Http\Controllers\UsuarioController::class, 'create'])
->name('usuarios.create')
->middleware('auth');
Route::post('/admin/usuarios/create', [App\Http\Controllers\UsuarioController::class, 'store'])
->name('usuarios.store')
->middleware('auth');
Route::get('/admin/usuarios/{id}', [App\Http\Controllers\UsuarioController::class, 'show'])
->name('usuarios.show')
->middleware('auth');
Route::get('/admin/usuarios/{id}/edit', [App\Http\Controllers\UsuarioController::class, 'edit'])
->name('usuarios.edit')
->middleware('auth');
Route::put('/admin/usuarios/{id}', [App\Http\Controllers\UsuarioController::class, 'update'])
->name('usuarios.update')
->middleware('auth');

//RUTA PARA CENTRO DE COSTOS
Route::get('/admin/ctrocostos/index', [App\Http\Controllers\CtroCostoController::class, 'index'])
->name('ctrocostos.index')
->middleware('auth');
Route::get('/admin/ctrocostos/create', [App\Http\Controllers\CtroCostoController::class, 'create'])
->name('ctrocostos.create')
->middleware('auth');
Route::post('/admin/ctrocostos/create', [App\Http\Controllers\CtroCostoController::class, 'store'])
->name('ctrocostos.store')
->middleware('auth');
Route::get('/admin/ctrocostos/{id}', [App\Http\Controllers\CtroCostoController::class, 'show'])
->name('ctrocostos.show')
->middleware('auth');
Route::get('/admin/ctrocostos/{id}/edit', [App\Http\Controllers\CtroCostoController::class, 'edit'])
->name('ctrocostos.edit')
->middleware('auth');
Route::get('/admin/ctrocostos/{id}/delete', [App\Http\Controllers\CtroCostoController::class, 'delete'])
->name('ctrocostos.delete')
->middleware('auth');
Route::put('/admin/ctrocostos/{id}', [App\Http\Controllers\CtroCostoController::class, 'update'])
->name('ctrocostos.update')
->middleware('auth');
Route::delete('/admin/ctrocostos/{id}', [App\Http\Controllers\CtroCostoController::class, 'destroy'])
->name('ctrocostos.destroy')
->middleware('auth');

//RUTAS PARA RUBROS
Route::get('/admin/rubros/index', [App\Http\Controllers\RubroController::class, 'index'])
->name('rubros.index')
->middleware('auth');
Route::get('/admin/rubros/create', [App\Http\Controllers\RubroController::class, 'create'])
->name('rubros.create')
->middleware('auth');
Route::post('/admin/rubros/create', [App\Http\Controllers\RubroController::class, 'store'])
->name('rubros.store')
->middleware('auth');
Route::get('/admin/rubros/{id}', [App\Http\Controllers\RubroController::class, 'show'])
->name('rubros.show')
->middleware('auth');
Route::get('/admin/rubros/{id}/edit', [App\Http\Controllers\RubroController::class, 'edit'])
->name('rubros.edit')
->middleware('auth');
Route::get('/admin/rubros/{id}/delete', [App\Http\Controllers\RubroController::class, 'delete'])
->name('rubros.delete')
->middleware('auth');
Route::put('/admin/rubros/{id}', [App\Http\Controllers\RubroController::class, 'update'])
->name('rubros.update')
->middleware('auth');
Route::delete('/admin/rubros/{id}', [App\Http\Controllers\RubroController::class, 'destroy'])
->name('rubros.destroy')
->middleware('auth');

//RUTA PARA ARTICULOS
Route::get('/admin/articulos/index', [App\Http\Controllers\ArticuloController::class, 'index'])
->name('articulos.index')
->middleware('auth');
Route::get('/admin/articulos/create', [App\Http\Controllers\ArticuloController::class, 'create'])
->name('articulos.create')
->middleware('auth');
Route::post('/admin/articulos/create', [App\Http\Controllers\ArticuloController::class, 'store'])
->name('articulos.store')
->middleware('auth');
Route::get('/admin/articulos/{id}', [App\Http\Controllers\ArticuloController::class, 'show'])
->name('articulos.show')
->middleware('auth');
Route::get('/admin/articulos/{id}/edit', [App\Http\Controllers\ArticuloController::class, 'edit'])
->name('articulos.edit')
->middleware('auth');
Route::get('/admin/articulos/{id}/delete', [App\Http\Controllers\ArticuloController::class, 'delete'])
->name('articulos.delete')
->middleware('auth');
Route::put('/admin/articulos/{id}', [App\Http\Controllers\ArticuloController::class, 'update'])
->name('articulos.update')
->middleware('auth');
Route::delete('/admin/articulos/{id}', [App\Http\Controllers\ArticuloController::class, 'destroy'])
->name('articulos.destroy')
->middleware('auth');

//RUTA PARA PROVEEDORES
Route::get('/admin/proveedores/index', [App\Http\Controllers\ProveedorController::class, 'index'])
->name('proveedores.index')
->middleware('auth');
Route::get('/admin/proveedores/create', [App\Http\Controllers\ProveedorController::class, 'create'])
->name('proveedores.create')
->middleware('auth');
Route::post('/admin/proveedores/create', [App\Http\Controllers\ProveedorController::class, 'store'])
->name('proveedores.store')
->middleware('auth');
Route::get('/admin/proveedores/{id}', [App\Http\Controllers\ProveedorController::class, 'show'])
->name('proveedores.show')
->middleware('auth');
Route::get('/admin/proveedores/{id}/edit', [App\Http\Controllers\ProveedorController::class, 'edit'])
->name('proveedores.edit')
->middleware('auth');
Route::get('/admin/proveedores/{id}/delete', [App\Http\Controllers\ProveedorController::class, 'delete'])
->name('proveedores.delete')
->middleware('auth');
Route::put('/admin/proveedores/{id}', [App\Http\Controllers\ProveedorController::class, 'update'])
->name('proveedores.update')
->middleware('auth');
Route::delete('/admin/proveedores/{id}', [App\Http\Controllers\ProveedorController::class, 'destroy'])
->name('proveedores.destroy')
->middleware('auth');

//RUTAS PARA PASAJEROS
Route::get('/admin/pasajeros/index', [App\Http\Controllers\PasajeroController::class, 'index'])
->name('pasajeros.index')
->middleware('auth');
Route::get('/admin/pasajeros/create', [App\Http\Controllers\PasajeroController::class, 'create'])
->name('pasajeros.create')
->middleware('auth');
Route::post('/admin/pasajeros/create', [App\Http\Controllers\PasajeroController::class, 'store'])
->name('pasajeros.store')
->middleware('auth');
Route::get('/admin/pasajeros/{id}', [App\Http\Controllers\PasajeroController::class, 'show'])
->name('pasajeros.show')
->middleware('auth');
Route::get('/admin/pasajeros/{id}/edit', [App\Http\Controllers\PasajeroController::class, 'edit'])
->name('pasajeros.edit')
->middleware('auth');
Route::get('/admin/pasajeros/{id}/delete', [App\Http\Controllers\PasajeroController::class, 'delete'])
->name('pasajeros.delete')
->middleware('auth');
Route::put('/admin/pasajeros/{id}', [App\Http\Controllers\PasajeroController::class, 'update'])
->name('pasajeros.update')
->middleware('auth');
Route::delete('/admin/pasajeros/{id}', [App\Http\Controllers\PasajeroController::class, 'destroy'])
->name('pasajeros.destroy')
->middleware('auth');

//RUTAS PARA COMANDAS
Route::get('/admin/comandas/index', [App\Http\Controllers\ComandaController::class, 'index'])
->name('comandas.index')
->middleware('auth');
Route::get('/admin/comandas/create', [App\Http\Controllers\ComandaController::class, 'create'])
->name('comandas.create')
->middleware('auth');

//RUTAS PARA PASAJEROS (AJAX)
Route::get('/admin/pasajeros/habitacion/{habitacion}', [App\Http\Controllers\PasajeroController::class, 'getByHabitacion'])
->name('pasajeros.habitacion')
->middleware('auth');

//RUTAS PARA LOCALIDADES (AJAX)
Route::get('/admin/localidades/provincia/{idProv}', [App\Http\Controllers\LocalidadController::class, 'getLocalidades'])
->name('localidades.provincia')
->middleware('auth');
Route::get('/admin/localidades/codigos-postales/{idLocal}', [App\Http\Controllers\LocalidadController::class, 'getCodigosPostales'])
->name('localidades.codigos_postales')
->middleware('auth');