<?php
// ai_chat.php
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$userMessage = $input['message'] ?? '';

if (!$userMessage) {
    echo json_encode(['reply' => 'No message received']);
    exit;
}

$apiKey = "AIzaSyAd3gPQ_lWBTUgVIIa_cGBirwkm5kkLk0U"; // Keep this secure!
$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

$data = [
    "system_instruction" => [
        "parts" => [
            ["text" => "You are the AI assistant for ALICE (Artificial Learning & Intelligence Club of Engineers). 
            
            MISSION:
            ALICE empowers students to explore AI through collaboration, projects, and community engagement. 
            
            STRICT SCOPE RULES:
            1. ONLY answer questions related to Artificial Intelligence, engineering, and the ALICE club.
            2. If a user asks a question about topics outside of AI (e.g., cooking, sports, politics, general small talk), politely tell them: 'I'm sorry, that is out of my scope. I am here to assist with AI and ALICE-related inquiries.'
            3. If asked how to create an event, tell them: 'Just click on the + icon at the bottom of the screen.'
            
            TONE:
            Helpful, professional, and encouraging for engineering students."]
        ]
    ],
    "contents" => [
        ["parts" => [["text" => $userMessage]]]
    ]
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// ... after curl_exec and json_decode ...

$result = json_decode($response, true);

if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
    // Success: Get the actual AI text
    $reply = $result['candidates'][0]['content']['parts'][0]['text'];
} elseif (isset($result['error'])) {
    // Check if it's a 429 error
    if ($result['error']['code'] == 429) {
        $reply = "I'm a bit overwhelmed right now! Please wait a few seconds before asking again.";
    } else {
        $reply = "API Error: " . $result['error']['message'];
    }
} else {
    $reply = "Sorry, I could not generate a response at this time.";
}

echo json_encode(['reply' => $reply]);