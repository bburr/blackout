<?php declare(strict_types=1);

namespace App\Http\Requests\Game;

use App\Http\Requests\Request;
use App\State\GameSettings;

/**
 * @phpstan-import-type InputGameSettings from GameSettings
 */
class StartGame extends Request
{
    /**
     * @phpstan-return InputGameSettings
     */
    public function getGameSettings(): array
    {
        return $this->only(array_keys($this->rules()));
    }

    public function rules(): array
    {
        return [
            'ending_num_tricks' => 'int',
            'max_num_tricks' => 'int',
            'points_for_correct_bet' => 'int',
            'starting_num_tricks' => 'int',
        ];
    }
}
