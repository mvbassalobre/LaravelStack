<?php
Route::post('store', '\App\vStack\Controllers\ResourceController@store')->name("resource.store");
Route::get('{resource}', '\App\vStack\Controllers\ResourceController@index')->name("resource.index");
Route::get('{resource}/create', '\App\vStack\Controllers\ResourceController@create')->name("resource.create");
Route::get('{resource}/{code}', '\App\vStack\Controllers\ResourceController@edit')->middleware(['hashids:code'])->name("resource.edit");
Route::delete('{resource}/{code}/destroy', '\App\vStack\Controllers\ResourceController@destroy')->middleware(['hashids:code'])->name("resource.destroy");
Route::post('inputs/option_list', '\App\vStack\Controllers\ResourceController@option_list')->name("resource.inputs.option_list");
