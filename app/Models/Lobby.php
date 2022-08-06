<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Lobby extends Model
{
    use HasFactory;

    const CACHE_KEY_CURRENT_LOBBY_ID = 'current-lobby-id';

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            $model->setAttribute('invite_code', str_pad((string) random_int(0, 999999), 6, '0'));
        });
    }

    public function getInviteCode(): string
    {
        return $this->getAttribute('invite_code');
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->getAttribute('users');
    }

    /**
     * @return BelongsToMany<User>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot(['is_owner']);
    }
}
