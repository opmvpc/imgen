<?php

use App\Livewire\ChatConversation;
use App\Livewire\ProjectList;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::middleware(['auth'])->group(function () {
    Route::get('/projects', ProjectList::class)->name('projects');
    Route::get('/chat/create', function () {
        return redirect()->route('chat', ['project' => auth()->user()->projects()->create(['name' => 'Nouvelle conversation'])]);
    })->name('chat.create');
    Route::get('/chat/{project}', ChatConversation::class)->name('chat');
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile')
;

require __DIR__.'/auth.php';
