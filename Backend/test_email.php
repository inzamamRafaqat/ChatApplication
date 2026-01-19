<?php
namespace Backend;
require_once 'utils/Email.php';

$email = new Email();
$result = $email->sendChannelInvitation(
    'test@example.com',
    'Test User',
    'Test Channel',
    'Admin',
    'test_token_123'
);

echo $result ? 'Email sent!' : 'Email failed!';
?>