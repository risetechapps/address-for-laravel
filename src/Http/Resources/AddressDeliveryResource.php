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

    private function getCountryDescription(): ?string
    {
        try {

            $result = \RiseTechApps\OrchestratorLink\Feature\Service::getCountryInfo($this->country);

            if ($result['success'] === true) {
                $data = collect($result['data']);
                return collect($data->get('translations'))->get(app()->getLocale(), $data->get('name'));
            }
            return null;

        } catch (\Exception $exception) {
            logglyError()->exception($exception)
                ->withTags(['action' => 'getStateDescription'])
                ->performedOn(['model' => $this, 'class' => self::class])
                ->log("Error getting country description");
            return null;
        }
    }

    private function getStateDescription(): ?string
    {
        try {
            $result = \RiseTechApps\OrchestratorLink\Feature\Service::getStateInfo($this->country, $this->state);

            if ($result['success'] === true) {
                $data = collect($result['data']);
                return $data->get('name');
            }
            return null;
        } catch (\Exception $exception) {
            logglyError()->exception($exception)
                ->withTags(['action' => 'getStateDescription'])
                ->performedOn(['model' => $this, 'class' => self::class])
                ->log("Error getting state description");
            return null;
        }
    }
}
