<?php

use Illuminate\Support\Facades\Route;
use App\Filament\Pages\Ujian;


Route::group(['middleware' => 'auth'], function () {
    Route::get('/do-ujian/{packageId}', Ujian::class)->name('do-ujian');
});


Route::get('/login', function(){
    return redirect('admin/login');
})->name('login');

Route::get('/', function () {
    return view('welcome');
});
