<?php

namespace App\Http\Requests\Api\V1\Account;

use App\Http\Requests\Api\BaseApiRequest;

class DashboardRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
