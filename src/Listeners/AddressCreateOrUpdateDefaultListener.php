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
            // Pular se o modelo foi deletado
            if(!is_null($event->model->getOriginal('deleted_at'))){
                return;
            }

            // Pega todos os dados da request para buscar o endereço
            $requestData = $event->request->all();

            // Usa o método centralizado que busca em 'address' ou 'person.address'
            AddressModel::syncForModel(
                $event->model,
                $requestData,
                AddressModel::TYPE_DEFAULT,
                true
            );

        } catch (\Exception $exception) {
            // Log para debug - se loggly falhar, ainda temos o laravel log
            \Log::error('Address sync failed: ' . $exception->getMessage(), [
                'model' => get_class($event->model),
                'model_id' => $event->model->getKey(),
                'trace' => $exception->getTraceAsString()
            ]);

            // Não relançar - evento não deve quebrar a requisição
        }
    }
}
