<?php

namespace Zerp\Jitsi\Services;

use Firebase\JWT\JWT;
use Illuminate\Support\Str;

class JitsiService
{
    /**
     * Generate a Jitsi meeting: a room name, the meeting URL (public meet.jit.si
     * by default, or a configured self-hosted/JaaS domain), and — if JaaS
     * credentials are configured — a signed JWT appended to the URL.
     */
    public function createMeeting(array $data): array
    {
        $domain = company_setting('jitsi_domain') ?: 'meet.jit.si';
        $roomName = $this->generateRoomName($data['title']);

        $url = "https://{$domain}/{$roomName}";

        $appId = company_setting('jitsi_app_id');
        $jwtSecret = company_setting('jitsi_jwt_secret');

        if ($appId && $jwtSecret) {
            $token = $this->generateToken($roomName, $appId, $jwtSecret, $data);
            $url .= '?jwt=' . $token;
        }

        return [
            'id' => $roomName,
            'start_url' => $url,
            'join_url' => $url,
        ];
    }

    public function updateMeeting(string $roomName, array $data): array
    {
        // Jitsi rooms are just named URLs; there's nothing server-side to
        // update (unlike Zoom/Google, there's no meeting object to patch).
        // Re-derive the same URL/token so callers can refresh stored links.
        $domain = company_setting('jitsi_domain') ?: 'meet.jit.si';
        $url = "https://{$domain}/{$roomName}";

        $appId = company_setting('jitsi_app_id');
        $jwtSecret = company_setting('jitsi_jwt_secret');

        if ($appId && $jwtSecret) {
            $token = $this->generateToken($roomName, $appId, $jwtSecret, $data);
            $url .= '?jwt=' . $token;
        }

        return [
            'start_url' => $url,
            'join_url' => $url,
        ];
    }

    public function deleteMeeting(string $roomName): bool
    {
        // No server-side resource to delete for a Jitsi room.
        return true;
    }

    public function getMeeting(string $roomName): array
    {
        $domain = company_setting('jitsi_domain') ?: 'meet.jit.si';
        $url = "https://{$domain}/{$roomName}";

        return [
            'id' => $roomName,
            'start_url' => $url,
            'join_url' => $url,
        ];
    }

    public function getStartUrl(string $roomName): string
    {
        return $this->getMeeting($roomName)['start_url'] ?? '';
    }

    private function generateRoomName(string $title): string
    {
        $slug = Str::slug($title);
        $suffix = Str::lower(Str::random(8));

        return $slug ? "{$slug}-{$suffix}" : $suffix;
    }

    private function generateToken(string $roomName, string $appId, string $jwtSecret, array $data): string
    {
        $now = time();

        $payload = [
            'iss' => $appId,
            'aud' => 'jitsi',
            'sub' => company_setting('jitsi_domain') ?: 'meet.jit.si',
            'room' => $roomName,
            'iat' => $now,
            'exp' => $now + (((int) ($data['duration'] ?? 60)) * 60) + 3600,
            'context' => [
                'user' => [
                    'name' => auth()->user()->name ?? 'Zerp User',
                ],
            ],
        ];

        return JWT::encode($payload, $jwtSecret, 'HS256');
    }
}
