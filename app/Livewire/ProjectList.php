<?php

namespace App\Livewire;

use App\Models\Project;
use Livewire\Component;
use Livewire\WithPagination;

class ProjectList extends Component
{
    use WithPagination;

    public $projectIdToDelete;

    public function deleteProject()
    {
        Project::find($this->projectIdToDelete)->delete();
        $this->projectIdToDelete = null;
    }

    public function render()
    {
        return view('livewire.project-list', [
            'projects' => Project::where('user_id', auth()->id())
                ->latest('updated_at')
                ->paginate(12)
        ]);
    }
}
