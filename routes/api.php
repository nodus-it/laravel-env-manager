<?php

use App\Http\Controllers\GetEnvironmentKeyController;
use App\Http\Controllers\PutEnvironmentKeyController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth:sanctum',
    'ability:env.read',
])->get('/environment', GetEnvironmentKeyController::class);

Route::middleware([
    'auth:sanctum',
    'ability:env.write',
])->put('/environment', PutEnvironmentKeyController::class);
