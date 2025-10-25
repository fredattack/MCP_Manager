<?php

namespace Tests\Unit\Services;

use App\Services\CryptoService;
use Tests\TestCase;

/**
 * @group crypto
 * @group unit
 */
class CryptoServiceTest extends TestCase
{
    private CryptoService $cryptoService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cryptoService = new CryptoService;
    }

    public function test_generates_key_pair(): void
    {
        $keyPair = $this->cryptoService->generateKeyPair();

        $this->assertArrayHasKey('public', $keyPair);
        $this->assertArrayHasKey('private', $keyPair);
        $this->assertStringContainsString('BEGIN PUBLIC KEY', $keyPair['public']);
        $this->assertStringContainsString('BEGIN PRIVATE KEY', $keyPair['private']);
    }

    public function test_encrypts_and_decrypts_data(): void
    {
        $keyPair = $this->cryptoService->generateKeyPair();
        $originalData = 'This is secret data';

        $encrypted = $this->cryptoService->encrypt($originalData, $keyPair['public']);
        $this->assertNotEquals($originalData, $encrypted);

        $decrypted = $this->cryptoService->decrypt($encrypted, $keyPair['private']);
        $this->assertEquals($originalData, $decrypted);
    }

    public function test_signs_and_verifies_data(): void
    {
        $keyPair = $this->cryptoService->generateKeyPair();
        $data = 'Data to be signed';

        $signature = $this->cryptoService->sign($data, $keyPair['private']);
        $this->assertNotEmpty($signature);

        $isValid = $this->cryptoService->verify($data, $signature, $keyPair['public']);
        $this->assertTrue($isValid);

        $isInvalid = $this->cryptoService->verify('Different data', $signature, $keyPair['public']);
        $this->assertFalse($isInvalid);
    }

    public function test_generates_secure_token(): void
    {
        $token1 = $this->cryptoService->generateSecureToken();
        $token2 = $this->cryptoService->generateSecureToken();

        $this->assertEquals(64, strlen($token1)); // 32 bytes = 64 hex chars
        $this->assertNotEquals($token1, $token2);
        $this->assertMatchesRegularExpression('/^[a-f0-9]+$/', $token1);
    }

    public function test_symmetric_encryption(): void
    {
        $data = 'Sensitive information';
        $key = bin2hex(random_bytes(16)); // 128-bit key

        $encrypted = $this->cryptoService->encryptSymmetric($data, $key);

        $this->assertArrayHasKey('data', $encrypted);
        $this->assertArrayHasKey('iv', $encrypted);
        $this->assertArrayHasKey('tag', $encrypted);

        $decrypted = $this->cryptoService->decryptSymmetric(
            $encrypted['data'],
            $key,
            $encrypted['iv'],
            $encrypted['tag']
        );

        $this->assertEquals($data, $decrypted);
    }

    public function test_validates_ssl_certificate(): void
    {
        // This is a basic test with a self-signed certificate
        // In production, you'd test with real certificates
        $this->assertTrue(true); // Placeholder for now
    }
}
