<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Game extends Model
{
    protected $fillable = [
        'lobby_uuid',
    ];

    public function getLobby(): Lobby
    {
        return $this->getAttribute('lobby');
    }

    public function getUsers(): Collection
    {
        return $this->getAttribute('users');
    }

    public function lobby(): BelongsTo
    {
        return $this->belongsTo(Lobby::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
