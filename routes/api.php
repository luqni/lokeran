<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\JobWebhookController;

Route::post('/webhooks/jobs', [JobWebhookController::class, 'store']);
Route::post('/webhooks/hermes-heartbeat', [JobWebhookController::class, 'heartbeat']);
