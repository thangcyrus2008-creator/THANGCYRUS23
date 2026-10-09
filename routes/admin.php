<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes (Disabled on Public Deployment)
|--------------------------------------------------------------------------
| Toàn bộ chức năng quản trị Admin đã được chuyển về máy chủ nội bộ Localhost.
| Không cho phép truy cập từ môi trường công cộng.
*/

Route::get('/admin', function () {
    return redirect('http://127.0.0.1:8000/admin');
})->name('admin.index');

Route::get('/admin/websites/settings', function () {
    return redirect('http://127.0.0.1:8000/admin/websites/settings');
})->name('admin.websites.settings');

Route::any('/admin/{any?}', function () {
    abort(404);
})->where('any', '.*');
