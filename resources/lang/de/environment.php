<?php

return [
    'types' => [
        'production' => 'Produktion',
        'staging' => 'Staging',
        'testing' => 'Testing',
        'local' => 'Lokal',
        'custom' => 'Benutzerdefiniert',
    ],

    'effective_variables' => [
        'title' => 'Wirksame Variablen',
        'source' => [
            'environment' => 'Umgebung',
            'project' => 'Projekt',
            'default' => 'Standard',
        ],
        'actions' => [
            'edit_at_source' => 'An der Quelle bearbeiten',
            'adopt_as_project_default' => 'Als Projekt-Default übernehmen',
            'adopt_as_default' => 'Als Standard übernehmen',
        ],
        'notifications' => [
            'adopted_project_default' => 'Wert als Projekt-Default übernommen.',
            'adopted_default' => 'Wert als Standard übernommen.',
        ],
    ],
];
