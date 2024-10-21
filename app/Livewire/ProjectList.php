<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Project;

class ProjectList extends Component
{
    public function render()
    {
        $projects = Project::where('user_id', auth()->id())->latest()->get();
        return view('livewire.project-list', ['projects' => $projects]);
    }
}
