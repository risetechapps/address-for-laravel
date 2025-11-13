<?php

namespace RiseTechApps\Address\Support;

use Illuminate\Http\Request;

class AddressPayloadResolver
{
    public static function single(Request $request, string $key, array $fallback = []): array
    {
        if ($request->has($key)) {
            return (array) $request->input($key);
        }

        $nestedKey = sprintf('person.%s', $key);
        if ($request->has($nestedKey)) {
            return (array) $request->input($nestedKey);
        }

        return $fallback;
    }

    public static function multiple(Request $request, string $key, array $fallback = []): array
    {
        $payload = static::single($request, $key, $fallback);

        if (empty($payload)) {
            return [];
        }

        return isset($payload[0]) && is_array($payload[0]) ? $payload : [$payload];
    }
}
