<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Illuminate\Support\Facades\Log;

class FacebookController extends Controller
{
    /**
     * Configure Socialite runtime credentials from DB or env
     */
    protected function configureDriver()
    {
        $clientId = config_get('login_social.facebook.client_id') ?: config('services.facebook.client_id');
        $clientSecret = config_get('login_social.facebook.client_secret') ?: config('services.facebook.client_secret');
        $redirect = config_get('login_social.facebook.redirect') ?: config('services.facebook.redirect');

        if (empty($redirect) || (app()->environment('production') && str_contains($redirect, 'localhost'))) {
            $redirect = url('/auth/facebook/callback');
        }

        config([
            'services.facebook.client_id' => $clientId,
            'services.facebook.client_secret' => $clientSecret,
            'services.facebook.redirect' => $redirect,
        ]);

        return !empty($clientId) && !empty($clientSecret);
    }

    /**
     * Redirect the user to the Facebook authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToFacebook()
    {
        if (!config_get('login_social.facebook.active', false)) {
            return redirect()->route('login')->with('error', 'Tính năng đăng nhập bằng Facebook hiện đang tắt.');
        }

        if (!$this->configureDriver()) {
            return redirect()->route('login')->with('error', 'Chưa cấu hình Facebook App ID / Secret trong hệ thống.');
        }

        try {
            return Socialite::driver('facebook')->redirect();
        } catch (\Throwable $e) {
            Log::error('Facebook redirect error: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Không thể chuyển hướng đến Facebook: ' . $e->getMessage());
        }
    }

    /**
     * Obtain the user information from Facebook.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleFacebookCallback()
    {
        if (request()->has('error')) {
            $error = request()->get('error_description', request()->get('error'));
            return redirect()->route('login')->with('error', 'Bạn đã hủy kết nối Facebook: ' . $error);
        }

        $this->configureDriver();

        try {
            try {
                $facebookUser = Socialite::driver('facebook')->user();
            } catch (InvalidStateException $e) {
                $facebookUser = Socialite::driver('facebook')->stateless()->user();
            }

            if (!$facebookUser || empty($facebookUser->getId())) {
                return redirect()->route('login')->with('error', 'Không thể lấy thông tin xác thực từ Facebook.');
            }

            // 1. Tìm user đã liên kết facebook_id
            $user = User::where('facebook_id', $facebookUser->getId())->first();

            // 2. Nếu chưa có facebook_id, tìm theo email nếu có email
            $email = $facebookUser->getEmail();
            if (!$user && !empty($email)) {
                $user = User::where('email', $email)->first();
                if ($user) {
                    $user->facebook_id = $facebookUser->getId();
                    if (empty($user->avatar) && !empty($facebookUser->getAvatar())) {
                        $user->avatar = $facebookUser->getAvatar();
                    }
                    $user->save();
                }
            }

            // 3. Nếu chưa tồn tại, tạo tài khoản mới
            if (!$user) {
                if (empty($email)) {
                    $email = 'fb_' . $facebookUser->getId() . '@shopgame.local';
                }

                $rawName = $facebookUser->getName() ?: explode('@', $email)[0];
                $baseName = Str::slug($rawName, '_');
                if (empty($baseName)) {
                    $baseName = 'fb_' . $facebookUser->getId();
                }

                $username = $baseName;
                $counter = 1;
                while (User::where('username', $username)->exists()) {
                    $username = $baseName . $counter;
                    $counter++;
                }

                $user = User::create([
                    'username' => $username,
                    'email' => $email,
                    'facebook_id' => $facebookUser->getId(),
                    'password' => Hash::make(Str::random(24)),
                    'email_verified_at' => now(),
                    'avatar' => $facebookUser->getAvatar(),
                    'balance' => 0,
                    'role' => 'member',
                ]);

                Auth::login($user, true);
                return redirect()->intended(route('home'))
                    ->with('success', 'Bạn đã đăng ký và đăng nhập thành công với Facebook!');
            }

            // Kiểm tra bị khóa
            if ($user->banned) {
                return redirect()->route('login')->with('error', 'Tài khoản của bạn đã bị khóa.');
            }

            Auth::login($user, true);
            return redirect()->intended(route('home'))->with('success', 'Đăng nhập Facebook thành công!');

        } catch (\Throwable $e) {
            Log::error('Facebook login error: ' . $e->getMessage());
            return redirect()->route('login')
                ->with('error', 'Đăng nhập Facebook thất bại: ' . $e->getMessage());
        }
    }
}