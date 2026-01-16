<?php
// Simple Bcrypt implementation (no external libraries)
class Bcrypt {
    
    // Hash a password
    public static function hash($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    }
    
    // Verify a password against a hash
    public static function verify($password, $hash) {
        return password_verify($password, $hash);
    }
}
?>