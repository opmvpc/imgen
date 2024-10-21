<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\ChatConversation;
use App\Livewire\ProjectList;
use App\Http\Controllers\ProjectController;

Route::view('/', 'welcome');

Route::middleware(['auth'])->group(function () {
    Route::get('/projects', ProjectList::class)->name('projects');
    Route::get('/chat/{project?}', ChatConversation::class)->name('chat');
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
