<?php

use Vinkla\Hashids\Facades\Hashids;

if (!function_exists('encode_id')) {
    function encode_id($id, $model = 'default')
    {
        $connections = config('hashids.connections', []);
        $connection = $connections[$model] ?? $connections['main'];
        return Hashids::connection($connection)->encode($id);
    }
}

if (!function_exists('decode_id')) {
    function decode_id($hash, $model = 'default')
    {
        try {
            $connections = config('hashids.connections', []);
            $connection = $connections[$model] ?? $connections['main'];
            $decoded = Hashids::connection($connection)->decode($hash);
            return !empty($decoded) ? $decoded[0] : null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
