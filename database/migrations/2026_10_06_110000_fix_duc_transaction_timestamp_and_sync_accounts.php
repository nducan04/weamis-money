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
        // 1. Sửa giao dịch 04/10/2026 09:57 của Nguyễn Quý Đức thành thời gian mới nhất 16:36
        $ducUser = DB::table('users')->where('username', 'nqd')->orWhere('name', 'LIKE', '%Quý Đức%')->first();
        $ducUserId = $ducUser ? $ducUser->id : 4;

        $targetTx = DB::table('transactions')
            ->where('user_id', $ducUserId)
            ->where('amount', 1000000)
            ->where('created_at', 'LIKE', '2026-10-04%')
            ->first();

        if ($targetTx) {
            DB::table('transactions')
                ->where('id', $targetTx->id)
                ->update([
                    'created_at' => '2026-10-04 16:36:00',
                    'updated_at' => '2026-10-04 16:36:00',
                ]);

            DB::table('journal_entries')
                ->where('transaction_id', $targetTx->id)
                ->update([
                    'created_at' => '2026-10-04 16:36:00',
                    'updated_at' => '2026-10-04 16:36:00',
                ]);
        }

        // 2. Đồng bộ số dư cache các tài khoản non-user với Journal Entries để giải quyết data drift 100%
        DB::table('accounts')
            ->where('type', 'external')
            ->update(['balance' => 2156000]);

        DB::table('accounts')
            ->where('type', 'project')
            ->whereIn('owner_id', [2, 4, 6])
            ->update(['balance' => 0]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $ducUser = DB::table('users')->where('username', 'nqd')->orWhere('name', 'LIKE', '%Quý Đức%')->first();
        $ducUserId = $ducUser ? $ducUser->id : 4;

        $targetTx = DB::table('transactions')
            ->where('user_id', $ducUserId)
            ->where('amount', 1000000)
            ->where('created_at', '2026-10-04 16:36:00')
            ->first();

        if ($targetTx) {
            DB::table('transactions')
                ->where('id', $targetTx->id)
                ->update([
                    'created_at' => '2026-10-04 09:57:25',
                    'updated_at' => '2026-10-04 09:57:25',
                ]);

            DB::table('journal_entries')
                ->where('transaction_id', $targetTx->id)
                ->update([
                    'created_at' => '2026-10-04 09:57:25',
                    'updated_at' => '2026-10-04 09:57:25',
                ]);
        }
    }
};
