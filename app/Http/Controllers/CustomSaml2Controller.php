<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\LoginDetail;
use Aacotroneo\Saml2\Saml2Auth;

class CustomSaml2Controller extends Controller
{
    public function acs(Request $request)
    {
        $saml2Auth = app('aacotroneo.saml2')->loadOneLoginAuthFromIpdConfig('test'); // 'test' = your IDP name
        $saml2Auth->acs();

        $samlUser = $saml2Auth->getSaml2User();
        $attributes = $samlUser->getAttributes();

        Log::info('🔐 SAML ACS Called');
        Log::info('🎯 Attributes: ', $attributes);

        $email = $attributes['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress'][0] ?? null;
        $name = $attributes['http://schemas.microsoft.com/identity/claims/displayname'][0] 
            ?? $attributes['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/name'][0] 
            ?? 'SSO User';

        if (!$email) {
            Log::error("❌ No email returned in SAML response.");
            return redirect('/login')->with('error', 'Email not found in SAML response.');
        }

        $user = User::where('email', $email)->first();

        /*if (!$user) {
            // Optionally create user
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => bcrypt(str_random(16)), // random password
            ]);
            Log::info("🆕 New user auto-created for $email");
        }*/

        Auth::login($user);
        $this->storeLoginDetails($user);

        Log::info("✅ User logged in via SAML: $email");

        return redirect(config('saml2_settings.loginRoute', '/dashboard'));
    }

    private function storeLoginDetails($user)
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        $whichbrowser = new \WhichBrowser\Parser($_SERVER['HTTP_USER_AGENT']);
        if ($whichbrowser->device->type === 'bot') return;

        $details = [
            'ip' => $ip,
            'browser_name' => $whichbrowser->browser->name ?? null,
            'os_name' => $whichbrowser->os->name ?? null,
            'device_type' => $this->getDeviceType($_SERVER['HTTP_USER_AGENT']),
            'browser_language' => mb_substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '', 0, 2),
            'referrer' => $_SERVER['HTTP_REFERER'] ?? null,
        ];

        $log = new LoginDetail();
        $log->user_id = $user->id;
        $log->ip = $ip;
        $log->date = now();
        $log->Details = json_encode($details);
        $log->created_by = $user->creatorId();
        $log->save();
    }

    private function getDeviceType($ua)
    {
        if (preg_match('/mobile/i', $ua)) return 'Mobile';
        if (preg_match('/tablet/i', $ua)) return 'Tablet';
        return 'Desktop';
    }
}
