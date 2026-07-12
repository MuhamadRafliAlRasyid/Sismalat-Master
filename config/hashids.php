<?php

return [
    'default' => 'main',

    'connections' => [
        'main' => [
            'salt' => env('HASHIDS_SALT', 'SistemManajemenAlatRahasia123!'),
            'length' => 10, // Panjang 6 untuk keseimbangan unik dan pendek
            'alphabet' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890',
        ],
        'alternative' => [
            'salt' => env('HASHIDS_SALT_ALTERNATIVE', 'rahasia-lain-untuk-alternatif'),
            'length' => 10,
            'alphabet' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890',
        ],
    ],

];
