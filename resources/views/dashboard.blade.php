@extends('layouts.app')

@section('content')
<div x-data="{ 
    showAddModal: false,
    showEditModal: false,
    showDeleteModal: false,
    showDistributionModal: false,
    showMemberModal: false,
    selectedTx: null,
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

    <!-- Page Header & Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                Báo Cáo Thu Chi & Quản Lý Quỹ
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Theo dõi tổng quan tài chính, danh sách giao dịch thu chi và công cụ phân chia tiền theo % cổ phần.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <button @click="showAddModal = true" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                <span>Thêm Thu Chi Mới</span>
            </button>

            <button @click="showDistributionModal = true" class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-extrabold text-xs rounded-xl shadow-sm transition flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                <span>Phân Chia % Quỹ</span>
            </button>

            <button @click="showMemberModal = true" class="px-3.5 py-2.5 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-200 font-bold text-xs rounded-xl transition flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <span>Thành Viên ({{ $members->count() }})</span>
            </button>
        </div>
    </div>

    <!-- 1. Top Stat Cards Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Card 1: Số Dư Quỹ -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Số Dư Quỹ Hiện Tại</span>
                <span class="p-2 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-300">
                    💵
                </span>
            </div>
            <p class="text-2xl font-extrabold text-slate-900 dark:text-white mt-2">
                {{ number_format($fund->balance, 0, ',', '.') }}<span class="text-lg font-bold">đ</span>
            </p>
            <p class="text-[11px] text-slate-400 mt-1">
                Lợi nhuận tích lũy: <strong class="text-emerald-600 dark:text-emerald-400">{{ number_format($fund->total_profit, 0, ',', '.') }}đ</strong>
            </p>
        </div>

        <!-- Card 2: Tổng Thu -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tổng Thu (Góp + Trả)</span>
                <span class="p-2 rounded-xl bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300">
                    📥
                </span>
            </div>
            <p class="text-2xl font-extrabold text-blue-600 dark:text-blue-400 mt-2">
                +{{ number_format($totalIncome, 0, ',', '.') }}<span class="text-lg font-bold">đ</span>
            </p>
            <p class="text-[11px] text-slate-400 mt-1">Tất cả nguồn tiền đã nạp</p>
        </div>

        <!-- Card 3: Tổng Chi -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tổng Chi Nhóm</span>
                <span class="p-2 rounded-xl bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-300">
                    📤
                </span>
            </div>
            <p class="text-2xl font-extrabold text-rose-600 dark:text-rose-400 mt-2">
                -{{ number_format($totalExpense, 0, ',', '.') }}<span class="text-lg font-bold">đ</span>
            </p>
            <p class="text-[11px] text-slate-400 mt-1">Các khoản chi tiêu tập thể</p>
        </div>

        <!-- Card 4: Tổng Cho Vay -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tổng Tiền Đang Cho Vay</span>
                <span class="p-2 rounded-xl bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-300">
                    🤝
                </span>
            </div>
            <p class="text-2xl font-extrabold text-amber-600 dark:text-amber-400 mt-2">
                {{ number_format($totalLoans, 0, ',', '.') }}<span class="text-lg font-bold">đ</span>
            </p>
            <p class="text-[11px] text-slate-400 mt-1">Dư nợ cá nhân chưa hoàn trả</p>
        </div>
    </div>

    <!-- Admin Pending Requests Alert Bar -->
    @if($pendingTransactions->count() > 0)
        <div class="mb-6 bg-amber-500/10 border-2 border-amber-500/30 rounded-2xl p-4">
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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach($pendingTransactions as $pending)
                    <div class="flex items-center justify-between p-3 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-amber-200 dark:border-amber-900/40">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-800 font-bold text-xs flex items-center justify-center">
                                {{ $pending->user->avatar ?? substr($pending->user->name, 0, 2) }}
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-900 dark:text-white">
                                    {{ $pending->user->name }}
                                    <span class="text-[10px] text-slate-400 font-normal">({{ $pending->type === 'expense' ? 'Chi chung' : 'Vay cá nhân' }})</span>
                                </p>
                                <p class="text-xs text-slate-600 dark:text-slate-300 font-medium">"{{ $pending->description }}"</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="font-extrabold text-sm text-rose-600">-{{ number_format($pending->amount, 0, ',', '.') }}đ</span>
                            <form action="{{ route('transactions.approve', $pending) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition">Duyệt</button>
                            </form>
                            <form action="{{ route('transactions.reject', $pending) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3 py-1 bg-slate-200 hover:bg-rose-600 hover:text-white text-slate-700 rounded-lg text-xs font-bold transition">Hủy</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- 2. Member Breakdown Table -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm mb-6">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-700">
            <div>
                <h3 class="font-extrabold text-slate-900 dark:text-white text-base">Thống Kê Thu Chi Theo Cá Nhân</h3>
                <p class="text-xs text-slate-400">Tổng quan tình hình đóng góp, nợ nần và số tiền nhận khi chia quỹ theo % cổ phần</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-500 uppercase font-bold text-[10px] tracking-wider">
                    <tr>
                        <th class="py-3 px-4 rounded-l-xl">Thành viên</th>
                        <th class="py-3 px-4">Tỷ lệ % Cổ phần</th>
                        <th class="py-3 px-4">Tổng Đã Góp (Nạp)</th>
                        <th class="py-3 px-4">Tổng Đã Rút/Vay</th>
                        <th class="py-3 px-4">Dư Nợ Cá Nhân</th>
                        <th class="py-3 px-4 rounded-r-xl">Tiền Nhận Khi Chia Quỹ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                    @foreach($memberStats as $stat)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-700/30 transition">
                            <td class="py-3 px-4 font-bold text-slate-900 dark:text-white flex items-center space-x-2.5">
                                <div class="w-7 h-7 rounded-full bg-slate-800 text-emerald-400 font-bold text-xs flex items-center justify-center">
                                    {{ $stat['user']->avatar ?? substr($stat['user']->name, 0, 2) }}
                                </div>
                                <div>
                                    <span>{{ $stat['user']->name }}</span>
                                    @if($stat['user']->role === 'admin')
                                        <span class="ml-1 text-[9px] bg-amber-400 text-slate-950 font-extrabold px-1.5 py-0.2 rounded">Chủ quỹ</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-extrabold text-emerald-600 dark:text-emerald-400 text-sm">
                                    {{ $stat['share'] }}%
                                </span>
                            </td>
                            <td class="py-3 px-4 font-bold text-emerald-600 dark:text-emerald-400">
                                +{{ number_format($stat['contributed'], 0, ',', '.') }}đ
                            </td>
                            <td class="py-3 px-4 font-bold text-rose-600 dark:text-rose-400">
                                -{{ number_format($stat['withdrawn'], 0, ',', '.') }}đ
                            </td>
                            <td class="py-3 px-4 font-bold">
                                @if($stat['debt'] > 0)
                                    <span class="px-2 py-0.5 bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 rounded font-bold">
                                        {{ number_format($stat['debt'], 0, ',', '.') }}đ
                                    </span>
                                @else
                                    <span class="text-slate-400">0đ</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-extrabold text-amber-600 dark:text-amber-400 text-sm">
                                {{ number_format($stat['estimated_payout'], 0, ',', '.') }}đ
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- 3. Transaction Data Table with Filter & Full CRUD -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-5 pb-4 border-b border-slate-100 dark:border-slate-700">
            <div>
                <h3 class="font-extrabold text-slate-900 dark:text-white text-base">Lịch Sử Thu Chi & Giao Dịch</h3>
                <p class="text-xs text-slate-400">Quản lý, tìm kiếm, chỉnh sửa và xóa lịch sử thu chi tiền của team</p>
            </div>

            <!-- Filter Bar -->
            <form action="{{ route('dashboard') }}" method="GET" class="flex flex-wrap items-center gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm kiếm nội dung..." class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-medium focus:ring-2 focus:ring-emerald-500">
                
                <select name="member_id" class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-medium focus:ring-2 focus:ring-emerald-500">
                    <option value="">Tất cả thành viên</option>
                    @foreach($members as $m)
                        <option value="{{ $m->id }}" {{ request('member_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                    @endforeach
                </select>

                <select name="type" class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-medium focus:ring-2 focus:ring-emerald-500">
                    <option value="">Tất cả loại GD</option>
                    <option value="contribution" {{ request('type') == 'contribution' ? 'selected' : '' }}>Góp quỹ (Thu)</option>
                    <option value="expense" {{ request('type') == 'expense' ? 'selected' : '' }}>Chi tiêu (Chi)</option>
                    <option value="loan" {{ request('type') == 'loan' ? 'selected' : '' }}>Vay cá nhân</option>
                    <option value="repayment" {{ request('type') == 'repayment' ? 'selected' : '' }}>Trả nợ vay</option>
                    <option value="distribution" {{ request('type') == 'distribution' ? 'selected' : '' }}>Chia tiền %</option>
                </select>

                <button type="submit" class="px-3 py-1.5 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition">
                    Lọc
                </button>
                
                @if(request()->hasAny(['search', 'member_id', 'type', 'status']))
                    <a href="{{ route('dashboard') }}" class="px-2.5 py-1.5 text-xs font-bold text-rose-600 hover:underline">Xóa lọc</a>
                @endif
            </form>
        </div>

        <!-- Data Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-500 uppercase font-bold text-[10px] tracking-wider">
                    <tr>
                        <th class="py-3 px-4 rounded-l-xl">#</th>
                        <th class="py-3 px-4">Thời Gian</th>
                        <th class="py-3 px-4">Thành Viên</th>
                        <th class="py-3 px-4">Phân Loại GD</th>
                        <th class="py-3 px-4">Số Tiền (VNĐ)</th>
                        <th class="py-3 px-4">Nội Dung / Mô Tả</th>
                        <th class="py-3 px-4">Trạng Thái</th>
                        <th class="py-3 px-4 text-right rounded-r-xl">Hành Động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                    @forelse($transactions as $tx)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-700/30 transition">
                            <td class="py-3.5 px-4 font-semibold text-slate-400">{{ $tx->id }}</td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-slate-500">
                                {{ $tx->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white flex items-center space-x-2">
                                <span class="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200 font-bold text-[10px] flex items-center justify-center">
                                    {{ $tx->user->avatar ?? substr($tx->user->name, 0, 2) }}
                                </span>
                                <span>{{ $tx->user->name }}</span>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($tx->type === 'contribution')
                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 font-bold rounded">Góp quỹ</span>
                                @elseif($tx->type === 'expense')
                                    <span class="px-2 py-0.5 bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 font-bold rounded">Chi tiêu team</span>
                                @elseif($tx->type === 'loan')
                                    <span class="px-2 py-0.5 bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300 font-bold rounded">Vay cá nhân</span>
                                @elseif($tx->type === 'repayment')
                                    <span class="px-2 py-0.5 bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 font-bold rounded">Trả nợ vay</span>
                                @elseif($tx->type === 'distribution')
                                    <span class="px-2 py-0.5 bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 font-bold rounded">Chia tiền %</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-extrabold whitespace-nowrap">
                                @if(in_array($tx->type, ['contribution', 'repayment']))
                                    <span class="text-emerald-600 dark:text-emerald-400">+{{ number_format($tx->amount, 0, ',', '.') }}đ</span>
                                @else
                                    <span class="text-slate-900 dark:text-slate-100">-{{ number_format($tx->amount, 0, ',', '.') }}đ</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-medium text-slate-700 dark:text-slate-300 max-w-xs truncate">
                                {{ $tx->description }}
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($tx->status === 'approved')
                                    <span class="text-emerald-600 font-bold">✓ Đã duyệt</span>
                                @elseif($tx->status === 'pending')
                                    <span class="text-amber-600 font-bold">⏳ Chờ duyệt</span>
                                @else
                                    <span class="text-slate-400 font-bold line-through">Từ chối</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap space-x-1">
                                <!-- Edit Button -->
                                <button @click="selectedTx = {{ json_encode($tx) }}; showEditModal = true" class="px-2.5 py-1 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded font-bold transition">
                                    ✏️ Sửa
                                </button>

                                <!-- Delete Button -->
                                <button @click="selectedTx = {{ json_encode($tx) }}; showDeleteModal = true" class="px-2.5 py-1 bg-rose-50 dark:bg-rose-900/30 hover:bg-rose-600 hover:text-white text-rose-600 font-bold rounded transition">
                                    🗑️ Xóa
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-slate-400">Không tìm thấy giao dịch nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-700">
            {{ $transactions->links() }}
        </div>
    </div>

    <!-- MODAL: THÊM GIAO DỊCH MỚI -->
    <div x-show="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div @click.away="showAddModal = false" class="bg-white dark:bg-slate-800 rounded-3xl p-6 max-w-md w-full shadow-2xl border border-slate-100 dark:border-slate-700">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">➕ Thêm Giao Dịch Thu Chi</h3>

            <form action="{{ route('transactions.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Thành viên thực hiện</label>
                    <select name="user_id" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-medium focus:ring-2 focus:ring-emerald-500">
                        @foreach($members as $m)
                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Phân loại Giao dịch</label>
                    <select name="type" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-medium focus:ring-2 focus:ring-emerald-500">
                        <option value="contribution">🟢 Góp Quỹ (Tiền thu vào quỹ)</option>
                        <option value="expense">🔴 Chi Tiêu Nhóm (Rút quỹ chi chung)</option>
                        <option value="loan">🟣 Vay Cá Nhân (Tạo nợ cá nhân)</option>
                        <option value="repayment">🔵 Trả Nợ Vay (Hoàn nợ cho quỹ)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Số tiền (VNĐ)</label>
                    <input type="number" name="amount" required step="1000" min="1000" placeholder="VD: 500000" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-bold focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nội dung / Lí do</label>
                    <input type="text" name="description" required placeholder="VD: Tiền mua nước uống họp team..." class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm focus:ring-2 focus:ring-emerald-500">
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2 text-xs font-bold text-slate-500">Hủy</button>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 text-white rounded-xl text-xs font-bold shadow-md hover:bg-emerald-700">Lưu Giao Dịch</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: CHỈNH SỬA GIAO DỊCH -->
    <div x-show="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div @click.away="showEditModal = false" class="bg-white dark:bg-slate-800 rounded-3xl p-6 max-w-md w-full shadow-2xl border border-slate-100 dark:border-slate-700">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">✏️ Chỉnh Sửa Giao Dịch</h3>

            <template x-if="selectedTx">
                <form :action="`/transactions/${selectedTx.id}`" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Thành viên</label>
                        <select name="user_id" x-model="selectedTx.user_id" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-medium">
                            @foreach($members as $m)
                                <option value="{{ $m->id }}">{{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Loại Giao Dịch</label>
                        <select name="type" x-model="selectedTx.type" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-medium">
                            <option value="contribution">Góp quỹ (Thu)</option>
                            <option value="expense">Chi tiêu chung (Chi)</option>
                            <option value="loan">Vay cá nhân</option>
                            <option value="repayment">Trả nợ vay</option>
                            <option value="distribution">Chia tiền %</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Số tiền (VNĐ)</label>
                        <input type="number" name="amount" x-model="selectedTx.amount" required step="1000" min="1000" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-bold">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nội dung</label>
                        <input type="text" name="description" x-model="selectedTx.description" required class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm">
                    </div>

                    <div class="p-3 bg-amber-50 dark:bg-amber-900/30 rounded-xl text-[11px] text-amber-800 dark:text-amber-200">
                        ⚠️ Việc chỉnh sửa giao dịch đã duyệt sẽ tự động điều chỉnh lại Số dư quỹ và Dư nợ cá nhân tương ứng.
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-2">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2 text-xs font-bold text-slate-500">Hủy</button>
                        <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700">Cập Nhật</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    <!-- MODAL: XÓA GIAO DỊCH -->
    <div x-show="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div @click.away="showDeleteModal = false" class="bg-white dark:bg-slate-800 rounded-3xl p-6 max-w-sm w-full shadow-2xl border border-slate-100 dark:border-slate-700">
            <h3 class="text-lg font-bold text-rose-600 mb-2">🗑️ Xác Nhận Xóa Giao Dịch</h3>
            
            <template x-if="selectedTx">
                <div class="space-y-3">
                    <p class="text-xs text-slate-600 dark:text-slate-300">
                        Bạn có chắc chắn muốn xóa giao dịch: <strong x-text="selectedTx.description"></strong> (<span x-text="new Intl.NumberFormat('vi-VN').format(selectedTx.amount) + 'đ'"></span>)?
                    </p>
                    <p class="text-[11px] text-slate-400">
                        Hệ thống sẽ hoàn tác tác động của giao dịch này đối với số dư quỹ.
                    </p>

                    <form :action="`/transactions/${selectedTx.id}`" method="POST" class="flex items-center justify-end space-x-2 pt-3">
                        @csrf
                        @method('DELETE')
                        <button type="button" @click="showDeleteModal = false" class="px-3 py-1.5 text-xs font-bold text-slate-500">Hủy</button>
                        <button type="submit" class="px-4 py-2 bg-rose-600 text-white rounded-xl text-xs font-bold hover:bg-rose-700">Xóa Giao Dịch</button>
                    </form>
                </div>
            </template>
        </div>
    </div>

    <!-- MODAL: PHÂN CHIA LỢI NHUẬN % (CALCULATOR) -->
    <div x-show="showDistributionModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div @click.away="showDistributionModal = false" class="bg-white dark:bg-slate-800 rounded-3xl p-6 max-w-lg w-full shadow-2xl border border-slate-100 dark:border-slate-700">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">📊 Phân Chia Quỹ / Lợi Nhuận Theo % Cổ Phần</h3>
            <p class="text-xs text-slate-500 mb-4">Nhập tổng số tiền cần rút chia, hệ thống sẽ tự động phân bổ cho từng thành viên theo tỷ lệ % đã gán.</p>

            <form action="{{ route('distributions.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Số tiền đem chia (VNĐ)</label>
                    <input type="number" name="total_amount" x-model="distributeAmount" required step="1000" min="1000" max="{{ $fund->balance }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-base font-extrabold text-emerald-600 focus:ring-2 focus:ring-emerald-500">
                    <p class="text-[11px] text-slate-400 mt-1">Số dư quỹ tối đa: {{ number_format($fund->balance, 0, ',', '.') }}đ</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Ghi chú đợt chia</label>
                    <input type="text" name="note" placeholder="VD: Chia lợi nhuận quý 3..." class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm focus:ring-2 focus:ring-emerald-500">
                </div>

                <!-- Live Preview -->
                <div class="bg-slate-50 dark:bg-slate-700/40 p-4 rounded-2xl border border-slate-200/60 dark:border-slate-600/60 space-y-2">
                    <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Bảng phân bổ thực nhận:</h4>
                    <template x-for="payout in calculatedPayouts" :key="payout.id">
                        <div class="flex items-center justify-between text-xs py-1 border-b border-slate-200/40 dark:border-slate-600/40 last:border-0">
                            <div class="flex items-center space-x-2">
                                <span class="w-6 h-6 rounded-full bg-slate-800 text-white font-bold text-[10px] flex items-center justify-center" x-text="payout.avatar"></span>
                                <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="payout.name"></span>
                                <span class="text-[10px] text-slate-400">('<span x-text="payout.share"></span>%')</span>
                            </div>
                            <span class="font-extrabold text-emerald-600 dark:text-emerald-400" x-text="new Intl.NumberFormat('vi-VN').format(payout.amount) + 'đ'"></span>
                        </div>
                    </template>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-2">
                    <button type="button" @click="showDistributionModal = false" class="px-4 py-2 text-xs font-bold text-slate-500">Hủy</button>
                    <button type="submit" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-slate-950 rounded-xl text-xs font-extrabold shadow-md">Thực Hiện Chia Tiền</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: QUẢN LÝ THÀNH VIÊN & % CỔ PHẦN -->
    <div x-show="showMemberModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div @click.away="showMemberModal = false" class="bg-white dark:bg-slate-800 rounded-3xl p-6 max-w-lg w-full shadow-2xl border border-slate-100 dark:border-slate-700">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-3">👥 Quản Lý Thành Viên & % Cổ Phần</h3>

            <div class="space-y-3 mb-6 max-h-60 overflow-y-auto pr-1">
                @foreach($members as $m)
                    <form action="{{ route('members.updateShare', $m) }}" method="POST" class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
                        @csrf
                        @method('PUT')
                        <div class="flex items-center space-x-2.5">
                            <div class="w-7 h-7 rounded-full bg-slate-800 text-white font-bold text-xs flex items-center justify-center">
                                {{ $m->avatar ?? substr($m->name, 0, 2) }}
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-900 dark:text-white">{{ $m->name }}</p>
                                <p class="text-[10px] text-slate-400">{{ $m->email }}</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-2">
                            <input type="number" name="share_percentage" value="{{ $m->share_percentage }}" step="0.1" min="0" max="100" class="w-20 px-2 py-1 rounded-lg border border-slate-300 dark:border-slate-600 dark:bg-slate-800 text-xs font-bold text-center">
                            <span class="text-xs font-bold text-slate-500">%</span>
                            <button type="submit" class="px-2.5 py-1 bg-emerald-600 text-white rounded-lg text-xs font-bold hover:bg-emerald-700">Lưu</button>
                        </div>
                    </form>
                @endforeach
            </div>

            <!-- Form Add Member -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-700">
                <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">+ Thêm Thành Viên Mới</h4>
                <form action="{{ route('members.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                    @csrf
                    <input type="text" name="name" required placeholder="Họ và tên" class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs">
                    <input type="email" name="email" required placeholder="Email" class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs">
                    <div class="flex items-center space-x-1">
                        <input type="number" name="share_percentage" required value="0" step="0.1" placeholder="% cổ phần" class="w-full px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-bold">
                        <button type="submit" class="px-3 py-1.5 bg-slate-900 text-white rounded-xl text-xs font-bold flex-shrink-0">Thêm</button>
                    </div>
                </form>
            </div>

            <div class="flex items-center justify-end pt-4">
                <button type="button" @click="showMemberModal = false" class="px-4 py-2 text-xs font-bold text-slate-500">Đóng</button>
            </div>
        </div>
    </div>

</div>
@endsection
