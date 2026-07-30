@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ showEditModal: false, activeEvidence: null }">

    <!-- Header Section -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 sm:p-6 border border-slate-200/80 dark:border-slate-700 shadow-md flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2.5 mb-1">
                <a href="{{ route('projects.index') }}" class="text-xs font-bold text-slate-400 hover:text-slate-600 transition">← Tất cả dự án</a>
                <span class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-black text-xs rounded-lg uppercase tracking-wider">
                    {{ $project->code }}
                </span>
            </div>
            <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center space-x-2">
                <span>{{ $project->name }}</span>
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 font-medium mt-1">
                {{ $project->description ?: 'Chưa có mô tả dự án' }}
            </p>
        </div>

        <div class="flex items-center space-x-2">
            <button @click="showEditModal = true" class="px-3.5 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-extrabold text-xs rounded-xl transition flex items-center space-x-1.5 cursor-pointer">
                <span>⚙️ Cấu Hình Dự Án</span>
            </button>
            <form action="{{ route('projects.destroy', $project) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa dự án này?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-3 py-2 bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 hover:bg-rose-600 hover:text-white font-extrabold text-xs rounded-xl transition cursor-pointer">
                    🗑️ Xóa
                </button>
            </form>
        </div>
    </div>

    <!-- Financial Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200/80 dark:border-slate-700 shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Tổng Doanh Thu</p>
            <p class="text-lg font-black text-emerald-600 dark:text-emerald-400">+{{ number_format($totalIncome, 0, ',', '.') }}đ</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200/80 dark:border-slate-700 shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Trích Quỹ Weamis ({{ number_format($project->weamis_fund_percentage, 0) }}%)</p>
            <p class="text-lg font-black text-amber-600 dark:text-amber-400">{{ number_format($fundCut, 0, ',', '.') }}đ</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200/80 dark:border-slate-700 shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Tiền Đem Chia Thành Viên</p>
            <p class="text-lg font-black text-indigo-600 dark:text-indigo-400">{{ number_format($distributable, 0, ',', '.') }}đ</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200/80 dark:border-slate-700 shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Tổng Chi Phí Phát Sinh</p>
            <p class="text-lg font-black text-rose-600 dark:text-rose-400">-{{ number_format($totalExpense, 0, ',', '.') }}đ</p>
        </div>
    </div>

    <!-- Member Payouts Table -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm">
        <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider mb-4 flex items-center space-x-2">
            <span>💎 Phân Bổ Tiền Dự Án Cho Thành Viên</span>
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
            @foreach($memberPayouts as $payout)
                <div class="p-3.5 bg-slate-50 dark:bg-slate-700/30 rounded-2xl border border-slate-100 dark:border-slate-700 space-y-2">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-extrabold text-slate-900 dark:text-white">{{ $payout['user']->name }}</p>
                            <p class="text-[10px] text-indigo-600 dark:text-indigo-400 font-extrabold">Cổ phần: {{ number_format($payout['share_percentage'], 1) }}%</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] text-slate-400 font-bold uppercase">Số tiền nhận</p>
                            <p class="text-xs font-black text-emerald-600 dark:text-emerald-400">+{{ number_format($payout['estimated_payout'], 0, ',', '.') }}đ</p>
                        </div>
                    </div>
                    <!-- Share progress bar -->
                    <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-gradient-to-r from-emerald-500 to-indigo-600 h-full rounded-full" style="width: {{ min(100, max(5, $payout['share_percentage'])) }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Project Transactions Audit Table -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm">
        <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider mb-4 flex items-center space-x-2">
            <span>📜 Nhật Ký Giao Dịch Audit Dự Án ({{ $project->transactions->count() }})</span>
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-100 dark:bg-slate-700/60 text-slate-500 uppercase font-bold text-[10px] tracking-wider">
                    <tr>
                        <th class="py-3 px-3 rounded-l-xl">Thời gian</th>
                        <th class="py-3 px-3">Người nhập</th>
                        <th class="py-3 px-3">Nội dung</th>
                        <th class="py-3 px-3">Người trách nhiệm / Đòi tiền</th>
                        <th class="py-3 px-3">Bằng chứng (Evidence)</th>
                        <th class="py-3 px-3 text-right rounded-r-xl">Số tiền</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                    @forelse($project->transactions as $tx)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                            <td class="py-3 px-3 font-semibold text-slate-500">{{ $tx->created_at ? $tx->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                            <td class="py-3 px-3 font-bold text-slate-900 dark:text-white">{{ $tx->user->name ?? 'N/A' }}</td>
                            <td class="py-3 px-3 font-medium">{{ $tx->description }}</td>
                            <td class="py-3 px-3 text-[11px]">
                                @if($tx->responsibleUser)
                                    <span class="px-1.5 py-0.5 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300 font-bold rounded">TN: {{ $tx->responsibleUser->name }}</span>
                                @endif
                                @if($tx->claimantUser)
                                    <span class="px-1.5 py-0.5 bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-300 font-bold rounded ml-1">Đòi: {{ $tx->claimantUser->name }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                @if($tx->evidence_type === 'file' && $tx->evidence_value)
                                    <button @click="activeEvidence = { type: 'image', value: '{{ $tx->evidence_value }}' }" class="px-2 py-1 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 font-bold rounded text-[10px] cursor-pointer">🖼️ Xem ảnh bill</button>
                                @elseif($tx->evidence_type === 'link' && $tx->evidence_value)
                                    <a href="{{ $tx->evidence_value }}" target="_blank" class="px-2 py-1 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-bold rounded text-[10px]">🔗 Mở Link</a>
                                @elseif($tx->evidence_type === 'text' && $tx->evidence_value)
                                    <button @click="activeEvidence = { type: 'text', value: `{{ addslashes($tx->evidence_value) }}` }" class="px-2 py-1 bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 font-bold rounded text-[10px] cursor-pointer">📝 Momo text</button>
                                @else
                                    <span class="text-slate-400 text-[10px]">Không có</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-right font-extrabold {{ in_array($tx->type, ['contribution', 'repayment']) ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                {{ in_array($tx->type, ['contribution', 'repayment']) ? '+' : '-' }}{{ number_format($tx->amount, 0, ',', '.') }}đ
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-400">Chưa có giao dịch thu chi nào gắn với dự án này.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- EVIDENCE MODAL PREVIEW -->
    <div x-show="activeEvidence" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 backdrop-blur-sm p-4" x-cloak x-transition>
        <div @click.away="activeEvidence = null" class="bg-white dark:bg-slate-800 rounded-3xl p-5 w-full max-w-lg shadow-2xl border border-slate-100 dark:border-slate-700">
            <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100 dark:border-slate-700">
                <h4 class="font-bold text-sm text-slate-900 dark:text-white">📌 Bằng chứng (Evidence)</h4>
                <button @click="activeEvidence = null" class="text-slate-400 font-bold">✕</button>
            </div>
            <template x-if="activeEvidence && activeEvidence.type === 'image'">
                <div class="text-center">
                    <img :src="activeEvidence.value" class="max-h-[60vh] mx-auto rounded-2xl shadow border border-slate-200">
                </div>
            </template>
            <template x-if="activeEvidence && activeEvidence.type === 'text'">
                <div class="p-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl text-xs font-mono text-slate-800 dark:text-slate-200 whitespace-pre-wrap">
                    <p x-text="activeEvidence.value"></p>
                </div>
            </template>
        </div>
    </div>

    <!-- EDIT PROJECT MODAL -->
    <div x-show="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-cloak x-transition>
        <div @click.away="showEditModal = false" class="bg-white dark:bg-slate-800 rounded-3xl p-5 sm:p-6 w-full max-w-lg shadow-2xl border border-slate-100 dark:border-slate-700 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-100 dark:border-slate-700">
                <h3 class="text-lg font-black text-slate-900 dark:text-white">⚙️ Cấu Hình Dự Án</h3>
                <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
            </div>

            <form action="{{ route('projects.update', $project) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Tên Dự Án</label>
                        <input type="text" name="name" value="{{ $project->name }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs sm:text-sm font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Trạng thái</label>
                        <select name="status" class="w-full px-2 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none">
                            <option value="active" {{ $project->status === 'active' ? 'selected' : '' }}>Đang chạy</option>
                            <option value="completed" {{ $project->status === 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                            <option value="cancelled" {{ $project->status === 'cancelled' ? 'selected' : '' }}>Hủy</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Mô tả</label>
                    <textarea name="description" rows="2" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-medium text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none">{{ $project->description }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">% Trích Quỹ Weamis</label>
                        <input type="number" name="weamis_fund_percentage" value="{{ $project->weamis_fund_percentage }}" min="0" max="100" step="0.5" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-extrabold text-amber-600 focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Project Lead</label>
                        <select name="lead_user_id" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none">
                            <option value="">-- Chọn Lead --</option>
                            @foreach($allMembers as $m)
                                <option value="{{ $m->id }}" {{ $project->lead_user_id == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Member Shares Config -->
                <div class="pt-2">
                    <label class="block text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider mb-2">Tỷ lệ % Cổ Phần Thành Viên</label>
                    <div class="space-y-2 bg-slate-50 dark:bg-slate-700/30 p-3 rounded-2xl border border-slate-200/60 dark:border-slate-700">
                        @foreach($allMembers as $index => $m)
                            @php
                                $pm = $project->projectMembers->where('user_id', $m->id)->first();
                                $share = $pm ? $pm->share_percentage : 0;
                            @endphp
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-800 dark:text-slate-200 w-1/2">{{ $m->name }}</span>
                                <input type="hidden" name="members[{{ $index }}][user_id]" value="{{ $m->id }}">
                                <div class="flex items-center space-x-1 w-1/3">
                                    <input type="number" name="members[{{ $index }}][share_percentage]" value="{{ $share }}" min="0" max="100" step="0.5" class="w-full px-2 py-1 rounded-lg border border-slate-200 dark:border-slate-600 dark:bg-slate-700 font-extrabold text-xs text-indigo-600 dark:text-indigo-300 text-center outline-none">
                                    <span class="font-bold text-slate-400">%</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="pt-3 flex justify-end space-x-2">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-xl">Hủy</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-md">Lưu Thay Đổi</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
