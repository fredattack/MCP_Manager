<?php

use App\Http\Controllers\NotionController;
use Illuminate\Support\Facades\Route;

Route::get('notion/fetch', [NotionController::class, 'fetch']);
