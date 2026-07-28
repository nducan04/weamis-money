<?php

namespace App\Http\Controllers;

use App\Models\Fund;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Distribution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DistributionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'total_amount' => 'required|numeric|min:1000',
            'note' => 'nullable|string|max:255',
        ]);

        $fund = Fund::firstOrFail();
        $totalAmount = (float) $validated['total_amount'];

        if ($totalAmount > $fund->balance) {
            return redirect()->back()->with('error', 'Số tiền chia vượt quá số dư hiện tại của quỹ!');
        }

        $members = User::where('share_percentage', '>', 0)->get();
        if ($members->isEmpty()) {
            return redirect()->back()->with('error', 'Chưa có thành viên nào được thiết lập tỷ lệ % cổ phần!');
        }

        $adminUser = User::where('role', 'admin')->first() ?? $members->first();

        DB::transaction(function () use ($fund, $members, $totalAmount, $validated, $adminUser) {
            $payoutDetails = [];
            foreach ($members as $member) {
                $memberPayout = round(($totalAmount * $member->share_percentage) / 100, 0);
                $payoutDetails[] = [
                    'user_id' => $member->id,
                    'name' => $member->name,
                    'avatar' => $member->avatar,
                    'share_percentage' => $member->share_percentage,
                    'amount' => $memberPayout,
                ];
            }

            // Create distribution record
            Distribution::create([
                'fund_id' => $fund->id,
                'total_amount' => $totalAmount,
                'note' => $validated['note'] ?? 'Chia tiền quỹ/lợi nhuận theo % cổ phần',
                'payout_details' => $payoutDetails,
                'created_by' => $adminUser->id,
            ]);

            // Deduct balance from Fund
            $fund->decrement('balance', $totalAmount);

            // Log distribution transaction
            Transaction::create([
                'fund_id' => $fund->id,
                'user_id' => $adminUser->id,
                'type' => 'distribution',
                'amount' => $totalAmount,
                'description' => 'Chia tiền quỹ cho team: ' . number_format($totalAmount, 0, ',', '.') . 'đ (' . ($validated['note'] ?? 'Chia % cổ phần') . ')',
                'status' => 'approved',
                'approved_by' => $adminUser->id,
            ]);
        });

        return redirect()->back()->with('success', 'Đã thực hiện chia tiền quỹ theo % thành công!');
    }
}
