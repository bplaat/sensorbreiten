<?php

// Custum middleware
$canRevokeSession = function ($session) {
    if ($session->user_id == Auth::id()) {
        return true;
    } else {
        return abort(403);
    }
};

// ######## Page routes ########
Route::view('/', 'home')->name('home');

// ######## API routes ########
Route::post('/api/measurements', 'ApiMeasurementsController::store')
    ->name('api.measurements.store')->middleware('api');

// ######## Auth routes ########

// Station routes
Route::get('/stations', 'StationsController::index')
    ->name('stations.index')->middleware('auth');
Route::view('/stations/create', 'stations.create')->name('stations.create');
Route::post('/stations', 'StationsController::store')
    ->name('stations.store')->middleware('moderator');
Route::get('/stations/{Stations}/edit', 'StationsController::edit')
    ->name('stations.edit')->middleware('moderator');
Route::post('/stations/{Stations}', 'StationsController::update')
    ->name('stations.update')->middleware('moderator');
Route::get('/stations/{Stations}/delete', 'StationsController::delete')
    ->name('stations.delete')->middleware('moderator');
Route::get('/stations/{Stations}', 'StationsController::show')
    ->name('stations.show')->middleware('auth');

// Events routes
Route::get('/events', 'EventsController::index')
    ->name('events.index')->middleware('auth');
Route::get('/events/create', 'EventsController::create')
    ->name('events.create')->middleware('moderator');
Route::post('/events', 'EventsController::store')
    ->name('events.store')->middleware('moderator');
Route::get('/events/{Events}/edit', 'EventsController::edit')
    ->name('events.edit')->middleware('moderator');
Route::post('/events/{Events}', 'EventsController::update')
    ->name('events.update')->middleware('moderator');
Route::get('/events/{Events}/delete', 'EventsController::delete')
    ->name('events.delete')->middleware('moderator');
Route::get('/events/{Events}', 'EventsController::show')
    ->name('events.show')->middleware('auth');

// Admin routes
Route::view('/admin', 'admin.home')->name('admin.home');

// Admin user routes
Route::get('/admin/users', 'AdminUsersController::index')
    ->name('admin.users.index')->middleware('admin');
Route::view('/admin/users/create', 'admin.users.create')->name('admin.users.create');
Route::post('/admin/users', 'AdminUsersController::store')
    ->name('admin.users.store')->middleware('admin');
Route::get('/admin/users/{Users}/edit', 'AdminUsersController::edit')
    ->name('admin.users.edit')->middleware('admin');
Route::post('/admin/users/{Users}', 'AdminUsersController::update')
    ->name('admin.users.update')->middleware('admin');
Route::get('/admin/users/{Users}/delete', 'AdminUsersController::delete')
    ->name('admin.users.delete')->middleware('admin');
Route::get('/admin/users/{Users}', 'AdminUsersController::show')
    ->name('admin.users.show')->middleware('admin');

// Settings routes
Route::get('/settings', 'SettingsController::settings')
    ->name('settings')->middleware('auth');
Route::post('/settings/change_details', 'SettingsController::changeDetails')
    ->name('settings.change_details')->middleware('auth');
Route::post('/settings/change_password', 'SettingsController::changePassword')
    ->name('settings.change_password')->middleware('auth');
Route::get('/sessions/{Sessions}/revoke', 'SettingsController::revokeSession')
    ->name('settings.revoke_session')->middleware([ 'auth', $canRevokeSession ]);

// Auth routes
Route::get('/auth/logout', 'AuthController::logout')->name('auth.logout')->middleware('auth');

// ######## Guest routes ########

// Auth routes
Route::view('/auth/login', 'auth.login')->name('auth.login')->middleware('guest');
Route::post('/auth/login', 'AuthController::login')->middleware('guest');
