<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Game extends Model
{
    const CACHE_KEY_CURRENT_GAME_ID = 'current-game-id';

    protected $fillable = [
        'lobby_uuid',
    ];

    public function getUsers(): Collection
    {
        return $this->getAttribute('users');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
