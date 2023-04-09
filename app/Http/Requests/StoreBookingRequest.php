<?php

namespace App\Http\Requests;

use App\Rules\ApartmentAvailableRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('bookings-manage');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'apartment_id' => ['required', 'numeric', Rule::exists('apartments', 'id'), new ApartmentAvailableRule()],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'guest_adults' => ['required', 'numeric'],
            'guest_children' => ['required', 'numeric'],
        ];
    }
}
