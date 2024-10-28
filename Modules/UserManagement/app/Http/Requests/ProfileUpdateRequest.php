<?php

namespace Modules\UserManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'phone_number' => ['nullable', 'string', 'regex:/^([+]?\d{1,3}[-]?)?\d{10}$/', 'max:20'],
            'avatar' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg',
                'max:2048', // 2MB max size
                'dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000'
            ],
            'meta_data' => ['nullable', 'array'],
            'meta_data.date_of_birth' => ['nullable', 'date', 'before:today'],
            'meta_data.address' => ['nullable', 'array'],
            'meta_data.address.street' => ['required_with:meta_data.address', 'string', 'max:100'],
            'meta_data.address.city' => ['required_with:meta_data.address', 'string', 'max:50'],
            'meta_data.address.state' => ['required_with:meta_data.address', 'string', 'max:50'],
            'meta_data.address.country' => ['required_with:meta_data.address', 'string', 'max:50'],
            'meta_data.address.postal_code' => ['required_with:meta_data.address', 'string', 'max:20'],
            'meta_data.social_links' => ['nullable', 'array'],
            'meta_data.social_links.*' => ['nullable', 'url'],
            'meta_data.bio' => ['nullable', 'string', 'max:500'],
            'meta_data.preferences' => ['nullable', 'array'],
            'meta_data.preferences.language' => ['nullable', 'string', 'max:10'],
            'meta_data.preferences.timezone' => ['nullable', 'string', 'timezone'],
            'meta_data.preferences.notifications' => ['nullable', 'array'],
            'meta_data.preferences.notifications.*' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone_number.regex' => 'The phone number format is invalid. Please use a valid international format.',
            'avatar.dimensions' => 'The avatar must be between 100x100 and 2000x2000 pixels.',
            'avatar.max' => 'The avatar may not be greater than 2MB.',
            'meta_data.date_of_birth.before' => 'The date of birth must be a date before today.',
            'meta_data.social_links.*.url' => 'The social link must be a valid URL.',
            'meta_data.preferences.timezone' => 'Please select a valid timezone.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Clean phone number format
        if ($this->has('phone_number')) {
            $this->merge([
                'phone_number' => preg_replace('/[^0-9+]/', '', $this->phone_number)
            ]);
        }

        // Ensure meta_data structure
        if ($this->has('meta_data')) {
            $metaData = $this->meta_data;
            
            // Convert date format if exists
            if (isset($metaData['date_of_birth'])) {
                $metaData['date_of_birth'] = date('Y-m-d', strtotime($metaData['date_of_birth']));
            }

            // Ensure social links are arrays
            if (isset($metaData['social_links']) && !is_array($metaData['social_links'])) {
                $metaData['social_links'] = [];
            }

            // Initialize preferences if not set
            if (!isset($metaData['preferences'])) {
                $metaData['preferences'] = [
                    'language' => config('app.locale'),
                    'timezone' => config('app.timezone'),
                    'notifications' => [
                        'email' => true,
                        'push' => true,
                        'sms' => false
                    ]
                ];
            }

            $this->merge(['meta_data' => $metaData]);
        }
    }
}

namespace Modules\UserManagement\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'phone_number' => ['nullable', 'string', 'regex:/^([+]?\d{1,3}[-]?)?\d{10}$/', 'max:20'],
            'avatar' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg',
                'max:2048',
                'dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000'
            ],
            'meta_data' => ['nullable', 'array'],
            'meta_data.date_of_birth' => ['nullable', 'date', 'before:today'],
            'meta_data.address' => ['nullable', 'array'],
            'meta_data.address.street' => ['required_with:meta_data.address', 'string', 'max:100'],
            'meta_data.address.city' => ['required_with:meta_data.address', 'string', 'max:50'],
            'meta_data.address.state' => ['required_with:meta_data.address', 'string', 'max:50'],
            'meta_data.address.country' => ['required_with:meta_data.address', 'string', 'max:50'],
            'meta_data.address.postal_code' => ['required_with:meta_data.address', 'string', 'max:20'],
            'meta_data.social_links' => ['nullable', 'array'],
            'meta_data.social_links.*' => ['nullable', 'url'],
            'meta_data.bio' => ['nullable', 'string', 'max:500'],
            'meta_data.preferences' => ['nullable', 'array'],
            'meta_data.preferences.language' => ['nullable', 'string', 'max:10'],
            'meta_data.preferences.timezone' => ['nullable', 'string', 'timezone'],
            'meta_data.preferences.notifications' => ['nullable', 'array'],
            'meta_data.preferences.notifications.*' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone_number.regex' => 'The phone number format is invalid. Please use a valid international format.',
            'avatar.dimensions' => 'The avatar must be between 100x100 and 2000x2000 pixels.',
            'avatar.max' => 'The avatar may not be greater than 2MB.',
            'meta_data.date_of_birth.before' => 'The date of birth must be a date before today.',
            'meta_data.social_links.*.url' => 'The social link must be a valid URL.',
            'meta_data.preferences.timezone' => 'Please select a valid timezone.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('phone_number')) {
            $this->merge([
                'phone_number' => preg_replace('/[^0-9+]/', '', $this->phone_number)
            ]);
        }

        if ($this->has('meta_data')) {
            $metaData = $this->meta_data;

            // Standardize date format
            if (isset($metaData['date_of_birth'])) {
                $metaData['date_of_birth'] = date('Y-m-d', strtotime($metaData['date_of_birth']));
            }

            // Initialize or clean social links
            if (isset($metaData['social_links'])) {
                $metaData['social_links'] = array_filter((array)$metaData['social_links']);
            }

            // Set default preferences if not provided
            if (!isset($metaData['preferences'])) {
                $metaData['preferences'] = [
                    'language' => config('app.locale'),
                    'timezone' => config('app.timezone'),
                    'notifications' => [
                        'email' => true,
                        'push' => true,
                        'sms' => false
                    ]
                ];
            }

            $this->merge(['meta_data' => $metaData]);
        }
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated($key, $default);
        
        // Clean and standardize the data for API consumption
        if (isset($validated['meta_data'])) {
            // Remove any null or empty values from the meta_data
            $validated['meta_data'] = array_filter($validated['meta_data'], function ($value) {
                return !is_null($value) && (!is_array($value) || !empty($value));
            });
        }

        return $validated;
    }
}