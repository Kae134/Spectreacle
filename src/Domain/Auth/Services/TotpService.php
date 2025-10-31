<?php

declare(strict_types=1);

namespace Spectreacle\Domain\Auth\Services;

use Spectreacle\Domain\User\Entities\User;

class TotpService
{
    private const WINDOW = 2; // Fenêtre de tolérance (60 secondes avant/après)
    private const INTERVAL = 30; // Intervalle de temps en secondes
    private const DIGITS = 6; // Nombre de chiffres du code TOTP

    public function generateSecret(): string
    {
        // Base32 alphabet (RFC 4648)
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        
        // Générer un secret de 16 caractères (plus compatible)
        for ($i = 0; $i < 16; $i++) {
            $secret .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        return $secret;
    }

    public function generateQrCodeUrl(User $user, string $issuer = 'Spectreacle'): string
    {
        $secret = $user->getTotpSecret();
        if (!$secret) {
            throw new \InvalidArgumentException('User has no TOTP secret');
        }

        // Supprimer le padding pour compatibilité Google Authenticator
        $cleanSecret = rtrim($secret, '=');

        $label = urlencode($issuer . ':' . $user->getUsername());
        $params = http_build_query([
            'secret' => $cleanSecret,
            'issuer' => $issuer,
            'algorithm' => 'SHA1',
            'digits' => self::DIGITS,
            'period' => self::INTERVAL
        ]);

        return "otpauth://totp/{$label}?{$params}";
    }

    public function verifyCode(string $secret, string $code): bool
    {
        // Nettoyer le secret (supprimer espaces, padding et convertir en majuscules)
        $cleanSecret = strtoupper(str_replace([' ', '='], '', $secret));
        
        // Nettoyer le code (supprimer les espaces et caractères non-numériques)
        $cleanCode = preg_replace('/[^0-9]/', '', $code);
        
        if (strlen($cleanCode) !== self::DIGITS) {
            return false;
        }
        
        $timestamp = intval(time() / self::INTERVAL);
        
        // Vérifier le code dans une fenêtre de tolérance
        for ($i = -self::WINDOW; $i <= self::WINDOW; $i++) {
            if ($this->generateCodeForTimestamp($cleanSecret, $timestamp + $i) === $cleanCode) {
                return true;
            }
        }
        
        return false;
    }

    public function generateCurrentCode(string $secret): string
    {
        $timestamp = intval(time() / self::INTERVAL);
        return $this->generateCodeForTimestamp($secret, $timestamp);
    }

    private function generateCodeForTimestamp(string $secret, int $timestamp): string
    {
        // Décoder le secret base32
        $secretBinary = $this->base32Decode($secret);
        
        // Convertir le timestamp en binaire (8 octets, big-endian)
        $timestampBinary = pack('N*', 0, $timestamp);
        
        // Générer le HMAC-SHA1
        $hmac = hash_hmac('sha1', $timestampBinary, $secretBinary, true);
        
        // Calcul dynamique du code TOTP
        $offset = ord($hmac[strlen($hmac) - 1]) & 0x0F;
        $code = (
            ((ord($hmac[$offset]) & 0x7F) << 24) |
            ((ord($hmac[$offset + 1]) & 0xFF) << 16) |
            ((ord($hmac[$offset + 2]) & 0xFF) << 8) |
            (ord($hmac[$offset + 3]) & 0xFF)
        ) % (10 ** self::DIGITS);
        
        return str_pad((string)$code, self::DIGITS, '0', STR_PAD_LEFT);
    }

    private function base32Decode(string $secret): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = strtoupper(str_replace('=', '', $secret));
        
        // Ajouter le padding manquant si nécessaire (pour Google Authenticator)
        $padLength = 8 - (strlen($secret) % 8);
        if ($padLength !== 8) {
            $secret .= str_repeat('=', $padLength);
        }
        
        $binaryString = '';
        for ($i = 0; $i < strlen($secret); $i++) {
            $char = $secret[$i];
            if ($char === '=') {
                break; // Stop au padding
            }
            $pos = strpos($alphabet, $char);
            if ($pos === false) {
                throw new \InvalidArgumentException('Invalid Base32 character: ' . $char);
            }
            $binaryString .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }
        
        $result = '';
        for ($i = 0; $i < strlen($binaryString); $i += 8) {
            $byte = substr($binaryString, $i, 8);
            if (strlen($byte) === 8) {
                $result .= chr(bindec($byte));
            }
        }
        
        return $result;
    }
}