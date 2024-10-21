<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = ['content', 'role'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function image()
    {
        return $this->hasOne(Image::class);
    }
}
