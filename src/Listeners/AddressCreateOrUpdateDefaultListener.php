<?php

namespace RiseTechApps\Address\Listeners;

use Illuminate\Support\Arr;
use RiseTechApps\Address\Events\Address\AddressCreateOrUpdateDefaultEvent;
use RiseTechApps\Address\Models\Address;

class AddressCreateOrUpdateDefaultListener
{
    public function __construct()
    {
    }

    public function handle(AddressCreateOrUpdateDefaultEvent $event): void
    {
        try {

            $address = [];

            if(!is_null($event->model->getOriginal('deleted_at'))){
                return;
            }

            $created = !is_null($event->model->address);

            if ($event->request->has('address')) {
                $address = $event->request->input('address');
            }else if ($event->request->has('person.address')) {
                $address = $event->request->input('person.address');
            }else{
                if(!empty(\RiseTechApps\Address\Address::getAddress())){
                    $address = \RiseTechApps\Address\Address::getAddress();
                }
            }

            if (!Arr::exists($address, 'zip_code')) {
                $address = Arr::add($address, 'zip_code', $created ?
                    $event->model->address->getOriginal('zip_code') : null);
            }
            if (!Arr::exists($address, 'state')) {
                $address = Arr::add($address, 'state', $created ?
                    $event->model->address->getOriginal('state') : null);
            }
            if (!Arr::exists($address, 'city')) {
                $address = Arr::add($address, 'city', $created ?
                    $event->model->address->getOriginal('city') : null);
            }
            if (!Arr::exists($address, 'district')) {
                $address = Arr::add($address, 'district', $created ?
                    $event->model->address->getOriginal('district') : null);
            }
            if (!Arr::exists($address, 'address')) {
                $address = Arr::add($address, 'address', $created ?
                    $event->model->address->getOriginal('address') : null);
            }
            if (!Arr::exists($address, 'number')) {
                $address = Arr::add($address, 'number', $created ?
                    $event->model->address->getOriginal('number') : null);
            }
            if (!Arr::exists($address, 'complement')) {
                $address = Arr::add($address, 'complement', $created ?
                    $event->model->address->getOriginal('complement') : null);
            }

            if($created === true){
                $event->model->address->update($address);
            }else{
                $address['address_type'] = get_class($event->model);
                $address['address_id'] = $event->model->getKey();
                $address['type'] = 'default';
                Address::create($address);
            }

        } catch (\Exception $exception) {
            logglyError()->exception($exception)->performedOn($event->model)->log("Error registering address");
        }
    }
}
