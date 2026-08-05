<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'share_percentage',
        'effective_from',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'share_percentage' => 'float',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Get the active share configuration for a given date.
     * Returns the most recent effective_from <= $date rows per user.
     */
    public static function getActiveShares(int $projectId, ?string $date = null)
    {
        $date = $date ?? now()->format('Y-m-d');

        return static::where('project_id', $projectId)
            ->where('effective_from', '<=', $date)
            ->whereIn('id', function ($query) use ($projectId, $date) {
                $query->selectRaw('MAX(id)')
                    ->from('project_members')
                    ->where('project_id', $projectId)
                    ->where('effective_from', '<=', $date)
                    ->groupBy('user_id');
            })
            ->with('user')
            ->get();
    }

    /**
     * Get all distinct periods (effective_from dates) for a project.
     */
    public static function getPeriods(int $projectId)
    {
        return static::where('project_id', $projectId)
            ->select('effective_from')
            ->distinct()
            ->orderBy('effective_from')
            ->pluck('effective_from');
    }
}

