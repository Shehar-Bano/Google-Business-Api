<?php

namespace App\Http\Requests\Api\V1\Player;

use App\Http\Requests\Api\BaseApiRequest;

class RegisterTournamentRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tournament_id' => ['required', 'integer', 'min:1'],
            'payment_method_id' => ['required', 'string', 'in:card,wallet,cash,jazzcash,easypaisa'],
        ];
    }
}
