<?php

declare(strict_types=1);

namespace Spectreacle\Domain\Auth\Services;

use Spectreacle\Domain\User\Entities\User;

class TotpService
{
    private const WINDOW = 1; // Fenêtre de tolérance (30 secondes avant/après)
    private const INTERVAL = 30; // Intervalle de temps en secondes
    private const DIGITS = 6; // Nombre de chiffres du code TOTP

    public function generateSecret(): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        
        for ($i = 0; $i < 32; $i++) {
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

        $label = urlencode($issuer . ':' . $user->getUsername());
        $params = http_build_query([
            'secret' => $secret,
            'issuer' => $issuer,
            'algorithm' => 'SHA1',
            'digits' => self::DIGITS,
            'period' => self::INTERVAL
        ]);

        return "otpauth://totp/{$label}?{$params}";
    }

    public function verifyCode(string $secret, string $code): bool
    {
        $timestamp = intval(time() / self::INTERVAL);
        
        // Vérifier le code dans une fenêtre de tolérance
        for ($i = -self::WINDOW; $i <= self::WINDOW; $i++) {
            if ($this->generateCodeForTimestamp($secret, $timestamp + $i) === $code) {
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
        $base32chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $base32charsFlipped = array_flip(str_split($base32chars));
        
        $paddingCharCount = substr_count($secret, '=');
        $allowedValues = [6, 4, 3, 1, 0];
        
        if (!in_array($paddingCharCount, $allowedValues)) {
            return '';
        }
        
        for ($i = 0; $i < 4; $i++) {
            if ($paddingCharCount === $allowedValues[$i] &&
                substr($secret, -($allowedValues[$i])) !== str_repeat('=', $allowedValues[$i])) {
                return '';
            }
        }
        
        $secret = str_replace('=', '', $secret);
        $secret = str_split($secret);
        $binaryString = '';
        
        for ($i = 0; $i < count($secret); $i += 8) {
            $x = '';
            if (!in_array($secret[$i], $base32charsFlipped)) {
                return '';
            }
            
            for ($j = 0; $j < 8; $j++) {
                $x .= str_pad(base_convert((string)@$base32charsFlipped[@$secret[$i + $j]], 10, 2), 5, '0', STR_PAD_LEFT);
            }
            
            $eightBits = str_split($x, 8);
            for ($z = 0; $z < count($eightBits); $z++) {
                $binaryString .= (($y = chr(base_convert($eightBits[$z], 2, 10))) || ord($y) === 48) ? $y : '';
            }
        }
        
        return $binaryString;
    }
}