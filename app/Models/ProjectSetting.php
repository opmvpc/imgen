<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $project_id
 * @property string|null $model
 * @property float $temperature
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ProjectSetting extends Model
{
    use HasFactory;

    protected $fillable = ['model', 'temperature'];

    protected $casts = [
        'temperature' => 'float',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
