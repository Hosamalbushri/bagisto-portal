<?php

namespace Platform\Portal\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MenuRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $id = $this->route('id');
        $channelId = $this->input('channel_id') ?: null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                $id ? 'required' : 'nullable',
                'string',
                'max:255',
                Rule::unique('portal_menus', 'slug')
                    ->ignore($id)
                    ->where(function ($query) use ($channelId) {
                        return $channelId
                            ? $query->where('channel_id', $channelId)
                            : $query->whereNull('channel_id');
                    }),
            ],
            'channel_id' => ['nullable', 'integer', 'exists:channels,id'],
            'status' => ['nullable', 'boolean'],
            'items' => ['nullable', 'array'],
        ];
    }
}
