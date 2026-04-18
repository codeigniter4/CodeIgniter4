<@php

namespace {namespace};

use CodeIgniter\HTTP\FormRequest;

class {class} extends FormRequest
{
    /**
     * Returns the validation rules that apply to this request.
     *
     * @return array<string, list<string>|string>
     */
    public function rules(): array
    {
        return [
            // 'field' => 'required',
        ];
    }

    // /**
    //  * Custom error messages keyed by field.rule.
    //  *
    //  * @return array<string, array<string, string>>
    //  */
    // public function messages(): array
    // {
    //     return [];
    // }

    // /**
    //  * Determines if the current user is authorized to make this request.
    //  *
    //  * Defaults to true in FormRequest. Override only when authorization
    //  * depends on application logic.
    //  */
    // public function isAuthorized(): bool
    // {
    //     return true;
    // }
}
