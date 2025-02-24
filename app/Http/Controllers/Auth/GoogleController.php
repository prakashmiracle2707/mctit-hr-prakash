<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LoginDetail;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\RedirectResponse;

class GoogleController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle(): RedirectResponse
    {
        // return Socialite::driver('google')->redirect();
        return Socialite::driver('google')
        ->with(['prompt' => 'select_account']) // Forces account selection
        ->redirect();
    }

    /**
     * Handle the callback from Google after authentication.
     */
    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // echo "<pre>";print_r($googleUser);exit;
            $user = User::Where('email', $googleUser->email)->first();

            // if (!$user) {
            //     $user = User::create([
            //         'name' => $googleUser->name,
            //         'email' => $googleUser->email,
            //         'google_id' => $googleUser->id,
            //         'password' => bcrypt(uniqid()), // Generate a secure random password
            //     ]);
            // }
            if($user){
                Auth::login($user);
                $this->storeLoginDetails($user);

                return redirect()->intended(route('home'));
            }else{
               return redirect()->route('login')->with('error', 'Unregistered user. Contact admin for authentication.'); 
            }
            
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Google login failed. Please try again.');
        }
    }

    /**
     * Store user login details, including IP, browser, device, and location.
     */
    private function storeLoginDetails($user)
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $query = $this->getUserLocationDetails($ip);

        $whichbrowser = new \WhichBrowser\Parser($_SERVER['HTTP_USER_AGENT']);
        if ($whichbrowser->device->type == 'bot') {
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

        $login_detail = new LoginDetail();
        $login_detail->user_id = $user->id;
        $login_detail->ip = $ip;
        $login_detail->date = now();
        $login_detail->Details = json_encode($query);
        $login_detail->created_by = $user->creatorId();
        $login_detail->save();
    }

    /**
     * Get user location details from an external API.
     */
    private function getUserLocationDetails($ip)
    {
        $query = @unserialize(file_get_contents("http://ip-api.com/php/{$ip}"));
        return $query ?: [];
    }

    /**
     * Detect the user's device type.
     */
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
