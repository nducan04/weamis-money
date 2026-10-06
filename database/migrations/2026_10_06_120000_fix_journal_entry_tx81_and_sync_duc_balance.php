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
        // 1. Sửa bút toán kép của giao dịch 81 (Quý Đức bà nội phẫu thuật) từ chi quỹ ghi có thành chi quỹ ghi nợ ví cá nhân
        $tx81 = DB::table('transactions')
            ->where('description', 'LIKE', '%bà nội phẫu thuật%')
            ->where('amount', 1000000)
            ->first();

        if ($tx81) {
            $ducAccount = DB::table('accounts')->where('type', 'user')->where('owner_id', $tx81->user_id)->first();
            $fundAccount = DB::table('accounts')->where('type', 'fund')->first();

            if ($ducAccount && $fundAccount) {
                DB::table('journal_entries')
                    ->where('transaction_id', $tx81->id)
                    ->update([
                        'from_account_id' => $ducAccount->id,
                        'to_account_id'   => $fundAccount->id,
                        'memo'            => 'expense: ' . $tx81->description . ' (Trừ: Nguyễn Quý Đức)',
                        'updated_at'      => now(),
                    ]);

                // 2. Tính lại số dư ví cá nhân của Nguyễn Quý Đức
                $in = DB::table('journal_entries')->where('to_account_id', $ducAccount->id)->whereNull('deleted_at')->sum('amount');
                $out = DB::table('journal_entries')->where('from_account_id', $ducAccount->id)->whereNull('deleted_at')->sum('amount');

                DB::table('accounts')->where('id', $ducAccount->id)->update([
                    'balance'    => (float) ($in - $out),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tx81 = DB::table('transactions')
            ->where('description', 'LIKE', '%bà nội phẫu thuật%')
            ->where('amount', 1000000)
            ->first();

        if ($tx81) {
            $ducAccount = DB::table('accounts')->where('type', 'user')->where('owner_id', $tx81->user_id)->first();
            $fundAccount = DB::table('accounts')->where('type', 'fund')->first();

            if ($ducAccount && $fundAccount) {
                DB::table('journal_entries')
                    ->where('transaction_id', $tx81->id)
                    ->update([
                        'from_account_id' => $fundAccount->id,
                        'to_account_id'   => $ducAccount->id,
                        'memo'            => 'expense: ' . $tx81->description,
                        'updated_at'      => now(),
                    ]);

                $in = DB::table('journal_entries')->where('to_account_id', $ducAccount->id)->whereNull('deleted_at')->sum('amount');
                $out = DB::table('journal_entries')->where('from_account_id', $ducAccount->id)->whereNull('deleted_at')->sum('amount');

                DB::table('accounts')->where('id', $ducAccount->id)->update([
                    'balance'    => (float) ($in - $out),
                    'updated_at' => now(),
                ]);
            }
        }
    }
};
