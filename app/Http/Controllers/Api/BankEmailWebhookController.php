<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankDeposit;
use App\Models\MoneyTransaction;
use App\Models\User;
use App\Models\AffiliateHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BankEmailWebhookController extends Controller
{
    /**
     * Xử lý webhook từ Google Apps Script (đọc email biến động số dư ngân hàng)
     */
    public function handleEmailWebhook(Request $request)
    {
        // Lấy dữ liệu từ cả Laravel Request và raw JSON body
        $raw = json_decode($request->getContent(), true) ?: [];
        if (empty($raw)) {
            $raw = $request->all();
        }

        // Hỗ trợ payOS kiểm tra xác thực webhook url (ping test từ payOS)
        if ($request->has('webhookUrl') || isset($raw['webhookUrl'])) {
            return response()->json(['status' => 'success', 'message' => 'payOS Webhook URL verified'], 200);
        }

        $isPayOs = $request->is('*payos*') || (isset($raw['data']) && isset($raw['signature'])) || $request->hasHeader('x-api-key');

        // 1. Kiểm tra secret key bảo mật (nếu không phải payload có signature từ payOS)
        if (!$isPayOs) {
            $configuredSecret = env('BANK_EMAIL_SECRET', 'ThangCyrusBankSecure2026');
            $authHeader = $request->header('Authorization');
            $receivedSecret = $request->header('X-Webhook-Secret') ?: $request->input('secret') ?: $request->query('secret');
            if (!$receivedSecret && $authHeader && preg_match('/Apikey\s+(.*)/i', $authHeader, $matches)) {
                $receivedSecret = trim($matches[1]);
            }

            if ($receivedSecret !== $configuredSecret) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized: Sai mã bảo mật secret key.'
                ], 401);
            }
        }

        // Bỏ qua nếu là giao dịch tiền ra (SePay transferType === 'out')
        if ($request->input('transferType') === 'out' || ($raw['transferType'] ?? '') === 'out') {
            return response()->json(['status' => 'ignored', 'message' => 'Giao dịch chuyển tiền đi (out)'], 200);
        }

        // Hỗ trợ payOS (dữ liệu giao dịch nằm trong object 'data')
        $payosData = (isset($raw['data']) && is_array($raw['data'])) ? $raw['data'] : (is_array($request->input('data')) ? $request->input('data') : []);
        $orderCode = $payosData['orderCode'] ?? null;
        $amount = (float) ($request->input('amount') ?: $request->input('transferAmount') ?: ($raw['amount'] ?? ($raw['transferAmount'] ?? ($payosData['amount'] ?? 0))));

        // Nếu là payOS và có orderCode, ưu tiên hoàn tất qua ProfileController::completePayOsOrder
        if (!empty($orderCode) && $amount >= 1000) {
            $completed = \App\Http\Controllers\User\ProfileController::completePayOsOrder($orderCode, $amount);
            if ($completed) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Cộng tiền tự động payOS thành công!'
                ], 200);
            }
        }

        // 2. Chuẩn hóa dữ liệu đầu vào (hỗ trợ payOS, SePay và Google Apps Script)
        $bank = $request->input('bank') ?: $request->input('gateway') ?: ($raw['bank'] ?? ($raw['gateway'] ?? ($payosData['counterAccountBankId'] ?? 'MBBank')));
        $content = trim((string) ($request->input('content') ?: $request->input('description') ?: ($raw['content'] ?? ($raw['description'] ?? ($payosData['description'] ?? '')))));
        if (!empty($orderCode)) {
            $transactionId = (string) $orderCode;
        } else {
            $transactionId = trim((string) ($request->input('transaction_id') ?: $request->input('referenceCode') ?: $request->input('id') ?: ($raw['transaction_id'] ?? ($raw['referenceCode'] ?? ($payosData['reference'] ?? '')))));
        }

        if (empty($transactionId) && !empty($payosData)) {
            $transactionId = 'PAYOS_' . time() . '_' . rand(1000, 9999);
        }

        if ($amount < 1000 || empty($content) || empty($transactionId)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dữ liệu giao dịch không hợp lệ (thiếu số tiền, nội dung hoặc mã GD).'
            ], 400);
        }

        $prefix = env('BANK_PREFIX', 'naptien');
        $user = null;

        // Trích xuất chuỗi ngay sau prefix (ví dụ: "naptien phung232010@gmail.com" hoặc "naptien minhthang")
        $extracted = '';
        if (preg_match('/' . preg_quote($prefix, '/') . '\s*([^\s,;]+)/i', $content, $m)) {
            $extracted = trim($m[1]);
        }

        // 3.1. Thử tìm theo Email chính xác
        if (!empty($extracted)) {
            $user = User::where('email', $extracted)->first();

            // 3.2. Thử tìm theo Username chính xác
            if (!$user) {
                $user = User::where('username', $extracted)->first();
            }

            // 3.3. Thử tìm theo phần đầu email (nếu ngân hàng lược bỏ đuôi @gmail.com)
            if (!$user && !str_contains($extracted, '@')) {
                $user = User::where('email', 'like', $extracted . '@%')->first();
            }

            // 3.3.1. Thử so khớp nếu ngân hàng lọc bỏ ký tự @ và dấu . trong email
            if (!$user) {
                $cleanExtracted = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $extracted));
                if (!empty($cleanExtracted) && strlen($cleanExtracted) >= 4) {
                    $candidateUsers = User::whereNotNull('email')->take(50)->get(['id', 'email', 'username']);
                    foreach ($candidateUsers as $u) {
                        $cleanEmail = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $u->email));
                        $emailPrefix = strtolower(explode('@', $u->email)[0]);
                        if ($cleanEmail === $cleanExtracted || str_starts_with($cleanExtracted, $emailPrefix)) {
                            $user = $u;
                            break;
                        }
                    }
                }
            }
        }

        // 3.4. Quét tìm trực tiếp địa chỉ Email đầy đủ trong toàn bộ nội dung
        if (!$user) {
            if (preg_match('/([a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+)/i', $content, $m)) {
                $user = User::where('email', $m[1])->first();
            }
        }

        // 3.5. Quét tìm Username của các user đang có trong hệ thống
        if (!$user) {
            $cleanContent = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $content));
            $users = User::whereNotNull('username')->get();
            foreach ($users as $u) {
                $cleanUser = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $u->username));
                if (!empty($cleanUser) && strlen($cleanUser) >= 3 && str_contains($cleanContent, $cleanUser)) {
                    $user = $u;
                    break;
                }
            }
        }

        // 3.6. Cuối cùng mới fallback tìm theo ID số nguyên (nếu khách chuyển theo ID cũ như naptien 4)
        if (!$user) {
            $numericId = get_id_bank($prefix, $content);
            if ($numericId <= 0 && preg_match('/' . preg_quote($prefix, '/') . '\s*(\d+)/i', $content, $m)) {
                $numericId = (int) $m[1];
            }
            if ($numericId > 0) {
                $user = User::find($numericId);
            }
        }

        // 3.7. Nếu là đơn payOS, tìm theo cache orderCode đã liên kết
        if (!$user && !empty($orderCode)) {
            $cachedUserId = \Illuminate\Support\Facades\Cache::get('payos_order_' . $orderCode);
            if ($cachedUserId) {
                $user = User::find($cachedUserId);
            }
        }

        if (!$user) {
            return response()->json([
                'status' => 'ignored',
                'message' => "Không tìm thấy người dùng hợp lệ từ nội dung: \"$content\""
            ], 200);
        }

        $userId = $user->id;

        // 4. Kiểm tra mã giao dịch đã từng xử lý chưa (chống cộng tiền trùng lặp)
        if (BankDeposit::where('transaction_id', $transactionId)->exists()) {
            return response()->json([
                'status' => 'already_processed',
                'message' => "Giao dịch $transactionId đã được xử lý trước đó."
            ], 200);
        }

        // 6. Thực hiện cộng tiền an toàn bằng Database Transaction
        try {
            DB::beginTransaction();

            // Lưu lịch sử nạp ngân hàng
            $bankDeposit = BankDeposit::create([
                'transaction_id' => $transactionId,
                'user_id' => $userId,
                'account_number' => $request->input('account_number', 'EMAIL_NOTIF'),
                'amount' => $amount,
                'content' => $content,
                'bank' => $bank,
                'status' => 'completed',
            ]);

            // Cộng tiền vào tài khoản người dùng
            $balanceBefore = $user->balance;
            $user->balance += $amount;
            $user->total_deposited += $amount;
            $user->save();

            // Ghi log biến động số dư
            MoneyTransaction::create([
                'user_id' => $userId,
                'type' => 'deposit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $user->balance,
                'description' => "Nạp tiền tự động qua {$bank} (Email) - Mã GD: {$transactionId}",
                'reference_id' => $transactionId
            ]);

            // Hoa hồng giới thiệu (10% nếu có người giới thiệu)
            if ($user->referrer_id) {
                $referrer = User::find($user->referrer_id);
                if ($referrer) {
                    $commission = (int) ($amount * 0.10);
                    $referrer->balance += $commission;
                    $referrer->total_commission += $commission;
                    $referrer->save();

                    AffiliateHistory::create([
                        'referrer_id' => $referrer->id,
                        'referred_id' => $user->id,
                        'commission_amount' => $commission,
                        'type' => 'deposit',
                        'description' => "Hoa hồng nạp tiền từ thành viên {$user->username} (10%)"
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Cộng tiền tự động thành công!',
                'data' => [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'amount' => $amount,
                    'new_balance' => $user->balance,
                    'transaction_id' => $transactionId
                ]
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Bank Email Webhook Error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi xử lý nạp tiền: ' . $e->getMessage()
            ], 500);
        }
    }
}
