@extends('layouts.admin.app')
@section('title', 'Cấu hình API Website & Tên Miền')
@section('content')
<div>
    <div class="page-header">
        <div class="page-block mb-3">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h2 class="mb-0">Cấu hình API Tạo Website & Tên Miền</h2>
                        <p class="text-muted">Quản lý API Token kết nối TheGioiDev, DichVuDark.vip, tỷ lệ lợi nhuận % và dịch vụ tên miền</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ti ti-check me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ti ti-alert-circle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Alert Hướng dẫn quan trọng -->
    <div class="alert alert-info border-0 shadow-sm mb-4">
        <div class="d-flex align-items-start">
            <div class="me-3 fs-3 text-info"><i class="ti ti-info-circle"></i></div>
            <div>
                <h5 class="alert-heading fw-bold mb-1">Mô hình liên kết Tự động hóa 100%:</h5>
                <ul class="mb-0 ps-3 small text-muted">
                    <li><strong>Máy chủ & Mã nguồn:</strong> Cung cấp tự động bởi <a href="https://thegioidev.com" target="_blank" class="fw-bold text-primary">TheGioiDev.com</a> (Cấu hình aaPanel, MySQL, SSL, Cloudflare Zone).</li>
                    <li><strong>Tên miền quốc tế (.com, .net, .xyz...):</strong> Đăng ký và trỏ DNS tự động qua API <a href="https://dichvudark.vip" target="_blank" class="fw-bold text-success">DichVuDark.vip</a>.</li>
                    <li><strong>Quy trình:</strong> Khách chọn "Mua tên miền mới" ➔ Hệ thống tự tra cứu khả dụng ➔ Tạo web trên TheGioiDev lấy cặp Cloudflare NS ➔ Đăng ký tên miền trên DichVuDark trỏ ngay vào cặp NS ➔ Kích hoạt website tức thì!</li>
                </ul>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.websites.settings.update') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-lg-8">
                <!-- 1. TheGioiDev Settings -->
                <div class="card shadow-sm border border-dashed mb-4">
                    <div class="card-header bg-transparent border-bottom">
                        <h5 class="card-title mb-0"><i class="ti ti-key me-2 text-primary"></i>1. Cấu hình Website (TheGioiDev.com)</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label fw-bold">TheGioiDev API Token <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" id="apiTokenInput" name="thegioidev_api_token"
                                       class="form-control"
                                       value="{{ old('thegioidev_api_token', $apiToken) }}"
                                       placeholder="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...">
                                <button type="button" class="btn btn-outline-secondary" onclick="toggleTokenVisibility('apiTokenInput', 'toggleIcon1')">
                                    <i class="ti ti-eye" id="toggleIcon1"></i>
                                </button>
                                <button type="button" class="btn btn-primary" id="btnTestConn" onclick="testConnection()">
                                    <i class="ti ti-plug me-1"></i> Kiểm tra TheGioiDev
                                </button>
                            </div>
                            <div id="testConnectionResult" class="mt-2" style="display:none;"></div>
                            <small class="text-muted d-block mt-1">Lấy tại: <a href="https://thegioidev.com" target="_blank">thegioidev.com</a> &bull; Chuẩn header: <code>Authorization: Bearer</code>.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Tỷ lệ lợi nhuận Website (% Markup) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.1" min="0" max="500" name="thegioidev_website_markup"
                                           class="form-control"
                                           value="{{ old('thegioidev_website_markup', $markup) }}"
                                           placeholder="Ví dụ: 15">
                                    <span class="input-group-text">%</span>
                                </div>
                                <small class="text-muted">Cộng % vào giá gốc gói website của TheGioiDev.</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Trạng thái dịch vụ</label>
                                <div class="form-control bg-light d-flex align-items-center">
                                    <span class="badge bg-success me-2">🟢 Đang mở bán</span>
                                    <small class="text-muted">(Luôn mở bán 24/7)</small>
                                </div>
                                <input type="hidden" name="thegioidev_website_status" value="1">
                                <small class="text-muted">Hệ thống luôn mở bán tự động và không cho phép tạm ngưng.</small>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-bold">Thông báo / Lưu ý trang Tạo Website</label>
                            <textarea name="thegioidev_website_notice" rows="2" class="form-control"
                                      placeholder="Nội dung ghi chú hiển thị ở đầu trang tạo website...">{{ old('thegioidev_website_notice', $notice) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- 2. DichVuDark Domain Settings -->
                <div class="card shadow-sm border border-dashed mb-4">
                    <div class="card-header bg-transparent border-bottom">
                        <h5 class="card-title mb-0"><i class="ti ti-world me-2 text-success"></i>2. Cấu hình Mua Tên Miền Tự Động (DichVuDark.vip)</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label fw-bold">DichVuDark API Token <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" id="dichvudarkTokenInput" name="dichvudark_api_token"
                                       class="form-control"
                                       value="{{ old('dichvudark_api_token', $dichvudarkToken) }}"
                                       placeholder="Token API cá nhân lấy tại dichvudark.vip/account/profile">
                                <button type="button" class="btn btn-outline-secondary" onclick="toggleTokenVisibility('dichvudarkTokenInput', 'toggleIcon2')">
                                    <i class="ti ti-eye" id="toggleIcon2"></i>
                                </button>
                                <button type="button" class="btn btn-success" id="btnTestDvd" onclick="testDichVuDarkConnection()">
                                    <i class="ti ti-plug me-1"></i> Kiểm tra DichVuDark
                                </button>
                            </div>
                            <div id="testDvdResult" class="mt-2" style="display:none;"></div>
                            <small class="text-muted d-block mt-1">
                                Dùng để kiểm tra khả năng đăng ký tên miền (WHOIS) và tự động mua tên miền từ số dư ví DichVuDark.vip của bạn.
                            </small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Tỷ lệ lợi nhuận Tên Miền (% Markup) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.1" min="0" max="500" name="dichvudark_domain_markup"
                                           class="form-control"
                                           value="{{ old('dichvudark_domain_markup', $domainMarkup) }}"
                                           placeholder="Ví dụ: 10">
                                    <span class="input-group-text">%</span>
                                </div>
                                <small class="text-muted">Ví dụ: Tên miền gốc 290.000đ, markup 10% thì giá bán khách là 319.000đ/năm.</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Lưu ý tài khoản DichVuDark</label>
                                <div class="small text-muted p-2 bg-light rounded border">
                                    Cần cập nhật đầy đủ thông tin chủ thể (Họ tên, SĐT, Địa chỉ, Zipcode) tại <a href="https://dichvudark.vip/account/profile" target="_blank" class="fw-bold">dichvudark.vip/account/profile</a> để tránh lỗi khi đăng ký tên miền.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mb-4">
                    <button type="submit" class="btn btn-success btn-lg px-5">
                        <i class="ti ti-device-floppy me-1"></i> Lưu toàn bộ cấu hình
                    </button>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border border-dashed mb-4">
                    <div class="card-header bg-transparent border-bottom">
                        <h5 class="card-title mb-0"><i class="ti ti-bolt me-2 text-warning"></i>Thao tác nhanh</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.websites.index') }}" class="btn btn-outline-primary text-start">
                                <i class="ti ti-layout-grid me-2"></i> Danh sách mẫu Web
                            </a>
                            <a href="{{ route('admin.websites.orders') }}" class="btn btn-outline-info text-start">
                                <i class="ti ti-shopping-cart me-2"></i> Quản lý đơn hàng Website
                            </a>
                            <a href="{{ route('websites.index') }}" target="_blank" class="btn btn-outline-success text-start">
                                <i class="ti ti-external-link me-2"></i> Mở trang Tạo Website (Client)
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border border-dashed">
                    <div class="card-header bg-transparent border-bottom">
                        <h5 class="card-title mb-0"><i class="ti ti-shield-check me-2 text-success"></i>Cách hoạt động Mua Tên Miền</h5>
                    </div>
                    <div class="card-body small text-muted">
                        <p class="mb-2"><strong>1. Tra cứu WHOIS:</strong> Khách gõ tên miền, hệ thống gọi API DichVuDark kiểm tra xem tên miền còn trống không.</p>
                        <p class="mb-2"><strong>2. Báo giá:</strong> Giá gốc từ DichVuDark tự động nhân với % Markup và cộng vào tổng đơn hàng.</p>
                        <p class="mb-2"><strong>3. Tự động liên kết:</strong> Sau khi TheGioiDev cấp cặp Nameserver Cloudflare, hệ thống gửi yêu cầu mua domain tới DichVuDark kèm cặp Nameserver đó.</p>
                        <p class="mb-0"><strong>4. Hoàn tiền an toàn:</strong> Nếu đăng ký tên miền gặp sự cố, hệ thống tự động hoàn lại tiền tên miền về ví khách hàng ngay lập tức.</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function toggleTokenVisibility(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('ti-eye');
            icon.classList.add('ti-eye-off');
        } else {
            input.type = 'password';
            icon.classList.remove('ti-eye-off');
            icon.classList.add('ti-eye');
        }
    }

    function testConnection() {
        const token = document.getElementById('apiTokenInput').value;
        const btn = document.getElementById('btnTestConn');
        const resultBox = document.getElementById('testConnectionResult');

        if (!token.trim()) {
            resultBox.style.display = 'block';
            resultBox.innerHTML = '<div class="alert alert-warning py-2 mb-0"><i class="ti ti-alert-triangle me-1"></i> Vui lòng nhập API Token TheGioiDev trước khi kiểm tra!</div>';
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Đang kiểm tra...';
        resultBox.style.display = 'none';

        fetch("{{ route('admin.websites.test-connection') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ token: token })
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="ti ti-plug me-1"></i> Kiểm tra TheGioiDev';
            resultBox.style.display = 'block';

            if (data.status === 'success' || data.error === 0) {
                resultBox.innerHTML = '<div class="alert alert-success py-2 mb-0"><i class="ti ti-check me-1"></i> ' + (data.message || 'Kết nối thành công đến API TheGioiDev!') + '</div>';
            } else {
                resultBox.innerHTML = '<div class="alert alert-danger py-2 mb-0"><i class="ti ti-x me-1"></i> ' + (data.message || 'Kết nối thất bại hoặc Token không đúng!') + '</div>';
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="ti ti-plug me-1"></i> Kiểm tra TheGioiDev';
            resultBox.style.display = 'block';
            resultBox.innerHTML = '<div class="alert alert-danger py-2 mb-0"><i class="ti ti-x me-1"></i> Lỗi: ' + err.message + '</div>';
        });
    }

    function testDichVuDarkConnection() {
        const token = document.getElementById('dichvudarkTokenInput').value;
        const btn = document.getElementById('btnTestDvd');
        const resultBox = document.getElementById('testDvdResult');

        if (!token.trim()) {
            resultBox.style.display = 'block';
            resultBox.innerHTML = '<div class="alert alert-warning py-2 mb-0"><i class="ti ti-alert-triangle me-1"></i> Vui lòng nhập API Token DichVuDark trước khi kiểm tra!</div>';
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Đang kiểm tra...';
        resultBox.style.display = 'none';

        fetch("{{ route('admin.websites.test-dichvudark') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ token: token })
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="ti ti-plug me-1"></i> Kiểm tra DichVuDark';
            resultBox.style.display = 'block';

            if (data.status === 'success') {
                resultBox.innerHTML = '<div class="alert alert-success py-2 mb-0"><i class="ti ti-check me-1"></i> ' + (data.message || 'Kết nối thành công đến API DichVuDark.vip!') + '</div>';
            } else {
                resultBox.innerHTML = '<div class="alert alert-danger py-2 mb-0"><i class="ti ti-x me-1"></i> ' + (data.message || 'Kết nối thất bại hoặc Token không đúng!') + '</div>';
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="ti ti-plug me-1"></i> Kiểm tra DichVuDark';
            resultBox.style.display = 'block';
            resultBox.innerHTML = '<div class="alert alert-danger py-2 mb-0"><i class="ti ti-x me-1"></i> Lỗi: ' + err.message + '</div>';
        });
    }
</script>
@endpush
