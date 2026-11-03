<?php

return [
    /*
     * Path URL Dokumentasi Swagger UI
     */
    'ui' => [
        'title' => 'ScaffoldSync REST API Documentation',
        'path' => 'docs/api',
        'hide_try_it' => false,
    ],

    'routes' => [
        'api_path' => 'api',
    ],

    /*
     * Ubah bagian ini: Kosongkan middleware agar tidak mencari tabel sessions
     */
    'middleware' => [],
];