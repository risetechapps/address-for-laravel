<?php

namespace RiseTechApps\Address\Listeners;

use RiseTechApps\Address\Address;
use RiseTechApps\Address\Events\Address\AddressCreateOrUpdateBillingEvent;
use RiseTechApps\Address\Models\Address as AddressModel;
use RiseTechApps\Address\Support\AddressPayloadResolver;

class AddressCreateOrUpdateBillingListener
{
    public function __construct()
    {
    }

    public function handle(AddressCreateOrUpdateBillingEvent $event): void
    {

        try {
            $created = !is_null($event->model->address);

            $BillingAddresses = AddressPayloadResolver::multiple(
                $event->request,
                'address_billing',
                Address::getAddressBilling()
            );

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
                    $address['type'] = Address::TYPE_BILLING;
                    AddressModel::create($address);
                }
            }

        } catch (\Exception $exception) {
            logglyError()->exception($exception)->performedOn($event->model)->log("Error registering address");
        }
    }
}
