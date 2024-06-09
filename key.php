<?php
header('Content-Type: application/json');

// Function to fetch the source code of a URL using cURL
function fetchSourceCode($url) {
    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute cURL session
    $sourceCode = curl_exec($ch);

    // Check for errors
    if (curl_errno($ch)) {
        echo json_encode(['error' => 'Curl error: ' . curl_error($ch)], JSON_PRETTY_PRINT);
        exit;
    }

    // Close cURL session
    curl_close($ch);

    // Return the fetched source code
    return $sourceCode;
}

// URL of the website
$url = "https://www.sonyliv.com/";

// Fetch the source code of the website
$sourceCode = fetchSourceCode($url);

// Use regex to extract the value of jwtoken
if (preg_match('/resultObj:"(.*?)"/', $sourceCode, $matches)) {
    // Extracted value
    $jwtoken = $matches[1];
    
    // Output as JSON
    $data = ['jwtoken' => $jwtoken];
    echo json_encode($data, JSON_PRETTY_PRINT);
} else {
    echo json_encode(['error' => 'jwtoken not found.'], JSON_PRETTY_PRINT);
}
?>
