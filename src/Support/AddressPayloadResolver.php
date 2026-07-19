<?php

namespace RiseTechApps\Address\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class AddressPayloadResolver
{
    public static function single(Request|array $source, string $key, array $fallback = []): array
    {
        $data = $source instanceof Request ? $source->all() : $source;

        if (Arr::has($data, $key)) {
            return (array) Arr::get($data, $key);
        }

        $nestedKey = sprintf('person.%s', $key);
        if (Arr::has($data, $nestedKey)) {
            return (array) Arr::get($data, $nestedKey);
        }

        return $fallback;
    }

    public static function multiple(Request|array $source, string $key, array $fallback = []): array
    {
        $payload = static::single($source, $key, $fallback);

        if (empty($payload)) {
            return [];
        }

        return isset($payload[0]) && is_array($payload[0]) ? $payload : [$payload];
    }
}
