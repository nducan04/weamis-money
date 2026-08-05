@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ 
    showEditModal: false, 
    showAddTxModal: false, 
    showEditTxModal: false,
    editTxForm: { id: null, user_id: null, type: 'contribution', amount: '', description: '', billing_cycle: '' },
    openEditTxModal(tx) {
        this.editTxForm = Object.assign({}, tx);
        this.showEditTxModal = true;
    },
    activeTxTab: 'attach',
    txSearchQuery: '',
    selectedTxIds: [],
    activeEvidence: null, 
    txEvidenceMode: 'none', 
    selectedFileName: '', 
    selectedFilePreview: '', 
    weamisFundPct: {{ $project->weamis_fund_percentage }},
    memberShares: {
        @foreach($allMembers->where('role', '!=', 'admin') as $m)
            @php $pm = $project->projectMembers->where('user_id', $m->id)->first(); @endphp
            'm_{{ $m->id }}': {{ $pm ? $pm->share_percentage : 0 }},
        @endforeach
    },
    get sumShares() {
        return Object.values(this.memberShares).reduce((acc, val) => acc + (parseFloat(val) || 0), 0);
    },
    get totalPct() {
        return (parseFloat(this.weamisFundPct) || 0) + this.sumShares;
    },
    triggerTxFilePick() { 
        this.txEvidenceMode = 'file'; 
        this.$refs.txEvidenceFileInput.click(); 
    }, 
    onTxFileSelected(e) { 
        let file = e.target.files[0]; 
        if (file) { 
            this.selectedFileName = file.name; 
            if (file.type.startsWith('image/')) { 
                let reader = new FileReader(); 
                reader.onload = (evt) => { this.selectedFilePreview = evt.target.result; }; 
                reader.readAsDataURL(file); 
            } else { 
                this.selectedFilePreview = ''; 
            } 
        } 
    }, 
    clearTxFile() { 
        if (this.$refs.txEvidenceFileInput) this.$refs.txEvidenceFileInput.value = ''; 
        this.selectedFileName = ''; 
        this.selectedFilePreview = ''; 
        this.txEvidenceMode = 'none'; 
    } 
}">

    <!-- Top Back Navigation -->
    <div>
        <a href="{{ route('projects.index') }}" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-extrabold text-xs sm:text-sm rounded-xl border border-slate-200/80 dark:border-slate-700 shadow-sm transition-all duration-200 cursor-pointer">
            <span>Quay lại</span>
        </a>
    </div>

    <!-- Header Section -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 sm:p-6 border border-slate-200/80 dark:border-slate-700 shadow-md flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2.5 mb-1">
                <span class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-black text-xs rounded-lg uppercase tracking-wider">
                    {{ $project->code }}
                </span>
                @if($project->release_date)
                    <span class="px-2.5 py-0.5 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-bold text-xs rounded-lg">
                        Release: {{ $project->release_date->format('d/m/Y') }}
                    </span>
                @endif
            </div>
            <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center space-x-2">
                <span>{{ $project->name }}</span>
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 font-medium mt-1">
                {{ $project->description ?: 'Chưa có mô tả dự án' }}
            </p>
        </div>

        @if($project->canManage(auth()->user()))
        <div class="flex flex-wrap items-center gap-2">
            @if($project->status === 'active')
                <form action="{{ route('projects.update', $project) }}" method="POST" onsubmit="return confirm('Xác nhận HOÀN THÀNH dự án này? Số tiền {{ number_format($fundCut, 0, ',', '.') }}đ ({{ number_format($project->weamis_fund_percentage, 0) }}% Trích Về Quỹ) sẽ được CỘNG THẲNG VÀO QUỸ CHUNG!')">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="name" value="{{ $project->name }}">
                    <input type="hidden" name="description" value="{{ $project->description }}">
                    <input type="hidden" name="weamis_fund_percentage" value="{{ $project->weamis_fund_percentage }}">
                    <input type="hidden" name="lead_user_id" value="{{ $project->lead_user_id }}">
                    <input type="hidden" name="status" value="completed">
                    @foreach($project->projectMembers as $index => $pm)
                        <input type="hidden" name="members[{{ $index }}][user_id]" value="{{ $pm->user_id }}">
                        <input type="hidden" name="members[{{ $index }}][share_percentage]" value="{{ $pm->share_percentage }}">
                    @endforeach
                    <button type="submit" class="px-3.5 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-extrabold text-xs rounded-xl transition flex items-center space-x-1.5 cursor-pointer">
                        <span>Đánh Dấu Hoàn Thành</span>
                    </button>
                </form>
            @elseif($project->status === 'completed')
                <span class="px-3.5 py-2 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30 font-extrabold text-xs rounded-xl flex items-center space-x-1">
                    <span>Dự Án Đã Hoàn Thành (+{{ number_format($project->fund_credited_amount, 0, ',', '.') }}đ Vào Quỹ)</span>
                </span>
            @endif

            <button @click="showEditModal = true" class="px-3.5 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-extrabold text-xs rounded-xl transition flex items-center space-x-1.5 cursor-pointer">
                <span>Chỉnh Sửa Dự Án</span>
            </button>
            <form action="{{ route('projects.destroy', $project) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa dự án này?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-3 py-2 bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 hover:bg-rose-600 hover:text-white font-extrabold text-xs rounded-xl transition cursor-pointer">
                    Xóa
                </button>
            </form>
        </div>
        @else
        <div class="px-3 py-1.5 bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 text-xs font-semibold rounded-xl">
            Chỉ Lead/Admin mới có quyền sửa/xóa
        </div>
        @endif
    </div>

    <!-- High Level Overview Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Tổng Doanh Thu Dự Án</p>
                <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400">+{{ number_format($totalIncome, 0, ',', '.') }}đ</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-black text-xl flex-shrink-0">
                💰
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Thành Viên Tham Gia</p>
                <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $project->projectMembers->count() }} <span class="text-sm font-bold text-slate-500">Thành viên</span></p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-black text-xl flex-shrink-0">
                👥
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Tổng Giao Dịch Ghi Nhận</p>
                <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $project->transactions->where('status', 'approved')->count() }} <span class="text-sm font-bold text-slate-500">Giao dịch</span></p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-black text-xl flex-shrink-0">
                🧾
            </div>
        </div>
    </div>

    <!-- Member & Fund Revenue Distribution Section -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 sm:p-6 border border-slate-200/80 dark:border-slate-700 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center space-x-2">
                <span>Phân Bổ Tiền Dự Án Cho Quỹ & Các Thành Viên</span>
            </h3>
            <span class="text-xs font-bold text-slate-400">Doanh thu khả dụng: +{{ number_format($totalIncome, 0, ',', '.') }}đ</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3.5">
            <!-- 1. Weamis Fund Cut Card -->
            <div class="p-4 bg-gradient-to-br from-amber-500/10 via-amber-500/5 to-transparent rounded-2xl border border-amber-500/30 space-y-2.5 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center space-x-1.5">
                            <span class="text-base">🏛️</span>
                            <p class="text-xs font-black text-amber-950 dark:text-amber-300">Quỹ Chung Weamis</p>
                        </div>
                        <p class="text-[10px] text-amber-600 dark:text-amber-400 font-extrabold mt-0.5">Tỷ lệ trích: {{ number_format($project->weamis_fund_percentage, 0) }}%</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] text-amber-600/80 dark:text-amber-400/80 font-bold uppercase">Trích về Quỹ</p>
                        <p class="text-sm font-black text-amber-600 dark:text-amber-400">+{{ number_format($fundCut, 0, ',', '.') }}đ</p>
                    </div>
                </div>

                <!-- Fund Cut Progress Bar -->
                <div class="w-full bg-amber-200/50 dark:bg-amber-900/30 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-gradient-to-r from-amber-500 to-yellow-400 h-full rounded-full" style="width: {{ min(100, max(5, $project->weamis_fund_percentage)) }}%"></div>
                </div>

                <p class="text-[9px] font-bold text-amber-600/90 dark:text-amber-400/90 pt-0.5">
                    {{ $project->status === 'completed' ? '✓ Đã cộng vào Quỹ Chung' : '➔ Cộng vào Quỹ khi Đánh dấu Hoàn Thành' }}
                </p>
            </div>

            <!-- 2. Member Payout Cards -->
            @foreach($memberPayouts as $payout)
                <div class="p-4 bg-slate-50 dark:bg-slate-700/30 rounded-2xl border border-slate-100 dark:border-slate-700/80 space-y-2.5 flex flex-col justify-between">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-extrabold text-slate-900 dark:text-white">{{ $payout['user']->name }}</p>
                            <p class="text-[10px] text-indigo-600 dark:text-indigo-400 font-extrabold mt-0.5">Tỷ lệ phân bổ: {{ number_format($payout['share_percentage'], 1) }}%</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] text-slate-400 font-bold uppercase">Số tiền nhận</p>
                            <p class="text-sm font-black text-emerald-600 dark:text-emerald-400">+{{ number_format($payout['estimated_payout'], 0, ',', '.') }}đ</p>
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
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 pb-2 border-b border-slate-100 dark:border-slate-700">
            <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center space-x-2">
                <span>Nhật Ký Giao Dịch Dự Án ({{ $project->transactions->count() }})</span>
            </h3>
            <button @click="showAddTxModal = true" class="px-3.5 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-extrabold text-xs rounded-xl shadow-md transition flex items-center space-x-1.5 cursor-pointer self-start sm:self-auto">
                <span>Thêm Giao Dịch</span>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-100 dark:bg-slate-700/60 text-slate-500 uppercase font-bold text-[10px] tracking-wider">
                    <tr>
                        <th class="py-3 px-3 rounded-l-xl">Mã ID</th>
                        <th class="py-3 px-3">Thời gian</th>
                        <th class="py-3 px-3">Người nhập</th>
                        <th class="py-3 px-3">Nội dung</th>
                        <th class="py-3 px-3">Chu kỳ thu phí</th>
                        <th class="py-3 px-3">Bằng chứng</th>
                        <th class="py-3 px-3 text-right">Số tiền</th>
                        <th class="py-3 px-3 text-center rounded-r-xl">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                    @forelse($project->transactions as $tx)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                            <td class="py-3 px-3 font-mono font-black text-indigo-600 dark:text-indigo-400 text-xs">#{{ $tx->id }}</td>
                            <td class="py-3 px-3 font-semibold text-slate-500">{{ $tx->created_at ? $tx->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                            <td class="py-3 px-3 font-bold text-slate-900 dark:text-white">{{ $tx->user->name ?? 'N/A' }}</td>
                            <td class="py-3 px-3 font-medium text-slate-800 dark:text-slate-200">
                                {{ $tx->description }}
                            </td>
                            <td class="py-3 px-3 font-semibold text-xs">
                                @if($tx->billing_cycle)
                                    <span class="inline-flex items-center px-2 py-0.5 bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 font-extrabold text-[11px] rounded-lg border border-indigo-200 dark:border-indigo-800">
                                        📅 {{ $tx->billing_cycle }}
                                    </span>
                                @else
                                    <span class="text-slate-400 text-[11px]">—</span>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                @if($tx->evidence_type === 'file' && $tx->evidence_value)
                                    <button @click="activeEvidence = { type: 'image', value: '{{ $tx->evidence_value }}' }" class="px-2 py-1 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 font-bold rounded-lg text-[10px] cursor-pointer">🖼️ Xem bill</button>
                                @elseif($tx->evidence_type === 'link' && $tx->evidence_value)
                                    <a href="{{ $tx->evidence_value }}" target="_blank" class="px-2 py-1 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-bold rounded-lg text-[10px]">🔗 Mở Link</a>
                                @elseif($tx->evidence_type === 'text' && $tx->evidence_value)
                                    <button @click="activeEvidence = { type: 'text', value: `{{ addslashes($tx->evidence_value) }}` }" class="px-2 py-1 bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 font-bold rounded-lg text-[10px] cursor-pointer">📝 Momo text</button>
                                @else
                                    <span class="text-slate-400 text-[10px]">Không có</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-right font-extrabold {{ in_array($tx->type, ['contribution', 'repayment']) ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                {{ in_array($tx->type, ['contribution', 'repayment']) ? '+' : '-' }}{{ number_format($tx->amount, 0, ',', '.') }}đ
                            </td>
                            <td class="py-3 px-3 text-center">
                                <div class="flex items-center justify-center space-x-1.5">
                                    <button @click="openEditTxModal({
                                        id: {{ $tx->id }},
                                        user_id: {{ $tx->user_id }},
                                        user_name: `{{ addslashes($tx->user->name ?? '') }}`,
                                        type: '{{ $tx->type }}',
                                        amount: {{ $tx->amount }},
                                        description: `{{ addslashes($tx->description) }}`,
                                        billing_cycle: `{{ addslashes($tx->billing_cycle ?? '') }}`
                                    })" title="Chỉnh sửa giao dịch" class="p-1.5 bg-slate-100 dark:bg-slate-700 hover:bg-indigo-600 hover:text-white text-slate-600 dark:text-slate-300 rounded-lg transition text-xs font-bold cursor-pointer">
                                        ✏️
                                    </button>
                                    <form action="{{ route('transactions.destroy', $tx) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa giao dịch #{{ $tx->id }} này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Xóa giao dịch (Soft Delete)" class="p-1.5 bg-slate-100 dark:bg-slate-700 hover:bg-rose-600 hover:text-white text-slate-600 dark:text-slate-300 rounded-lg transition text-xs font-bold cursor-pointer">
                                            🗑️
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-slate-400">Chưa có giao dịch thu chi nào gắn với dự án này.</td>
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
                <h4 class="font-bold text-sm text-slate-900 dark:text-white">📌 Bằng chứng</h4>
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
            <div class="flex items-center justify-end mb-2">
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
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Quản lý dự án</label>
                        <select name="lead_user_id" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none">
                            <option value="">-- Chọn lead --</option>
                            @foreach($allMembers->where('role', '!=', 'admin') as $m)
                                <option value="{{ $m->id }}" {{ $project->lead_user_id == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Ngày release</label>
                        <input type="date" name="release_date" value="{{ $project->release_date ? $project->release_date->format('Y-m-d') : '' }}" class="w-full px-2 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-semibold text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                </div>

                <!-- Member Shares Config -->
                <div class="pt-2">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">Tỷ lệ % Phân Bổ Thành Viên</label>
                        <span class="px-2.5 py-1 rounded-lg text-xs font-black transition-all"
                              :class="{
                                  'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300': totalPct === 100,
                                  'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300': totalPct < 100,
                                  'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 animate-pulse': totalPct > 100
                              }">
                            Tổng: <span x-text="totalPct"></span>% / 100%
                        </span>
                    </div>

                    <template x-if="totalPct > 100">
                        <div class="p-2.5 mb-2 bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 text-xs font-bold rounded-xl border border-rose-200 dark:border-rose-800 flex items-center space-x-1.5">
                            <span>⚠️</span>
                            <span>Cảnh báo: Tổng % (Trích Quỹ + Phân bổ thành viên) là <strong x-text="totalPct + '%'"></strong>, vượt quá 100%! Vui lòng điều chỉnh lại.</span>
                        </div>
                    </template>

                    <div class="space-y-2 bg-slate-50 dark:bg-slate-700/30 p-3 rounded-2xl border border-slate-200/60 dark:border-slate-700">
                        <!-- Quỹ Weamis (Tương tự 1 thành viên nhận) -->
                        <div class="flex items-center justify-between text-xs pb-2 mb-1 border-b border-slate-200/60 dark:border-slate-700/60">
                            <span class="font-bold text-amber-600 dark:text-amber-400 w-1/2 flex items-center space-x-1">
                                <span>Quỹ Weamis</span>
                            </span>
                            <div class="flex items-center space-x-1 w-1/3">
                                <input type="number" name="weamis_fund_percentage" x-model.number="weamisFundPct" min="0" max="100" step="0.5" required class="w-full px-2 py-1 rounded-lg border border-slate-200 dark:border-slate-600 dark:bg-slate-700 font-extrabold text-xs text-amber-600 dark:text-amber-400 text-center outline-none">
                                <span class="font-bold text-slate-400">%</span>
                            </div>
                        </div>

                        @foreach($allMembers->where('role', '!=', 'admin') as $index => $m)
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-800 dark:text-slate-200 w-1/2">{{ $m->name }}</span>
                                <input type="hidden" name="members[{{ $index }}][user_id]" value="{{ $m->id }}">
                                <div class="flex items-center space-x-1 w-1/3">
                                    <input type="number" name="members[{{ $index }}][share_percentage]" x-model.number="memberShares['m_{{ $m->id }}']" min="0" max="100" step="0.5" class="w-full px-2 py-1 rounded-lg border border-slate-200 dark:border-slate-600 dark:bg-slate-700 font-extrabold text-xs text-indigo-600 dark:text-indigo-300 text-center outline-none">
                                    <span class="font-bold text-slate-400">%</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="pt-3 flex justify-end space-x-2">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-xl">Hủy</button>
                    <button type="submit" :disabled="totalPct > 100" :class="totalPct > 100 ? 'opacity-50 cursor-not-allowed bg-slate-400' : 'bg-emerald-600 hover:bg-emerald-700'" class="px-5 py-2 text-white font-extrabold text-xs rounded-xl shadow-md transition">Lưu</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: THÊM / GẮN GIAO DỊCH CHO DỰ ÁN -->
    <div x-show="showAddTxModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-cloak x-transition>
        <div @click.away="showAddTxModal = false" class="bg-white dark:bg-slate-800 rounded-3xl p-5 sm:p-6 w-full max-w-xl shadow-2xl border border-slate-100 dark:border-slate-700 max-h-[90vh] flex flex-col">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700 flex-shrink-0">
                <div>
                    <h3 class="text-base font-black text-slate-900 dark:text-white">Thêm Giao Dịch Cho Dự Án</h3>
                    <p class="text-xs font-bold text-emerald-600 dark:text-emerald-400">{{ $project->name }} ({{ $project->code }})</p>
                </div>
                <button @click="showAddTxModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 font-bold p-1 text-lg leading-none cursor-pointer">✕</button>
            </div>

            <!-- Tab Navigation Header -->
            <div class="flex items-center space-x-2 my-3 p-1 bg-slate-100 dark:bg-slate-700/60 rounded-2xl flex-shrink-0">
                <button type="button" @click="activeTxTab = 'attach'"
                        :class="activeTxTab === 'attach' ? 'bg-white dark:bg-slate-800 text-emerald-600 dark:text-emerald-400 shadow-xs' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200'"
                        class="flex-1 py-2 text-xs font-extrabold rounded-xl transition-all text-center cursor-pointer flex items-center justify-center space-x-1.5">
                    <span>Gắn GD Có Sẵn</span>
                    <span class="px-2 py-0.5 text-[10px] rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-black">
                        {{ $unassignedTransactions->count() }}
                    </span>
                </button>
                <button type="button" @click="activeTxTab = 'create'"
                        :class="activeTxTab === 'create' ? 'bg-white dark:bg-slate-800 text-emerald-600 dark:text-emerald-400 shadow-xs' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200'"
                        class="flex-1 py-2 text-xs font-extrabold rounded-xl transition-all text-center cursor-pointer flex items-center justify-center space-x-1.5">
                    <span>Tạo GD Mới</span>
                </button>
            </div>

            <!-- TAB 1: GẮN GIAO DỊCH CÓ SẴN (CHƯA THUỘC DỰ ÁN NÀO) -->
            <div x-show="activeTxTab === 'attach'" class="flex-1 overflow-y-auto min-h-0 space-y-3 pt-1">
                <div>
                    <input type="text" x-model="txSearchQuery" placeholder="Tìm theo ID, nội dung,..." class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <form action="{{ route('projects.attach-transactions', $project) }}" method="POST" class="space-y-3">
                    @csrf
                    <div class="space-y-2 max-h-[45vh] overflow-y-auto pr-1">
                        @forelse($unassignedTransactions as $uTx)
                            <label x-show="!txSearchQuery || '{{ $uTx->id }} {{ strtolower(addslashes($uTx->description)) }} {{ strtolower(addslashes($uTx->user->name ?? '')) }}'.includes(txSearchQuery.trim().toLowerCase())"
                                   class="flex items-start space-x-3 p-3 bg-slate-50 dark:bg-slate-700/40 hover:bg-emerald-50/50 dark:hover:bg-emerald-900/20 rounded-2xl border border-slate-200/80 dark:border-slate-700 transition cursor-pointer">
                                <input type="checkbox" name="transaction_ids[]" value="{{ $uTx->id }}" x-model="selectedTxIds" class="mt-1 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-1.5 min-w-0">
                                            <span class="px-1.5 py-0.5 bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300 rounded font-mono font-extrabold text-[10px] flex-shrink-0">
                                                ID: {{ $uTx->id }}
                                            </span>
                                            <p class="text-xs font-extrabold text-slate-900 dark:text-white truncate">{{ $uTx->description }}</p>
                                        </div>
                                        <span class="text-xs font-black flex-shrink-0 ml-2 {{ in_array($uTx->type, ['contribution', 'repayment']) ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                            {{ in_array($uTx->type, ['contribution', 'repayment']) ? '+' : '-' }}{{ number_format($uTx->amount, 0, ',', '.') }}đ
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 mt-1 font-medium">
                                        <span>{{ $uTx->user->name ?? 'N/A' }} • {{ $uTx->created_at ? $uTx->created_at->format('d/m/Y H:i') : '' }}</span>
                                        <span class="uppercase text-[9px] font-bold px-1.5 py-0.5 rounded bg-slate-200 dark:bg-slate-600 text-slate-700 dark:text-slate-300">
                                            {{ $uTx->type === 'contribution' ? 'Doanh thu' : ($uTx->type === 'expense' ? 'Chi tiêu' : $uTx->type) }}
                                        </span>
                                    </div>
                                </div>
                            </label>
                        @empty
                            <div class="text-center py-8 text-xs text-slate-400">
                                Không có giao dịch nào chưa gắn dự án trong Lịch sử GD.
                            </div>
                        @endforelse
                    </div>

                    <div class="pt-3 border-t border-slate-100 dark:border-slate-700 flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400">
                            Đã chọn: <strong class="text-emerald-600 dark:text-emerald-400" x-text="selectedTxIds.length"></strong> giao dịch
                        </span>
                        <div class="flex space-x-2">
                            <button type="button" @click="showAddTxModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-xl hover:bg-slate-200 transition cursor-pointer">Hủy</button>
                            <button type="submit" :disabled="selectedTxIds.length === 0" :class="selectedTxIds.length === 0 ? 'opacity-50 cursor-not-allowed bg-slate-400' : 'bg-emerald-600 hover:bg-emerald-700'" class="px-5 py-2 text-white font-extrabold text-xs rounded-xl shadow-md transition cursor-pointer">
                                Gắn Vào Dự Án
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- TAB 2: TẠO MỚI GIAO DỊCH TỐI GIẢN (5 TRƯỜNG CHÍNH) -->
            <div x-show="activeTxTab === 'create'" class="flex-1 overflow-y-auto min-h-0 pt-1">
                <form action="{{ route('transactions.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3.5">
                    @csrf
                    <input type="hidden" name="project_id" value="{{ $project->id }}">
                    <input type="hidden" name="user_id" value="{{ auth()->id() }}">

                    <!-- 1. Loại giao dịch -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Loại Giao Dịch</label>
                        <select name="type" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none">
                            <option value="contribution">Góp quỹ</option>
                            <option value="expense">Chi tiêu chung</option>
                            <option value="loan">Vay cá nhân</option>
                            <option value="repayment">Trả nợ</option>
                        </select>
                    </div>

                    <!-- 2. Số tiền -->
                    <div x-data="{
                        rawAmount: '',
                        get formattedAmount() {
                            if (!this.rawAmount) return '';
                            let clean = this.rawAmount.toString().replace(/\D/g, '');
                            return clean ? parseInt(clean, 10).toLocaleString('vi-VN') : '';
                        },
                        set formattedAmount(val) {
                            let clean = val.replace(/\D/g, '');
                            this.rawAmount = clean;
                        }
                    }">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Số Tiền (VNĐ)</label>
                        <div class="relative">
                            <input type="text" x-model="formattedAmount" required placeholder="0" class="w-full px-3.5 py-2.5 pr-8 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-black text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none">
                            <input type="hidden" name="amount" :value="rawAmount">
                            <span class="absolute right-3 top-2.5 text-xs font-bold text-slate-400">đ</span>
                        </div>
                    </div>

                    <!-- 3. Ghi chú nội dung & 4. Chu kỳ thu phí -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Ghi Chú Nội Dung</label>
                            <input type="text" name="description" required placeholder="VD: Phí vận hành Server..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-semibold text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Chu Kỳ Thu Phí</label>
                            <input type="text" name="billing_cycle" placeholder="VD: Tháng 05/2026, Quý 2/2026..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-semibold text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none">
                        </div>
                    </div>

                    <!-- 5. Đính Kèm Bằng Chứng -->
                    <div class="space-y-1.5 pt-1">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Đính Kèm Bằng Chứng</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button type="button" @click="triggerTxFilePick()" class="w-full py-2 rounded-xl text-xs font-extrabold transition-all flex items-center justify-center space-x-1 cursor-pointer active:scale-95 text-center" :class="txEvidenceMode === 'file' && selectedFileName ? 'bg-emerald-600 text-white ring-2 ring-emerald-500' : 'bg-slate-50 dark:bg-slate-700/60 text-emerald-600 dark:text-emerald-400 border border-slate-200 dark:border-slate-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/30'">
                                <span>Bill</span>
                            </button>
                            <button type="button" @click="txEvidenceMode = txEvidenceMode === 'link' ? 'none' : 'link'" class="w-full py-2 rounded-xl text-xs font-extrabold transition-all flex items-center justify-center space-x-1 cursor-pointer active:scale-95 text-center" :class="txEvidenceMode === 'link' ? 'bg-blue-600 text-white ring-2 ring-blue-500' : 'bg-slate-50 dark:bg-slate-700/60 text-blue-600 dark:text-blue-400 border border-slate-200 dark:border-slate-600 hover:bg-blue-50 dark:hover:bg-blue-900/30'">
                                <span>Link</span>
                            </button>
                            <button type="button" @click="txEvidenceMode = txEvidenceMode === 'text' ? 'none' : 'text'" class="w-full py-2 rounded-xl text-xs font-extrabold transition-all flex items-center justify-center space-x-1 cursor-pointer active:scale-95 text-center" :class="txEvidenceMode === 'text' ? 'bg-amber-600 text-white ring-2 ring-amber-500' : 'bg-slate-50 dark:bg-slate-700/60 text-amber-600 dark:text-amber-400 border border-slate-200 dark:border-slate-600 hover:bg-amber-50 dark:hover:bg-amber-900/30'">
                                <span>Momo</span>
                            </button>
                        </div>

                        <input type="hidden" name="evidence_type" :value="txEvidenceMode">
                        <input type="file" x-ref="txEvidenceFileInput" name="evidence_file" accept="image/*,.pdf" class="hidden" @change="onTxFileSelected($event)">

                        <div x-show="txEvidenceMode === 'file' && selectedFileName" x-cloak class="flex items-center justify-between p-2 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700/50 rounded-xl mt-2">
                            <div class="flex items-center space-x-2 min-w-0">
                                <template x-if="selectedFilePreview">
                                    <img :src="selectedFilePreview" class="w-8 h-8 object-cover rounded-lg shadow-sm flex-shrink-0">
                                </template>
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate" x-text="selectedFileName"></span>
                            </div>
                            <button type="button" @click="clearTxFile()" class="p-1 text-rose-500 hover:text-rose-700 font-bold text-xs">✕</button>
                        </div>

                        <div x-show="txEvidenceMode === 'link'" x-cloak class="mt-2">
                            <input type="url" name="evidence_link" placeholder="https://momo.vn/transaction/..." class="w-full text-xs px-3 py-2 border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white">
                        </div>
                        <div x-show="txEvidenceMode === 'text'" x-cloak class="mt-2">
                            <textarea name="evidence_text" rows="2" placeholder="Dán thông tin sao kê MoMo..." class="w-full text-xs px-3 py-2 border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white"></textarea>
                        </div>
                    </div>

                    <div class="pt-3 mt-4 border-t border-slate-100 dark:border-slate-700 flex items-center justify-end space-x-2">
                        <button type="button" @click="showAddTxModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-xl hover:bg-slate-200 dark:hover:bg-slate-600 transition cursor-pointer">Hủy</button>
                        <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-md transition cursor-pointer">Lưu Giao Dịch</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: CHỈNH SỬA GIAO DỊCH -->
    <div x-show="showEditTxModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-cloak x-transition>
        <div @click.away="showEditTxModal = false" class="bg-white dark:bg-slate-800 rounded-3xl p-5 sm:p-6 w-full max-w-lg shadow-2xl border border-slate-100 dark:border-slate-700 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100 dark:border-slate-700">
                <h4 class="font-extrabold text-base text-slate-900 dark:text-white flex items-center space-x-2">
                    <span>✏️ Chỉnh Sửa Giao Dịch</span>
                    <span class="text-xs font-mono text-indigo-600 dark:text-indigo-400" x-text="'#' + editTxForm.id"></span>
                </h4>
                <button @click="showEditTxModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 font-bold p-1 text-lg leading-none cursor-pointer">✕</button>
            </div>

            <form :action="'/transactions/' + editTxForm.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="project_id" value="{{ $project->id }}">

                <!-- Member Selector -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Thành viên thực hiện</label>
                    @if(auth()->user()?->isAdmin())
                        <select name="user_id" x-model="editTxForm.user_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-bold text-slate-900 dark:text-white outline-none">
                            @foreach($allMembers as $m)
                                <option value="{{ $m->id }}">{{ $m->name }}</option>
                            @endforeach
                        </select>
                    @else
                        <input type="hidden" name="user_id" :value="editTxForm.user_id">
                        <input type="text" :value="editTxForm.user_name || '{{ auth()->user()->name }}'" readonly class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-100 dark:bg-slate-700/50 text-xs font-bold text-slate-700 dark:text-slate-300 outline-none">
                    @endif
                </div>

                <!-- Transaction Type & Amount -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Loại Giao Dịch</label>
                        <select name="type" x-model="editTxForm.type" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-bold text-slate-900 dark:text-white outline-none">
                            <option value="contribution">Góp quỹ</option>
                            <option value="expense">Chi tiêu chung</option>
                            <option value="loan">Vay cá nhân</option>
                            <option value="repayment">Trả nợ</option>
                            <option value="withdrawal">Rút lương</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Số Tiền (VNĐ)</label>
                        <input type="number" name="amount" x-model="editTxForm.amount" min="1000" step="1000" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-black text-slate-900 dark:text-white outline-none">
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nội dung ghi chú</label>
                    <input type="text" name="description" x-model="editTxForm.description" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-semibold text-slate-900 dark:text-white outline-none">
                </div>

                <!-- Billing Cycle -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Chu kỳ thu phí</label>
                    <input type="text" name="billing_cycle" x-model="editTxForm.billing_cycle" placeholder="VD: Tháng 08/2026..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-semibold text-slate-900 dark:text-white outline-none">
                </div>

                <!-- Action Buttons -->
                <div class="pt-3 flex justify-end space-x-2 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="showEditTxModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-xl hover:bg-slate-200 dark:hover:bg-slate-600 transition cursor-pointer">Hủy</button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs rounded-xl shadow-md transition cursor-pointer">Lưu Thay Đổi</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
