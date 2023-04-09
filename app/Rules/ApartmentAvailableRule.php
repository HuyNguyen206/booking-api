<?php

namespace App\Rules;

use App\Models\Apartment;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class ApartmentAvailableRule implements ValidationRule, DataAwareRule
{
    protected $data = [];

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $apartment = Apartment::find($value);
        if ((int) $this->data['guest_adults'] > $apartment->capacity_adults || (int) $this->data['guest_children'] > $apartment->capacity_children) {
            $fail('Sorry, this apartment does not fit all your guests');
        }

        if ($apartment->bookings()
            ->whereBetween('bookings.start_date', [$this->data['start_date'], $this->data['end_date']])
            ->orWhereBetween('bookings.end_date', [$this->data['start_date'], $this->data['end_date']])
            ->exists()) {
            $fail('Sorry, this apartment is not available for those dates');
        }

    }

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }
}
