<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@hotwired/turbo' => [
        'version' => '8.0.23',
    ],
    'just-extend' => [
        'version' => '5.1.1',
    ],
    'tus-js-client' => [
        'version' => '4.3.1',
    ],
    'js-base64' => [
        'version' => '3.7.7',
    ],
    'url-parse' => [
        'version' => '1.5.10',
    ],
    'requires-port' => [
        'version' => '1.0.0',
    ],
    'querystringify' => [
        'version' => '2.2.0',
    ],
];
