<?php
class Email {
    private $from = "noreply@chatsystem.com";
    private $fromName = "Chat System";
    
    // Send channel invitation email
    public function sendChannelInvitation($toEmail, $toName, $channelName, $inviterName, $token) {
        $subject = "You're invited to join '{$channelName}' channel";
        
        // Create acceptance link
        $acceptLink = $this->getBaseUrl() . "/ChatApplication/Backend/api/accept_invitation.php?token=" . urlencode($token);
        
        // HTML Email Template
        $message = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 50px auto; background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .header { text-align: center; padding-bottom: 20px; border-bottom: 2px solid #3b82f6; }
                .header h1 { color: #3b82f6; margin: 0; }
                .content { padding: 30px 0; }
                .button { display: inline-block; padding: 15px 30px; background-color: #3b82f6; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .button:hover { background-color: #2563eb; }
                .footer { text-align: center; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🎉 Channel Invitation</h1>
                </div>
                <div class='content'>
                    <p>Hello <strong>{$toName}</strong>,</p>
                    <p><strong>{$inviterName}</strong> has invited you to join the private channel:</p>
                    <h2 style='color: #3b82f6; text-align: center;'>🔒 {$channelName}</h2>
                    <p>Click the button below to accept the invitation and join the channel:</p>
                    <div style='text-align: center;'>
                        <a href='{$acceptLink}' class='button'>Accept Invitation</a>
                    </div>
                    <p style='color: #6b7280; font-size: 14px;'>Or copy this link to your browser:<br>
                    <a href='{$acceptLink}'>{$acceptLink}</a></p>
                    <p style='margin-top: 30px;'>This invitation will expire in 7 days.</p>
                </div>
                <div class='footer'>
                    <p>This is an automated email from Chat System. Please do not reply to this email.</p>
                    <p>&copy; 2026 Chat System. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Email headers
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
        $headers .= "From: {$this->fromName} <{$this->from}>" . "\r\n";
        $headers .= "Reply-To: {$this->from}" . "\r\n";
        
        // Send email
        return mail($toEmail, $subject, $message, $headers);
    }
    
    // Get base URL
    private function getBaseUrl() {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        return $protocol . '://' . $host;
    }
    
    // Send simple notification email
    public function sendNotification($toEmail, $subject, $message) {
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
        $headers .= "From: {$this->fromName} <{$this->from}>" . "\r\n";
        
        return mail($toEmail, $subject, $message, $headers);
    }
}
?>