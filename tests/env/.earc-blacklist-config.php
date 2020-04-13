<?php

use eArc\EventTreeTests\env\other\otherTreeRoot\patience\newInOtherTree\BasicListener;
use eArc\EventTreeTests\env\treeroot\patience\NoPatienceListener;
use eArc\EventTreeTests\env\treeroot\patience\PatienceListener2;

return ['earc' => [
    'is_prod_environment' => false,
    'event_tree' => [
        'directories' => [
            '../tests/env/treeroot' => 'eArc\\EventTreeTests\\env\\treeroot',
            '../tests/env/other/otherTreeRoot' => 'eArc\\EventTreeTests\\env\\other\\otherTreeRoot',
        ],
        'blacklist' => [
            NoPatienceListener::class => true,
            PatienceListener2::class => true,
            BasicListener::class => true,
        ]
    ]
]];
