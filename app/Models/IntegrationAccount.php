<?php

namespace App\Models;

use App\Enums\IntegrationStatus;
use App\Enums\IntegrationType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property IntegrationType $type
 * @property string $access_token
 * @property array<string, mixed>|null $meta
 * @property IntegrationStatus $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class IntegrationAccount extends Model
{
    /** @use HasFactory<\Database\Factories\IntegrationAccountFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'organization_id',
        'type',
        'access_token',
        'meta',
        'status',
        'scope',
        'shared_with',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => IntegrationType::class,
        'status' => IntegrationStatus::class,
        'meta' => 'array',
        'access_token' => 'encrypted',
        'shared_with' => 'array',
    ];

    /**
     * Get the user that owns the integration account.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the organization that owns the integration account.
     *
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Scope a query to only include active integration accounts.
     *
     * @param  Builder<IntegrationAccount>  $builder
     * @return Builder<IntegrationAccount>
     */
    public function scopeActive(Builder $builder): Builder
    {
        return $builder->where('status', IntegrationStatus::ACTIVE);
    }
}
