<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\MoneyTransaction;
use App\Models\User;
use App\Models\WebsiteOrder;
use App\Services\DichVuDarkService;
use App\Services\TheGioiDevService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebsiteController extends Controller
{
    protected $api;
    protected $domainApi;

    public function __construct(TheGioiDevService $api, DichVuDarkService $domainApi)
    {
        $this->api = $api;
        $this->domainApi = $domainApi;
    }

    /**
     * Làm sạch và chuyển đổi giá trị tiền tệ thành số float
     */
    public static function parsePrice($val): float
    {
        if (is_numeric($val)) {
            return (float)$val;
        }
        if (is_string($val)) {
            $digitsOnly = preg_replace('/[^\d]/', '', $val);
            if ($digitsOnly !== '') {
                return (float)$digitsOnly;
            }
        }
        return 0.0;
    }

    /**
     * Trích xuất và chuẩn hóa bảng giá của mẫu website từ API TheGioiDev
     * Hỗ trợ mọi cấu trúc: minPricePerMonth, pricings, pricing, array, json string, root price
     */
    public static function extractPricing(array $item, float $markup = 0): array
    {
        // 1. Kiểm tra mảng pricings (chi tiết) hoặc pricing / prices
        $rawPricing = $item['pricings'] ?? ($item['pricing'] ?? ($item['prices'] ?? null));

        if (is_string($rawPricing)) {
            $decoded = json_decode($rawPricing, true);
            if (is_array($decoded)) {
                $rawPricing = $decoded;
            }
        }

        $processed = [];
        $minPricePerMonth = 0;

        // Phân tích nếu có danh sách pricing
        if (is_array($rawPricing) && !empty($rawPricing)) {
            foreach ($rawPricing as $key => $p) {
                if (is_string($p)) {
                    $decodedP = json_decode($p, true);
                    if (is_array($decodedP)) {
                        $p = $decodedP;
                    }
                }

                $origMonthPrice = 0.0;
                $duration = 1;
                $origExtendPrice = 0.0;

                if (is_numeric($p) || (is_string($p) && is_numeric(trim($p)))) {
                    $origMonthPrice = self::parsePrice($p);
                    $duration = is_numeric($key) && (int)$key > 0 ? (int)$key : 1;
                    $origExtendPrice = $origMonthPrice;
                } elseif (is_array($p)) {
                    $origMonthPrice = self::parsePrice(
                        $p['pricePerMonth'] ?? 
                        ($p['minPricePerMonth'] ?? 
                        ($p['price'] ?? 
                        ($p['price_per_month'] ?? 
                        ($p['monthly_price'] ?? 
                        ($p['price_month'] ?? 
                        ($p['priceMonth'] ?? 
                        ($p['amount'] ?? 
                        ($p['cost'] ?? 
                        ($p['originalPrice'] ?? 0)))))))))
                    );

                    $duration = (int)(
                        $p['durationMonths'] ?? 
                        ($p['duration_months'] ?? 
                        ($p['months'] ?? 
                        ($p['month'] ?? 
                        ($p['duration'] ?? 
                        (is_numeric($key) && (int)$key > 0 ? (int)$key : 1)))))
                    );
                    if ($duration <= 0) $duration = 1;

                    // Nếu giá tháng bằng 0 nhưng có tổng giá và thời hạn > 0
                    if ($origMonthPrice <= 0) {
                        $totalVal = self::parsePrice($p['totalPrice'] ?? ($p['total_price'] ?? ($p['total'] ?? 0)));
                        if ($totalVal > 0) {
                            $origMonthPrice = round($totalVal / $duration);
                        }
                    }

                    $origExtendPrice = self::parsePrice(
                        $p['priceExtendPerMonth'] ?? 
                        ($p['price_extend_per_month'] ?? 
                        ($p['priceExtend'] ?? 
                        ($p['price_extend'] ?? 
                        ($p['renew_price'] ?? 
                        ($p['renewPrice'] ?? 
                        ($p['priceRenew'] ?? 
                        ($p['extend_price'] ?? 
                        ($p['extendPrice'] ?? $origMonthPrice))))))))
                    );
                    if ($origExtendPrice <= 0) {
                        $origExtendPrice = $origMonthPrice;
                    }
                }

                if ($origMonthPrice > 0) {
                    $userMonthPrice = (int)round($origMonthPrice * (1 + $markup / 100));
                    $userExtendPrice = (int)round($origExtendPrice * (1 + $markup / 100));
                    $totalPrice = $userMonthPrice * $duration;

                    $processed[$duration] = [
                        'durationMonths' => $duration,
                        'originalPricePerMonth' => (int)$origMonthPrice,
                        'pricePerMonth' => $userMonthPrice,
                        'priceExtendPerMonth' => $userExtendPrice,
                        'totalPrice' => $totalPrice,
                    ];

                    if ($minPricePerMonth === 0 || $userMonthPrice < $minPricePerMonth) {
                        $minPricePerMonth = $userMonthPrice;
                    }
                }
            }
        }

        // 2. Fallback giá ở cấp gốc (root-level price) - ĐẶC BIỆT LÀ minPricePerMonth TỪ LIST API
        $rootOrigPrice = self::parsePrice(
            $item['minPricePerMonth'] ?? 
            ($item['min_price_per_month'] ?? 
            ($item['pricePerMonth'] ?? 
            ($item['price'] ?? 
            ($item['price_month'] ?? 
            ($item['price_per_month'] ?? 
            ($item['originalPrice'] ?? 
            ($item['original_price'] ?? 
            ($item['monthly_price'] ?? 
            ($item['amount'] ?? 
            ($item['salePrice'] ?? 
            ($item['cost'] ?? 0)))))))))))
        );

        if ($rootOrigPrice > 0) {
            $userMonthPrice = (int)round($rootOrigPrice * (1 + $markup / 100));
            if ($minPricePerMonth === 0 || $userMonthPrice < $minPricePerMonth) {
                $minPricePerMonth = $userMonthPrice;
            }

            // Lấy các mốc từ durationOptions nếu có
            $durations = !empty($item['durationOptions']) && is_array($item['durationOptions'])
                ? $item['durationOptions']
                : [1, 3, 6, 12];

            // Nếu chỉ có 1 mốc, bổ sung thêm 3, 6, 12 tháng
            if (count($durations) === 1 && in_array(1, $durations)) {
                $durations = [1, 3, 6, 12];
            }

            foreach ($durations as $m) {
                $m = (int)$m;
                if ($m <= 0) continue;
                if (!isset($processed[$m])) {
                    $processed[$m] = [
                        'durationMonths' => $m,
                        'originalPricePerMonth' => (int)$rootOrigPrice,
                        'pricePerMonth' => $userMonthPrice,
                        'priceExtendPerMonth' => $userMonthPrice,
                        'totalPrice' => $userMonthPrice * $m,
                    ];
                }
            }
        }

        // 3. Nếu chỉ có duy nhất 1 mốc thời hạn, bổ sung thêm các mốc phổ biến 3, 6, 12 tháng
        if (count($processed) === 1 && isset($processed[1])) {
            $base = $processed[1];
            foreach ([3, 6, 12] as $m) {
                if (!isset($processed[$m])) {
                    $processed[$m] = [
                        'durationMonths' => $m,
                        'originalPricePerMonth' => $base['originalPricePerMonth'],
                        'pricePerMonth' => $base['pricePerMonth'],
                        'priceExtendPerMonth' => $base['priceExtendPerMonth'],
                        'totalPrice' => $base['pricePerMonth'] * $m,
                    ];
                }
            }
        }

        // Sắp xếp theo durationMonths tăng dần
        $processedList = array_values($processed);
        usort($processedList, function ($a, $b) {
            return $a['durationMonths'] <=> $b['durationMonths'];
        });

        // Nếu vẫn không có giá nào (0đ), kiểm tra fallback tối thiểu
        if ($minPricePerMonth === 0 && !empty($processedList)) {
            $minPricePerMonth = $processedList[0]['pricePerMonth'] ?? 0;
        }

        return [
            'pricing' => $processedList,
            'displayPrice' => $minPricePerMonth
        ];
    }

    /**
     * Danh sách các mẫu website mở bán
     */
    public function index(Request $request)
    {
        $status = config_get('thegioidev_website_status', 1);
        $notice = config_get('thegioidev_website_notice', '');
        $markup = (float)config_get('thegioidev_website_markup', 0);
        $domainMarkup = (float)config_get('dichvudark_domain_markup', 0);

        $page = (int)$request->get('page', 1);
        $search = $request->get('search', '');
        $categoryId = $request->get('category_id', '');

        $params = [
            'page' => $page,
            'limit' => 24,
            'withMeta' => 'true'
        ];
        if (!empty($search)) $params['search'] = $search;
        if (!empty($categoryId)) $params['categoryId'] = $categoryId;

        $apiRes = $this->api->getWebsites($params);

        $templates = [];
        $categories = [];
        $pagination = [
            'page' => 1,
            'limit' => 24,
            'total' => 0,
            'totalPages' => 1
        ];

        // Kiểm tra xem phản hồi API có thành công hay không theo nhiều định dạng
        $isSuccess = false;
        if (isset($apiRes['error']) && ($apiRes['error'] === 0 || $apiRes['error'] === '0' || $apiRes['error'] === false)) {
            $isSuccess = true;
        } elseif (isset($apiRes['status']) && strtolower($apiRes['status']) === 'success') {
            $isSuccess = true;
        } elseif (isset($apiRes['success']) && $apiRes['success']) {
            $isSuccess = true;
        } elseif (isset($apiRes['code']) && $apiRes['code'] == 200) {
            $isSuccess = true;
        }

        $apiError = null;
        if (!$isSuccess) {
            $apiError = $apiRes['msg'] ?? ($apiRes['message'] ?? 'Không thể tải danh sách mẫu website từ API TheGioiDev');
        }

        if ($isSuccess && isset($apiRes['data'])) {
            $data = $apiRes['data'];

            // Xử lý danh sách items linh hoạt
            $items = [];
            if (isset($data['items']) && is_array($data['items'])) {
                $items = $data['items'];
            } elseif (isset($data['data']) && is_array($data['data'])) {
                $items = $data['data'];
            } elseif (isset($data['websites']) && is_array($data['websites'])) {
                $items = $data['websites'];
            } elseif (is_array($data) && !isset($data['categories'])) {
                $items = $data;
            }

            $categories = $data['categories'] ?? ($apiRes['categories'] ?? []);
            $pagination = $data['pagination'] ?? ($apiRes['pagination'] ?? $pagination);

            // Chuẩn hóa và tính giá cho từng template bằng extractPricing
            foreach ($items as $item) {
                if (!is_array($item)) continue;

                $pricingData = self::extractPricing($item, $markup);
                $item['calculatedPricing'] = $pricingData['pricing'];
                $item['displayPrice'] = $pricingData['displayPrice'];

                $templates[] = $item;
            }
        }

        return view('user.website.index', [
            'title' => 'Tạo Website Trọn Gói - Chuẩn SEO & Tự Động',
            'templates' => $templates,
            'categories' => $categories,
            'pagination' => $pagination,
            'notice' => $notice,
            'status' => $status,
            'markup' => $markup,
            'domainMarkup' => $domainMarkup,
            'currentSearch' => $search,
            'currentCategory' => $categoryId,
            'apiError' => $apiError
        ]);
    }

    /**
     * Tra cứu & Kiểm tra khả dụng tên miền qua DichVuDark.vip API
     */
    public function checkDomain(Request $request)
    {
        $domain = trim($request->get('domain', ''));
        if (empty($domain)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vui lòng nhập tên miền cần kiểm tra!'
            ], 400);
        }

        $res = $this->domainApi->checkDomain($domain);

        if (isset($res['status']) && $res['status'] === 'success') {
            $markup = (float)config_get('dichvudark_domain_markup', 0);
            $origPrice = (int)($res['price'] ?? 0);
            $userPrice = (int)round($origPrice * (1 + $markup / 100));

            $res['original_price'] = $origPrice;
            $res['display_price'] = $userPrice;
            $res['formatted_price'] = number_format($userPrice) . 'đ';
            $res['markup_percent'] = $markup;

            return response()->json($res);
        }

        return response()->json([
            'status' => 'error',
            'message' => $res['message'] ?? 'Không thể kiểm tra trạng thái tên miền!'
        ], 400);
    }

    /**
     * Lấy chi tiết mẫu website (JSON)
     */
    public function show($slug)
    {
        $markup = (float)config_get('thegioidev_website_markup', 0);
        $res = $this->api->getWebsiteDetail($slug);

        $isSuccess = (isset($res['error']) && ($res['error'] === 0 || $res['error'] === '0' || $res['error'] === false))
            || (isset($res['status']) && strtolower($res['status']) === 'success');

        if ($isSuccess && isset($res['data'])) {
            $item = $res['data'];
            $pricingData = self::extractPricing($item, $markup);
            $item['calculatedPricing'] = $pricingData['pricing'];
            $item['displayPrice'] = $pricingData['displayPrice'];

            return response()->json(['status' => 'success', 'data' => $item]);
        }

        return response()->json([
            'status' => 'error',
            'message' => $res['msg'] ?? ($res['message'] ?? 'Không tìm thấy thông tin mẫu website!')
        ], 404);
    }

    /**
     * Mua / Tạo website mới (Safe Transaction Flow + Hỗ trợ mua tên miền mới DichVuDark)
     */
    public function buy(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vui lòng đăng nhập để thực hiện đặt thuê website!'
            ], 401);
        }

        $request->validate([
            'service_id' => 'required',
            'domain' => 'required|string|min:3|max:100',
            'duration_months' => 'required|integer|in:1,3,6,12,24,36',
            'shop_account' => 'nullable|string|max:50',
            'shop_password' => 'nullable|string|max:100',
            'buy_new_domain' => 'nullable|in:0,1',
        ], [
            'service_id.required' => 'Chưa chọn mẫu website!',
            'domain.required' => 'Vui lòng nhập tên miền của bạn!',
            'duration_months.required' => 'Vui lòng chọn thời hạn thuê!',
        ]);

        $serviceId = trim($request->service_id);
        $domain = strtolower(trim($request->domain));
        // Loại bỏ http://, https://, dấu gạch chéo cuối
        $domain = preg_replace('#^https?://#', '', $domain);
        $domain = rtrim($domain, '/');

        $durationMonths = (int)$request->duration_months;
        $shopAccount = trim((string)$request->input('shop_account')) ?: 'admin';
        $shopPassword = trim((string)$request->input('shop_password')) ?: ('Admin@' . \Illuminate\Support\Str::random(8));
        $buyNewDomain = (int)$request->input('buy_new_domain', 0) === 1;

        // 1. Lấy chi tiết mẫu website từ TheGioiDev API để xác định giá gốc chính xác
        $serviceSlug = $request->input('service_slug') ?: $serviceId;
        $detailRes = $this->api->getWebsiteDetail($serviceSlug);

        $isDetailSuccess = (isset($detailRes['error']) && ($detailRes['error'] === 0 || $detailRes['error'] === '0' || $detailRes['error'] === false))
            || (isset($detailRes['status']) && strtolower($detailRes['status']) === 'success');

        if (!$isDetailSuccess && $serviceSlug !== $serviceId) {
            $detailRes = $this->api->getWebsiteDetail($serviceId);
            $isDetailSuccess = (isset($detailRes['error']) && ($detailRes['error'] === 0 || $detailRes['error'] === '0' || $detailRes['error'] === false))
                || (isset($detailRes['status']) && strtolower($detailRes['status']) === 'success');
        }

        $template = ($isDetailSuccess && isset($detailRes['data'])) ? $detailRes['data'] : [];
        $serviceName = $template['serviceName'] ?? ($template['name'] ?? 'Website Marketplace');
        $resolvedSlug = $template['slug'] ?? $serviceSlug;

        // Trích xuất cấu hình giá một cách chuẩn xác
        $markup = (float)config_get('thegioidev_website_markup', 0);
        $pricingInfo = self::extractPricing($template, $markup);

        $foundPricing = null;
        foreach ($pricingInfo['pricing'] as $p) {
            if ((int)$p['durationMonths'] === $durationMonths) {
                $foundPricing = $p;
                break;
            }
        }

        if ($foundPricing) {
            $websitePrice = (int)$foundPricing['totalPrice'];
            $originalTotal = (int)($foundPricing['originalPricePerMonth'] * $durationMonths);
        } else {
            $baseMonthPrice = (int)$pricingInfo['displayPrice'];
            if ($baseMonthPrice > 0) {
                $websitePrice = $baseMonthPrice * $durationMonths;
                $originalTotal = (int)round($websitePrice / max(1, (1 + $markup / 100)));
            } else {
                $websitePrice = 150000 * $durationMonths;
                $originalTotal = 150000 * $durationMonths;
            }
        }

        // 2. Nếu khách chọn mua kèm tên miền mới qua DichVuDark:
        $domainPrice = 0;
        $domainOrigPrice = 0;
        if ($buyNewDomain) {
            $checkRes = $this->domainApi->checkDomain($domain);
            if (!isset($checkRes['status']) || $checkRes['status'] !== 'success' || empty($checkRes['available'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Tên miền '{$domain}' đã được đăng ký hoặc không còn khả dụng trên hệ thống quốc tế. Vui lòng chọn tên miền khác!"
                ], 400);
            }

            $domainMarkup = (float)config_get('dichvudark_domain_markup', 0);
            $domainOrigPrice = (int)($checkRes['price'] ?? 0);
            $domainPrice = (int)round($domainOrigPrice * (1 + $domainMarkup / 100));
        }

        $totalOrderPrice = $websitePrice + $domainPrice;
        $user = Auth::user();

        // 3. Kiểm tra số dư ví
        if ($user->balance < $totalOrderPrice) {
            return response()->json([
                'status' => 'error',
                'message' => 'Số dư không đủ! Cần ' . number_format($totalOrderPrice) . 'đ (Web: ' . number_format($websitePrice) . 'đ' . ($buyNewDomain ? ' + Tên miền: ' . number_format($domainPrice) . 'đ' : '') . '), số dư hiện có: ' . number_format($user->balance) . 'đ. Vui lòng nạp thêm tiền.',
                'need_deposit' => true
            ], 400);
        }

        // 4. Safe Transaction: Trừ tiền tạm ứng & tạo nhật ký giao dịch
        DB::beginTransaction();
        try {
            $balanceBefore = $user->balance;
            $balanceAfter = $balanceBefore - $totalOrderPrice;

            User::where('id', $user->id)->update(['balance' => $balanceAfter]);

            $txDesc = 'Thuê Website: ' . $domain . ' (' . $serviceName . ' - ' . $durationMonths . ' tháng)'
                    . ($buyNewDomain ? ' + Đăng ký tên miền mới (' . number_format($domainPrice) . 'đ)' : '');

            $tx = MoneyTransaction::create([
                'user_id' => $user->id,
                'type' => 'purchase',
                'amount' => -$totalOrderPrice,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => $txDesc,
                'reference_id' => null
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi trừ tiền thuê website: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi khi xử lý trừ tiền ví tài khoản!'
            ], 500);
        }

        // 5. Gửi yêu cầu khởi tạo website lên TheGioiDev API
        $apiBuyData = [
            'serviceId' => $serviceId,
            'domainName' => $domain,
            'durationMonths' => $durationMonths,
            'shopAccount' => $shopAccount,
            'shopPassword' => $shopPassword,
        ];

        $buyRes = $this->api->buyWebsite($apiBuyData);

        // 6. Xử lý phản hồi từ TheGioiDev API
        if (isset($buyRes['error']) && $buyRes['error'] === 0 && isset($buyRes['data'])) {
            $resData = $buyRes['data'];
            $orderApiId = $resData['orderId'] ?? ($resData['id'] ?? null);

            // Lấy thêm thông tin chi tiết đơn hàng (Nameserver & cấu hình gia hạn)
            $cfNs = null;
            $orderDetail = [];
            if ($orderApiId) {
                $orderDetailRes = $this->api->getWebsiteOrderDetail($orderApiId);
                if (isset($orderDetailRes['data'])) {
                    $orderDetail = $orderDetailRes['data'];
                    if (!empty($orderDetail['nameServers'])) {
                        $cfNs = $orderDetail['nameServers'];
                    } elseif (!empty($orderDetail['provisioning']['nameServers'])) {
                        $cfNs = $orderDetail['provisioning']['nameServers'];
                    } elseif (!empty($orderDetail['provisioning']['cloudflareNameServers'])) {
                        $cfNs = $orderDetail['provisioning']['cloudflareNameServers'];
                    }
                }
            }

            $domainRegistered = false;
            $domainRegisterMessage = '';

            // 7. Nếu mua kèm tên miền mới: Gọi DichVuDark API để mua & trỏ NS Cloudflare
            if ($buyNewDomain) {
                $ns1 = (!empty($cfNs) && isset($cfNs[0])) ? $cfNs[0] : 'duke.ns.cloudflare.com';
                $ns2 = (!empty($cfNs) && isset($cfNs[1])) ? $cfNs[1] : 'uma.ns.cloudflare.com';

                $domainBuyRes = $this->domainApi->buyDomain($domain, 1, $ns1, $ns2);

                if (isset($domainBuyRes['status']) && $domainBuyRes['status'] === 'success') {
                    $domainRegistered = true;
                    $domainRegisterMessage = 'Đã đăng ký tên miền thành công và trỏ cặp Cloudflare Nameserver!';
                    
                    // Kích hoạt ngay Check NS trên TheGioiDev để hệ thống tự động triển khai mã nguồn
                    if ($orderApiId) {
                        try {
                            $this->api->checkWebsiteNs($orderApiId);
                        } catch (\Exception $e) {
                            Log::warning('Auto check NS trigger error: ' . $e->getMessage());
                        }
                    }
                } else {
                    // Mua tên miền thất bại -> Tự động hoàn lại số tiền tên miền cho khách!
                    $domainErrMsg = $domainBuyRes['message'] ?? ($domainBuyRes['msg'] ?? 'Lỗi từ nhà cung cấp tên miền');
                    Log::error("Mua tên miền {$domain} thất bại: " . $domainErrMsg);

                    DB::beginTransaction();
                    try {
                        $u = User::find($user->id);
                        $refBefore = $u->balance;
                        $refAfter = $refBefore + $domainPrice;
                        $u->update(['balance' => $refAfter]);

                        MoneyTransaction::create([
                            'user_id' => $user->id,
                            'type' => 'refund',
                            'amount' => $domainPrice,
                            'balance_before' => $refBefore,
                            'balance_after' => $refAfter,
                            'description' => "Hoàn tiền đăng ký tên miền {$domain} thất bại: {$domainErrMsg}",
                            'reference_id' => null
                        ]);

                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                    }

                    $domainRegisterMessage = "Website khởi tạo thành công, tuy nhiên việc tự động đăng ký tên miền thất bại ({$domainErrMsg}). Đã hoàn lại " . number_format($domainPrice) . "đ vào ví của bạn. Vui lòng tự trỏ tên miền của bạn theo cặp Nameserver được cấp.";
                }
            }

            // Tính đơn giá gia hạn hàng tháng chuẩn xác (theo Mục 5.4 thegioidev.md)
            $rawPriceExtend = isset($orderDetail['priceExtend']) ? (int)$orderDetail['priceExtend'] : 0;
            $calculatedRenewPerMonth = $rawPriceExtend > 0
                ? (int)round($rawPriceExtend * (1 + $markup / 100))
                : (int)round($websitePrice / max(1, $durationMonths));

            // Lưu đơn hàng vào cơ sở dữ liệu
            $order = WebsiteOrder::create([
                'user_id' => $user->id,
                'order_id' => $orderApiId,
                'service_id' => $serviceId,
                'service_name' => $resData['serviceName'] ?? $serviceName,
                'service_slug' => $serviceSlug,
                'domain' => $domain,
                'duration_months' => $durationMonths,
                'price' => $domainRegistered ? $totalOrderPrice : $websitePrice,
                'original_price' => ($resData['total'] ?? $originalTotal) + ($domainRegistered ? $domainOrigPrice : 0),
                'shop_account' => $shopAccount,
                'shop_password' => $shopPassword,
                'cloudflare_nameservers' => $cfNs,
                'status' => $resData['status'] ?? 'ns_pending',
                'status_message' => $domainRegistered
                    ? 'Tên miền đã được mua và trỏ tự động về máy chủ website.'
                    : 'Đã tạo đơn thành công, vui lòng trỏ cặp Nameserver Cloudflare để hệ thống tự động cài đặt.',
                'expired_at' => now()->addMonths($durationMonths),
                'meta_data' => array_merge(
                    (array)$resData,
                    (array)$orderDetail,
                    [
                        'buy_new_domain' => $buyNewDomain,
                        'domain_registered' => $domainRegistered,
                        'domain_price' => $domainPrice,
                        'website_price' => $websitePrice,
                        'priceExtend' => $rawPriceExtend,
                        'price_extend_per_month' => $calculatedRenewPerMonth
                    ]
                )
            ]);

            // Cập nhật reference_id cho transaction
            if (isset($tx)) {
                $tx->update(['reference_id' => $order->id]);
            }

            $successMsg = $buyNewDomain
                ? ($domainRegistered ? "Khởi tạo Website & Đăng ký tên miền '{$domain}' thành công 100%! Website đang được kích hoạt tự động." : $domainRegisterMessage)
                : 'Đặt thuê website thành công! Vui lòng trỏ cặp Nameserver được cấp để kích hoạt website.';

            return response()->json([
                'status' => 'success',
                'message' => $successMsg,
                'order_id' => $order->id,
                'redirect' => route('websites.my-orders')
            ]);
        }

        // 8. Nếu tạo website thất bại -> Tự động hoàn lại 100% tiền đã trừ
        $errorMsg = $buyRes['msg'] ?? ($buyRes['message'] ?? 'Nhà cung cấp từ chối yêu cầu khởi tạo website!');

        DB::beginTransaction();
        try {
            $userCurrent = User::find($user->id);
            $refundBefore = $userCurrent->balance;
            $refundAfter = $refundBefore + $totalOrderPrice;

            $userCurrent->update(['balance' => $refundAfter]);

            MoneyTransaction::create([
                'user_id' => $user->id,
                'type' => 'refund',
                'amount' => $totalOrderPrice,
                'balance_before' => $refundBefore,
                'balance_after' => $refundAfter,
                'description' => 'Hoàn tiền thuê website ' . $domain . ' thất bại: ' . $errorMsg,
                'reference_id' => null
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi hoàn tiền thuê website: ' . $e->getMessage());
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Khởi tạo thất bại: ' . $errorMsg . '. Tiền đã được hoàn lại ví của bạn!'
        ], 400);
    }

    /**
     * Danh sách website đã thuê của người dùng
     */
    public function myOrders(Request $request)
    {
        $orders = WebsiteOrder::where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        // Tự động kiểm tra và đồng bộ Nameserver / Order ID cho các đơn hàng
        foreach ($orders as $order) {
            // 1. Nếu đơn hàng chưa có order_id, tự động tra cứu từ TheGioiDev API theo tên miền
            if (empty($order->order_id) && !empty($order->domain)) {
                try {
                    $ordersRes = $this->api->getWebsiteOrders(['search' => $order->domain, 'limit' => 5]);
                    $items = $ordersRes['data']['items'] ?? ($ordersRes['data'] ?? []);
                    if (is_array($items)) {
                        foreach ($items as $item) {
                            $itemDomain = $item['domainName'] ?? ($item['domain'] ?? '');
                            if (strtolower(trim($itemDomain)) === strtolower(trim($order->domain))) {
                                $foundId = $item['orderId'] ?? ($item['id'] ?? ($item['_id'] ?? null));
                                if ($foundId) {
                                    $order->update([
                                        'order_id' => $foundId,
                                        'status' => $item['status'] ?? $order->status,
                                        'meta_data' => array_merge((array)$order->meta_data, (array)$item)
                                    ]);
                                    break;
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("Auto sync order {$order->id} order_id error: " . $e->getMessage());
                }
            }

            // 2. Nếu đã có order_id nhưng chưa có Nameserver, đồng bộ chi tiết đơn hàng
            if (!empty($order->order_id) && empty($order->display_nameservers)) {
                try {
                    $detailRes = $this->api->getWebsiteOrderDetail($order->order_id);
                    if (isset($detailRes['data'])) {
                        $apiData = $detailRes['data'];
                        $ns = !empty($apiData['nameServers']) ? $apiData['nameServers']
                            : (!empty($apiData['provisioning']['nameServers']) ? $apiData['provisioning']['nameServers']
                            : (!empty($apiData['provisioning']['cloudflareNameServers']) ? $apiData['provisioning']['cloudflareNameServers'] : null));

                        $order->update([
                            'status' => $apiData['status'] ?? $order->status,
                            'cloudflare_nameservers' => $ns ?: $order->cloudflare_nameservers,
                            'meta_data' => array_merge((array)$order->meta_data, $apiData)
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning("Auto sync order {$order->id} NS error: " . $e->getMessage());
                }
            }
        }

        return view('user.website.orders', [
            'title' => 'Website Đã Thuê',
            'orders' => $orders
        ]);
    }

    /**
     * Kiểm tra Nameserver & Kích hoạt triển khai (Check NS)
     */
    public function checkNs(Request $request, $id)
    {
        $order = WebsiteOrder::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (empty($order->order_id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Đơn hàng không có mã Order ID hợp lệ từ nhà cung cấp!'
            ], 400);
        }

        // 1. Gọi lệnh Check NS trên TheGioiDev API
        $res = $this->api->checkWebsiteNs($order->order_id);

        // 2. Đồng bộ lại chi tiết đơn hàng (lấy đúng nameServers)
        $detailRes = $this->api->getWebsiteOrderDetail($order->order_id);
        if (isset($detailRes['data'])) {
            $apiData = $detailRes['data'];
            $ns = !empty($apiData['nameServers']) ? $apiData['nameServers']
                : (!empty($apiData['provisioning']['nameServers']) ? $apiData['provisioning']['nameServers']
                : (!empty($apiData['provisioning']['cloudflareNameServers']) ? $apiData['provisioning']['cloudflareNameServers'] : null));

            $order->update([
                'status' => $apiData['status'] ?? $order->status,
                'cloudflare_nameservers' => $ns ?: $order->cloudflare_nameservers,
                'meta_data' => array_merge((array)$order->meta_data, $apiData)
            ]);
        }

        $currentNs = $order->fresh()->display_nameservers;

        if (isset($res['error']) && $res['error'] === 0) {
            return response()->json([
                'status' => 'success',
                'message' => $res['message'] ?? 'Nameserver đã được nhận diện! Hệ thống đang tiến hành cài đặt website.',
                'nameservers' => $currentNs,
                'order' => $order
            ]);
        }

        $failMsg = $res['msg'] ?? ($res['message'] ?? 'Kiểm tra NS chưa hoàn tất. Vui lòng đảm bảo bạn đã lưu cặp Nameserver tại nhà cung cấp tên miền!');
        return response()->json([
            'status' => 'error',
            'message' => $failMsg,
            'nameservers' => $currentNs,
            'order' => $order
        ]);
    }

    /**
     * Thử lại triển khai website khi bị lỗi (Retry Deploy)
     */
    public function retry(Request $request, $id)
    {
        $order = WebsiteOrder::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (empty($order->order_id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Đơn hàng chưa có mã Order ID!'
            ], 400);
        }

        $res = $this->api->retryWebsiteDeploy($order->order_id);

        if (isset($res['error']) && $res['error'] === 0) {
            $order->update(['status' => 'processing']);
            return response()->json([
                'status' => 'success',
                'message' => 'Đã gửi yêu cầu thử lại cài đặt website vào hàng đợi xử lý!'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => $res['msg'] ?? ($res['message'] ?? 'Không thể gửi yêu cầu thử lại cài đặt!')
        ], 400);
    }

    /**
     * Gia hạn website đã thuê (Mục 5.4 tài liệu thegioidev.md)
     */
    public function renew(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vui lòng đăng nhập để thực hiện gia hạn!'
            ], 401);
        }

        $order = WebsiteOrder::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $request->validate([
            'months' => 'required|integer|min:1|max:36'
        ], [
            'months.required' => 'Vui lòng chọn thời gian gia hạn!',
            'months.min' => 'Thời gian gia hạn tối thiểu 1 tháng!',
            'months.max' => 'Thời gian gia hạn tối đa 36 tháng (theo Mục 5.4 thegioidev.md)!'
        ]);

        // Nếu order_id chưa có, tự động tra cứu từ TheGioiDev API theo tên miền
        if (empty($order->order_id) && !empty($order->domain)) {
            try {
                $ordersRes = $this->api->getWebsiteOrders(['search' => $order->domain, 'limit' => 5]);
                $items = $ordersRes['data']['items'] ?? ($ordersRes['data'] ?? []);
                if (is_array($items)) {
                    foreach ($items as $item) {
                        $itemDomain = $item['domainName'] ?? ($item['domain'] ?? '');
                        if (strtolower(trim($itemDomain)) === strtolower(trim($order->domain))) {
                            $foundId = $item['orderId'] ?? ($item['id'] ?? ($item['_id'] ?? null));
                            if ($foundId) {
                                $order->update([
                                    'order_id' => $foundId,
                                    'status' => $item['status'] ?? $order->status,
                                    'meta_data' => array_merge((array)$order->meta_data, (array)$item)
                                ]);
                                break;
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning("Order {$order->id} auto sync order_id for renew failed: " . $e->getMessage());
            }
        }

        if (empty($order->order_id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Đơn hàng chưa có mã đối tác TheGioiDev để thực hiện gia hạn! Vui lòng bấm "Kiểm Tra NS & Kích Hoạt" để đồng bộ đơn hàng trước.'
            ], 400);
        }

        $months = (int)$request->months;

        // Tính phí gia hạn chuẩn xác theo Mục 5.4 tài liệu thegioidev.md (priceExtend + markup, tách biệt phí tên miền)
        $monthPrice = (int)$order->monthly_renew_price;
        $totalRenewPrice = $monthPrice * $months;

        $user = Auth::user();
        if ($user->balance < $totalRenewPrice) {
            return response()->json([
                'status' => 'error',
                'message' => 'Số dư không đủ! Cần ' . number_format($totalRenewPrice) . 'đ để gia hạn ' . $months . ' tháng (' . number_format($monthPrice) . 'đ/tháng). Số dư ví hiện có: ' . number_format($user->balance) . 'đ.',
                'need_deposit' => true
            ], 400);
        }

        // 1. Trừ tiền tạm ứng (Safe Transaction Flow)
        DB::beginTransaction();
        try {
            $u = User::where('id', $user->id)->lockForUpdate()->first();
            if ($u->balance < $totalRenewPrice) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Số dư không đủ để thực hiện gia hạn!'
                ], 400);
            }

            $before = $u->balance;
            $after = $before - $totalRenewPrice;
            $u->update(['balance' => $after]);

            $tx = MoneyTransaction::create([
                'user_id' => $user->id,
                'type' => 'purchase',
                'amount' => -$totalRenewPrice,
                'balance_before' => $before,
                'balance_after' => $after,
                'description' => 'Gia hạn Website ' . $order->domain . ' thêm ' . $months . ' tháng (' . number_format($monthPrice) . 'đ/tháng)',
                'reference_id' => $order->id
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi trừ tiền gia hạn website: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Lỗi trừ tiền gia hạn!'], 500);
        }

        // 2. Gửi yêu cầu gia hạn lên TheGioiDev API (POST /api/agency/websites/renew)
        $renewRes = $this->api->renewWebsite($order->order_id, $months);

        $isSuccess = (isset($renewRes['error']) && ($renewRes['error'] === 0 || $renewRes['error'] === '0' || $renewRes['error'] === false))
            || (isset($renewRes['status']) && strtolower($renewRes['status']) === 'success');

        if ($isSuccess) {
            $resData = $renewRes['data'] ?? [];

            // Xác định thời hạn hết hạn mới
            $newExpired = null;
            if (!empty($resData['expiredDate'])) {
                $newExpired = date('Y-m-d H:i:s', strtotime($resData['expiredDate']));
            } elseif (!empty($resData['newExpired'])) {
                $newExpired = date('Y-m-d H:i:s', (int)$resData['newExpired']);
            } elseif (!empty($resData['expired'])) {
                $newExpired = date('Y-m-d H:i:s', (int)$resData['expired']);
            } else {
                $baseDate = ($order->expired_at && $order->expired_at->isFuture()) ? $order->expired_at : now();
                $newExpired = $baseDate->copy()->addMonths($months)->format('Y-m-d H:i:s');
            }

            $meta = (array)$order->meta_data;
            $meta['last_renew'] = [
                'months' => $months,
                'cost' => $totalRenewPrice,
                'renewed_at' => now()->toIso8601String(),
                'api_response' => $resData
            ];

            $order->update([
                'expired_at' => $newExpired,
                'status' => $resData['status'] ?? 'active',
                'duration_months' => (int)$order->duration_months + $months,
                'meta_data' => $meta
            ]);

            return response()->json([
                'status' => 'success',
                'message' => $renewRes['message'] ?? ('Gia hạn website ' . $order->domain . ' thành công thêm ' . $months . ' tháng!'),
                'new_expired' => date('d/m/Y H:i', strtotime($newExpired)),
                'status_badge' => $order->fresh()->status_badge
            ]);
        }

        // 3. Nếu thất bại -> Hoàn lại 100% tiền vào ví người dùng
        $failMsg = $renewRes['msg'] ?? ($renewRes['message'] ?? 'Nhà cung cấp từ chối yêu cầu gia hạn');

        DB::beginTransaction();
        try {
            $u = User::where('id', $user->id)->lockForUpdate()->first();
            $refBefore = $u->balance;
            $refAfter = $refBefore + $totalRenewPrice;
            $u->update(['balance' => $refAfter]);

            MoneyTransaction::create([
                'user_id' => $user->id,
                'type' => 'refund',
                'amount' => $totalRenewPrice,
                'balance_before' => $refBefore,
                'balance_after' => $refAfter,
                'description' => 'Hoàn tiền gia hạn website ' . $order->domain . ' thất bại: ' . $failMsg,
                'reference_id' => $order->id
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi hoàn tiền gia hạn website: ' . $e->getMessage());
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Gia hạn thất bại: ' . $failMsg . '. Tiền đã được hoàn lại 100% vào tài khoản của bạn!'
        ], 400);
    }
}
