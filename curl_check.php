<?php
$ch = curl_init("https://www.google.com/");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

if ($response) {
    echo "✅ cURL request success.";
} else {
    echo "❌ cURL failed: " . $error;
}
