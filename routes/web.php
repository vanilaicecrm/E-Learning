<?php

use Illuminate\Support\Facades\Route;
use App\Filament\Pages\Ujian;
use Illuminate\Support\Facades\Http;



Route::group(['middleware' => 'auth'], function () {
    Route::get('/do-ujian/{packageId}', Ujian::class)->name('do-ujian');
});


Route::get('/login', function(){
    return redirect('admin/login');
})->name('login');

Route::get('/', function () {
    return view('welcome');
});

// Di routes/web.php sementara untuk testing
Route::get('/test-gemini', function() {
    $response = Http::get("https://generativelanguage.googleapis.com/v1beta/models?key=".env('GEMINI_API_KEY'));
   
    dd($response->json());
});