<?php

// Set content type header
header('Content-Type: application/json');

// Include key.php to get the JSON data
include 'key.php';

// Check if the jwtoken is provided in the JSON data
if (!isset($jwtoken)) {
    echo json_encode(['error' => 'jwtoken not found in key.php'], JSON_PRETTY_PRINT);
    exit;
}

// Get the id from the query string
$id = isset($_GET['id']) ? $_GET['id'] : '';

// Check if the id is provided
if (empty($id)) {
    echo json_encode(['error' => 'id parameter is missing'], JSON_PRETTY_PRINT);
    exit;
}

// API endpoint with the id parameter
$url = 'https://apiv2.sonyliv.com/AGL/3.8/A/ENG/WEB/IN/WB/CONTENT/VIDEOURL/VOD/' . $id;

// Set up cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:126.0) Gecko/20100101 Firefox/126.0',
    'Security_token: ' . $jwtoken, // Include the extracted jwtoken here
    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
    'Referer: https://www.sonyliv.com',
    'Accept-Language: en-US,en;q=0.5',
    'Accept-Encoding: gzip, deflate, br',
    'Upgrade-Insecure-Requests: 1',
    'Sec-Fetch-Dest: document',
    'Sec-Fetch-Mode: navigate',
    'Sec-Fetch-Site: none',
    'Sec-Fetch-User: ?1',
    'Priority: u=1',
    'Te: trailers'
));

// Execute the request
$response = curl_exec($ch);

// Check for errors
if ($response === false) {
    echo json_encode(['error' => 'Curl error: ' . curl_error($ch)], JSON_PRETTY_PRINT);
    exit;
}

// Close cURL session
curl_close($ch);

// Decompress the response if it's gzipped
if (substr($response, 0, 2) === "\x1f\x8b") {
    $response = gzdecode($response);
}

// Modify the videoURL domain
$response = str_replace('sonynondrmvod.akamaized.net', 'streamingcf.sonyliv.com', $response);
$response = str_replace('securetoken.sonyliv.com', 'streamingcf.sonyliv.com', $response);

// Decode the JSON response into an associative array
$data = json_decode($response, true);

// Check if decoding was successful
if ($data === null) {
    echo json_encode(['error' => 'Failed to decode JSON data'], JSON_PRETTY_PRINT);
    exit;
}

// Function to recursively search for "videoURL" field and extract it
function extractVideoURL($data) {
    $videoURL = null;
    foreach ($data as $key => $value) {
        if ($key === 'videoURL') {
            $videoURL = $value;
        } elseif (is_array($value)) {
            $videoURL = extractVideoURL($value);
        }
        if ($videoURL !== null) {
            break;
        }
    }
    return $videoURL;
}

// Extract the "videoURL" field from the JSON response
$videoURL = extractVideoURL($data);

// Output the "videoURL" field
if ($videoURL !== null) {
    echo json_encode(['videoURL' => $videoURL], JSON_PRETTY_PRINT);
} else {
    echo json_encode(['error' => 'videoURL not found in response'], JSON_PRETTY_PRINT);
}

?>
