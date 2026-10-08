<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class WebsiteOrder extends Model
{
    use HasFactory;

    protected $table = 'website_orders';

    protected $fillable = [
        'user_id',
        'order_id',
        'service_id',
        'service_name',
        'service_slug',
        'domain',
        'duration_months',
        'price',
        'original_price',
        'shop_account',
        'shop_password',
        'cloudflare_nameservers',
        'status',
        'status_message',
        'expired_at',
        'meta_data',
    ];

    protected $casts = [
        'cloudflare_nameservers' => 'array',
        'meta_data' => 'array',
        'expired_at' => 'datetime',
        'price' => 'integer',
        'original_price' => 'integer',
        'duration_months' => 'integer',
    ];

    /**
     * Tự động khởi tạo cấu trúc bảng nếu chưa tồn tại trong cơ sở dữ liệu
     */
    protected static function boot()
    {
        parent::boot();

        if (!Schema::hasTable('website_orders')) {
            Schema::create('website_orders', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('order_id')->nullable()->index();
                $table->string('service_id')->nullable();
                $table->string('service_name')->nullable();
                $table->string('service_slug')->nullable()->index();
                $table->string('domain')->index();
                $table->integer('duration_months')->default(1);
                $table->bigInteger('price')->default(0);
                $table->bigInteger('original_price')->default(0);
                $table->string('shop_account')->nullable();
                $table->string('shop_password')->nullable();
                $table->text('cloudflare_nameservers')->nullable();
                $table->string('status')->default('pending')->index();
                $table->text('status_message')->nullable();
                $table->timestamp('expired_at')->nullable();
                $table->text('meta_data')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Label trạng thái hiển thị thân thiện
     */
    public function getStatusBadgeAttribute()
    {
        $status = strtolower($this->status);
        switch ($status) {
            case 'active':
                return '<span class="badge bg-success" style="background:#10b981; color:#fff; padding:4px 10px; border-radius:6px; font-weight:600; font-size:12px;">Đang hoạt động</span>';
            case 'ns_pending':
            case 'cf_pending':
                return '<span class="badge bg-warning" style="background:#f59e0b; color:#fff; padding:4px 10px; border-radius:6px; font-weight:600; font-size:12px;">Chờ trỏ Nameserver</span>';
            case 'processing':
                return '<span class="badge bg-info" style="background:#3b82f6; color:#fff; padding:4px 10px; border-radius:6px; font-weight:600; font-size:12px;">Đang triển khai</span>';
            case 'failed':
                return '<span class="badge bg-danger" style="background:#ef4444; color:#fff; padding:4px 10px; border-radius:6px; font-weight:600; font-size:12px;">Triển khai thất bại</span>';
            case 'expired':
                return '<span class="badge bg-secondary" style="background:#6b7280; color:#fff; padding:4px 10px; border-radius:6px; font-weight:600; font-size:12px;">Đã hết hạn</span>';
            case 'suspended':
                return '<span class="badge bg-dark" style="background:#1f2937; color:#fff; padding:4px 10px; border-radius:6px; font-weight:600; font-size:12px;">Tạm khóa</span>';
            default:
                return '<span class="badge bg-warning" style="background:#f59e0b; color:#fff; padding:4px 10px; border-radius:6px; font-weight:600; font-size:12px;">Đang xử lý</span>';
        }
    }

    /**
     * Đơn giá gia hạn website 1 tháng (theo quy chuẩn tài liệu thegioidev.md)
     */
    public function getMonthlyRenewPriceAttribute()
    {
        $markup = (float)config_get('thegioidev_website_markup', 0);

        // 1. Nếu đã lưu cấu hình price_extend_per_month trong meta_data
        if (!empty($this->meta_data['price_extend_per_month']) && (int)$this->meta_data['price_extend_per_month'] > 0) {
            return (int)$this->meta_data['price_extend_per_month'];
        }

        // 2. Nếu có priceExtend gốc từ TheGioiDev API (Mục 5.4 tài liệu thegioidev.md)
        if (isset($this->meta_data['priceExtend']) && (int)$this->meta_data['priceExtend'] > 0) {
            $basePrice = (int)$this->meta_data['priceExtend'];
            return (int)round($basePrice * (1 + $markup / 100));
        }

        // 3. Nếu mua kèm tên miền, loại bỏ phí mua tên miền ban đầu
        $websitePrice = (int)$this->price;
        if (!empty($this->meta_data['buy_new_domain']) && !empty($this->meta_data['domain_price'])) {
            $websitePrice = max(0, $websitePrice - (int)$this->meta_data['domain_price']);
        }

        $months = max(1, (int)$this->duration_months);
        $monthly = (int)round($websitePrice / $months);

        return max(1000, $monthly);
    }

    /**
     * Đơn giá tháng định dạng tiền tệ VND
     */
    public function getFormattedMonthlyRenewPriceAttribute()
    {
        return number_format($this->monthly_renew_price) . 'đ';
    }

    /**
     * Cặp Nameserver Cloudflare để trỏ tên miền
     */
    public function getDisplayNameserversAttribute()
    {
        // 1. Kiểm tra trực tiếp cột cloudflare_nameservers
        if (!empty($this->cloudflare_nameservers) && is_array($this->cloudflare_nameservers) && count($this->cloudflare_nameservers) > 0) {
            return $this->cloudflare_nameservers;
        }

        // 2. Kiểm tra trong meta_data
        if (!empty($this->meta_data) && is_array($this->meta_data)) {
            if (!empty($this->meta_data['nameServers']) && is_array($this->meta_data['nameServers'])) {
                return $this->meta_data['nameServers'];
            }
            if (!empty($this->meta_data['provisioning']['nameServers']) && is_array($this->meta_data['provisioning']['nameServers'])) {
                return $this->meta_data['provisioning']['nameServers'];
            }
            if (!empty($this->meta_data['provisioning']['cloudflareNameServers']) && is_array($this->meta_data['provisioning']['cloudflareNameServers'])) {
                return $this->meta_data['provisioning']['cloudflareNameServers'];
            }
        }

        return [];
    }
}
