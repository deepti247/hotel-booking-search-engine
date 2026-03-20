<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SearchController;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/search', [SearchController::class, 'search']); // public
