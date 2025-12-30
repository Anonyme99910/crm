<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\SectionController;
use App\Http\Controllers\Api\PortfolioController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\TestimonialController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\UploadController;

Route::post('/login', [AuthController::class, 'login']);

Route::get('/settings', [SettingController::class, 'index']);
Route::get('/sections', [SectionController::class, 'index']);
Route::get('/portfolio', [PortfolioController::class, 'index']);
Route::get('/portfolio/{portfolio}', [PortfolioController::class, 'show']);
Route::get('/testimonials', [TestimonialController::class, 'index']);
Route::get('/services', [ServiceController::class, 'index']);

Route::post('/contact', [ContactController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/upload', [UploadController::class, 'store']);
    Route::delete('/upload', [UploadController::class, 'destroy']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    Route::put('/settings/bulk', [SettingController::class, 'bulkUpdate']);
    Route::put('/settings/{key}', [SettingController::class, 'update']);
    
    Route::post('/sections', [SectionController::class, 'store']);
    Route::put('/sections/{section}', [SectionController::class, 'update']);
    Route::delete('/sections/{section}', [SectionController::class, 'destroy']);
    Route::post('/sections/reorder', [SectionController::class, 'reorder']);
    
    Route::post('/portfolio', [PortfolioController::class, 'store']);
    Route::put('/portfolio/{portfolio}', [PortfolioController::class, 'update']);
    Route::delete('/portfolio/{portfolio}', [PortfolioController::class, 'destroy']);
    
    Route::post('/media', [MediaController::class, 'store']);
    Route::put('/media/{media}', [MediaController::class, 'update']);
    Route::delete('/media/{media}', [MediaController::class, 'destroy']);
    
    Route::post('/testimonials', [TestimonialController::class, 'store']);
    Route::put('/testimonials/{testimonial}', [TestimonialController::class, 'update']);
    Route::delete('/testimonials/{testimonial}', [TestimonialController::class, 'destroy']);
    
    Route::post('/services', [ServiceController::class, 'store']);
    Route::put('/services/{service}', [ServiceController::class, 'update']);
    Route::delete('/services/{service}', [ServiceController::class, 'destroy']);
    
    Route::get('/contact-submissions', [ContactController::class, 'index']);
    Route::put('/contact-submissions/{id}/read', [ContactController::class, 'markAsRead']);
    Route::delete('/contact-submissions/{id}', [ContactController::class, 'destroy']);
});
