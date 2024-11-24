<?php

namespace RiseTechApps\Address\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use RiseTechApps\FormRequest\Services\ServicesForm;

class AddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "zip_code" => $this->zip_code,
            "country" => $this->country,
            "state" => $this->state,
            "city" => $this->city,
            "district" => $this->district,
            "address" => $this->address,
            "number" => $this->number,
            "complement" => $this->complement,
            "country_description" => $this->getCountryDescription(),
            "state_description" => $this->getStateDescription(),
            'deleted' => !is_null($this->deleted_at),
        ];
    }

    private function getCountryDescription(): ?string
    {
        try {

            $country = collect(ServicesForm::getCountryInfo($this->country));
            return collect($country->get('translations'))->get(app()->getLocale(), $country->get('name'));
        } catch (\Exception $exception) {
            return null;
        }
    }

    private function getStateDescription(): ?string
    {
        try {

            return collect(ServicesForm::getStateInfo($this->country, $this->state))->get('name');
        } catch (\Exception $exception) {
            return null;
        }
    }
}
