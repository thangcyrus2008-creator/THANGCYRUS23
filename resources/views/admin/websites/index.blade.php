@extends('layouts.admin.app')
@section('title', 'Danh sách mẫu Website (TheGioiDev)')
@section('content')
<div>
    <div class="page-header">
        <div class="page-block mb-3">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h2 class="mb-0">MẪU WEBSITE (THEGIOIDEV)</h2>
                            <p class="text-muted">Kho mẫu website tự động kết nối trực tiếp qua API TheGioiDev</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.websites.settings') }}" class="btn btn-outline-primary">
                                <i class="ti ti-settings me-1"></i> Cấu hình & Kết nối API
                            </a>
                            <a href="{{ route('admin.websites.orders') }}" class="btn btn-primary">
                                <i class="ti ti-shopping-cart me-1"></i> Quản lý đơn hàng
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(!$apiConnected)
    <div class="alert alert-warning border-0 shadow-sm mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <i class="ti ti-alert-triangle fs-4 me-2 align-middle"></i>
                <strong>Chưa cấu hình API Token TheGioiDev:</strong> Vui lòng nhập API Token để tải toàn bộ danh sách mẫu website và cho phép khách mua dịch vụ.
            </div>
            <a href="{{ route('admin.websites.settings') }}" class="btn btn-warning btn-sm fw-bold">
                Cấu hình ngay
            </a>
        </div>
    </div>
    @elseif(!empty($apiError))
    <div class="alert alert-danger border-0 shadow-sm mb-4">
        <i class="ti ti-alert-circle me-1"></i> <strong>Lỗi kết nối API:</strong> {{ $apiError }}
    </div>
    @endif

    <div class="card overflow-hidden shadow-sm border border-dashed mb-4">
        <div class="card-body p-3 bg-light-subtle d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <span>Tỷ lệ lợi nhuận đang áp dụng:</span>
                <strong class="text-success fs-5 ms-1">+{{ $markup }}%</strong>
            </div>
            <form action="{{ route('admin.websites.index') }}" method="GET" class="d-flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Tìm tên mẫu...">
                <button type="submit" class="btn btn-sm btn-primary">Tìm</button>
            </form>
        </div>
    </div>

    <div class="row g-4">
        @forelse($templates as $tmpl)
        @php
            $imgUrl = $tmpl['image'] ?? ($tmpl['thumbnail'] ?? 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=600&auto=format&fit=crop');
            if (!str_starts_with($imgUrl, 'http')) {
                $imgUrl = 'https://thegioidev.com' . $imgUrl;
            }
            $salePrice = $tmpl['displayPrice'] ?? 0;
            $origPrice = $tmpl['calculatedPricing'][0]['originalPricePerMonth'] ?? round($salePrice / max(1, (1 + $markup / 100)));
        @endphp
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card h-100 shadow-sm border overflow-hidden">
                <div style="height: 180px; overflow: hidden; background: #eee;">
                    <img src="{{ $imgUrl }}" class="card-img-top w-100 h-100" style="object-fit: cover;" alt="{{ $tmpl['serviceName'] ?? 'Template' }}">
                </div>
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title fw-bold mb-2 text-truncate" title="{{ $tmpl['serviceName'] ?? '' }}">
                        {{ $tmpl['serviceName'] ?? 'Mẫu Website' }}
                    </h5>
                    <div class="mb-3">
                        <small class="text-muted d-block">Giá gốc: <del>{{ number_format($origPrice) }}đ/tháng</del></small>
                        <span class="text-danger fw-bold fs-5">{{ number_format($salePrice) }}đ</span><small class="text-muted">/tháng (+{{ $markup }}%)</small>
                    </div>

                    <div class="mt-auto d-flex gap-2 pt-2 border-top">
                        @if(!empty($tmpl['demoUrl']))
                        <a href="{{ $tmpl['demoUrl'] }}" target="_blank" class="btn btn-sm btn-outline-secondary w-50">
                            <i class="ti ti-eye me-1"></i> Demo
                        </a>
                        @endif
                        @if(!empty($tmpl['adminDemoUrl']))
                        <a href="{{ $tmpl['adminDemoUrl'] }}" target="_blank" class="btn btn-sm btn-outline-info w-50">
                            <i class="ti ti-lock me-1"></i> Admin
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5 text-muted">
            <i class="ti ti-inbox fs-1 d-block mb-2"></i>
            Chưa có dữ liệu mẫu website hoặc chưa kết nối API thành công.
        </div>
        @endforelse
    </div>
</div>
@endsection
