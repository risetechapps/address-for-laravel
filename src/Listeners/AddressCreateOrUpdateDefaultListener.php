<?php

namespace RiseTechApps\Address\Listeners;

use RiseTechApps\Address\Address;
use RiseTechApps\Address\Events\Address\AddressCreateOrUpdateDefaultEvent;
use RiseTechApps\Address\Models\Address as AddressModel;
use RiseTechApps\Address\Support\AddressPayloadResolver;

class AddressCreateOrUpdateDefaultListener
{
    public function __construct()
    {
    }

    public function handle(AddressCreateOrUpdateDefaultEvent $event): void
    {
        try {

            if(!is_null($event->model->getOriginal('deleted_at'))){
                return;
            }

            $created = !is_null($event->model->address);

            $address = AddressPayloadResolver::single(
                $event->request,
                'address',
                Address::getAddress()
            );

            $address = Address::fillWithDefault($address, $event->model);

            if($created === true){
                $event->model->address->update($address);
            }else{
                $address['address_type'] = get_class($event->model);
                $address['address_id'] = $event->model->getKey();
                $address['type'] = Address::TYPE_DEFAULT;
                AddressModel::create($address);
            }

        } catch (\Exception $exception) {
            logglyError()->exception($exception)->performedOn($event->model)->log("Error registering address");
        }
    }
}
