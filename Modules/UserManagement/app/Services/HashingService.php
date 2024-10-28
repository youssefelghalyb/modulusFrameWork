<?php

namespace Modules\UserManagement\Services;

use Exception;
use Modules\UserManagement\Models\HashingAlgorithm;
use RuntimeException;

class HashingService
{
    private const PEPPER = 'your-secure-pepper-key'; // Store this in .env

    public function hash(string $password, ?string $salt = null): array
    {
        $salt = $salt ?? $this->generateSalt();
        $algorithm = $this->getDefaultAlgorithm();
        
        $pepperedPassword = $this->pepperPassword($password);
        $saltedPassword = $this->saltPassword($pepperedPassword, $salt);
        
        $hash = match ($algorithm->algorithm_name) {
            'Argon2id' => $this->hashWithArgon2id($saltedPassword, $algorithm->parameters),
            'Bcrypt' => $this->hashWithBcrypt($saltedPassword, $algorithm->parameters),
            default => throw new RuntimeException("Unsupported hashing algorithm")
        };

        return [
            'hash' => $hash,
            'salt' => $salt,
            'algorithm_id' => $algorithm->hash_algorithm_id
        ];
    }

    public function verify(
        string $password,
        string $storedHash,
        string $salt,
        HashingAlgorithm $algorithm
    ): bool {
        $pepperedPassword = $this->pepperPassword($password);
        $saltedPassword = $this->saltPassword($pepperedPassword, $salt);

        return match ($algorithm->algorithm_name) {
            'Argon2id' => $this->verifyArgon2id($saltedPassword, $storedHash),
            'Bcrypt' => $this->verifyBcrypt($saltedPassword, $storedHash),
            default => throw new RuntimeException("Unsupported hashing algorithm")
        };
    }

    private function hashWithArgon2id(string $password, array $parameters): string
    {
        $options = [
            'memory_cost' => $parameters['memory'] ?? PASSWORD_ARGON2_DEFAULT_MEMORY_COST,
            'time_cost' => $parameters['time'] ?? PASSWORD_ARGON2_DEFAULT_TIME_COST,
            'threads' => $parameters['threads'] ?? PASSWORD_ARGON2_DEFAULT_THREADS
        ];

        return password_hash($password, PASSWORD_ARGON2ID, $options);
    }

    private function hashWithBcrypt(string $password, array $parameters): string
    {
        return password_hash(
            $password,
            PASSWORD_BCRYPT,
            ['cost' => $parameters['rounds'] ?? 12]
        );
    }

    private function verifyArgon2id(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    private function verifyBcrypt(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    private function generateSalt(int $length = 32): string
    {
        try {
            return bin2hex(random_bytes($length / 2));
        } catch (Exception $e) {
            throw new RuntimeException("Could not generate secure salt");
        }
    }

    private function pepperPassword(string $password): string
    {
        return hash_hmac('sha256', $password, self::PEPPER);
    }

    private function saltPassword(string $pepperedPassword, string $salt): string
    {
        return $salt . $pepperedPassword;
    }

    private function getDefaultAlgorithm(): HashingAlgorithm
    {
        return HashingAlgorithm::where('algorithm_name', 'Argon2id')
            ->where('is_active', true)
            ->firstOrFail();
    }
}