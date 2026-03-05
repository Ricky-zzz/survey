<?php

return [
    'app_name' => 'Survey App',
    'app_url' => 'http://localhost:8000',
    
    'upload_path' => __DIR__ . '/../public/uploads',
    'upload_max_size' => 5242880, 
    'allowed_extensions' => ['pdf'],
    
    // Link sharing settings
    'enable_link_sharing' => true,
    'link_expiry_days' => 30, 
];
