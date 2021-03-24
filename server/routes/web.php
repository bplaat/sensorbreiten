<?php

// Middleware
$canRevokeSession = function ($session) {
    if ($session->user_id == Auth::id()) {
        return true;
    } else {
        return abort(403);
    }
};

// ######## Page routes ########
Route::view('/', 'home')->name('home');

// ######## Auth routes ########

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
Route::view('/auth/login', 'auth.login')->name('auth.login')->middleware('guest');
Route::post('/auth/login', 'AuthController::login')->middleware('guest');
