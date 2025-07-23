<?php

namespace App\Listeners;

use Aacotroneo\Saml2\Events\Saml2LoginEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\LoginDetail;

class Saml2LoginEventListener
{
    public function handle(Saml2LoginEvent $event)
    {
        $samlUser = $event->getSaml2User();
        $attributes = $samlUser->getAttributes();

        // Extract the email and name from SAML attributes
        $email = $attributes['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress'][0] ?? null;
        $name = $attributes['http://schemas.microsoft.com/identity/claims/displayname'][0] 
            ?? $attributes['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/name'][0] 
            ?? 'SSO User';

        Log::info("🔐 SAML login attempt with email: " . ($email ?? 'NULL'));

        if (!$email) {
            Log::error("SAML login failed: No email address returned.");
            return;
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            Auth::login($user);
            $this->storeLoginDetails($user);
            Log::info("✅ SAML login successful for: $email");
        } else {
            Log::warning("❌ SAML login attempt failed for unregistered email: $email");
            // Optionally: redirect or show message — not possible in listener, log only
        }
    }

    private function storeLoginDetails($user)
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $query = $this->getUserLocationDetails($ip);

        $whichbrowser = new \WhichBrowser\Parser($_SERVER['HTTP_USER_AGENT']);
        if ($whichbrowser->device->type === 'bot') {
            return;
        }

        $query['browser_name'] = $whichbrowser->browser->name ?? null;
        $query['os_name'] = $whichbrowser->os->name ?? null;
        $query['browser_language'] = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? mb_substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : null;
        $query['device_type'] = $this->getDeviceType($_SERVER['HTTP_USER_AGENT']);
        $query['referrer_host'] = isset($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) : null;
        $query['referrer_path'] = isset($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH) : null;

        if (isset($query['timezone'])) {
            date_default_timezone_set($query['timezone']);
        }

        $loginDetail = new LoginDetail();
        $loginDetail->user_id = $user->id;
        $loginDetail->ip = $ip;
        $loginDetail->date = now();
        $loginDetail->Details = json_encode($query);
        $loginDetail->created_by = $user->creatorId();
        $loginDetail->save();
    }

    private function getUserLocationDetails($ip)
    {
        $query = @unserialize(file_get_contents("http://ip-api.com/php/{$ip}"));
        return $query ?: [];
    }

    private function getDeviceType($userAgent)
    {
        if (preg_match('/mobile/i', $userAgent)) {
            return 'Mobile';
        } elseif (preg_match('/tablet/i', $userAgent)) {
            return 'Tablet';
        } else {
            return 'Desktop';
        }
    }
}
