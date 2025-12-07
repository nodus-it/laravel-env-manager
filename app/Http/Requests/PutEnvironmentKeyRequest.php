<?php

namespace App\Http\Requests;

use App\Enums\VariableKeySource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PutEnvironmentKeyRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'keys' => ['array', 'min:1'],
            'keys.*.key' => ['required', 'string'],
            'keys.*.value' => ['nullable'],
            'keys.*.source' => ['required', Rule::enum(VariableKeySource::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'key.required' => __('The variable key is required.'),
            'source.required' => __('The source is required.'),
            'source.enum' => __('The source must be one of: environment, project, variable_key.'),
        ];
    }

    /**
     * Normalize incoming payload to a list of items to write.
     *
     * @return array<int, array{key:string, value:mixed, source:string}>
     */
    public function items(): array
    {
        $keys = $this->input('keys');

        if (is_array($keys)) {
            return array_map(function (array $item) {
                return [
                    'key' => (string)($item['key'] ?? ''),
                    'value' => $item['value'] ?? null,
                    'source' => (string)($item['source'] ?? ''),
                ];
            }, $keys);
        }

        // Fallback to single-key payload
        return [[
            'key' => (string)$this->string('key'),
            'value' => $this->input('value'),
            'source' => (string)$this->string('source'),
        ]];
    }
}
