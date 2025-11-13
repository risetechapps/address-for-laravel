<?php

namespace RiseTechApps\Address\Listeners;

use RiseTechApps\Address\Address;
use RiseTechApps\Address\Events\Address\AddressCreateOrUpdateDeliveryEvent;
use RiseTechApps\Address\Models\Address as AddressModel;
use RiseTechApps\Address\Support\AddressPayloadResolver;

class AddressCreateOrUpdateDeliveryListener
{
    public function __construct()
    {
    }

    public function handle(AddressCreateOrUpdateDeliveryEvent $event): void
    {

        try {
            $created = !is_null($event->model->address);

            $chargeAddresses = AddressPayloadResolver::multiple(
                $event->request,
                'address_delivery',
                Address::getAddressDelivery()
            );

            if (!is_null($event->model->getOriginal('deleted_at'))) {
                return;
            }

            $addresses = array_filter($chargeAddresses, function ($address) {
                return collect($address)->filter()->isNotEmpty();
            });

            if (empty($addresses)) {
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
                    $address['type'] = Address::TYPE_DELIVERY;
                    AddressModel::create($address);
                }
            }

        } catch (\Exception $exception) {
            logglyError()->exception($exception)->performedOn($event->model)->log("Error registering address");
        }
    }
}
