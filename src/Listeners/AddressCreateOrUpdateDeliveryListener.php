<?php

namespace RiseTechApps\Address\Listeners;

use RiseTechApps\Address\Events\Address\AddressCreateOrUpdateDeliveryEvent;
use RiseTechApps\Address\Model\Address as AddressModel;

class AddressCreateOrUpdateDeliveryListener
{
    public function __construct()
    {
    }

    public function handle(AddressCreateOrUpdateDeliveryEvent $event): void
    {

        try {
            $created = !is_null($event->model->address);
            $chargeAddresses = $event->request->input('address_delivery', []);

            if (!is_null($event->model->getOriginal('deleted_at'))) {
                return;
            }

            if(count($chargeAddresses) > 0){

                if ($created) {
                    $event->model->addressDelivery()->delete();
                }

                foreach ($chargeAddresses as $address) {
                    $address = \RiseTechApps\Address\Address::fillWithDefault($address, $event->model);

                    $address['address_type'] = get_class($event->model);
                    $address['address_id'] = $event->model->getKey();
                    $address['type'] = 'delivery';
                    AddressModel::create($address);
                }
            }

        } catch (\Exception $exception) {
            logglyError()->exception($exception)
                ->withRequest($event->request)
                ->performedOn(static::class)
                ->log("Error registering address");

        }
    }
}
