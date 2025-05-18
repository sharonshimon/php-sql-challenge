<?php
require_once 'config.php';

// Define the function if not already defined (or include the file where it is declared)
function fetchDataUsingCurl(string $url): string {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        die("cURL error: " . curl_error($ch));
    }
    curl_close($ch);
    return $response;
}

// Test the function with the API URL (e.g., retrieve a list of users)
$response = fetchDataUsingCurl(API_URL . '/users');
$users = json_decode($response, true);

// Output the response for testing
echo "<pre>";
print_r($users);
echo "</pre>";
?>