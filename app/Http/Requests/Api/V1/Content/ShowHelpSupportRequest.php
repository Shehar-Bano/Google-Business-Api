<?php

namespace App\Http\Requests\Api\V1\Content;

use App\Http\Requests\Api\BaseApiRequest;

class ShowHelpSupportRequest extends BaseApiRequest
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
