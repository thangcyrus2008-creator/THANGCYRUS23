<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Tự động xử lý lỗi 419 Page Expired mà không bao giờ để người dùng thấy trang chết
        $this->renderable(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if ($request->is('logout') || $request->is('*/logout')) {
                try { \Illuminate\Support\Facades\Auth::logout(); } catch (\Throwable $t) {}
                return redirect('/');
            }

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.',
                    'code' => 419,
                ], 419);
            }

            return redirect()->back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('error', 'Phiên làm việc đã hết hạn do trang để quá lâu. Vui lòng thử lại!');
        });

        $this->renderable(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            if ($e->getStatusCode() === 419) {
                if ($request->is('logout') || $request->is('*/logout')) {
                    try { \Illuminate\Support\Facades\Auth::logout(); } catch (\Throwable $t) {}
                    return redirect('/');
                }

                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.',
                        'code' => 419,
                    ], 419);
                }

                return redirect()->back()
                    ->withInput($request->except('password', 'password_confirmation'))
                    ->with('error', 'Phiên làm việc đã hết hạn do trang để quá lâu. Vui lòng thử lại!');
            }
        });
    }
}
