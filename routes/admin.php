<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes (Disabled on Public Deployment)
|--------------------------------------------------------------------------
| Toàn bộ chức năng quản trị Admin đã được chuyển về máy chủ nội bộ Localhost.
| Không cho phép truy cập từ môi trường công cộng.
*/

Route::any('/admin/{any?}', function () {
    abort(404);
})->where('any', '.*');
