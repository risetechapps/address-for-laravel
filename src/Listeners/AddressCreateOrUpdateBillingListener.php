<?php

namespace RiseTechApps\Address\Listeners;

use RiseTechApps\Address\Address;
use RiseTechApps\Address\Events\Address\AddressCreateOrUpdateBillingEvent;
use RiseTechApps\Address\Model\Address as AddressModel;

class AddressCreateOrUpdateBillingListener
{
    public function __construct()
    {
    }

    public function handle(AddressCreateOrUpdateBillingEvent $event): void
    {

        try {
            $created = !is_null($event->model->address);
            $BillingAddresses = $event->request->input('address_billing', []);


            if (!is_null($event->model->getOriginal('deleted_at'))) {
                return;
            }

            if (count($BillingAddresses) > 0) {
                if ($created) {
                    $event->model->addressBilling()->delete();
                }

                foreach ($BillingAddresses as $address) {
                    $address = Address::fillWithDefault($address, $event->model);

                    $address['address_type'] = get_class($event->model);
                    $address['address_id'] = $event->model->getKey();
                    $address['type'] = 'billing';
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
