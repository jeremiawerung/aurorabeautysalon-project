<?php

return [
    'badge' => 'TRANSACTIONS',
    'title' => 'Choose Service Category',
    'meta' => [
        // use {!! !!} in blade because :count contains <strong>...</strong>
        'services_html' => 'Services: :count available',
    ],
    'actions' => [
        'choose' => 'Choose Category',
    ],
    'empty' => 'No service categories.',
];
