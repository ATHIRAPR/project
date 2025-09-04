<?php
 
return [
    'find' => [
        'maxdepth' => 3,
    ],
 
    'sendmail' => [
        'from' => 'JP-IT-WebSysman-Confirm@olympus.com',
        'inventory_notification_day' => 7,
    ],
 
    'local_repos' => [
        // Full paths on your local filesystem where repos will be cloned
       'ot' => rtrim(APPPATH, '/') . '/ot',
        'og' => rtrim(APPPATH, '/') . '/og',
    ],
 
    'git' => [
        //  Only needed if repo is private
        'pat' => 'GITHUB_PERSONAL_ACCESS_TOKEN',
 
        // Clone URLs for the OT and OG repos
        'ot_repo_url' => 'https://github.com/AthiraPR-2025/demo-ot-html.git',
        'og_repo_url' => 'https://github.com/AthiraPR-2025/demo-og-html.git',
    ],
];
