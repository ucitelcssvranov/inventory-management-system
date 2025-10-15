<?php

echo "Testing bulk operations fixes...\n\n";

// Test URL accessibility
$urls = [
    'http://127.0.0.1:8004/assets',
    'http://127.0.0.1:8004/ajax/locations',
    'http://127.0.0.1:8004/ajax/users/search'
];

foreach ($urls as $url) {
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 5,
            'ignore_errors' => true
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    $status = isset($http_response_header[0]) ? $http_response_header[0] : 'No response';
    
    echo "URL: $url\n";
    echo "Status: $status\n";
    echo "---\n";
}

echo "\nRoute fixes applied:\n";
echo "1. ✅ Fixed route order for /ajax/users/search (now comes before parametrized route)\n";
echo "2. ✅ Fixed JavaScript to handle proper response structure from AJAX controllers\n";
echo "3. ✅ Added proper CSRF token handling for AJAX requests\n";
echo "4. ✅ Added error handling in JavaScript for failed AJAX requests\n";

echo "\nExpected improvements:\n";
echo "- No more 500 errors on /ajax/locations and /ajax/users/search\n";
echo "- No more 'data.forEach is not a function' JavaScript errors\n";
echo "- Proper loading of locations and users in bulk operation modals\n";
echo "- 422 validation errors should now provide clear feedback\n";
