<?php declare(strict_types=1);

namespace App\Http\Requests\Player;

class GetUserHand extends GetHand
{
    public function rules()
    {
        return array_merge(parent::rules(), ['auth_user_id' => 'required']);
    }
}
