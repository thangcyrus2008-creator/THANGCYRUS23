@extends('layouts.user.app')

@section('title', 'Tạo Website Trọn Gói - Chuẩn SEO & Tự Động')

@push('css')
<style>
    .website-page {
        padding: 30px 0 60px;
        min-height: 100vh;
    }
    .website-hero {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(59, 130, 246, 0.1) 100%);
        border: 1px solid var(--border-color, #e5e7eb);
        border-radius: 16px;
        padding: 32px 24px;
        margin-bottom: 30px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .website-hero h1 {
        font-size: 2rem;
        font-weight: 800;
        color: var(--text-color, #111827);
        margin-bottom: 10px;
    }
    .website-hero p {
        color: var(--text-muted, #6b7280);
        font-size: 1rem;
        max-width: 700px;
        margin: 0 auto;
    }
    .website-filter-bar {
        background: var(--bg-card, #fff);
        border: 1px solid var(--border-color, #e5e7eb);
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 24px;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
        justify-content: space-between;
    }
    .website-categories-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .chip-item {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.88rem;
        font-weight: 500;
        text-decoration: none;
        color: var(--text-muted, #6b7280);
        background: rgba(0,0,0,0.03);
        border: 1px solid var(--border-color, #e5e7eb);
        transition: all 0.2s;
    }
    .chip-item:hover, .chip-item.active {
        background: var(--primary, #10b981);
        color: #fff;
        border-color: var(--primary, #10b981);
    }
    .website-search-box {
        display: flex;
        align-items: center;
        gap: 8px;
        background: var(--bg-card, #fff);
        border: 1px solid var(--border-color, #e5e7eb);
        border-radius: 8px;
        padding: 6px 12px;
        min-width: 260px;
    }
    .website-search-box input {
        border: none;
        outline: none;
        background: transparent;
        color: var(--text-color, #111827);
        font-size: 0.9rem;
        width: 100%;
    }
    .website-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 24px;
        margin-bottom: 40px;
    }
    .website-card {
        background: var(--bg-card, #fff);
        border: 1px solid var(--border-color, #e5e7eb);
        border-radius: 14px;
        overflow: hidden;
        transition: all 0.25s ease;
        display: flex;
        flex-direction: column;
        box-shadow: 0 2px 4px rgba(0,0,0,0.04);
    }
    .website-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 20px -5px rgba(0,0,0,0.1);
        border-color: var(--primary, #10b981);
    }
    .website-thumb-wrapper {
        position: relative;
        width: 100%;
        height: 190px;
        overflow: hidden;
        background: #f3f4f6;
    }
    .website-thumb {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }
    .website-card:hover .website-thumb {
        transform: scale(1.05);
    }
    .website-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        background: rgba(16, 185, 129, 0.9);
        backdrop-filter: blur(4px);
        color: #fff;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 20px;
    }
    .website-card-body {
        padding: 16px;
        display: flex;
        flex-direction: column;
        flex: 1;
    }
    .website-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-color, #111827);
        margin-bottom: 8px;
        line-height: 1.4;
    }
    .website-features {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 16px;
        flex: 1;
    }
    .feature-tag {
        font-size: 0.75rem;
        background: rgba(16, 185, 129, 0.08);
        color: #10b981;
        padding: 2px 8px;
        border-radius: 4px;
        font-weight: 500;
    }
    .website-card-footer {
        padding-top: 14px;
        border-top: 1px solid var(--border-color, #e5e7eb);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }
    .website-price-box {
        display: flex;
        flex-direction: column;
    }
    .website-price-label {
        font-size: 0.75rem;
        color: var(--text-muted, #6b7280);
    }
    .website-price-val {
        font-size: 1.15rem;
        font-weight: 800;
        color: #ef4444;
    }
    .website-btn-group {
        display: flex;
        gap: 6px;
    }
    .btn-demo {
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 0.82rem;
        font-weight: 600;
        border: 1px solid var(--border-color, #e5e7eb);
        color: var(--text-color, #111827);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: transparent;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-demo:hover {
        background: rgba(0,0,0,0.05);
    }
    .btn-buy {
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        background: var(--primary, #10b981);
        color: #fff;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.2s;
    }
    .btn-buy:hover {
        opacity: 0.9;
        transform: scale(1.02);
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
        display: flex;
    }
    .website-modal-content {
        background: var(--bg-card, #fff);
        border: 1px solid var(--border-color, #e5e7eb);
        border-radius: 16px;
        max-width: 600px;
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
        font-size: 1.25rem;
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
    .duration-chips {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        margin-top: 6px;
    }
    .duration-chip {
        padding: 8px 10px;
        border: 1px solid var(--border-color, #e5e7eb);
        border-radius: 8px;
        text-align: center;
        cursor: pointer;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-color, #111827);
        transition: all 0.2s;
    }
    .duration-chip.selected {
        border-color: var(--primary, #10b981);
        background: rgba(16, 185, 129, 0.1);
        color: var(--primary, #10b981);
    }
    .duration-chip .sub {
        font-size: 0.72rem;
        color: var(--text-muted, #6b7280);
        display: block;
        font-weight: normal;
    }
    .form-group-custom {
        margin-bottom: 16px;
    }
    .form-group-custom label {
        display: block;
        font-size: 0.88rem;
        font-weight: 600;
        margin-bottom: 6px;
        color: var(--text-color, #111827);
    }
    .form-input-custom {
        width: 100%;
        padding: 10px 14px;
        border-radius: 8px;
        border: 1px solid var(--border-color, #e5e7eb);
        background: var(--bg-card, #fff);
        color: var(--text-color, #111827);
        font-size: 0.92rem;
        box-sizing: border-box;
    }
    .form-input-custom:focus {
        outline: none;
        border-color: var(--primary, #10b981);
    }

    /* Domain Selection Cards */
    .domain-type-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-bottom: 12px;
    }
    .domain-type-card {
        border: 1.5px solid var(--border-color, #e5e7eb);
        border-radius: 10px;
        padding: 12px;
        cursor: pointer;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        background: var(--bg-card, #fff);
        transition: all 0.2s;
    }
    .domain-type-card.selected {
        border-color: var(--primary, #10b981);
        background: rgba(16, 185, 129, 0.06);
    }
    .domain-type-card input[type="radio"] {
        margin-top: 3px;
        cursor: pointer;
    }
    .domain-type-card strong {
        display: block;
        font-size: 0.88rem;
        color: var(--text-color, #111827);
    }
    .domain-type-card .sub {
        display: block;
        font-size: 0.74rem;
        color: var(--text-muted, #6b7280);
        margin-top: 2px;
    }

    .price-summary-box {
        background: rgba(16, 185, 129, 0.05);
        border: 1px dashed #10b981;
        border-radius: 10px;
        padding: 14px;
        margin-bottom: 20px;
    }
    .price-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 6px;
        font-size: 0.9rem;
        color: var(--text-color, #111827);
    }
    .price-row.total {
        font-size: 1.15rem;
        font-weight: 800;
        color: #ef4444;
        margin-bottom: 0;
        padding-top: 8px;
        border-top: 1px dashed rgba(16, 185, 129, 0.3);
    }
</style>
@endpush

@section('content')
<div class="website-page">
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 16px;">
        <!-- Hero Banner -->
        <div class="website-hero">
            <span class="badge" style="background:#10b981; color:#fff; padding:4px 12px; border-radius:20px; font-weight:600; font-size:0.8rem; margin-bottom:12px; display:inline-block;">Dịch vụ Cloud Tự Động</span>
            <h1>Khởi Tạo Website Trọn Gói Trong 60 Giây</h1>
            <p>{{ $notice ?: 'Hệ thống tự động kích hoạt Cloudflare, cấp chứng chỉ SSL, cấu hình Database và máy chủ tốc độ cao. Dành cho mọi lĩnh vực: Bán hàng, Tin tức, Dịch vụ game, Giới thiệu công ty.' }}</p>
            <div style="margin-top: 18px; display: flex; justify-content: center; gap: 12px;">
                <a href="{{ route('websites.my-orders') }}" class="btn-demo" style="background:#fff; border-color:#10b981; color:#10b981; font-weight:700;">
                    <span class="iconify" data-icon="ant-design:appstore-outlined"></span> Quản lý Website Đã Thuê
                </a>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="website-filter-bar">
            <div class="website-categories-chips">
                <a href="{{ route('websites.index', ['search' => $currentSearch]) }}" class="chip-item {{ empty($currentCategory) ? 'active' : '' }}">
                    Tất cả mẫu
                </a>
                @foreach($categories as $cat)
                <a href="{{ route('websites.index', ['category_id' => $cat['id'] ?? ($cat['_id'] ?? ''), 'search' => $currentSearch]) }}" 
                   class="chip-item {{ $currentCategory == ($cat['id'] ?? ($cat['_id'] ?? '')) ? 'active' : '' }}">
                    {{ $cat['name'] ?? ($cat['categoryName'] ?? 'Danh mục') }}
                </a>
                @endforeach
            </div>

            <form action="{{ route('websites.index') }}" method="GET" class="website-search-box">
                @if(!empty($currentCategory))
                    <input type="hidden" name="category_id" value="{{ $currentCategory }}">
                @endif
                <span class="iconify" data-icon="ant-design:search-outlined" style="color:var(--text-muted, #9ca3af);"></span>
                <input type="text" name="search" value="{{ $currentSearch }}" placeholder="Tìm kiếm mẫu website...">
                @if(!empty($currentSearch))
                    <a href="{{ route('websites.index', ['category_id' => $currentCategory]) }}" style="color:var(--text-muted, #9ca3af); text-decoration:none;">✕</a>
                @endif
            </form>
        </div>

        @if(!empty($apiError))
        <div style="background:#fef2f2; border:1px solid #fca5a5; color:#b91c1c; border-radius:10px; padding:14px 18px; margin-bottom:20px; display:flex; align-items:center; gap:10px;">
            <i class="fa-solid fa-triangle-exclamation" style="font-size:1.2rem;"></i>
            <div>
                <strong>Thông báo từ hệ thống API:</strong> {{ $apiError }}.
                @if(Auth::check() && Auth::user()->role === 'admin')
                    <a href="{{ route('admin.websites.settings') }}" style="color:#b91c1c; font-weight:700; text-decoration:underline; margin-left:6px;">Kiểm tra cấu hình API</a>
                @endif
            </div>
        </div>
        @endif

        <!-- Website Grid -->
        @if(empty($templates))
        <div style="text-align: center; padding: 60px 20px; background: var(--bg-card, #fff); border-radius: 12px; border: 1px solid var(--border-color, #e5e7eb);">
            <span class="iconify" data-icon="ant-design:inbox-outlined" style="font-size: 3.5rem; color: #9ca3af;"></span>
            <h4 style="margin: 14px 0 6px; font-weight: 600;">Chưa có mẫu website nào phù hợp</h4>
            <p style="color: var(--text-muted, #6b7280); font-size: 0.95rem;">Vui lòng thử tìm kiếm bằng từ khóa khác hoặc quay lại danh mục tất cả.</p>
            <a href="{{ route('websites.index') }}" class="btn-buy" style="margin-top: 10px;">Xem tất cả</a>
        </div>
        @else
        <div class="website-grid">
            @foreach($templates as $tmpl)
            @php
                $imgUrl = $tmpl['image'] ?? ($tmpl['thumbnail'] ?? 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=600&auto=format&fit=crop');
                if (!str_starts_with($imgUrl, 'http')) {
                    $imgUrl = 'https://thegioidev.com' . $imgUrl;
                }
                $pricing = $tmpl['calculatedPricing'] ?? [];
            @endphp
            <div class="website-card">
                <div class="website-thumb-wrapper">
                    <img src="{{ $imgUrl }}" alt="{{ $tmpl['serviceName'] ?? 'Mẫu Website' }}" class="website-thumb" loading="lazy">
                    <span class="website-badge">Tự động 100%</span>
                </div>
                <div class="website-card-body">
                    <h3 class="website-title">{{ $tmpl['serviceName'] ?? 'Mẫu Website' }}</h3>
                    
                    <div class="website-features">
                        @if(!empty($tmpl['featuresList']) && is_array($tmpl['featuresList']))
                            @foreach(array_slice($tmpl['featuresList'], 0, 4) as $feat)
                                <span class="feature-tag"><i class="fa-solid fa-check" style="font-size:10px;"></i> {{ $feat }}</span>
                            @endforeach
                        @else
                            <span class="feature-tag"><i class="fa-solid fa-check" style="font-size:10px;"></i> Chuẩn SEO</span>
                            <span class="feature-tag"><i class="fa-solid fa-check" style="font-size:10px;"></i> SSL Miễn Phí</span>
                            <span class="feature-tag"><i class="fa-solid fa-check" style="font-size:10px;"></i> Cloudflare CDN</span>
                        @endif
                    </div>

                    <div class="website-card-footer">
                        <div class="website-price-box">
                            <span class="website-price-label">Giá chỉ từ</span>
                            <span class="website-price-val">{{ number_format($tmpl['displayPrice'] ?: ($tmpl['price'] ?? 0)) }}đ<small style="font-size:0.75rem; color:#6b7280; font-weight:normal;">/tháng</small></span>
                        </div>
                        <div class="website-btn-group">
                            @if(!empty($tmpl['demoUrl']))
                            <a href="{{ $tmpl['demoUrl'] }}" target="_blank" rel="noopener noreferrer" class="btn-demo" title="Xem trước giao diện">
                                <span class="iconify" data-icon="ant-design:eye-outlined"></span> Demo
                            </a>
                            @endif
                            <button type="button" class="btn-buy" onclick="openOrderModal({{ json_encode($tmpl) }})">
                                <span class="iconify" data-icon="ant-design:rocket-outlined"></span> Tạo Ngay
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

<!-- Order Modal -->
<div id="orderWebsiteModal" class="website-modal" onclick="if(event.target===this)closeOrderModal()">
    <div class="website-modal-content">
        <div class="modal-header-custom">
            <h3 id="modalTemplateName">Đặt Thuê Website</h3>
            <button type="button" class="modal-close-btn" onclick="closeOrderModal()">✕</button>
        </div>

        <form id="orderWebsiteForm" onsubmit="submitOrder(event)">
            @csrf
            <input type="hidden" name="service_id" id="formServiceId">
            <input type="hidden" name="service_slug" id="formServiceSlug">
            <input type="hidden" name="buy_new_domain" id="buyNewDomainInput" value="0">

            <div class="form-group-custom">
                <label>Thời hạn sử dụng <span style="color:#ef4444;">*</span></label>
                <div class="duration-chips" id="durationChipsContainer">
                    <!-- Sẽ render bằng JS -->
                </div>
            </div>

            <!-- Tùy chọn Tên Miền (DichVuDark API) -->
            <div class="form-group-custom">
                <label>Tùy chọn Tên miền <span style="color:#ef4444;">*</span></label>
                <div class="domain-type-grid">
                    <label class="domain-type-card selected" id="optExistingCard" onclick="selectDomainOption('existing')">
                        <input type="radio" name="domain_choice" value="existing" checked>
                        <div>
                            <strong>Tôi đã có tên miền</strong>
                            <span class="sub">Tự trỏ Cloudflare NS sau khi tạo</span>
                        </div>
                    </label>
                    <label class="domain-type-card" id="optBuyCard" onclick="selectDomainOption('buy')">
                        <input type="radio" name="domain_choice" value="buy">
                        <div>
                            <strong style="color:#10b981;">Mua tên miền mới</strong>
                            <span class="sub">Đăng ký tự động qua DichVuDark</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Input Tên miền -->
            <div class="form-group-custom">
                <label for="domainInput" id="domainLabel">Tên miền của bạn <span style="color:#ef4444;">*</span></label>
                <div style="display:flex; gap:8px;">
                    <input type="text" id="domainInput" name="domain" class="form-input-custom" 
                           placeholder="Ví dụ: mybrandshop.com, shopvip.net" required oninput="onDomainInputChange()">
                    <button type="button" class="btn-buy" id="btnCheckDomain" onclick="checkDomainAvailability()" style="display:none; white-space:nowrap; padding:0 16px;">
                        <span class="iconify" data-icon="ant-design:search-outlined"></span> Kiểm tra
                    </button>
                </div>
                
                <!-- Kết quả tra cứu tên miền -->
                <div id="domainCheckResult" style="display:none; margin-top:8px;"></div>
                
                <small id="domainHelpText" style="color:var(--text-muted, #6b7280); font-size:0.78rem; margin-top:4px; display:block;">
                    Sau khi hoàn tất đơn, bạn sẽ được cấp cặp Nameserver Cloudflare để trỏ tên miền về.
                </small>
            </div>

            <!-- Price Breakdown -->
            <div class="price-summary-box">
                <div class="price-row">
                    <span>Đơn giá website / tháng:</span>
                    <strong id="summaryMonthlyPrice">0đ</strong>
                </div>
                <div class="price-row">
                    <span>Thời hạn website:</span>
                    <strong id="summaryMonths">1 tháng</strong>
                </div>
                <div class="price-row" id="rowDomainPrice" style="display:none;">
                    <span>Phí đăng ký tên miền (1 năm):</span>
                    <strong id="summaryDomainPrice" style="color:#10b981;">0đ</strong>
                </div>
                <div class="price-row">
                    <span>Số dư ví hiện tại:</span>
                    <strong>{{ Auth::check() ? number_format(Auth::user()->balance) . 'đ' : 'Chưa đăng nhập' }}</strong>
                </div>
                <div class="price-row total">
                    <span>Tổng thanh toán:</span>
                    <span id="summaryTotalPrice">0đ</span>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="btn-demo" onclick="closeOrderModal()">Hủy bỏ</button>
                @if(Auth::check())
                <button type="submit" class="btn-buy" id="submitOrderBtn" style="padding:10px 24px; font-size:0.95rem;">
                    <span class="iconify" data-icon="ant-design:check-circle-outlined"></span> Xác Nhận Thanh Toán
                </button>
                @else
                <a href="{{ route('login') }}" class="btn-buy" style="padding:10px 24px; text-decoration:none;">
                    Đăng nhập để thanh toán
                </a>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentTemplate = null;
    let selectedDuration = 1;
    let selectedPricePerMonth = 0;
    let selectedWebsiteTotal = 0;
    let isBuyNewDomain = false;
    let selectedDomainPrice = 0;
    let isDomainAvailable = false;
    let checkedDomainName = '';

    function openOrderModal(tmpl) {
        currentTemplate = tmpl;
        document.getElementById('modalTemplateName').textContent = 'Thuê: ' + (tmpl.serviceName || tmpl.name || 'Website');
        document.getElementById('formServiceId').value = tmpl.id || tmpl._id || tmpl.slug;
        const slugEl = document.getElementById('formServiceSlug');
        if (slugEl) {
            slugEl.value = tmpl.slug || tmpl.id || tmpl._id;
        }

        // Reset domain option to existing
        selectDomainOption('existing');
        document.getElementById('domainInput').value = '';
        resetDomainCheck();

        // Render durations
        const container = document.getElementById('durationChipsContainer');
        container.innerHTML = '';

        let pricing = tmpl.calculatedPricing || [];
        if (!Array.isArray(pricing) || pricing.length === 0) {
            const base = tmpl.displayPrice || tmpl.minPricePerMonth || tmpl.price || 0;
            pricing = [
                { durationMonths: 1, pricePerMonth: base, totalPrice: base },
                { durationMonths: 3, pricePerMonth: base, totalPrice: base * 3 },
                { durationMonths: 6, pricePerMonth: base, totalPrice: base * 6 },
                { durationMonths: 12, pricePerMonth: base, totalPrice: base * 12 }
            ];
        }

        pricing.forEach((p, idx) => {
            const chip = document.createElement('div');
            chip.className = 'duration-chip' + (idx === 0 ? ' selected' : '');
            chip.innerHTML = `${p.durationMonths} Tháng<span class="sub">${new Intl.NumberFormat('vi-VN').format(p.pricePerMonth)}đ/tháng</span>`;
            chip.onclick = function() {
                document.querySelectorAll('.duration-chip').forEach(c => c.classList.remove('selected'));
                chip.classList.add('selected');
                updatePriceSummary(p.durationMonths, p.pricePerMonth, p.totalPrice);
            };
            container.appendChild(chip);
        });

        // Chọn cái đầu tiên
        updatePriceSummary(pricing[0].durationMonths, pricing[0].pricePerMonth, pricing[0].totalPrice);

        document.getElementById('orderWebsiteModal').classList.add('show');
    }

    function closeOrderModal() {
        document.getElementById('orderWebsiteModal').classList.remove('show');
    }

    function selectDomainOption(type) {
        isBuyNewDomain = (type === 'buy');
        document.getElementById('buyNewDomainInput').value = isBuyNewDomain ? '1' : '0';

        const optExisting = document.getElementById('optExistingCard');
        const optBuy = document.getElementById('optBuyCard');
        const btnCheck = document.getElementById('btnCheckDomain');
        const rowDomain = document.getElementById('rowDomainPrice');
        const helpText = document.getElementById('domainHelpText');
        const label = document.getElementById('domainLabel');

        if (isBuyNewDomain) {
            optBuy.classList.add('selected');
            optExisting.classList.remove('selected');
            optBuy.querySelector('input').checked = true;
            btnCheck.style.display = 'inline-flex';
            rowDomain.style.display = 'flex';
            label.innerHTML = 'Tên miền muốn đăng ký mới <span style="color:#ef4444;">*</span>';
            helpText.textContent = 'Hệ thống DichVuDark sẽ tự động mua và kết nối tên miền này trực tiếp vào website của bạn.';
        } else {
            optExisting.classList.add('selected');
            optBuy.classList.remove('selected');
            optExisting.querySelector('input').checked = true;
            btnCheck.style.display = 'none';
            rowDomain.style.display = 'none';
            label.innerHTML = 'Tên miền của bạn <span style="color:#ef4444;">*</span>';
            helpText.textContent = 'Sau khi hoàn tất đơn, bạn sẽ được cấp cặp Nameserver Cloudflare để trỏ tên miền về.';
            selectedDomainPrice = 0;
            resetDomainCheck();
        }

        recalculateFinalTotal();
    }

    function onDomainInputChange() {
        if (isBuyNewDomain) {
            const currentVal = document.getElementById('domainInput').value.trim();
            if (currentVal !== checkedDomainName) {
                resetDomainCheck();
            }
        }
    }

    function resetDomainCheck() {
        isDomainAvailable = false;
        checkedDomainName = '';
        selectedDomainPrice = 0;
        document.getElementById('domainCheckResult').style.display = 'none';
        document.getElementById('domainCheckResult').innerHTML = '';
        document.getElementById('summaryDomainPrice').textContent = '0đ';
        recalculateFinalTotal();
    }

    function checkDomainAvailability() {
        const domain = document.getElementById('domainInput').value.trim();
        const resBox = document.getElementById('domainCheckResult');
        const btn = document.getElementById('btnCheckDomain');

        if (!domain) {
            resBox.style.display = 'block';
            resBox.innerHTML = '<div style="color:#ef4444; font-size:0.85rem;"><i class="fa-solid fa-circle-exclamation"></i> Vui lòng nhập tên miền trước khi kiểm tra!</div>';
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
        resBox.style.display = 'block';
        resBox.innerHTML = '<div style="color:var(--text-muted, #6b7280); font-size:0.85rem;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tra cứu WHOIS trên DichVuDark.vip...</div>';

        fetch(`/tao-website/check-domain?domain=${encodeURIComponent(domain)}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<span class="iconify" data-icon="ant-design:search-outlined"></span> Kiểm tra';

            if (data.status === 'success') {
                checkedDomainName = data.domain || domain;
                if (data.available) {
                    isDomainAvailable = true;
                    selectedDomainPrice = data.display_price || 0;
                    document.getElementById('summaryDomainPrice').textContent = (data.formatted_price || new Intl.NumberFormat('vi-VN').format(selectedDomainPrice) + 'đ');
                    resBox.innerHTML = `
                        <div style="background:#f0fdf4; border:1px solid #86efac; color:#15803d; padding:8px 12px; border-radius:8px; font-size:0.86rem; display:flex; justify-content:space-between; align-items:center;">
                            <span><i class="fa-solid fa-circle-check"></i> Tên miền <strong>${data.domain}</strong> còn trống!</span>
                            <strong>+${data.formatted_price || new Intl.NumberFormat('vi-VN').format(selectedDomainPrice) + 'đ'}/năm</strong>
                        </div>
                    `;
                    recalculateFinalTotal();
                } else {
                    isDomainAvailable = false;
                    selectedDomainPrice = 0;
                    resBox.innerHTML = `
                        <div style="background:#fef2f2; border:1px solid #fca5a5; color:#b91c1c; padding:8px 12px; border-radius:8px; font-size:0.86rem;">
                            <i class="fa-solid fa-circle-xmark"></i> Tên miền <strong>${data.domain}</strong> đã được đăng ký hoặc không còn khả dụng!
                        </div>
                    `;
                    recalculateFinalTotal();
                }
            } else {
                isDomainAvailable = false;
                selectedDomainPrice = 0;
                resBox.innerHTML = `<div style="color:#ef4444; font-size:0.85rem;"><i class="fa-solid fa-circle-exclamation"></i> ${data.message || 'Lỗi kiểm tra tên miền'}</div>`;
                recalculateFinalTotal();
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<span class="iconify" data-icon="ant-design:search-outlined"></span> Kiểm tra';
            resBox.innerHTML = `<div style="color:#ef4444; font-size:0.85rem;">Lỗi kết nối: ${err.message}</div>`;
        });
    }

    function updatePriceSummary(months, priceMonth, total) {
        selectedDuration = months;
        selectedPricePerMonth = priceMonth;
        selectedWebsiteTotal = total;

        document.getElementById('summaryMonthlyPrice').textContent = new Intl.NumberFormat('vi-VN').format(priceMonth) + 'đ';
        document.getElementById('summaryMonths').textContent = months + ' tháng';
        recalculateFinalTotal();
    }

    function recalculateFinalTotal() {
        const grandTotal = selectedWebsiteTotal + (isBuyNewDomain ? selectedDomainPrice : 0);
        document.getElementById('summaryTotalPrice').textContent = new Intl.NumberFormat('vi-VN').format(grandTotal) + 'đ';
    }

    function submitOrder(e) {
        e.preventDefault();

        const domain = document.getElementById('domainInput').value.trim();

        if (isBuyNewDomain) {
            if (!isDomainAvailable || domain !== checkedDomainName) {
                if (typeof FuiToast !== 'undefined') {
                    FuiToast.error('Vui lòng bấm nút "Kiểm tra" và chọn tên miền còn trống trước khi thanh toán!');
                } else {
                    alert('Vui lòng bấm nút "Kiểm tra" và chọn tên miền còn trống trước khi thanh toán!');
                }
                return;
            }
        }

        const btn = document.getElementById('submitOrderBtn');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang khởi tạo...';

        const formData = new FormData(document.getElementById('orderWebsiteForm'));
        formData.append('duration_months', selectedDuration);

        fetch("{{ route('websites.buy') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = originalText;

            if (data.status === 'success') {
                if (typeof FuiToast !== 'undefined') {
                    FuiToast.success(data.message || 'Đặt thuê website thành công!');
                } else {
                    alert(data.message || 'Đặt thuê website thành công!');
                }
                closeOrderModal();
                setTimeout(() => {
                    window.location.href = data.redirect || "{{ route('websites.my-orders') }}";
                }, 1200);
            } else {
                if (typeof FuiToast !== 'undefined') {
                    FuiToast.error(data.message || 'Có lỗi xảy ra!');
                } else {
                    alert(data.message || 'Có lỗi xảy ra!');
                }
                if (data.need_deposit) {
                    setTimeout(() => {
                        window.location.href = "{{ route('profile.deposit-card') }}";
                    }, 1500);
                }
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = originalText;
            alert('Lỗi kết nối máy chủ: ' + err.message);
        });
    }
</script>
@endpush
