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
        // 1. Kiểm tra secret key bảo mật (hỗ trợ X-Webhook-Secret, Authorization Apikey, body hoặc query)
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

        // Bỏ qua nếu là giao dịch tiền ra (SePay transferType === 'out')
        if ($request->input('transferType') === 'out') {
            return response()->json(['status' => 'ignored', 'message' => 'Giao dịch chuyển tiền đi (out)'], 200);
        }

        // 2. Chuẩn hóa dữ liệu đầu vào (hỗ trợ cả SePay và Google Apps Script)
        $bank = $request->input('bank') ?: $request->input('gateway') ?: 'MBBank';
        $amount = (float) ($request->input('amount') ?: $request->input('transferAmount') ?: 0);
        $content = trim($request->input('content') ?: $request->input('description') ?: '');
        $transactionId = trim((string) ($request->input('transaction_id') ?: $request->input('referenceCode') ?: $request->input('id') ?: ''));

        if ($amount < 1000 || empty($content) || empty($transactionId)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dữ liệu giao dịch không hợp lệ (thiếu số tiền, nội dung hoặc mã GD).'
            ], 400);
        }

        $prefix = env('BANK_PREFIX', 'naptien');

        // 3. Trích xuất User ID từ nội dung chuyển khoản
        $userId = get_id_bank($prefix, $content);

        // Fallback kiểm tra nếu khách viết dính hoặc có định dạng đặc biệt
        if ($userId <= 0) {
            if (preg_match('/' . preg_quote($prefix, '/') . '\s*(\d+)/i', $content, $m)) {
                $userId = (int) $m[1];
            }
        }

        if ($userId <= 0) {
            return response()->json([
                'status' => 'ignored',
                'message' => "Không tìm thấy mã người dùng hợp lệ trong nội dung: \"$content\""
            ], 200);
        }

        // 4. Kiểm tra mã giao dịch đã từng xử lý chưa (chống cộng tiền trùng lặp)
        if (BankDeposit::where('transaction_id', $transactionId)->exists()) {
            return response()->json([
                'status' => 'already_processed',
                'message' => "Giao dịch $transactionId đã được xử lý trước đó."
            ], 200);
        }

        // 5. Tìm user
        $user = User::find($userId);
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => "Không tìm thấy user với ID: $userId"
            ], 404);
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
