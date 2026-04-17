<?php

namespace Systems\Lib;

class Jwt
{
    private static function base64UrlEncode(string $input): string
    {
        return rtrim(strtr(base64_encode($input), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $input): ?string
    {
        if (!preg_match('/^[A-Za-z0-9\-_]*$/', $input)) {
            return null;
        }

        $remainder = strlen($input) % 4;
        if ($remainder !== 0) {
            $input .= str_repeat('=', 4 - $remainder);
        }

        $decoded = base64_decode(strtr($input, '-_', '+/'), true);
        return $decoded === false ? null : $decoded;
    }

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
        if ($header === false) {
            throw new \RuntimeException('Failed to encode JWT header');
        }
        
        $payload['iat'] = time();
        $payload['exp'] = time() + $expiry;
        $payloadJson = json_encode($payload);
        if ($payloadJson === false) {
            throw new \RuntimeException('Failed to encode JWT payload');
        }
        
        $base64UrlHeader = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode($payloadJson);
        
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
        $base64UrlSignature = self::base64UrlEncode($signature);
        
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
        if (count($parts) !== 3) {
            return false;
        }
        
        list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $parts;

        $headerJson = self::base64UrlDecode($base64UrlHeader);
        if ($headerJson === null) {
            return false;
        }
        $header = json_decode($headerJson, true);
        if (!is_array($header) || ($header['alg'] ?? null) !== 'HS256') {
            return false;
        }
        
        $signature = self::base64UrlDecode($base64UrlSignature);
        if ($signature === null) {
            return false;
        }
        $expectedSignature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
        
        if (!hash_equals($expectedSignature, $signature)) {
            return false;
        }
        
        $payloadJson = self::base64UrlDecode($base64UrlPayload);
        if ($payloadJson === null) {
            return false;
        }
        $payload = json_decode($payloadJson, true);
        if (!is_array($payload)) {
            return false;
        }
        
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false;
        }
        
        return $payload;
    }
}
