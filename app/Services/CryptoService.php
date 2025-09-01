<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class CryptoService
{
    /**
     * Generate RSA key pair
     */
    public function generateKeyPair(int $bits = 2048): array
    {
        $config = [
            'private_key_bits' => $bits,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        // Generate private key
        $privateKey = openssl_pkey_new($config);
        if (!$privateKey) {
            throw new \RuntimeException('Failed to generate private key');
        }

        // Export private key
        openssl_pkey_export($privateKey, $privateKeyPem);

        // Get public key
        $publicKeyDetails = openssl_pkey_get_details($privateKey);
        $publicKeyPem = $publicKeyDetails['key'];

        return [
            'private' => $privateKeyPem,
            'public' => $publicKeyPem,
        ];
    }

    /**
     * Encrypt data with public key
     */
    public function encrypt(string $data, string $publicKey): string
    {
        $publicKeyResource = openssl_pkey_get_public($publicKey);
        if (!$publicKeyResource) {
            throw new \RuntimeException('Invalid public key');
        }

        // For large data, we need to chunk it
        $maxLength = $this->getMaxEncryptionLength($publicKeyResource);
        $encrypted = '';
        
        // Split data into chunks
        $chunks = str_split($data, $maxLength);
        
        foreach ($chunks as $chunk) {
            $encryptedChunk = '';
            if (!openssl_public_encrypt($chunk, $encryptedChunk, $publicKeyResource)) {
                throw new \RuntimeException('Encryption failed');
            }
            $encrypted .= base64_encode($encryptedChunk) . '|';
        }

        return rtrim($encrypted, '|');
    }

    /**
     * Decrypt data with private key
     */
    public function decrypt(string $encryptedData, string $privateKey): string
    {
        $privateKeyResource = openssl_pkey_get_private($privateKey);
        if (!$privateKeyResource) {
            throw new \RuntimeException('Invalid private key');
        }

        $decrypted = '';
        $chunks = explode('|', $encryptedData);
        
        foreach ($chunks as $chunk) {
            if (empty($chunk)) {
                continue;
            }
            
            $encryptedChunk = base64_decode($chunk);
            $decryptedChunk = '';
            
            if (!openssl_private_decrypt($encryptedChunk, $decryptedChunk, $privateKeyResource)) {
                throw new \RuntimeException('Decryption failed');
            }
            
            $decrypted .= $decryptedChunk;
        }

        return $decrypted;
    }

    /**
     * Sign data with private key
     */
    public function sign(string $data, string $privateKey): string
    {
        $privateKeyResource = openssl_pkey_get_private($privateKey);
        if (!$privateKeyResource) {
            throw new \RuntimeException('Invalid private key');
        }

        $signature = '';
        if (!openssl_sign($data, $signature, $privateKeyResource, OPENSSL_ALGO_SHA256)) {
            throw new \RuntimeException('Signing failed');
        }

        return base64_encode($signature);
    }

    /**
     * Verify signature with public key
     */
    public function verify(string $data, string $signature, string $publicKey): bool
    {
        $publicKeyResource = openssl_pkey_get_public($publicKey);
        if (!$publicKeyResource) {
            throw new \RuntimeException('Invalid public key');
        }

        $result = openssl_verify(
            $data,
            base64_decode($signature),
            $publicKeyResource,
            OPENSSL_ALGO_SHA256
        );

        return $result === 1;
    }

    /**
     * Generate secure random token
     */
    public function generateSecureToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Hash password using bcrypt
     */
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * Verify password hash
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Encrypt data using AES-256-GCM
     */
    public function encryptSymmetric(string $data, string $key): array
    {
        $cipher = 'aes-256-gcm';
        $ivLength = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivLength);
        $tag = '';

        $encrypted = openssl_encrypt(
            $data,
            $cipher,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($encrypted === false) {
            throw new \RuntimeException('Symmetric encryption failed');
        }

        return [
            'data' => base64_encode($encrypted),
            'iv' => base64_encode($iv),
            'tag' => base64_encode($tag),
        ];
    }

    /**
     * Decrypt data using AES-256-GCM
     */
    public function decryptSymmetric(string $encryptedData, string $key, string $iv, string $tag): string
    {
        $cipher = 'aes-256-gcm';

        $decrypted = openssl_decrypt(
            base64_decode($encryptedData),
            $cipher,
            $key,
            OPENSSL_RAW_DATA,
            base64_decode($iv),
            base64_decode($tag)
        );

        if ($decrypted === false) {
            throw new \RuntimeException('Symmetric decryption failed');
        }

        return $decrypted;
    }

    /**
     * Get maximum encryption length for RSA key
     */
    private function getMaxEncryptionLength($publicKey): int
    {
        $keyDetails = openssl_pkey_get_details($publicKey);
        // RSA encryption max length = key_size - padding (11 bytes for PKCS1)
        return ($keyDetails['bits'] / 8) - 11;
    }

    /**
     * Validate SSL certificate
     */
    public function validateSSLCertificate(string $certificate, string $hostname = null): bool
    {
        try {
            $certInfo = openssl_x509_parse($certificate);
            
            if (!$certInfo) {
                return false;
            }

            // Check if certificate is expired
            $validFrom = $certInfo['validFrom_time_t'];
            $validTo = $certInfo['validTo_time_t'];
            $currentTime = time();

            if ($currentTime < $validFrom || $currentTime > $validTo) {
                Log::warning('SSL certificate is expired or not yet valid', [
                    'valid_from' => date('Y-m-d H:i:s', $validFrom),
                    'valid_to' => date('Y-m-d H:i:s', $validTo),
                ]);
                return false;
            }

            // Verify hostname if provided
            if ($hostname) {
                $cn = $certInfo['subject']['CN'] ?? '';
                $altNames = $certInfo['extensions']['subjectAltName'] ?? '';

                if (!$this->verifyHostname($hostname, $cn, $altNames)) {
                    Log::warning('SSL certificate hostname mismatch', [
                        'hostname' => $hostname,
                        'cn' => $cn,
                        'alt_names' => $altNames,
                    ]);
                    return false;
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error('SSL certificate validation failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Verify hostname against certificate
     */
    private function verifyHostname(string $hostname, string $cn, string $altNames): bool
    {
        // Check common name
        if ($this->matchHostname($hostname, $cn)) {
            return true;
        }

        // Check alternative names
        if (!empty($altNames)) {
            $names = explode(',', $altNames);
            foreach ($names as $name) {
                if (strpos($name, 'DNS:') === 0) {
                    $dnsName = trim(substr($name, 4));
                    if ($this->matchHostname($hostname, $dnsName)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Match hostname with wildcard support
     */
    private function matchHostname(string $hostname, string $pattern): bool
    {
        if ($hostname === $pattern) {
            return true;
        }

        // Handle wildcard certificates
        if (strpos($pattern, '*.') === 0) {
            $wildcardDomain = substr($pattern, 2);
            $hostnameParts = explode('.', $hostname);
            
            if (count($hostnameParts) > 1) {
                array_shift($hostnameParts);
                $hostnameWithoutSubdomain = implode('.', $hostnameParts);
                
                return $hostnameWithoutSubdomain === $wildcardDomain;
            }
        }

        return false;
    }
}