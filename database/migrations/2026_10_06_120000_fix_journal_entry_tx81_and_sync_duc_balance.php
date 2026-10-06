<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $ducUser = DB::table('users')->where('username', 'nqd')->orWhere('name', 'LIKE', '%Quý Đức%')->first();
        $ducUserId = $ducUser ? $ducUser->id : 4;
        $ducAccount = DB::table('accounts')->where('type', 'user')->where('owner_id', $ducUserId)->first();
        $fundAccount = DB::table('accounts')->where('type', 'fund')->first();

        // 1. Sửa bút toán kép của giao dịch 81 (Quý Đức bà nội phẫu thuật) từ chi quỹ ghi có thành chi quỹ ghi nợ ví cá nhân
        $tx81 = DB::table('transactions')
            ->where('description', 'LIKE', '%bà nội phẫu thuật%')
            ->where('amount', 1000000)
            ->first();

        if ($tx81 && $ducAccount && $fundAccount) {
            DB::table('journal_entries')
                ->where('transaction_id', $tx81->id)
                ->update([
                    'from_account_id' => $ducAccount->id,
                    'to_account_id'   => $fundAccount->id,
                    'memo'            => 'expense: ' . $tx81->description . ' (Trừ: Nguyễn Quý Đức)',
                    'updated_at'      => now(),
                ]);
        }

        // 2. Chuyển 3 giao dịch cá nhân cũ của Quý Đức (TX 65 SSD, TX 70 hoa, TX 72 sách) từ is_fund_only sang trừ ví cá nhân
        $personalTxs = DB::table('transactions')
            ->whereIn('id', [65, 70, 72])
            ->get();

        foreach ($personalTxs as $ptx) {
            DB::table('transactions')
                ->where('id', $ptx->id)
                ->update([
                    'user_id'      => $ducUserId,
                    'is_fund_only' => 0,
                    'updated_at'   => now(),
                ]);

            if ($ducAccount && $fundAccount) {
                DB::table('journal_entries')
                    ->where('transaction_id', $ptx->id)
                    ->update([
                        'from_account_id' => $ducAccount->id,
                        'to_account_id'   => $fundAccount->id,
                        'memo'            => $ptx->description . ' (Trừ: Nguyễn Quý Đức)',
                        'updated_at'      => now(),
                    ]);
            }
        }

        // 3. Tính lại số dư ví cá nhân của Nguyễn Quý Đức và tài khoản External
        if ($ducAccount) {
            $in = DB::table('journal_entries')->where('to_account_id', $ducAccount->id)->whereNull('deleted_at')->sum('amount');
            $out = DB::table('journal_entries')->where('from_account_id', $ducAccount->id)->whereNull('deleted_at')->sum('amount');

            DB::table('accounts')->where('id', $ducAccount->id)->update([
                'balance'    => (float) ($in - $out),
                'updated_at' => now(),
            ]);
        }

        $extAccount = DB::table('accounts')->where('type', 'external')->first();
        if ($extAccount) {
            $inExt = DB::table('journal_entries')->where('to_account_id', $extAccount->id)->whereNull('deleted_at')->sum('amount');
            $outExt = DB::table('journal_entries')->where('from_account_id', $extAccount->id)->whereNull('deleted_at')->sum('amount');

            DB::table('accounts')->where('id', $extAccount->id)->update([
                'balance'    => (float) ($inExt - $outExt),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $ducUser = DB::table('users')->where('username', 'nqd')->orWhere('name', 'LIKE', '%Quý Đức%')->first();
        $ducUserId = $ducUser ? $ducUser->id : 4;
        $ducAccount = DB::table('accounts')->where('type', 'user')->where('owner_id', $ducUserId)->first();
        $fundAccount = DB::table('accounts')->where('type', 'fund')->first();
        $extAccount = DB::table('accounts')->where('type', 'external')->first();

        $tx81 = DB::table('transactions')
            ->where('description', 'LIKE', '%bà nội phẫu thuật%')
            ->where('amount', 1000000)
            ->first();

        if ($tx81 && $ducAccount && $fundAccount) {
            DB::table('journal_entries')
                ->where('transaction_id', $tx81->id)
                ->update([
                    'from_account_id' => $fundAccount->id,
                    'to_account_id'   => $ducAccount->id,
                    'memo'            => 'expense: ' . $tx81->description,
                    'updated_at'      => now(),
                ]);
        }

        if ($fundAccount && $extAccount) {
            DB::table('transactions')->whereIn('id', [65, 70, 72])->update(['is_fund_only' => 1]);
            DB::table('journal_entries')->whereIn('transaction_id', [65, 70, 72])->update([
                'from_account_id' => $fundAccount->id,
                'to_account_id'   => $extAccount->id,
            ]);
        }

        if ($ducAccount) {
            $in = DB::table('journal_entries')->where('to_account_id', $ducAccount->id)->whereNull('deleted_at')->sum('amount');
            $out = DB::table('journal_entries')->where('from_account_id', $ducAccount->id)->whereNull('deleted_at')->sum('amount');
            DB::table('accounts')->where('id', $ducAccount->id)->update(['balance' => (float) ($in - $out)]);
        }

        if ($extAccount) {
            $inExt = DB::table('journal_entries')->where('to_account_id', $extAccount->id)->whereNull('deleted_at')->sum('amount');
            $outExt = DB::table('journal_entries')->where('from_account_id', $extAccount->id)->whereNull('deleted_at')->sum('amount');
            DB::table('accounts')->where('id', $extAccount->id)->update(['balance' => (float) ($inExt - $outExt)]);
        }
    }
};
