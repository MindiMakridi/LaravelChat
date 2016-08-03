<?php

Route::post(config('Chat')['Routes']['getMessages'], ['uses' => '\FrameworkTeam\Chat\MessageController@longpull']);
Route::post(config('Chat')['Routes']['sendMessage'], ['uses' => '\FrameworkTeam\Chat\MessageController@send']);
Route::post(config('Chat')['Routes']['deleteMessage'], ['uses' => '\FrameworkTeam\Chat\MessageController@deleteMessage']);


Route::get('/frameworkteam/{id}', ['uses' => 'App\Http\Controllers\ChatController@index']);
