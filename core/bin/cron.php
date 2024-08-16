<?php


// URL to make the POST request to
$url = "https://freshcartshop.com/cron/87654345678yzx";

// Data to be sent in the POST request
$data = [
    'password' => "MLUPGADXCHHREGNFXJEOIVBGOQOHYO"
];

// Initialize cURL
$curl = curl_init($url);

// Set cURL options
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data)); // Convert the data array to URL-encoded query string
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // Return the response as a string instead of outputting it directly

// Execute the POST request
$response = curl_exec($curl);

// Check for cURL errors
if ($response === false) {
    echo 'cURL Error: ' . curl_error($curl);
} else {
    echo 'Response: ' . $response;
}

// Close the cURL session
curl_close($curl);


