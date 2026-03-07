<?php

namespace App\Services;

use App\Models\User;
use App\Events\TokenCreatedEvent;
use Illuminate\Support\Facades\Config;

class CustomTokenService
{
    protected string $secret ;
    protected int $TTL =60; // Token Time To Live in seconds

    public function __construct(){
        $this->secret = Config::get('app.key');
    }
    public function createToken(User $user): string
    {
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT'
        ];
        
        $payload = [
            'user_id' => $user->id,
            'issued_at' => time(),
            'exp' => time() + $this->TTL
        ];

        // Signature
        $base64Header = base64_encode(json_encode($header));
        $base64Payload = base64_encode(json_encode($payload));
        $signature =hash_hmac('sha256', $base64Header.".".$base64Payload, $this->secret);

        $token = $base64Header.".".$base64Payload.".".$signature;

        return $token;
    }

        public function validateToken(string $token): ?User
        {
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return null;
            }
    
            [$base64Header, $base64Payload, $signature] = $parts;
    
            // Verify signature
            $expectedSignature = hash_hmac('sha256', $base64Header.".".$base64Payload, $this->secret);
            if (!hash_equals($expectedSignature, $signature)) {
                return null;
            }
    
            // Decode payload
            $payload = json_decode(base64_decode($base64Payload), true);
            
            // Check expiration
            if ($payload['exp'] < time()) {
                return null;
            }

            $user = User::find($payload['user_id']);
            return $user;
        }





}