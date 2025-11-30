<?php

return [
    'types' => [
        'production' => 'Production',
        'staging' => 'Staging',
        'testing' => 'Testing',
        'local' => 'Local',
        'custom' => 'Custom',
    ],

    'effective_variables' => [
        'title' => 'Effective Variables',
        'source' => [
            'environment' => 'Environment',
            'project' => 'Project',
            'default' => 'Default',
        ],
        'actions' => [
            'edit_at_source' => 'Edit at source',
            'adopt_as_project_default' => 'Adopt as project default',
            'adopt_as_default' => 'Adopt as default',
        ],
        'notifications' => [
            'adopted_project_default' => 'Adopted value as project default.',
            'adopted_default' => 'Adopted value as default.',
        ],
    ],
];
