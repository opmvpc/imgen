<?php

use App\Livewire\ChatConversation;
use App\Livewire\ProjectList;
use App\Livewire\Studio;
use App\Models\Generation;
use App\Services\ReplicateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::middleware(['auth'])->group(function () {
    Route::get('/projects', ProjectList::class)->name('projects');
    Route::get('/chat/create', function () {
        return redirect()->route('chat', ['project' => auth()->user()->projects()->create(['name' => 'Nouvelle conversation'])]);
    })->name('chat.create');
    Route::get('/chat/{project}', ChatConversation::class)->name('chat');
    Route::get('/studio', Studio::class)->name('studio');

    Route::get('/replicate/check/{generation}', function (Generation $generation) {
        if ($generation->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $replicate = app(ReplicateService::class);
            $result = $replicate->checkPrediction($generation->prediction_id);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    })->middleware('auth')->name('replicate.check');

    Route::post('/replicate/update/{generation}', function (Generation $generation, Request $request) {
    if ($generation->user_id !== auth()->id()) {
        abort(403);
    }

    $data = $request->all();
    $generation->update([
        'status' => $data['status'],
        'image_url' => $data['output'][0] ?? null,
        'result' => $data
    ]);

    return response()->json(['success' => true]);
})->name('replicate.update');
});


Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile')
;

require __DIR__.'/auth.php';
