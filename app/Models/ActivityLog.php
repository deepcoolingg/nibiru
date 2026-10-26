<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log an activity.
     *
     * @param  array<string, mixed>|null  $metadata
     */
    public static function log(string $action, ?string $description = null, ?string $userId = null, ?array $metadata = null): static
    {
        return static::query()->create([
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }
}