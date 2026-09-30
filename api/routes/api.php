<?php

use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('users')->group(function (): void {
    // 1. Get: Devuelve la paginación de los diez primeros usuarios.
    Route::get('/', [UserController::class, 'index'])->name('users.index');

    // 2. Create: Crea un usuario en la base de datos.
    Route::post('/', [UserController::class, 'store'])->name('users.store');
    Route::post('/create', [UserController::class, 'store'])->name('users.create');

    // 3. Login: Devuelve los datos de un usuario introduciendo solo el email y la contraseña.
    Route::post('/login', [UserController::class, 'login'])->name('users.login');

    // 4. Update username: Actualiza el username de un usuario pasándole el email y la contraseña.
    Route::match(['put', 'patch', 'post'], '/update-username', [UserController::class, 'updateUsername'])->name('users.update-username');
    Route::match(['put', 'patch'], '/username', [UserController::class, 'updateUsername'])->name('users.username');

    // 5. Update email: Actualiza el email de un usuario pasándole el email y la contraseña.
    Route::match(['put', 'patch', 'post'], '/update-email', [UserController::class, 'updateEmail'])->name('users.update-email');
    Route::match(['put', 'patch'], '/email', [UserController::class, 'updateEmail'])->name('users.email');

    // 6. Update password: Actualiza la contraseña de un usuario pasándole el email y la contraseña.
    Route::match(['put', 'patch', 'post'], '/update-password', [UserController::class, 'updatePassword'])->name('users.update-password');
    Route::match(['put', 'patch'], '/password', [UserController::class, 'updatePassword'])->name('users.password');

    // 7. Delete: Elimina el usuario pasándole email y contraseña.
    Route::delete('/', [UserController::class, 'destroy'])->name('users.destroy');
    Route::post('/delete', [UserController::class, 'destroy'])->name('users.destroy.post');
});
