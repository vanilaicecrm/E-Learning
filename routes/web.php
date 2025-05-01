<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\UjianPage;


Route::group(['middleware' => 'auth'], function () {
    Route::get('/do-ujian/{id}', UjianPage::class)->name('do-ujian');
});

Route::get('/', function () {
    return view('welcome');
});
