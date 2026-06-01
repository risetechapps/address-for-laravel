<?php

namespace RiseTechApps\Address\Support;

use Illuminate\Http\Request;

class AddressPayloadResolver
{
    /**
     * Resolve um endereço a partir de um Request ou array.
     *
     * Busca em $data[$key] ou $data['person'][$key].
     */
    public static function single(Request|array $data, string $key, array $fallback = []): array
    {
        $data = $data instanceof Request ? $data->all() : $data;

        if (array_key_exists($key, $data) && !empty($data[$key])) {
            return (array) $data[$key];
        }

        if (isset($data['person'][$key]) && !empty($data['person'][$key])) {
            return (array) $data['person'][$key];
        }

        return $fallback;
    }

    /**
     * Resolve um ou mais endereços, sempre retornando uma lista de arrays.
     *
     * Aceita tanto um endereço único quanto uma lista de endereços.
     */
    public static function multiple(Request|array $data, string $key, array $fallback = []): array
    {
        $payload = static::single($data, $key, $fallback);

        if (empty($payload)) {
            return [];
        }

        return isset($payload[0]) && is_array($payload[0]) ? $payload : [$payload];
    }
}
