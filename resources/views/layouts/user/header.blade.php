
    <!-- Preloader -->
    <div id="pagePreloader" style="position:fixed;inset:0;z-index:99999;background:rgba(var(--bs-body-bg-rgb,255,255,255),0.7);backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);display:flex;align-items:center;justify-content:center;transition:opacity .3s ease;">
      <span class="antd-spin"><i></i><i></i><i></i><i></i></span>
    </div>
    <style>
      .antd-spin {
        display: inline-block;
        width: 32px;
        height: 32px;
        position: relative;
        animation: antdSpinRotate 1.2s linear infinite;
      }

      .antd-spin i {
        position: absolute;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: var(--bs-primary, #5955D1);
        opacity: .3;
        animation: antdSpinDot 1s ease-in-out infinite;
      }

      .antd-spin i:nth-child(1) {
        top: 0;
        left: 50%;
        margin-left: -5px;
        animation-delay: 0s;
      }

      .antd-spin i:nth-child(2) {
        right: 0;
        top: 50%;
        margin-top: -5px;
        animation-delay: .4s;
      }

      .antd-spin i:nth-child(3) {
        bottom: 0;
        left: 50%;
        margin-left: -5px;
        animation-delay: .8s;
      }

      .antd-spin i:nth-child(4) {
        left: 0;
        top: 50%;
        margin-top: -5px;
        animation-delay: 1.2s;
      }

      @keyframes antdSpinRotate {
        to {
          transform: rotate(360deg);
        }
      }

      @keyframes antdSpinDot {

        0%,
        100% {
          opacity: .3;
          transform: scale(.6);
        }

        50% {
          opacity: 1;
          transform: scale(1);
        }
      }
    </style>
<style>
    /* Robust Navbar Layout */
    .navbar {
        height: 60px !important;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        z-index: 1000;
        background: rgba(255, 255, 255, 0.95);
        border-bottom: 1px solid #f0f0f0;
        backdrop-filter: blur(20px);
    }
    .nav-container {
        max-width: 1280px;
        margin: 0 auto;
        padding: 0 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        height: 60px;
        gap: 8px;
    }
    .nav-brand {
        display: inline-flex;
        align-items: center;
        flex-shrink: 0;
        max-width: 180px;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        text-decoration: none;
    }
    .nav-user {
        flex-shrink: 0;
        display: flex;
        align-items: center;
    }
    @media (min-width: 992px) {
        .nav-toggle {
            display: none !important;
        }
        .nav-links {
            display: flex !important;
            align-items: center !important;
            gap: 2px !important;
            list-style: none !important;
            margin: 0 !important;
            padding: 0 !important;
            flex-wrap: nowrap !important;
            flex-shrink: 1 !important;
        }
        .nav-links li {
            flex-shrink: 0 !important;
            border-bottom: none !important;
        }
        .nav-links a.nav-link-item,
        .nav-links .nav-link-item {
            display: inline-flex !important;
            align-items: center !important;
            gap: 5px !important;
            padding: 6px 10px !important;
            border-radius: 6px !important;
            font-weight: 500 !important;
            font-size: 0.88rem !important;
            white-space: nowrap !important;
            line-height: 1.2 !important;
            text-decoration: none !important;
            transition: all 0.2s ease !important;
        }
        .nav-mega-dropdown.full-width-dropdown {
            position: static !important;
        }
        .nav-mega-dropdown.full-width-dropdown .mega-menu {
            width: 100vw;
            left: 50% !important;
            right: auto !important;
            transform: translateX(-50%) !important;
            border-radius: 0;
            border-left: none;
            border-right: none;
            box-sizing: border-box;
        }
    }
    @media (min-width: 992px) and (max-width: 1240px) {
        .nav-container {
            padding: 0 10px !important;
            gap: 4px !important;
        }
        .nav-links {
            gap: 0px !important;
        }
        .nav-links a.nav-link-item,
        .nav-links .nav-link-item {
            padding: 5px 6px !important;
            font-size: 0.81rem !important;
            gap: 3px !important;
        }
        .nav-brand {
            max-width: 140px !important;
        }
        .ant-header-lang-dropdown {
            margin-right: 6px !important;
        }
        .ant-header-lang-trigger {
            padding: 4px 8px !important;
            font-size: 13px !important;
        }
    }
    @media (max-width: 991px) {
        .nav-toggle {
            display: flex !important;
            order: 3;
        }
        .nav-links {
            display: none !important;
        }
        .nav-links.show {
            display: flex !important;
        }
    }
</style>
<nav class="navbar">
    <div class="nav-container">
        <a href="/" class="nav-brand">
            <img src="{{ config_get('site_logo') ?: '/assets/img/logo.png' }}" 
                 alt="{{ config_get('site_name') }}" 
                 onerror="if(!this.dataset.failed){this.dataset.failed=1;this.src='/assets/img/logo.png';}else{this.style.display='none';var fb=this.parentNode.querySelector('.fallback-brand');if(fb)fb.style.display='inline-flex';}" 
                 width="120" height="32" 
                 style="height:32px; max-width:160px; width:auto; object-fit:contain; display:block;">
            <span class="fallback-brand" style="display:none; align-items:center; gap:8px; font-size:1.1rem; font-weight:800; color:var(--text-color, #1a1a1a); white-space:nowrap;">
                <span class="brand-icon" style="width:28px; height:28px; border-radius:6px; background:linear-gradient(135deg, #dc2626, #991b1b); display:inline-flex; align-items:center; justify-content:center; color:#fff; font-size:0.85rem;"><i class="fa-solid fa-gamepad"></i></span>
                <span>{{ \Illuminate\Support\Str::limit(config_get('site_name', 'THANGCYRUS GAMER'), 30) }}</span>
            </span>
        </a>

        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
            <span></span><span></span><span></span>
        </button>

        <ul class="nav-links" id="navLinks">
            <li class="nav-offcanvas-header">
                <a href="/" class="nav-brand" style="max-width: 160px; overflow: hidden;">
                    <img src="{{ config_get('site_logo') ?: '/assets/img/logo.png' }}" 
                         alt="{{ config_get('site_name') }}" 
                         onerror="if(!this.dataset.failed){this.dataset.failed=1;this.src='/assets/img/logo.png';}else{this.style.display='none';var fb=this.parentNode.querySelector('.fallback-brand');if(fb)fb.style.display='inline-flex';}" 
                         width="105" height="28" 
                         style="height:28px; max-width:130px; width:auto; object-fit:contain; display:block;">
                    <span class="fallback-brand" style="display:none; align-items:center; gap:6px; font-size:0.95rem; font-weight:700; color:var(--text-color, #1a1a1a); white-space:nowrap;">
                        <span class="brand-icon" style="width:24px; height:24px; border-radius:4px; background:linear-gradient(135deg, #dc2626, #991b1b); display:inline-flex; align-items:center; justify-content:center; color:#fff; font-size:0.75rem;"><i class="fa-solid fa-gamepad"></i></span>
                        <span>{{ \Illuminate\Support\Str::limit(config_get('site_name', 'THANGCYRUS GAMER'), 24) }}</span>
                    </span>
                </a>
                <button class="nav-close" id="navClose" onclick="closeNav()" aria-label="Close mobile menu">
                    <span class="iconify" data-icon="ant-design:close-outlined"></span>
                </button>
            </li>
            <li><a href="/" class="nav-link-item"><span class="iconify"
                        data-icon="ant-design:home-outlined"></span> Trang Chủ</a></li>
            <li class="nav-mega-dropdown full-width-dropdown">
                <a href="#" class="nav-link-item"><span class="iconify"
                        data-icon="ant-design:appstore-outlined"></span> Danh Mục <span class="iconify nav-arrow"
                        data-icon="ant-design:down-outlined" style="font-size:0.65rem;"></span></a>
                <div class="mega-menu">
                    <div class="mega-menu-inner">
                        @php
                            $navCategories = \App\Models\Category::where('active', 1)->get();
                            $navRandomCategories = \App\Models\RandomCategory::where('active', 1)->get();
                            $navServices = \App\Models\GameService::where('active', 1)->get();
                        @endphp
                        
                        @foreach($navCategories as $cat)
                        <a href="{{ '/category/' . $cat->slug }}" class="mega-menu-item">
                            @if($cat->thumbnail)
                            <img src="{{ $cat->thumbnail }}" alt="" class="mega-menu-icon">
                            @else
                            <span class="iconify mega-menu-icon-fallback" data-icon="ant-design:folder-outlined"></span>
                            @endif
                            <span>{{ $cat->name }}</span>
                        </a>
                        @endforeach

                        @foreach($navRandomCategories as $cat)
                        <a href="{{ '/random/' . $cat->slug }}" class="mega-menu-item">
                            @if($cat->thumbnail)
                            <img src="{{ $cat->thumbnail }}" alt="" class="mega-menu-icon">
                            @else
                            <span class="iconify mega-menu-icon-fallback" data-icon="ant-design:gift-outlined"></span>
                            @endif
                            <span>{{ $cat->name }}</span>
                        </a>
                        @endforeach

                        @foreach($navServices as $cat)
                        <a href="{{ '/service/' . $cat->slug }}" class="mega-menu-item">
                            @if($cat->thumbnail)
                            <img src="{{ $cat->thumbnail }}" alt="" class="mega-menu-icon">
                            @else
                            <span class="iconify mega-menu-icon-fallback" data-icon="ant-design:tool-outlined"></span>
                            @endif
                            <span>{{ $cat->name }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>
            </li>
            <li class="nav-dropdown">
                <a href="#" class="nav-link-item"><span class="iconify"
                        data-icon="ant-design:dollar-outlined"></span> Nạp Tiền <span class="iconify nav-arrow"
                        data-icon="ant-design:down-outlined" style="font-size:0.65rem;"></span></a>
                <ul class="nav-dropdown-menu">
                    <li><a href="{{ route('profile.deposit-card') }}"><span class="iconify"
                                data-icon="ant-design:credit-card-outlined"></span> Nạp thẻ cào</a></li>
                    <li><a href="{{ route('profile.deposit-atm') }}"><span class="iconify"
                                data-icon="ant-design:bank-outlined"></span> Nạp ngân hàng</a></li>
                    <li><a href="{{ route('profile.deposit-usdt') }}"><span class="iconify"
                                data-icon="ant-design:bank-outlined"></span> Nạp Usdt </a></li>
                </ul>
            </li>
            <li><a href="{{ route('profile.transaction-history') }}" class="nav-link-item"><span class="iconify"
                        data-icon="ant-design:history-outlined"></span> Lịch Sử</a></li>
            <li><a href="{{ route('news.index') }}" class="nav-link-item"><span class="iconify"
                        data-icon="ant-design:notification-outlined"></span> Tin Tức</a></li>
            <li><a href="{{ route('profile.affiliate') }}" class="nav-link-item" style="color: #10b981; font-weight: bold;"><span class="iconify"
                        data-icon="ant-design:link-outlined"></span> Tiếp Thị Liên Kết</a></li>
            <li><a href="{{ route('websites.index') }}" class="nav-link-item {{ request()->routeIs('websites.*') ? 'active' : '' }}"><span class="iconify"
                        data-icon="ant-design:global-outlined"></span> Tạo Website</a></li>
                
        </ul>
    

        <div class="nav-user">
            <!-- Premium Language Switcher Dropdown -->
            <div class="ant-header-lang-dropdown mr-2" style="position: relative; margin-right: 12px;">
                <div class="ant-header-lang-trigger" style="background: var(--bg-card, #fff); border: 1px solid var(--border-color, #e5e7eb); border-radius: 24px; padding: 4px 12px; height: 36px; display: flex; align-items: center; gap: 8px; cursor: pointer; transition: all 0.2s;">
                    <img src="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.4.3/flags/4x3/vn.svg" id="currentLangFlag" alt="Flag" style="width: 20px; height: 14px; border-radius: 2px; object-fit: cover;">
                    <span class="ant-header-lang-text" id="currentLangText" style="font-weight: 600; font-size: 14px; color: var(--text-color, #111827);">VI</span>
                    <span class="iconify" data-icon="ant-design:down-outlined" style="font-size: 12px; color: var(--text-muted, #6b7280);"></span>
                </div>
                <div class="ant-dropdown-menu" id="langDropdownMenu" style="position: absolute; z-index: 1000; width: 140px; right: 0; background: var(--bg-card, #fff); border: 1px solid var(--border-color, #e5e7eb); border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); margin-top: 8px; overflow: hidden; padding: 4px;">
                    <div class="ant-dropdown-item" onclick="setLanguage('vi')" style="padding: 10px 16px; border-radius: 8px; cursor: pointer;">
                        <img src="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.4.3/flags/4x3/vn.svg" alt="VN" style="width: 20px; height: 14px; border-radius: 2px; object-fit: cover; margin-right: 8px;">
                        <span style="font-weight: 500; color: var(--text-color, #111827);">Tiếng Việt</span>
                    </div>
                    <div class="ant-dropdown-item" onclick="setLanguage('en')" style="padding: 10px 16px; border-radius: 8px; cursor: pointer;">
                        <img src="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.4.3/flags/4x3/us.svg" alt="EN" style="width: 20px; height: 14px; border-radius: 2px; object-fit: cover; margin-right: 8px;">
                        <span style="font-weight: 500; color: var(--text-color, #111827);">English</span>
                    </div>
                    <div class="ant-dropdown-item" onclick="setLanguage('zh-CN')" style="padding: 10px 16px; border-radius: 8px; cursor: pointer;">
                        <img src="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.4.3/flags/4x3/cn.svg" alt="ZH" style="width: 20px; height: 14px; border-radius: 2px; object-fit: cover; margin-right: 8px;">
                        <span style="font-weight: 500; color: var(--text-color, #111827);">简体中文</span>
                    </div>
                    <div class="ant-dropdown-item" onclick="setLanguage('ko')" style="padding: 10px 16px; border-radius: 8px; cursor: pointer;">
                        <img src="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.4.3/flags/4x3/kr.svg" alt="KO" style="width: 20px; height: 14px; border-radius: 2px; object-fit: cover; margin-right: 8px;">
                        <span style="font-weight: 500; color: var(--text-color, #111827);">한국어</span>
                    </div>
                    <div class="ant-dropdown-item" onclick="setLanguage('ja')" style="padding: 10px 16px; border-radius: 8px; cursor: pointer;">
                        <img src="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.4.3/flags/4x3/jp.svg" alt="JA" style="width: 20px; height: 14px; border-radius: 2px; object-fit: cover; margin-right: 8px;">
                        <span style="font-weight: 500; color: var(--text-color, #111827);">日本語</span>
                    </div>
                </div>
            </div>

            <button class="theme-toggle" id="themeToggle" title="Chuyển giao diện" aria-label="Toggle dark mode">
                <span class="icon-sun"><span class="iconify" data-icon="ant-design:sun-outlined"></span></span>
                <span class="icon-moon"><span class="iconify" data-icon="ant-design:moon-outlined"></span></span>
            </button>

            @if(Auth::check())
            <div class="nav-avatar-wrapper" id="avatarWrapper">
                <div class="nav-user-profile" onclick="toggleAvatarMenu()"
                    style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                    <div style="text-align:right;">
                        <div style="font-weight:600;font-size:0.85rem;line-height:1.2;">{{ Auth::user()->username }}</div>
                        <div style="font-size:0.75rem;color:#737373;">{{ number_format(Auth::user()->balance) }}đ</div>
                    </div>
                    <button class="nav-avatar" id="avatarBtn" aria-label="User menu">
                        {{ strtoupper(substr(Auth::user()->username, 0, 1)) }}
                    </button>
                </div>
                <div class="avatar-dropdown" id="avatarDropdown">
                    <div class="dropdown-header">
                        <div class="dropdown-name">{{ Auth::user()->username }}</div>
                        <div class="dropdown-email">{{ Auth::user()->email ?? '' }}</div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="/profile" class="dropdown-item">
                        <span class="iconify" data-icon="ant-design:dashboard-outlined"></span> Tài Khoản
                    </a>
                    <a href="{{ route('profile.deposit-card') }}" class="dropdown-item">
                        <span class="iconify" data-icon="ant-design:dollar-outlined"></span> Nạp Tiền
                    </a>
                    <a href="{{ route('websites.my-orders') }}" class="dropdown-item">
                        <span class="iconify" data-icon="ant-design:global-outlined"></span> Website Đã Thuê
                    </a>
                    <a href="{{ route('profile.transaction-history') }}" class="dropdown-item">
                        <span class="iconify" data-icon="ant-design:history-outlined"></span> Lịch Sử Mua
                    </a>
                    @if (Auth::check() && Auth()->user()->role == 'admin')
                    <a href="{{ Route::has('admin.index') ? route('admin.index') : 'http://127.0.0.1:8000/admin' }}" class="dropdown-item" target="_blank">
                        <span class="iconify" data-icon="ant-design:dashboard-outlined"></span> Admin
                    </a>
                    @endif
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}" style="display: inline;width:100%;">
                        @csrf
                        <button type="submit" class="dropdown-item dropdown-logout" style="width:100%;text-align:left;background:transparent;border:none;cursor:pointer;">
                            <span class="iconify" data-icon="ant-design:logout-outlined"></span> Đăng Xuất
                        </button>
                    </form>
                </div>
            </div>
            @else
            <a href="{{ route('login') }}" style="text-decoration:none;font-weight:600;padding:8px 16px;border-radius:8px;background:var(--primary);color:#fff;">Đăng Nhập</a>
            @endif
        </div>
    </div>
</nav>
<div class="nav-overlay" id="navOverlay"></div>
