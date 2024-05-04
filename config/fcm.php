<?php

return [
    'driver' => env('FCM_PROTOCOL', 'http'),
    'log_enabled' => false,

    'http' => [
        'server_key' => env('FCM_SERVER_KEY', 'Your FCM server key'),
        'sender_id' => env('FCM_SENDER_ID', 'Your sender id'),
        'project_id' => env('FCM_PROJECT_ID', 'Your project id'),
        'server_send_url' => env('FCM_ENDPOINT_URL', 'https://fcm.googleapis.com/v1/projects/{project-id}/messages:send'),
        'server_group_url' => 'https://android.googleapis.com/gcm/notification',
        'timeout' => 30.0, // in second
    ],
];
