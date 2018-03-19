<?php

// Public Route
Route::post('/login','AuthenticationController@login')->name('login');

// Private Route
Route::middleware('auth:api')->group(function () {
    Route::get('/logout','AuthenticationController@logout')->name('logout');
});