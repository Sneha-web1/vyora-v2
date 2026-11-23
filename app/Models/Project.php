<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Minimal project model (client_projects) - used by Daily Reports. */
class Project extends Model
{
    protected $table = "client_projects";
    protected $primaryKey = "project_id";
    public $timestamps = false;

    protected $fillable = ["client_id", "project_name", "work_type", "notes", "status"];

    public function getIdAttribute()
    {
        return $this->attributes["project_id"] ?? null;
    }

    public function scopeActive($query)
    {
        return $query->where($this->getTable() . ".status", 1);
    }
}
