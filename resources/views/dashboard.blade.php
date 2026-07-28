@extends('layouts.app')

@section('content')
<div x-data="{ 
    showContributionModal: false, 
    showWithdrawModal: false, 
    showDistributionModal: false,
    showAddMemberModal: false,
    showEditShareModal: false,
    selectedUser: null,
    distributeAmount: {{ $fund->balance }},
    get calculatedPayouts() {
        let amount = parseFloat(this.distributeAmount) || 0;
        return [
            @foreach($members as $m)
            { id: {{ $m->id }}, name: '{{ $m->name }}', avatar: '{{ $m->avatar }}', share: {{ $m->share_percentage }}, amount: Math.round(amount * {{ $m->share_percentage }} / 100) },
            @endforeach
        ];
    }
}">

    <!-- Top Card: MoMo Styled Fund Header -->
    <div class="momo-gradient text-white rounded-3xl p-6 shadow-xl mb-6 relative overflow-hidden momo-card-shadow">
        <!-- Background Decorative Blobs -->
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute right-20 -top-10 w-32 h-32 bg-pink-400/20 rounded-full blur-xl pointer-events-none"></div>

        <!-- Fund Name & Avatars -->
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center font-bold text-white shadow-inner">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold tracking-tight">{{ $fund->name }}</h2>
                    <div class="flex items-center space-x-1 mt-0.5">
                        @foreach($members as $m)
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-white/20 text-[10px] font-bold border border-white/40">
                                {{ $m->avatar ?? substr($m->name, 0, 2) }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
            <span class="text-xs px-3 py-1 bg-white/20 backdrop-blur rounded-full font-semibold border border-white/30">
                MoMo Team Fund
            </span>
        </div>

        <!-- Fund Balance & Profit -->
        <div class="bg-white/10 backdrop-blur-md rounded-2xl p-5 border border-white/20 mb-5">
            <p class="text-xs text-pink-100 font-semibold uppercase tracking-wider mb-1">Số dư quỹ hiện tại</p>
            <div class="flex items-baseline space-x-2">
                <span class="text-3xl sm:text-4xl font-extrabold tracking-tight">
                    {{ number_format($fund->balance, 0, ',', '.') }}<span class="text-2xl font-bold ml-0.5">đ</span>
                </span>
            </div>

            <div class="mt-3 pt-3 border-t border-white/15 flex items-center justify-between text-xs text-pink-100">
                <div class="flex items-center space-x-1.5 bg-white/15 px-3 py-1 rounded-full">
                    <span>🌸 Tổng lợi nhuận: <strong class="text-white">{{ number_format($fund->total_profit, 0, ',', '.') }}đ</strong></span>
                </div>
                <span class="opacity-90">Cập nhật realtime</span>
            </div>
        </div>

        <!-- Quick Action Buttons -->
        <div class="grid grid-cols-3 gap-3">
            <button @click="showContributionModal = true" class="flex items-center justify-center space-x-2 py-3 px-4 rounded-xl bg-white text-momo-700 font-bold text-sm shadow-md hover:bg-pink-50 transition-all active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                <span>Góp quỹ</span>
            </button>

            <button @click="showWithdrawModal = true" class="flex items-center justify-center space-x-2 py-3 px-4 rounded-xl bg-white/20 backdrop-blur text-white font-bold text-sm border border-white/30 hover:bg-white/30 transition-all active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                <span>Yêu cầu rút</span>
            </button>

            <button @click="showDistributionModal = true" class="flex items-center justify-center space-x-2 py-3 px-4 rounded-xl bg-amber-400 text-slate-900 font-bold text-sm shadow-md hover:bg-amber-300 transition-all active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                <span>Chia tiền %</span>
            </button>
        </div>
    </div>

    <!-- Admin Approval Bar (If Pending Transactions Exist) -->
    @if($pendingTransactions->count() > 0)
        <div class="mb-6 bg-amber-500/10 border-2 border-amber-500/30 rounded-2xl p-4 dark:bg-amber-500/5">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center space-x-2">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                    </span>
                    <h3 class="font-bold text-sm text-amber-800 dark:text-amber-300">
                        Có {{ $pendingTransactions->count() }} yêu cầu chờ Chủ quỹ duyệt
                    </h3>
                </div>
            </div>

            <div class="space-y-3">
                @foreach($pendingTransactions as $pending)
                    <div class="flex items-center justify-between p-3 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-amber-200 dark:border-amber-900/40">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-200 font-bold text-xs flex items-center justify-center">
                                {{ $pending->user->avatar ?? substr($pending->user->name, 0, 2) }}
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-800 dark:text-slate-200">
                                    {{ $pending->user->name }}
                                    <span class="text-[11px] font-normal text-slate-500">
                                        ({{ $pending->type === 'expense' ? 'Chi tiêu chung' : 'Vay cá nhân' }})
                                    </span>
                                </p>
                                <p class="text-xs text-slate-600 dark:text-slate-400 font-medium">"{{ $pending->description }}"</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="font-extrabold text-sm text-rose-600 dark:text-rose-400">
                                -{{ number_format($pending->amount, 0, ',', '.') }}đ
                            </span>
                            
                            <form action="{{ route('transactions.approve', $pending) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition">
                                    Duyệt
                                </button>
                            </form>

                            <form action="{{ route('transactions.reject', $pending) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3 py-1 bg-slate-200 dark:bg-slate-700 hover:bg-rose-600 hover:text-white text-slate-700 dark:text-slate-300 rounded-lg text-xs font-bold transition">
                                    Từ chối
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Main Grid: Left Activities & Right Team Shares -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Column: Activity Feed (2 Cols) -->
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 shadow-sm border border-slate-200/60 dark:border-slate-700/60">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-700">
                    <h3 class="font-extrabold text-slate-900 dark:text-white text-base tracking-tight flex items-center space-x-2">
                        <span>Hoạt động gần đây</span>
                    </h3>
                    <span class="text-xs font-medium text-slate-400">Tất cả giao dịch</span>
                </div>

                <div class="divide-y divide-slate-100 dark:divide-slate-700/50">
                    @forelse($transactions as $tx)
                        <div class="py-3.5 flex items-start justify-between group hover:bg-slate-50/50 dark:hover:bg-slate-700/20 -mx-2 px-2 rounded-xl transition">
                            <div class="flex items-start space-x-3">
                                <!-- User Avatar -->
                                <div class="w-10 h-10 rounded-full bg-pink-100 dark:bg-pink-900/30 text-momo-600 dark:text-pink-300 font-extrabold text-sm flex items-center justify-center flex-shrink-0 mt-0.5 border border-pink-200 dark:border-pink-800/40">
                                    {{ $tx->user->avatar ?? substr($tx->user->name, 0, 2) }}
                                </div>

                                <div class="space-y-0.5">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-xs font-medium text-slate-400">
                                            {{ $tx->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                    <h4 class="font-bold text-sm text-slate-900 dark:text-white">
                                        {{ $tx->user->name }}
                                    </h4>
                                    
                                    <!-- Badge Type -->
                                    <div class="flex items-center space-x-2 mt-1">
                                        @if($tx->type === 'contribution')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                                Góp quỹ
                                            </span>
                                        @elseif($tx->type === 'expense')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300">
                                                Rút quỹ (Chi chung)
                                            </span>
                                        @elseif($tx->type === 'loan')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300">
                                                Vay cá nhân
                                            </span>
                                        @elseif($tx->type === 'repayment')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                                                Trả nợ tiền vay
                                            </span>
                                        @elseif($tx->type === 'distribution')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                                Chia tiền %
                                            </span>
                                        @endif

                                        @if($tx->status === 'pending')
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-200 text-amber-900">
                                                Chờ duyệt
                                            </span>
                                        @elseif($tx->status === 'rejected')
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-600 line-through">
                                                Từ chối
                                            </span>
                                        @endif
                                    </div>

                                    <p class="text-xs text-slate-600 dark:text-slate-300 font-medium pt-0.5">
                                        {{ $tx->description }}
                                    </p>
                                </div>
                            </div>

                            <!-- Amount -->
                            <div class="text-right flex-shrink-0">
                                @if(in_array($tx->type, ['contribution', 'repayment']))
                                    <span class="font-extrabold text-sm sm:text-base text-emerald-600 dark:text-emerald-400">
                                        +{{ number_format($tx->amount, 0, ',', '.') }}đ
                                    </span>
                                @else
                                    <span class="font-extrabold text-sm sm:text-base text-slate-800 dark:text-slate-100">
                                        -{{ number_format($tx->amount, 0, ',', '.') }}đ
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-center py-6 text-xs text-slate-400">Chưa có giao dịch nào.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Column: Members & Shares (1 Col) -->
        <div class="space-y-4">
            <!-- Team Members Card -->
            <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 shadow-sm border border-slate-200/60 dark:border-slate-700/60">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-700">
                    <div>
                        <h3 class="font-extrabold text-slate-900 dark:text-white text-base">Thành viên & Cổ phần</h3>
                        <p class="text-[11px] text-slate-400">Tỷ lệ % chia tiền khi xuất quỹ</p>
                    </div>
                    <button @click="showAddMemberModal = true" class="p-1.5 bg-pink-50 text-momo-600 dark:bg-pink-900/30 dark:text-pink-300 rounded-xl hover:bg-pink-100 transition text-xs font-bold">
                        + Thêm
                    </button>
                </div>

                <div class="space-y-4">
                    @foreach($members as $m)
                        <div class="p-3.5 bg-slate-50 dark:bg-slate-700/30 rounded-2xl border border-slate-100 dark:border-slate-700 space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2.5">
                                    <div class="w-8 h-8 rounded-full bg-momo-500 text-white font-extrabold text-xs flex items-center justify-center">
                                        {{ $m->avatar ?? substr($m->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-xs text-slate-900 dark:text-white flex items-center space-x-1">
                                            <span>{{ $m->name }}</span>
                                            @if($m->role === 'admin')
                                                <span class="text-[9px] bg-amber-400 text-slate-900 font-extrabold px-1.5 py-0.2 rounded">Chủ quỹ</span>
                                            @endif
                                        </h4>
                                        <p class="text-[10px] text-slate-400">{{ $m->email }}</p>
                                    </div>
                                </div>

                                <div class="text-right">
                                    <span class="font-extrabold text-sm text-momo-600 dark:text-pink-400">
                                        {{ $m->share_percentage }}%
                                    </span>
                                    <button @click="selectedUser = {{ json_encode($m) }}; showEditShareModal = true" class="block text-[10px] text-slate-400 hover:text-momo-600 underline">
                                        Sửa %
                                    </button>
                                </div>
                            </div>

                            <!-- Share Progress Bar -->
                            <div class="w-full bg-slate-200 dark:bg-slate-600 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-momo-500 h-1.5 rounded-full" style="width: {{ min(100, $m->share_percentage) }}%"></div>
                            </div>

                            <!-- Personal Debt status -->
                            @if($m->current_debt > 0)
                                <div class="flex items-center justify-between text-[11px] pt-1 text-rose-600 dark:text-rose-400 font-semibold">
                                    <span>⚠️ Dư nợ vay quỹ:</span>
                                    <span>{{ number_format($m->current_debt, 0, ',', '.') }}đ</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Total share verification -->
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-700 flex items-center justify-between text-xs text-slate-500">
                    <span>Tổng % cổ phần team:</span>
                    <span class="font-bold {{ $totalShare == 100 ? 'text-emerald-600' : 'text-amber-600' }}">
                        {{ $totalShare }}% {{ $totalShare == 100 ? '✓ Thuận lợi' : '(Chưa đủ 100%)' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 1: GÓP QUỸ / TRẢ NỢ -->
    <div x-show="showContributionModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div @click.away="showContributionModal = false" class="bg-white dark:bg-slate-800 rounded-3xl p-6 max-w-md w-full shadow-2xl border border-slate-100 dark:border-slate-700">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center space-x-2">
                <span>📥 Nạp tiền / Trả nợ Quỹ</span>
            </h3>

            <form action="{{ route('transactions.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Thành viên nạp tiền</label>
                    <select name="user_id" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-medium focus:ring-2 focus:ring-momo-500">
                        @foreach($members as $m)
                            <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->role === 'admin' ? 'Chủ quỹ' : 'Thành viên' }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Loại nạp</label>
                    <select name="type" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-medium focus:ring-2 focus:ring-momo-500">
                        <option value="contribution">Góp quỹ (Ví dụ: Góp % CNS / Góp hàng tháng)</option>
                        <option value="repayment">Trả nợ vay cá nhân (Trừ dư nợ)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Số tiền (VNĐ)</label>
                    <input type="number" name="amount" required step="1000" min="1000" placeholder="VD: 900000" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-semibold focus:ring-2 focus:ring-momo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nội dung ghi chú</label>
                    <input type="text" name="description" required placeholder="VD: CTO góp cns tháng 7..." class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm focus:ring-2 focus:ring-momo-500">
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3">
                    <button type="button" @click="showContributionModal = false" class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700">Hủy</button>
                    <button type="submit" class="px-5 py-2.5 bg-momo-500 hover:bg-momo-600 text-white rounded-xl text-xs font-bold shadow-md">Xác nhận Nạp</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: YÊU CẦU RÚT / VAY QUỸ -->
    <div x-show="showWithdrawModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div @click.away="showWithdrawModal = false" class="bg-white dark:bg-slate-800 rounded-3xl p-6 max-w-md w-full shadow-2xl border border-slate-100 dark:border-slate-700">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center space-x-2">
                <span>🔔 Yêu cầu Rút quỹ / Vay tiền</span>
            </h3>

            <form action="{{ route('transactions.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Người yêu cầu</label>
                    <select name="user_id" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-medium focus:ring-2 focus:ring-momo-500">
                        @foreach($members as $m)
                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Hình thức rút</label>
                    <select name="type" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-medium focus:ring-2 focus:ring-momo-500">
                        <option value="expense">Chi tiêu chung của Team (Rút quỹ chi networking, mua đồ...)</option>
                        <option value="loan">Vay cá nhân từ Quỹ (Tính vào Dư nợ cá nhân)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Số tiền rút (VNĐ)</label>
                    <input type="number" name="amount" required step="1000" min="1000" placeholder="VD: 535000" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-semibold focus:ring-2 focus:ring-momo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Mục đích rút tiền</label>
                    <input type="text" name="description" required placeholder="VD: Quỹ networking với anh 3T..." class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm focus:ring-2 focus:ring-momo-500">
                </div>

                <div class="p-3 bg-amber-50 dark:bg-amber-900/30 rounded-xl text-[11px] text-amber-800 dark:text-amber-200">
                    ℹ️ Yêu cầu này sẽ được gửi tới <strong>Chủ quỹ (Admin)</strong> phê duyệt trước khi trừ số dư.
                </div>

                <div class="flex items-center justify-end space-x-3 pt-2">
                    <button type="button" @click="showWithdrawModal = false" class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700">Hủy</button>
                    <button type="submit" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-md">Gửi yêu cầu</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: CHIA TIỀN / PHÂN BỔ LỢI NHUẬN THEO % (LIVE CALCULATOR) -->
    <div x-show="showDistributionModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div @click.away="showDistributionModal = false" class="bg-white dark:bg-slate-800 rounded-3xl p-6 max-w-lg w-full shadow-2xl border border-slate-100 dark:border-slate-700">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2 flex items-center space-x-2">
                <span>📊 Phân Chia Quỹ / Lợi Nhuận Theo % Cổ Phần</span>
            </h3>
            <p class="text-xs text-slate-500 mb-4">Nhập tổng số tiền cần rút chia, hệ thống sẽ tự động phân bổ cho từng thành viên theo tỷ lệ % đã quy định.</p>

            <form action="{{ route('distributions.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Số tiền đem chia (VNĐ)</label>
                    <input type="number" name="total_amount" x-model="distributeAmount" required step="1000" min="1000" max="{{ $fund->balance }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-base font-extrabold text-momo-600 dark:text-pink-400 focus:ring-2 focus:ring-momo-500">
                    <p class="text-[11px] text-slate-400 mt-1">Số dư tối đa có thể chia: {{ number_format($fund->balance, 0, ',', '.') }}đ</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Ghi chú đợt chia</label>
                    <input type="text" name="note" placeholder="VD: Chia lợi nhuận quý 3 / Chia tiền dư dự án..." class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm focus:ring-2 focus:ring-momo-500">
                </div>

                <!-- Live Preview Breakdown Table -->
                <div class="bg-slate-50 dark:bg-slate-700/40 p-4 rounded-2xl border border-slate-200/60 dark:border-slate-600/60 space-y-2">
                    <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Bảng dự toán thực nhận:</h4>
                    <template x-for="payout in calculatedPayouts" :key="payout.id">
                        <div class="flex items-center justify-between text-xs py-1 border-b border-slate-200/40 dark:border-slate-600/40 last:border-0">
                            <div class="flex items-center space-x-2">
                                <span class="w-6 h-6 rounded-full bg-momo-500 text-white font-bold text-[10px] flex items-center justify-center" x-text="payout.avatar"></span>
                                <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="payout.name"></span>
                                <span class="text-[10px] text-slate-400">('<span x-text="payout.share"></span>%')</span>
                            </div>
                            <span class="font-extrabold text-emerald-600 dark:text-emerald-400" x-text="new Intl.NumberFormat('vi-VN').format(payout.amount) + 'đ'"></span>
                        </div>
                    </template>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-2">
                    <button type="button" @click="showDistributionModal = false" class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700">Hủy</button>
                    <button type="submit" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-slate-900 rounded-xl text-xs font-extrabold shadow-md">Thực hiện Chia tiền</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 4: EDIT MEMBER SHARE % -->
    <div x-show="showEditShareModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div @click.away="showEditShareModal = false" class="bg-white dark:bg-slate-800 rounded-3xl p-6 max-w-sm w-full shadow-2xl border border-slate-100 dark:border-slate-700" x-data="{ shareVal: 0 }" x-effect="if (selectedUser) shareVal = selectedUser.share_percentage">
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-3">
                Cập nhật Tỷ lệ % Cổ phần
            </h3>
            
            <template x-if="selectedUser">
                <form :action="`/members/${selectedUser.id}/share`" method="POST" class="space-y-3">
                    @csrf
                    @method('PUT')
                    <div>
                        <p class="text-xs text-slate-500 mb-2">Thành viên: <strong class="text-slate-800 dark:text-white" x-text="selectedUser.name"></strong></p>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tỷ lệ % sở hữu cổ phần</label>
                        <input type="number" name="share_percentage" x-model="shareVal" step="0.1" min="0" max="100" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-bold text-momo-600 focus:ring-2 focus:ring-momo-500">
                    </div>

                    <div class="flex items-center justify-end space-x-2 pt-2">
                        <button type="button" @click="showEditShareModal = false" class="px-3 py-1.5 text-xs font-bold text-slate-500">Hủy</button>
                        <button type="submit" class="px-4 py-2 bg-momo-500 text-white rounded-xl text-xs font-bold">Lưu thay đổi</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    <!-- MODAL 5: ADD MEMBER -->
    <div x-show="showAddMemberModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div @click.away="showAddMemberModal = false" class="bg-white dark:bg-slate-800 rounded-3xl p-6 max-w-sm w-full shadow-2xl border border-slate-100 dark:border-slate-700">
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-3">
                Thêm Thành Viên Mới
            </h3>
            
            <form action="{{ route('members.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Họ và tên</label>
                    <input type="text" name="name" required placeholder="VD: Lê Văn A" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm focus:ring-2 focus:ring-momo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Email</label>
                    <input type="email" name="email" required placeholder="a.le@weamis.com" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm focus:ring-2 focus:ring-momo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tỷ lệ % cổ phần ban đầu</label>
                    <input type="number" name="share_percentage" required step="0.1" min="0" max="100" value="0" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-bold focus:ring-2 focus:ring-momo-500">
                </div>

                <div class="flex items-center justify-end space-x-2 pt-2">
                    <button type="button" @click="showAddMemberModal = false" class="px-3 py-1.5 text-xs font-bold text-slate-500">Hủy</button>
                    <button type="submit" class="px-4 py-2 bg-momo-500 text-white rounded-xl text-xs font-bold">Thêm thành viên</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
