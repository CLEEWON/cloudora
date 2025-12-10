<?php
require_once "./config/backblaze.php";


function b2Authorize() {
    global $B2_KEY_ID, $B2_APPLICATION_KEY;

    $credentials = base64_encode($B2_KEY_ID . ":" . $B2_APPLICATION_KEY);

    $ch = curl_init("https://api.backblazeb2.com/b2api/v2/b2_authorize_account");
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Basic $credentials"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);

    return $response;
}
