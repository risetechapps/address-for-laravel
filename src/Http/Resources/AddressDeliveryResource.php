<?php

namespace RiseTechApps\Address\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressDeliveryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            "full_address" => $this->full_address,
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

    private function getCountryDescription(): string
    {

        if (!$this->country) return "";

        try {
            $result = orchestrator()->getCountryInfo($this->country);

            if ($result['success'] === true) {
                $data = collect($result['data']);
                $translation = collect($data->get('translations', []));
                $defaultName = $data->get('native', "");
                return $translation->get(app()->getLocale(), $defaultName);
            }
            return "";

        } catch (\Exception $exception) {
            logglyError()->exception($exception)->performedOn($this)->log("Error getting country description");
            return "";
        }
    }

    private function getStateDescription(): string
    {
        if (!$this->country || !$this->state) return "";

        try {
            $result = orchestrator()->getStateInfo($this->country, $this->state);

            if ($result['success'] === true) {
                $data = collect($result['data']);
                return $data->get('name', "");
            }
            return "";
        } catch (\Exception $exception) {
            logglyError()->exception($exception)->performedOn($this)->log("Error getting state description");
            return "";
        }
    }
}
