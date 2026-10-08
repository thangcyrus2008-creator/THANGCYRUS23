@extends('layouts.user.app')

@section('title', 'Quản lý Website Đã Thuê')

@push('css')
<style>
    .orders-page {
        padding: 30px 0 60px;
        min-height: 100vh;
    }
    .orders-card {
        background: var(--bg-card, #fff);
        border: 1px solid var(--border-color, #e5e7eb);
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }
    .order-item-card {
        background: var(--bg-card, #fff);
        border: 1px solid var(--border-color, #e5e7eb);
        border-radius: 14px;
        padding: 20px;
        margin-bottom: 20px;
        transition: all 0.2s;
    }
    .order-item-card:hover {
        border-color: var(--primary, #10b981);
        box-shadow: 0 6px 12px rgba(0,0,0,0.06);
    }
    .order-header-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        border-bottom: 1px solid var(--border-color, #e5e7eb);
        padding-bottom: 14px;
        margin-bottom: 16px;
    }
    .order-domain-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-color, #111827);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .ns-box {
        background: #f0fdf4;
        border: 1px dashed #10b981;
        border-radius: 12px;
        padding: 16px;
        margin-top: 14px;
    }
    [data-theme="dark"] .ns-box {
        background: rgba(16, 185, 129, 0.08);
        border-color: #059669;
    }
    .ns-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--bg-card, #fff);
        border: 1px solid var(--border-color, #e5e7eb);
        border-radius: 8px;
        padding: 8px 14px;
        margin-top: 8px;
        font-family: monospace;
        font-size: 0.95rem;
    }
    .copy-btn {
        background: rgba(0,0,0,0.05);
        border: 1px solid var(--border-color, #e5e7eb);
        border-radius: 6px;
        padding: 4px 10px;
        font-size: 0.8rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .action-btn-group {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 16px;
    }
    .btn-action {
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 1px solid transparent;
        transition: all 0.2s;
    }
    .btn-action-primary {
        background: var(--primary, #10b981);
        color: #fff;
    }
    .btn-action-primary:hover {
        opacity: 0.9;
    }
    .btn-action-outline {
        border-color: var(--border-color, #e5e7eb);
        background: transparent;
        color: var(--text-color, #111827);
    }
    .btn-action-outline:hover {
        background: rgba(0,0,0,0.04);
    }
    .btn-action-warning {
        background: #f59e0b;
        color: #fff;
    }

    /* Modal Styling */
    .website-modal {
        display: none;
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.65);
        z-index: 10000;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(4px);
        padding: 16px;
    }
    .website-modal.show {
        display: flex !important;
    }
    .website-modal-content {
        background: var(--bg-card, #fff);
        border: 1px solid var(--border-color, #e5e7eb);
        border-radius: 16px;
        max-width: 480px;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
        padding: 24px;
        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);
        animation: modalScale .2s ease-out;
    }
    @keyframes modalScale {
        from { transform: scale(0.95); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
    .modal-header-custom {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid var(--border-color, #e5e7eb);
        padding-bottom: 14px;
        margin-bottom: 16px;
    }
    .modal-header-custom h3 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--text-color, #111827);
    }
    .modal-close-btn {
        background: none;
        border: none;
        font-size: 1.4rem;
        cursor: pointer;
        color: var(--text-muted, #9ca3af);
    }
    .modal-close-btn:hover {
        color: var(--text-color, #111827);
    }
    .form-group-custom {
        margin-bottom: 16px;
    }
    .form-input-custom {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid var(--border-color, #d1d5db);
        border-radius: 8px;
        font-size: 0.95rem;
        background: var(--bg-card, #fff);
        color: var(--text-color, #111827);
        outline: none;
        box-sizing: border-box;
    }
    .form-input-custom:focus {
        border-color: var(--primary, #10b981);
    }
    .price-summary-box {
        background: rgba(0,0,0,0.02);
        border: 1px solid var(--border-color, #e5e7eb);
        border-radius: 12px;
        padding: 16px;
        margin: 16px 0;
    }
    [data-theme="dark"] .price-summary-box {
        background: rgba(255,255,255,0.03);
    }
    .price-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.9rem;
        margin-bottom: 8px;
    }
    .price-row:last-child {
        margin-bottom: 0;
    }
    .price-row.total {
        border-top: 1px dashed var(--border-color, #e5e7eb);
        padding-top: 10px;
        margin-top: 10px;
        font-size: 1rem;
        font-weight: 700;
    }
</style>
@endpush

@section('content')
<div class="orders-page">
    <div class="container" style="max-width: 1000px; margin: 0 auto; padding: 0 16px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
            <div>
                <h1 style="font-size:1.75rem; font-weight:800; color:var(--text-color, #111827); margin:0;">Website Của Bạn</h1>
                <p style="color:var(--text-muted, #6b7280); margin:4px 0 0; font-size:0.95rem;">Quản lý thông tin truy cập, cập nhật Nameserver và gia hạn website.</p>
            </div>
            <a href="{{ route('websites.index') }}" class="btn-action btn-action-primary" style="text-decoration:none;">
                <span class="iconify" data-icon="ant-design:plus-outlined"></span> Thuê Thêm Mẫu Mới
            </a>
        </div>

        @if($orders->isEmpty())
        <div class="orders-card" style="text-align:center; padding:50px 20px;">
            <span class="iconify" data-icon="ant-design:global-outlined" style="font-size:3.5rem; color:#9ca3af;"></span>
            <h3 style="margin:16px 0 8px; font-weight:700;">Bạn chưa thuê website nào</h3>
            <p style="color:var(--text-muted, #6b7280); max-width:450px; margin:0 auto 20px;">Khám phá kho mẫu website chuyên nghiệp, hỗ trợ đa dạng ngành nghề với tốc độ tải trang cực nhanh.</p>
            <a href="{{ route('websites.index') }}" class="btn-action btn-action-primary" style="text-decoration:none; display:inline-flex;">
                Khám Phá Mẫu Website
            </a>
        </div>
        @else
            @foreach($orders as $order)
            <div class="order-item-card" id="order-card-{{ $order->id }}">
                <div class="order-header-row">
                    <div>
                        <div class="order-domain-title">
                            <span class="iconify" data-icon="ant-design:link-outlined" style="color:#10b981;"></span>
                            <a href="http://{{ $order->domain }}" target="_blank" rel="noopener noreferrer" style="color:inherit; text-decoration:none;">
                                {{ $order->domain }}
                            </a>
                        </div>
                        <div style="font-size:0.85rem; color:var(--text-muted, #6b7280); margin-top:4px;">
                            Mẫu: <strong>{{ $order->service_name }}</strong> &bull; Thời hạn: <strong>{{ $order->duration_months }} tháng</strong> &bull; Ngày tạo: {{ $order->created_at ? $order->created_at->format('d/m/Y H:i') : 'Mới tạo' }}
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:10px;">
                        {!! $order->status_badge !!}
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:12px; font-size:0.88rem; margin-bottom:12px;">
                    <div>
                        <span style="color:var(--text-muted, #6b7280);">Chi phí thanh toán:</span><br>
                        <strong style="color:#ef4444; font-size:1.05rem;">{{ number_format($order->price) }}đ</strong>
                    </div>
                    <div>
                        <span style="color:var(--text-muted, #6b7280);">Hạn sử dụng:</span><br>
                        <strong>{{ $order->expired_at ? $order->expired_at->format('d/m/Y H:i') : 'Đang cập nhật' }}</strong>
                    </div>
                    <div>
                        <span style="color:var(--text-muted, #6b7280);">Mã đơn TheGioiDev:</span><br>
                        <code style="font-size:0.85rem;">{{ $order->order_id ?: 'Chưa cấp' }}</code>
                    </div>
                </div>

                <!-- Nameservers Box -->
                @php
                    $nsList = $order->display_nameservers;
                @endphp
                @if(!empty($nsList) && is_array($nsList) && count($nsList) > 0)
                <div class="ns-box" id="ns-box-{{ $order->id }}">
                    <div style="font-weight:700; color:#10b981; display:flex; align-items:center; gap:6px; font-size:0.92rem;">
                        <span class="iconify" data-icon="ant-design:cloud-server-outlined"></span>
                        Cặp Nameserver Cloudflare để trỏ tên miền:
                    </div>
                    <p style="font-size:0.82rem; color:var(--text-muted, #6b7280); margin:4px 0 8px;">
                        Vui lòng truy cập trang quản lý tên miền (nhà đăng ký tên miền của bạn) và cập nhật 2 Nameserver sau:
                    </p>
                    @foreach($nsList as $idx => $ns)
                    <div class="ns-item">
                        <span><strong>NS {{ $idx + 1 }}:</strong> {{ $ns }}</span>
                        <button type="button" class="copy-btn" onclick="copyToClipboard('{{ $ns }}')">
                            <span class="iconify" data-icon="ant-design:copy-outlined"></span> Copy
                        </button>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="ns-box" id="ns-box-{{ $order->id }}" style="background:#fffbeb; border-color:#f59e0b;">
                    <div style="font-weight:700; color:#b45309; display:flex; align-items:center; gap:6px; font-size:0.92rem;">
                        <span class="iconify" data-icon="ant-design:warning-outlined"></span>
                        Chưa có cặp Nameserver Cloudflare để trỏ tên miền
                    </div>
                    <p style="font-size:0.82rem; color:#92400e; margin:4px 0 0;">
                        Vui lòng bấm nút <strong>"Kiểm Tra NS & Kích Hoạt"</strong> bên dưới để hệ thống đồng bộ ngay cặp Nameserver cho bạn!
                    </p>
                </div>
                @endif

                <!-- Actions -->
                <div class="action-btn-group">
                    <button type="button" class="btn-action btn-action-primary" onclick="checkNs({{ $order->id }}, this)">
                        <span class="iconify" data-icon="ant-design:sync-outlined"></span> Kiểm Tra NS & Kích Hoạt
                    </button>

                    @if($order->status == 'failed')
                    <button type="button" class="btn-action btn-action-warning" onclick="retryDeploy({{ $order->id }}, this)">
                        <span class="iconify" data-icon="ant-design:redo-outlined"></span> Thử Lại Triển Khai
                    </button>
                    @endif

                    <button type="button" class="btn-action btn-action-outline" onclick="openRenewModal({{ $order->id }}, '{{ addslashes($order->domain) }}', {{ (int)$order->monthly_renew_price }}, '{{ $order->expired_at ? $order->expired_at->format('d/m/Y') : '' }}')">
                        <span class="iconify" data-icon="ant-design:history-outlined"></span> Gia Hạn Website
                    </button>

                    @if(in_array($order->status, ['active', 'processing']))
                    <a href="http://{{ $order->domain }}/admin" target="_blank" rel="noopener noreferrer" class="btn-action btn-action-outline" style="text-decoration:none;">
                        <span class="iconify" data-icon="ant-design:login-outlined"></span> Truy Cập Quản Trị &rarr;
                    </a>
                    @endif
                </div>
            </div>
            @endforeach

            <!-- Pagination -->
            <div style="margin-top:24px;">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Renew Modal -->
<div id="renewModal" class="website-modal" onclick="if(event.target===this)closeRenewModal()">
    <div class="website-modal-content" style="max-width:460px;">
        <div class="modal-header-custom">
            <div>
                <h3 id="renewTitle" style="margin:0; font-size:1.2rem; font-weight:700;">Gia Hạn Website</h3>
                <small id="renewSubtitle" style="color:var(--text-muted, #6b7280); font-size:0.85rem;"></small>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeRenewModal()">✕</button>
        </div>

        <form id="renewForm" onsubmit="submitRenew(event)">
            @csrf
            <input type="hidden" id="renewOrderId">

            <div class="form-group-custom" style="margin-top:16px;">
                <label style="font-weight:600; font-size:0.9rem; margin-bottom:6px; display:block;">Thời gian gia hạn thêm:</label>
                <select id="renewMonthsSelect" class="form-input-custom" onchange="calculateRenewTotal()">
                    <option value="1">1 Tháng</option>
                    <option value="3">3 Tháng</option>
                    <option value="6">6 Tháng</option>
                    <option value="12">12 Tháng (1 Năm)</option>
                    <option value="24">24 Tháng (2 Năm)</option>
                    <option value="36">36 Tháng (3 Năm)</option>
                </select>
            </div>

            <div class="price-summary-box" style="margin:16px 0;">
                <div class="price-row" id="rowCurrentExpired" style="display:none;">
                    <span>Hạn dùng hiện tại:</span>
                    <strong id="renewCurrentExpired">-</strong>
                </div>
                <div class="price-row">
                    <span>Đơn giá gia hạn / tháng:</span>
                    <strong id="renewMonthlyPrice">0đ</strong>
                </div>
                <div class="price-row total" style="border-top:1px dashed #e5e7eb; padding-top:8px; margin-top:8px;">
                    <span>Tổng tiền thanh toán:</span>
                    <span id="renewTotalPrice" style="color:#ef4444; font-size:1.25rem; font-weight:700;">0đ</span>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:8px;">
                <button type="button" class="btn-action btn-action-outline" onclick="closeRenewModal()">Hủy</button>
                <button type="submit" class="btn-action btn-action-primary" id="btnConfirmRenew">
                    <span class="iconify" data-icon="ant-design:check-circle-outlined"></span> Xác Nhận Gia Hạn
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            if (typeof FuiToast !== 'undefined') {
                FuiToast.success('Đã sao chép vào bộ nhớ tạm: ' + text);
            } else {
                alert('Đã copy: ' + text);
            }
        });
    }

    function checkNs(orderId, btn) {
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang kiểm tra...';

        fetch(`/tao-website/order/${orderId}/check-ns`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = originalText;
            if (data.status === 'success') {
                if (typeof FuiToast !== 'undefined') {
                    FuiToast.success(data.message || 'Kiểm tra hoàn tất!');
                } else {
                    alert(data.message || 'Kiểm tra hoàn tất!');
                }
                setTimeout(() => location.reload(), 1200);
            } else {
                if (typeof FuiToast !== 'undefined') {
                    FuiToast.error(data.message || 'Chưa nhận diện cặp Nameserver!');
                } else {
                    alert(data.message || 'Chưa nhận diện cặp Nameserver!');
                }
                // Tự động tải lại trang sau 1.2s để render ngay cặp Nameserver vừa đồng bộ
                setTimeout(() => location.reload(), 1200);
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = originalText;
            alert('Lỗi kiểm tra NS: ' + err.message);
        });
    }

    function retryDeploy(orderId, btn) {
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang gửi yêu cầu...';

        fetch(`/tao-website/order/${orderId}/retry`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = originalText;
            if (data.status === 'success') {
                if (typeof FuiToast !== 'undefined') {
                    FuiToast.success(data.message);
                } else {
                    alert(data.message);
                }
                setTimeout(() => location.reload(), 1200);
            } else {
                if (typeof FuiToast !== 'undefined') {
                    FuiToast.error(data.message);
                } else {
                    alert(data.message);
                }
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = originalText;
            alert('Lỗi: ' + err.message);
        });
    }

    let currentRenewPricePerMonth = 0;
    let currentRenewExpiredDate = '';

    function openRenewModal(orderId, domain, pricePerMonth, expiredAt) {
        document.getElementById('renewOrderId').value = orderId;
        document.getElementById('renewTitle').textContent = 'Gia Hạn: ' + domain;
        document.getElementById('renewSubtitle').textContent = 'Tên miền: ' + domain;
        currentRenewPricePerMonth = Math.round(pricePerMonth) || 150000;
        currentRenewExpiredDate = expiredAt || '';

        const rowCurrentExpired = document.getElementById('rowCurrentExpired');
        if (currentRenewExpiredDate) {
            document.getElementById('renewCurrentExpired').textContent = currentRenewExpiredDate;
            rowCurrentExpired.style.display = 'flex';
        } else {
            rowCurrentExpired.style.display = 'none';
        }

        document.getElementById('renewMonthlyPrice').textContent = new Intl.NumberFormat('vi-VN').format(currentRenewPricePerMonth) + 'đ/tháng';
        calculateRenewTotal();
        const modal = document.getElementById('renewModal');
        modal.classList.add('show');
        modal.style.display = 'flex';
    }

    function closeRenewModal() {
        const modal = document.getElementById('renewModal');
        modal.classList.remove('show');
        modal.style.display = 'none';
    }

    function calculateRenewTotal() {
        const months = parseInt(document.getElementById('renewMonthsSelect').value) || 1;
        const total = currentRenewPricePerMonth * months;
        document.getElementById('renewTotalPrice').textContent = new Intl.NumberFormat('vi-VN').format(total) + 'đ';
    }

    function submitRenew(e) {
        e.preventDefault();
        const orderId = document.getElementById('renewOrderId').value;
        const months = document.getElementById('renewMonthsSelect').value;
        const btn = document.getElementById('btnConfirmRenew');

        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý...';

        fetch(`/tao-website/order/${orderId}/renew`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ months: parseInt(months) || 1 })
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<span class="iconify" data-icon="ant-design:check-circle-outlined"></span> Xác Nhận Gia Hạn';
            if (data.status === 'success') {
                if (typeof FuiToast !== 'undefined') {
                    FuiToast.success(data.message);
                } else {
                    alert(data.message);
                }
                closeRenewModal();
                setTimeout(() => location.reload(), 1200);
            } else {
                if (data.need_deposit) {
                    if (typeof FuiToast !== 'undefined') {
                        FuiToast.error(data.message);
                    }
                    if (confirm(data.message + '\n\nBạn có muốn chuyển đến trang Nạp Tiền ngay bây giờ không?')) {
                        window.location.href = "{{ route('profile.deposit-atm') }}";
                    }
                } else {
                    if (typeof FuiToast !== 'undefined') {
                        FuiToast.error(data.message || 'Gia hạn thất bại');
                    } else {
                        alert(data.message || 'Gia hạn thất bại');
                    }
                }
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<span class="iconify" data-icon="ant-design:check-circle-outlined"></span> Xác Nhận Gia Hạn';
            alert('Lỗi kết nối máy chủ: ' + err.message);
        });
    }
</script>
@endpush
