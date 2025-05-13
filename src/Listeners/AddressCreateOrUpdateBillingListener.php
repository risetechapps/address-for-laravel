<?php

namespace RiseTechApps\Address\Listeners;

use RiseTechApps\Address\Address;
use RiseTechApps\Address\Events\Address\AddressCreateOrUpdateBillingEvent;
use RiseTechApps\Address\Models\Address as AddressModel;

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

            if ($event->request->has('address_billing')) {
                $BillingAddresses = $event->request->input('address_billing');
            } else if ($event->request->has('person.address_delivery')) {
                $BillingAddresses = $event->request->input('person.address_billing');
            } else {
                if (!empty(\RiseTechApps\Address\Address::getAddressBilling())) {
                    $BillingAddresses = \RiseTechApps\Address\Address::getAddressBilling();
                }
            }

            if (!is_null($event->model->getOriginal('deleted_at'))) {
                return;
            }

            $addresses = array_filter($BillingAddresses, function ($address) {
                return collect($address)->filter()->isNotEmpty();
            });

            if (empty($addresses)) {
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
