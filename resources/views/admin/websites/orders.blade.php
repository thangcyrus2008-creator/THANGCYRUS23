@extends('layouts.admin.app')
@section('title', 'Quản lý Đơn hàng Website')
@section('content')
<div>
    <div class="page-header">
        <div class="page-block mb-3">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h2 class="mb-0">QUẢN LÝ ĐƠN HÀNG WEBSITE</h2>
                        <p class="text-muted">Danh sách các đơn hàng thuê website qua hệ thống TheGioiDev</p>
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

    <div class="card overflow-hidden shadow-sm border border-dashed">
        <div class="card-body px-0 py-0">
            <!-- Filter form -->
            <form class="p-3 bg-light-subtle border-bottom filter-form" method="GET">
                <div class="row align-items-center g-2">
                    <div class="col-md-2">
                        <select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="15" {{ request('per_page') == 15 ? 'selected' : '' }}>15 / trang</option>
                            <option value="30" {{ request('per_page') == 30 ? 'selected' : '' }}>30 / trang</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 / trang</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">-- Tất cả trạng thái --</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                            <option value="ns_pending" {{ request('status') == 'ns_pending' ? 'selected' : '' }}>Chờ trỏ NS</option>
                            <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Đang triển khai</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Thất bại</option>
                            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Hết hạn</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Tìm theo tên miền, order ID, username..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3 d-flex gap-2 ms-auto">
                        <button type="submit" class="btn btn-sm btn-primary w-100">
                            <i class="ti ti-search me-1"></i> Tìm kiếm
                        </button>
                        <a href="{{ route('admin.websites.orders') }}" class="btn btn-sm btn-light-danger w-100">
                            <i class="ti ti-trash me-1"></i> Bỏ lọc
                        </a>
                    </div>
                </div>
            </form>

            <div class="table-responsive table-border-style">
                <table class="table table-hover table-borderless align-middle mb-0 text-nowrap w-100">
                    <thead class="bg-light-subtle text-muted">
                        <tr>
                            <th>#ID / Ngày tạo</th>
                            <th>Khách hàng</th>
                            <th>Tên miền</th>
                            <th>Mẫu Web</th>
                            <th>Thời hạn / Giá</th>
                            <th>Nameserver</th>
                            <th>Trạng thái</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td>
                                <strong>#{{ $order->id }}</strong><br>
                                <small class="text-muted">{{ $order->created_at ? $order->created_at->format('d/m/Y H:i') : '' }}</small>
                            </td>
                            <td>
                                @if($order->user)
                                <a href="{{ route('admin.users.show', $order->user_id) }}" class="fw-bold text-primary">
                                    {{ $order->user->username }}
                                </a>
                                <br><small class="text-muted">{{ $order->user->email }}</small>
                                @else
                                <span class="text-muted">Đã xóa user</span>
                                @endif
                            </td>
                            <td>
                                <strong class="text-dark">{{ $order->domain }}</strong>
                                <a href="http://{{ $order->domain }}" target="_blank" class="ms-1 text-muted"><i class="ti ti-external-link"></i></a>
                                <br><small class="text-muted">API ID: {{ $order->order_id ?: 'N/A' }}</small>
                            </td>
                            <td>
                                <span>{{ $order->service_name }}</span>
                            </td>
                            <td>
                                <span class="badge bg-light-primary text-primary">{{ $order->duration_months }} tháng</span><br>
                                <strong class="text-danger">{{ number_format($order->price) }}đ</strong>
                            </td>
                            <td>
                                @php
                                    $nsList = $order->cloudflare_nameservers;
                                    if(empty($nsList) && isset($order->meta_data['provisioning']['cloudflareNameServers'])) {
                                        $nsList = $order->meta_data['provisioning']['cloudflareNameServers'];
                                    }
                                @endphp
                                @if(!empty($nsList) && is_array($nsList))
                                    @foreach($nsList as $ns)
                                        <small class="d-block text-secondary"><code>{{ $ns }}</code></small>
                                    @endforeach
                                @else
                                    <span class="text-muted small">Chưa cấp</span>
                                @endif
                            </td>
                            <td>
                                {!! $order->status_badge !!}
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary" title="Kiểm tra NS" onclick="adminCheckNs({{ $order->id }}, this)">
                                        <i class="ti ti-refresh"></i> Check NS
                                    </button>
                                    @if($order->status == 'failed')
                                    <button type="button" class="btn btn-outline-warning" title="Thử lại triển khai" onclick="adminRetry({{ $order->id }}, this)">
                                        <i class="ti ti-repeat"></i> Retry
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="ti ti-inbox fs-2 d-block mb-2"></i>
                                Chưa có đơn hàng tạo website nào!
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-top">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function adminCheckNs(orderId, btn) {
        btn.disabled = true;
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        fetch(`/admin/websites/orders/${orderId}/check-ns`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            alert(data.message || 'Kiểm tra NS hoàn tất!');
            location.reload();
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            alert('Lỗi: ' + err.message);
        });
    }

    function adminRetry(orderId, btn) {
        btn.disabled = true;
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        fetch(`/admin/websites/orders/${orderId}/retry`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            alert(data.message || 'Đã gửi yêu cầu thử lại triển khai!');
            location.reload();
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            alert('Lỗi: ' + err.message);
        });
    }
</script>
@endpush
