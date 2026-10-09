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

class GoogleController extends Controller
{
    /**
     * Configure Socialite runtime credentials from DB or env
     */
    protected function configureDriver()
    {
        $clientId = config_get('login_social.google.client_id') ?: config('services.google.client_id');
        $clientSecret = config_get('login_social.google.client_secret') ?: config('services.google.client_secret');
        $redirect = config_get('login_social.google.redirect') ?: config('services.google.redirect');

        if (empty($redirect) || (app()->environment('production') && str_contains($redirect, 'localhost'))) {
            $redirect = url('/auth/google/callback');
        }

        config([
            'services.google.client_id' => $clientId,
            'services.google.client_secret' => $clientSecret,
            'services.google.redirect' => $redirect,
        ]);

        return !empty($clientId) && !empty($clientSecret);
    }

    /**
     * Redirect the user to the Google authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToGoogle()
    {
        if (!config_get('login_social.google.active', false)) {
            return redirect()->route('login')->with('error', 'Tính năng đăng nhập bằng Google hiện đang tắt.');
        }

        if (!$this->configureDriver()) {
            return redirect()->route('login')->with('error', 'Chưa cấu hình Google Client ID / Secret trong hệ thống.');
        }

        try {
            return Socialite::driver('google')->redirect();
        } catch (\Throwable $e) {
            Log::error('Google redirect error: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Không thể chuyển hướng đến Google: ' . $e->getMessage());
        }
    }

    /**
     * Obtain the user information from Google.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleGoogleCallback()
    {
        if (request()->has('error')) {
            $error = request()->get('error_description', request()->get('error'));
            return redirect()->route('login')->with('error', 'Bạn đã hủy kết nối Google: ' . $error);
        }

        $this->configureDriver();

        try {
            try {
                $googleUser = Socialite::driver('google')->user();
            } catch (InvalidStateException $e) {
                $googleUser = Socialite::driver('google')->stateless()->user();
            }

            if (!$googleUser || empty($googleUser->getId())) {
                return redirect()->route('login')->with('error', 'Không thể lấy thông tin xác thực từ Google.');
            }

            // 1. Tìm user đã liên kết google_id
            $user = User::where('google_id', $googleUser->getId())->first();

            // 2. Nếu chưa có google_id, tìm theo email
            $email = $googleUser->getEmail();
            if (!$user && !empty($email)) {
                $user = User::where('email', $email)->first();
                if ($user) {
                    $user->google_id = $googleUser->getId();
                    if (empty($user->avatar) && !empty($googleUser->getAvatar())) {
                        $user->avatar = $googleUser->getAvatar();
                    }
                    $user->save();
                }
            }

            // 3. Nếu chưa tồn tại, tạo tài khoản mới
            if (!$user) {
                if (empty($email)) {
                    $email = 'google_' . $googleUser->getId() . '@shopgame.local';
                }

                $rawName = $googleUser->getName() ?: explode('@', $email)[0];
                $baseName = Str::slug($rawName, '_');
                if (empty($baseName)) {
                    $baseName = 'user_' . Str::random(6);
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
                    'google_id' => $googleUser->getId(),
                    'password' => Hash::make(Str::random(24)),
                    'email_verified_at' => now(),
                    'avatar' => $googleUser->getAvatar(),
                    'balance' => 0,
                    'role' => 'member',
                ]);

                Auth::login($user, true);
                return redirect()->intended(route('home'))
                    ->with('success', 'Bạn đã đăng ký và đăng nhập thành công với Google!');
            }

            // Kiểm tra bị khóa
            if ($user->banned) {
                return redirect()->route('login')->with('error', 'Tài khoản của bạn đã bị khóa.');
            }

            Auth::login($user, true);
            return redirect()->intended(route('home'))->with('success', 'Đăng nhập Google thành công!');

        } catch (\Throwable $e) {
            Log::error('Google login error: ' . $e->getMessage());
            return redirect()->route('login')
                ->with('error', 'Đăng nhập Google thất bại: ' . $e->getMessage());
        }
    }
}
