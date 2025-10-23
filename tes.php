<?php
$ch = curl_init('https://api.telegram.org/bot7913491515:AAFaq9O4qAGci7uDIWVyKCshQmX5iW0L408/getMe');
curl_setopt($ch, CURLOPT_CAINFO, 'C:\xampp\php\extras\ssl\cacert.pem');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
} else {
    echo 'Success:' . $response;
}
curl_close($ch);
?>