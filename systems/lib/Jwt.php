<?php

namespace Systems\Lib;

class Jwt
{
    /**
     * Generate JWT token
     * 
     * @param array $payload Data to encode
     * @param string $secret Secret key
     * @param int $expiry Expiry in seconds (default 1 hour)
     * @return string JWT token
     */
    public static function encode(array $payload, string $secret, int $expiry = 3600): string
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        
        $payload['iat'] = time();
        $payload['exp'] = time() + $expiry;
        
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($payload)));
        
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    /**
     * Verify and decode JWT token
     * 
     * @param string $token JWT token
     * @param string $secret Secret key
     * @return array|false Payload if valid, false otherwise
     */
    public static function verify(string $token, string $secret)
    {
        $parts = explode('.', $token);
        if (count($parts) != 3) {
            return false;
        }
        
        list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $parts;
        
        $signature = base64_decode(str_replace(['-', '_'], ['+', '/'], $base64UrlSignature));
        $expectedSignature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
        
        if (!hash_equals($expectedSignature, $signature)) {
            return false;
        }
        
        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $base64UrlPayload)), true);
        
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false;
        }
        
        return $payload;
    }
}
