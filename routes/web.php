<?php

use Pigzzz\Settings\Http\Controllers\SettingsController;

Route::resource('settings', SettingsController::class, ['only' => ['index', 'create', 'edit', 'store', 'update', 'destroy']]);
Route::get('settings/display', SettingsController::class.'@display')->name('settings.display');
Route::post('settings/display', SettingsController::class.'@handleDisplay')->name('settings.handleDisplay');