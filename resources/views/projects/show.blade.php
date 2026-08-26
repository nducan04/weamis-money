@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ 
    showEditModal: false, 
    showAddTxModal: false, 
    showEditTxModal: false,
    showCompleteModal: false,
    showDeleteProjectModal: false,
    showDeleteTxModal: false,
    deleteTxForm: { id: null },
    editTxForm: { id: null, user_id: null, responsible_user_id: '', type: 'contribution', is_fund_only: false, revenue_type: 'development', amount: '', description: '', billing_cycle: '', created_at: '' },
    createTxType: 'contribution',
    createTxMode: 'development',
    createResponsibleUserId: '',
    createIsFundOnly: false,
    editTxMode: 'development',
    openEditTxModal(tx) {
        this.editTxForm = Object.assign({}, tx);
        this.editTxForm.is_fund_only = Boolean(tx.is_fund_only);
        this.editTxForm.responsible_user_id = tx.responsible_user_id || '';
        this.editTxMode = tx.revenue_type === 'subscription' ? 'subscription' : 'development';
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
                <button type="button" @click="showCompleteModal = true" class="px-3.5 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-extrabold text-xs rounded-xl shadow-sm transition flex items-center space-x-1.5 cursor-pointer">
                    <span>Đánh Dấu Hoàn Thành</span>
                </button>
            @elseif($project->status === 'completed')
                <span class="px-3.5 py-2 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30 font-extrabold text-xs rounded-xl flex items-center space-x-1">
                    <span>Dự Án Đã Hoàn Thành (+{{ number_format($project->fund_credited_amount, 0, ',', '.') }}đ Vào Quỹ)</span>
                </span>
            @endif

            <button @click="showEditModal = true" class="px-3.5 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-extrabold text-xs rounded-xl transition flex items-center space-x-1.5 cursor-pointer">
                <span>Chỉnh Sửa Dự Án</span>
            </button>

            <button type="button" @click="showDeleteProjectModal = true" class="px-3 py-2 bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 hover:bg-rose-600 hover:text-white font-extrabold text-xs rounded-xl transition cursor-pointer">
                Xóa
            </button>
        </div>
        @else
        <div class="px-3 py-1.5 bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 text-xs font-semibold rounded-xl">
            Chỉ Lead/Admin mới có quyền sửa/xóa
        </div>
        @endif
    </div>

    <!-- High Level Overview Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-{{ $totalExpense > 0 ? '5' : '4' }} gap-3.5 sm:gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Tổng Doanh Thu</p>
            <p class="text-lg sm:text-2xl font-black text-emerald-600 dark:text-emerald-400">+{{ number_format($totalIncome, 0, ',', '.') }}đ</p>
        </div>

        @if($totalExpense > 0)
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-4 sm:p-5 border border-rose-200/60 dark:border-rose-700/40 shadow-sm">
            <p class="text-[10px] font-bold text-rose-500 uppercase tracking-wider mb-0.5">Tổng Chi Phí</p>
            <p class="text-lg sm:text-2xl font-black text-rose-600 dark:text-rose-400">-{{ number_format($totalExpense, 0, ',', '.') }}đ</p>
        </div>
        @endif

        <div class="bg-white dark:bg-slate-800 rounded-3xl p-4 sm:p-5 border border-blue-200/60 dark:border-blue-700/40 shadow-sm">
            <p class="text-[10px] font-bold text-blue-500 uppercase tracking-wider mb-0.5">Doanh Thu Phát Triển</p>
            <p class="text-lg sm:text-2xl font-black text-blue-600 dark:text-blue-400">+{{ number_format($devRevenue, 0, ',', '.') }}đ</p>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-3xl p-4 sm:p-5 border border-indigo-200/60 dark:border-indigo-700/40 shadow-sm">
            <p class="text-[10px] font-bold text-indigo-500 uppercase tracking-wider mb-0.5">Phí Vận Hành</p>
            <p class="text-lg sm:text-2xl font-black text-indigo-600 dark:text-indigo-400">+{{ number_format($subRevenue, 0, ',', '.') }}đ</p>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-3xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Thành Viên & GD</p>
            <p class="text-lg sm:text-2xl font-black text-slate-900 dark:text-white">{{ count($memberPayouts) }} <span class="text-xs font-semibold text-slate-400">TV</span> / {{ $project->transactions->where('status', 'approved')->count() }} <span class="text-xs font-semibold text-slate-400">GD</span></p>
        </div>
    </div>

    <!-- Member & Fund Revenue Distribution Section (Current Active Shares) -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 sm:p-6 border border-slate-200/80 dark:border-slate-700 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center space-x-2">
                <span>Phân Bổ Tiền Dự Án (Hiện Tại)</span>
            </h3>
            <span class="text-xs font-bold text-slate-400">Doanh thu đợt hiện tại: +{{ number_format($currentPeriodIncome, 0, ',', '.') }}đ</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3.5">
            <!-- 1. Weamis Fund Cut Card -->
            @if($project->weamis_fund_percentage > 0)
            <div class="p-4 bg-gradient-to-br from-amber-500/10 via-amber-500/5 to-transparent rounded-2xl border border-amber-500/30 space-y-2.5 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center space-x-1.5">
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
            @endif

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

    <!-- Share Timeline (Temporal Share Periods) -->
    @if(count($shareTimeline) >= 1)
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 sm:p-6 border border-slate-200/80 dark:border-slate-700 shadow-sm space-y-4">
        <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">
            Lịch Sử Cổ Phần Theo Giai Đoạn
        </h3>

        <div class="relative">
            <!-- Timeline Line -->
            <div class="absolute left-3 top-4 bottom-4 w-0.5 bg-emerald-200 dark:bg-emerald-800"></div>

            <div class="space-y-4">
                @foreach($shareTimeline as $idx => $period)
                <div class="relative pl-9">
                    <!-- Timeline Dot -->
                    <div class="absolute left-1 top-1.5 w-4 h-4 rounded-full border-2 {{ $idx === count($shareTimeline) - 1 ? 'bg-emerald-500 border-emerald-500' : 'bg-white dark:bg-slate-800 border-emerald-400' }} z-10"></div>

                    <div class="p-3.5 bg-slate-50 dark:bg-slate-700/30 rounded-2xl border border-slate-100 dark:border-slate-700/80">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
                            <span class="text-xs font-extrabold text-slate-900 dark:text-white flex items-center space-x-1.5">
                                <span>
                                    @if(count($shareTimeline) === 1)
                                        Từ trước đến nay
                                    @elseif($idx === 0)
                                        Từ trước đến {{ \Carbon\Carbon::parse($shareTimeline[1]['effective_from'])->format('d/m/Y') }}
                                    @elseif($idx < count($shareTimeline) - 1)
                                        Từ {{ \Carbon\Carbon::parse($period['effective_from'])->format('d/m/Y') }} đến {{ \Carbon\Carbon::parse($shareTimeline[$idx + 1]['effective_from'])->format('d/m/Y') }}
                                    @else
                                        Từ {{ \Carbon\Carbon::parse($period['effective_from'])->format('d/m/Y') }}
                                    @endif
                                </span>
                                @if($idx === count($shareTimeline) - 1)
                                    <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-[10px] font-black rounded-full">HIỆN TẠI</span>
                                @endif
                            </span>

                            <div class="flex items-center space-x-2">
                                <span class="text-[11px] font-extrabold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2.5 py-0.5 rounded-lg border border-emerald-200 dark:border-emerald-800/60">
                                    Doanh thu đợt: +{{ number_format($period['period_income'], 0, ',', '.') }}đ
                                </span>

                                @if($project->canManage(auth()->user()))
                                    <div x-data="{ editingDate: false, newDate: '{{ \Carbon\Carbon::parse($period['effective_from'])->format('Y-m-d') }}' }" class="relative">
                                        <button type="button" @click="editingDate = !editingDate" title="Sửa ngày mốc cổ phần" class="text-blue-500 hover:text-blue-700 dark:hover:text-blue-400 text-[11px] font-bold px-2 py-0.5 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/30 transition cursor-pointer flex items-center space-x-1">
                                            <span>Sửa ngày</span>
                                        </button>

                                        <div x-show="editingDate" x-cloak @click.away="editingDate = false" class="absolute z-30 mt-1 right-0 p-2 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 flex items-center space-x-2 min-w-[220px]">
                                            <form action="{{ route('projects.update-share-period', $project) }}" method="POST" class="flex items-center space-x-1.5 w-full">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="old_effective_from" value="{{ $period['effective_from'] }}">
                                                <input type="date" name="new_effective_from" x-model="newDate" required class="w-full px-2 py-1 text-xs font-bold rounded-lg border border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-slate-900 dark:text-white dark:[color-scheme:dark]">
                                                <button type="submit" class="px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs rounded-lg shadow-xs transition cursor-pointer">Lưu</button>
                                            </form>
                                        </div>
                                    </div>

                                    <form action="{{ route('projects.destroy-share-period', $project) }}" method="POST" onsubmit="return confirm('Xóa mốc cổ phần ngày {{ \Carbon\Carbon::parse($period['effective_from'])->format('d/m/Y') }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="effective_from" value="{{ $period['effective_from'] }}">
                                        <button type="submit" title="Xóa đợt cổ phần này" class="text-rose-500 hover:text-rose-700 dark:hover:text-rose-400 text-[11px] font-bold px-2 py-0.5 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-900/30 transition cursor-pointer flex items-center space-x-1">
                                            <span>Xóa mốc này</span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2 pt-1">
                            @if($project->weamis_fund_percentage > 0)
                            <span class="inline-flex items-center px-2.5 py-1 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 rounded-lg text-[11px] font-bold text-amber-900 dark:text-amber-300 shadow-sm">
                                Quỹ Weamis
                                <span class="ml-1 text-amber-600 dark:text-amber-400 font-black">({{ number_format($project->weamis_fund_percentage, 1) }}%)</span>
                                <span class="ml-1.5 text-amber-600 dark:text-amber-400 font-black">+{{ number_format($period['fund_cut'], 0, ',', '.') }}đ</span>
                            </span>
                            @endif
                            @foreach($period['members'] as $member)
                            <span class="inline-flex items-center px-2.5 py-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-lg text-[11px] font-bold text-slate-700 dark:text-slate-200 shadow-sm">
                                {{ $member['user'] ? $member['user']->name : 'Thành viên' }}
                                <span class="ml-1 text-indigo-600 dark:text-indigo-400 font-black">({{ number_format($member['share_percentage'], 1) }}%)</span>
                                <span class="ml-1.5 text-emerald-600 dark:text-emerald-400 font-black">+{{ number_format($member['estimated_payout'], 0, ',', '.') }}đ</span>
                            </span>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Project Transactions Audit Table -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 pb-2 border-b border-slate-100 dark:border-slate-700">
            <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center space-x-2">
                <span>Nhật Ký Giao Dịch Dự Án ({{ $projectEntries->count() }})</span>
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
                    @forelse($projectEntries as $entry)
                        @php 
                            $tx = $entry->transaction; 
                            $siblingEntries = $tx ? $tx->journalEntries : collect();
                            $isSplit = $siblingEntries->count() > 1;
                            $cleanDesc = $tx ? preg_replace('/^(contribution|expense|loan|repayment|withdrawal|profit|adjustment|Migrated):\s*/i', '', $tx->description ?? '') : ($entry->memo ?: 'N/A');
                            $customMemo = ($entry->memo && $tx && $entry->memo !== $tx->description && !str_starts_with($entry->memo, 'Migrated:') && !str_starts_with($entry->memo, ($tx->type ?? '') . ':')) ? $entry->memo : null;
                        @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                            <td class="py-3 px-3 font-mono font-black text-indigo-600 dark:text-indigo-400 text-xs">#{{ $tx?->id ?? $entry->id }}</td>
                            <td class="py-3 px-3 font-semibold text-slate-500">{{ $tx?->created_at ? $tx->created_at->format('d/m/Y H:i') : ($entry->created_at ? $entry->created_at->format('d/m/Y H:i') : 'N/A') }}</td>
                            <td class="py-3 px-3 font-bold text-slate-900 dark:text-white">{{ $tx?->user?->name ?? 'N/A' }}</td>
                            <td class="py-3 px-3">
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="font-bold text-slate-800 dark:text-slate-200 text-xs">{{ $cleanDesc }}</span>
                                        @if($tx && $tx->type === 'expense')
                                            <span class="px-2 py-0.5 bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-300 font-bold rounded text-[10px]">
                                                Chi phí
                                            </span>
                                        @elseif($tx && $tx->revenue_type === 'subscription')
                                            <span class="px-2 py-0.5 bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 font-bold rounded text-[10px]">
                                                Vận hành
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 font-bold rounded text-[10px]">
                                                Phát triển
                                            </span>
                                        @endif
                                        @if($isSplit && $tx)
                                            <span class="px-1.5 py-0.5 bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 font-extrabold rounded text-[10px]">
                                                Tách từ GD #{{ $tx->id }} (Gốc {{ number_format($tx->amount, 0, ',', '.') }}đ)
                                            </span>
                                        @endif
                                    </div>

                                    @if($customMemo)
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 italic">
                                            Ghi chú: {{ $customMemo }}
                                        </p>
                                    @endif

                                    @if($isSplit)
                                        <div class="flex flex-col gap-0.5 mt-0.5 pt-1 border-t border-slate-100 dark:border-slate-700/60 text-[10px]">
                                            <span class="text-slate-400 font-semibold">Phân bổ các phần:</span>
                                            @foreach($siblingEntries as $sibling)
                                                @php $sAccName = str_replace(['Dự án ', 'Ví '], '', $sibling->toAccount->name ?? 'N/A'); @endphp
                                                <div class="flex items-center gap-1 font-mono {{ $sibling->id === $entry->id ? 'font-black text-emerald-600 dark:text-emerald-400' : 'text-slate-500 dark:text-slate-400' }}">
                                                    <span>{{ $sibling->id === $entry->id ? '👉' : '↳' }}</span>
                                                    <span>{{ number_format($sibling->amount, 0, ',', '.') }}đ</span>
                                                    <span>→</span>
                                                    <span>{{ $sAccName }}</span>
                                                    @if($sibling->id === $entry->id)
                                                        <span class="text-[9px] px-1 bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 rounded font-bold">(Phần dự án này)</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-3 font-semibold text-xs">
                                @if($tx?->billing_cycle)
                                    <span class="inline-flex items-center px-2 py-0.5 bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 font-extrabold text-[11px] rounded-lg border border-indigo-200 dark:border-indigo-800">
                                        {{ $tx->billing_cycle }}
                                    </span>
                                @else
                                    <span class="text-slate-400 text-[11px]">—</span>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                @if($tx && $tx->evidence_type === 'file' && $tx->evidence_value)
                                    <button @click="activeEvidence = { type: 'image', value: '{{ $tx->evidence_value }}' }" class="px-2 py-1 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 font-bold rounded-lg text-[10px] cursor-pointer">Xem bill</button>
                                @elseif($tx && $tx->evidence_type === 'link' && $tx->evidence_value)
                                    <a href="{{ $tx->evidence_value }}" target="_blank" class="px-2 py-1 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-bold rounded-lg text-[10px]">Mở Link</a>
                                @elseif($tx && $tx->evidence_type === 'text' && $tx->evidence_value)
                                    <button @click="activeEvidence = { type: 'text', value: `{{ addslashes($tx->evidence_value) }}` }" class="px-2 py-1 bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 font-bold rounded-lg text-[10px] cursor-pointer">Momo text</button>
                                @else
                                    <span class="text-slate-400 text-[10px]">Không có</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-right font-extrabold {{ in_array($tx?->type ?? 'contribution', ['contribution', 'repayment', 'profit']) ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                {{ in_array($tx?->type ?? 'contribution', ['contribution', 'repayment', 'profit']) ? '+' : '-' }}{{ number_format($entry->amount, 0, ',', '.') }}đ
                            </td>
                            <td class="py-3 px-3 text-center">
                                @if($tx)
                                <div class="flex items-center justify-center space-x-1.5">
                                    <button @click="openEditTxModal({
                                        id: {{ $tx->id }},
                                        user_id: {{ $tx->user_id }},
                                        responsible_user_id: '{{ $tx->responsible_user_id ?? '' }}',
                                        is_fund_only: {{ $tx->is_fund_only ? 'true' : 'false' }},
                                        user_name: `{{ addslashes($tx->user->name ?? '') }}`,
                                        type: '{{ $tx->type }}',
                                        revenue_type: '{{ $tx->revenue_type ?? "development" }}',
                                        amount: {{ $tx->amount }},
                                        description: `{{ addslashes($tx->description) }}`,
                                        billing_cycle: `{{ addslashes($tx->billing_cycle ?? '') }}`,
                                        created_at: `{{ $tx->created_at ? $tx->created_at->format('Y-m-d\TH:i') : '' }}`
                                    })" title="Chỉnh sửa giao dịch" class="px-2 py-1 bg-slate-100 dark:bg-slate-700 hover:bg-indigo-600 hover:text-white text-slate-600 dark:text-slate-300 rounded-lg transition text-xs font-bold cursor-pointer">
                                        Sửa
                                    </button>
                                    <button type="button" @click="deleteTxForm.id = {{ $tx->id }}; showDeleteTxModal = true" title="Xóa giao dịch" class="px-2 py-1 bg-slate-100 dark:bg-slate-700 hover:bg-rose-600 hover:text-white text-slate-600 dark:text-slate-300 rounded-lg transition text-xs font-bold cursor-pointer">
                                        Xóa
                                    </button>
                                </div>
                                @else
                                    <span class="text-slate-400 text-xs">—</span>
                                @endif
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

                    <!-- Effective From Date (Temporal Share Period) -->
                    <div class="mb-3 p-2.5 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
                        <label class="block text-xs font-bold text-blue-700 dark:text-blue-300 mb-1">Ngày Hiệu Lực Cổ Phần</label>
                        <input type="date" name="share_effective_from" value="{{ now()->format('Y-m-d') }}" class="w-full px-3 py-1.5 rounded-lg border border-blue-200 dark:border-blue-700 dark:bg-slate-700 text-xs font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none dark:[color-scheme:dark]">
                    </div>

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

            <!-- TAB 2: TẠO MỚI GIAO DỊCH DỰ ÁN -->
            <div x-show="activeTxTab === 'create'" class="flex-1 overflow-y-auto min-h-0 pt-1">
                <form action="{{ route('transactions.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3.5">
                    @csrf
                    <input type="hidden" name="project_id" value="{{ $project->id }}">
                    <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                    <input type="hidden" name="type" :value="createTxType">
                    <input type="hidden" name="revenue_type" :value="createTxType === 'contribution' ? createTxMode : ''">

                    <!-- 1. Chọn Thu nhập / Chi tiêu -->
                    <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 dark:bg-slate-700/60 rounded-2xl text-center">
                        <button type="button" @click="createTxType = 'contribution'"
                                :class="createTxType === 'contribution' ? 'bg-emerald-600 text-white shadow-sm font-black' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white font-bold'"
                                class="py-2 px-2 rounded-xl text-xs transition cursor-pointer">
                            Thu nhập (Doanh thu)
                        </button>
                        <button type="button" @click="createTxType = 'expense'"
                                :class="createTxType === 'expense' ? 'bg-rose-600 text-white shadow-sm font-black' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white font-bold'"
                                class="py-2 px-2 rounded-xl text-xs transition cursor-pointer">
                            Chi tiêu (Chi phí / Trả thù lao)
                        </button>
                    </div>

                    <!-- 2. Loại Doanh Thu (chỉ hiện khi là contribution) -->
                    <div x-show="createTxType === 'contribution'" x-cloak>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Phân loại Doanh Thu</label>
                        <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 dark:bg-slate-700/60 rounded-2xl text-center">
                            <button type="button" @click="createTxMode = 'development'"
                                    :class="createTxMode === 'development' ? 'bg-white dark:bg-slate-800 text-emerald-700 dark:text-emerald-400 shadow-sm font-black' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white font-bold'"
                                    class="py-2.5 px-2 rounded-xl text-xs transition cursor-pointer">
                                Doanh thu Phát triển
                            </button>
                            <button type="button" @click="createTxMode = 'subscription'"
                                    :class="createTxMode === 'subscription' ? 'bg-white dark:bg-slate-800 text-indigo-700 dark:text-indigo-400 shadow-sm font-black' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white font-bold'"
                                    class="py-2.5 px-2 rounded-xl text-xs transition cursor-pointer">
                                Phí Vận hành
                            </button>
                        </div>
                    </div>

                    <!-- 3. Thành viên liên quan -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1" x-text="createTxType === 'expense' ? 'Chi tiêu / Rút tiền của' : 'Nộp thay cho thành viên'"></label>
                        <select name="responsible_user_id" x-model="createResponsibleUserId" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-bold text-slate-900 dark:text-white cursor-pointer outline-none">
                            <option value="">-- Chính mình ({{ auth()->user()?->name }}) --</option>
                            @foreach($allMembers->where('role', '!=', 'admin') as $m)
                                @if($m->id !== auth()->id())
                                    <option value="{{ $m->id }}">{{ $m->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <!-- 4. Tùy chọn Tính vào quỹ (khi Chi tiêu) -->
                    <div x-show="createTxType === 'expense'" x-cloak class="p-3 bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-700/50 rounded-2xl transition hover:border-amber-400/80">
                        <label class="flex items-start space-x-3 cursor-pointer">
                            <input type="checkbox" name="is_fund_only" value="1" x-model="createIsFundOnly" class="mt-0.5 w-4 h-4 rounded text-amber-600 focus:ring-amber-500 border-amber-300 dark:border-amber-600 cursor-pointer">
                            <div class="text-xs">
                                <p class="font-extrabold text-amber-900 dark:text-amber-300">Tính vào quỹ</p>
                                <p class="text-amber-700 dark:text-amber-400/90 font-medium text-[11px]">Trừ thẳng Két Quỹ chung, không tính vào Net & Gross cá nhân</p>
                            </div>
                        </label>
                    </div>

                    <!-- 5. Số tiền -->
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
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1" x-text="createTxType === 'expense' ? 'Số tiền chi' : 'Số tiền thu'"></label>
                        <div class="relative">
                            <input type="text" x-model="formattedAmount" required placeholder="0" class="w-full px-3.5 py-2.5 pr-8 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-black text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none">
                            <input type="hidden" name="amount" :value="rawAmount">
                            <span class="absolute right-3 top-2.5 text-xs font-bold text-slate-400">đ</span>
                        </div>
                    </div>

                    <!-- 6. Ghi chú nội dung & Chu kỳ thu phí -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Ghi Chú Nội Dung</label>
                            <input type="text" name="description" required placeholder="VD: Quỹ Weamis trả thù lao, Hợp đồng phát triển..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-semibold text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Chu Kỳ Thu Phí</label>
                            <input type="text" name="billing_cycle" placeholder="Không bắt buộc" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-semibold text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none">
                        </div>
                    </div>

                    <!-- 7. Ngày giao dịch -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Ngày Giao Dịch</label>
                        <input type="datetime-local" name="created_at" value="{{ now()->format('Y-m-d\TH:i') }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>

                    <!-- 8. Đính Kèm Bằng Chứng -->
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
                    <span>Chỉnh Sửa Giao Dịch</span>
                    <span class="text-xs font-mono text-indigo-600 dark:text-indigo-400" x-text="'#' + editTxForm.id"></span>
                </h4>
                <button @click="showEditTxModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 font-bold p-1 text-lg leading-none cursor-pointer">✕</button>
            </div>

            <form :action="'/transactions/' + editTxForm.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="project_id" value="{{ $project->id }}">
                <input type="hidden" name="type" :value="editTxForm.type">
                <input type="hidden" name="revenue_type" :value="editTxForm.type === 'contribution' ? editTxMode : ''">

                <!-- 1. Loại Giao Dịch -->
                <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 dark:bg-slate-700/60 rounded-2xl text-center">
                    <button type="button" @click="editTxForm.type = 'contribution'"
                            :class="editTxForm.type === 'contribution' ? 'bg-emerald-600 text-white shadow-sm font-black' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white font-bold'"
                            class="py-2 px-2 rounded-xl text-xs transition cursor-pointer">
                        Thu nhập (Doanh thu)
                    </button>
                    <button type="button" @click="editTxForm.type = 'expense'"
                            :class="editTxForm.type === 'expense' ? 'bg-rose-600 text-white shadow-sm font-black' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white font-bold'"
                            class="py-2 px-2 rounded-xl text-xs transition cursor-pointer">
                        Chi tiêu (Chi phí / Trả thù lao)
                    </button>
                </div>

                <!-- 2. Loại Doanh Thu (chỉ khi là contribution) -->
                <div x-show="editTxForm.type === 'contribution'" x-cloak>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Phân loại Doanh Thu</label>
                    <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 dark:bg-slate-700/60 rounded-2xl text-center">
                        <button type="button" @click="editTxMode = 'development'"
                                :class="editTxMode === 'development' ? 'bg-white dark:bg-slate-800 text-emerald-700 dark:text-emerald-400 shadow-sm font-black' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white font-bold'"
                                class="py-2.5 px-2 rounded-xl text-xs transition cursor-pointer">
                            Doanh thu Phát triển
                        </button>
                        <button type="button" @click="editTxMode = 'subscription'"
                                :class="editTxMode === 'subscription' ? 'bg-white dark:bg-slate-800 text-indigo-700 dark:text-indigo-400 shadow-sm font-black' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white font-bold'"
                                class="py-2.5 px-2 rounded-xl text-xs transition cursor-pointer">
                            Phí Vận hành
                        </button>
                    </div>
                </div>

                <!-- 3. Thành viên liên quan -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1" x-text="editTxForm.type === 'expense' ? 'Chi tiêu / Rút tiền của' : 'Nộp thay cho thành viên'"></label>
                    <select name="responsible_user_id" x-model="editTxForm.responsible_user_id" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-bold text-slate-900 dark:text-white cursor-pointer outline-none">
                        <option value="">-- Chính người tạo (Không nộp/rút hộ) --</option>
                        @foreach($allMembers->where('role', '!=', 'admin') as $m)
                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- 4. Tính vào quỹ (khi Chi tiêu) -->
                <div x-show="editTxForm.type === 'expense'" x-cloak class="p-3 bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-700/50 rounded-2xl transition hover:border-amber-400/80">
                    <label class="flex items-start space-x-3 cursor-pointer">
                        <input type="checkbox" name="is_fund_only" value="1" x-model="editTxForm.is_fund_only" class="mt-0.5 w-4 h-4 rounded text-amber-600 focus:ring-amber-500 border-amber-300 dark:border-amber-600 cursor-pointer">
                        <div class="text-xs">
                            <p class="font-extrabold text-amber-900 dark:text-amber-300">Tính vào quỹ</p>
                            <p class="text-amber-700 dark:text-amber-400/90 font-medium text-[11px]">Trừ thẳng Két Quỹ chung, không tính vào Net & Gross cá nhân</p>
                        </div>
                    </label>
                </div>

                <!-- 5. Amount -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1" x-text="editTxForm.type === 'expense' ? 'Số Tiền Chi (VNĐ)' : 'Số Tiền Thu (VNĐ)'"></label>
                    <input type="number" name="amount" x-model="editTxForm.amount" min="1" step="any" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-black text-slate-900 dark:text-white outline-none">
                </div>

                <!-- 6. Description -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nội dung ghi chú</label>
                    <input type="text" name="description" x-model="editTxForm.description" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-semibold text-slate-900 dark:text-white outline-none">
                </div>

                <!-- 7. Billing Cycle & Ngày giao dịch -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Chu kỳ thu phí</label>
                        <input type="text" name="billing_cycle" x-model="editTxForm.billing_cycle" placeholder="Không bắt buộc" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-semibold text-slate-900 dark:text-white outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Ngày Giao Dịch</label>
                        <input type="datetime-local" name="created_at" x-model="editTxForm.created_at" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-bold text-slate-900 dark:text-white outline-none">
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="pt-3 flex justify-end space-x-2 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="showEditTxModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-xl hover:bg-slate-200 dark:hover:bg-slate-600 transition cursor-pointer">Hủy</button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs rounded-xl shadow-md transition cursor-pointer">Lưu Thay Đổi</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Complete Project Confirmation Modal -->
    <div x-show="showCompleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="showCompleteModal = false" class="bg-white dark:bg-slate-800 rounded-3xl max-w-md w-full p-6 border border-slate-200 dark:border-slate-700 shadow-2xl space-y-5">
            <div>
                <h4 class="text-base font-black text-slate-900 dark:text-white">Xác Nhận Hoàn Thành Dự Án</h4>
                <p class="text-xs font-bold text-slate-400 font-mono">{{ $project->code }} - {{ $project->name }}</p>
            </div>

            <div class="p-4 bg-amber-50 dark:bg-amber-900/20 rounded-2xl border border-amber-200 dark:border-amber-800/40 space-y-2">
                <div class="flex items-center justify-between text-xs font-bold text-amber-900 dark:text-amber-200">
                    <span>Số tiền trích về Quỹ Chung ({{ number_format($project->weamis_fund_percentage, 0) }}%):</span>
                    <span class="text-sm font-black text-amber-600 dark:text-amber-400">+{{ number_format($fundCut, 0, ',', '.') }}đ</span>
                </div>
                <p class="text-[11px] font-semibold text-amber-700 dark:text-amber-300 leading-relaxed">
                    Số tiền <strong>+{{ number_format($fundCut, 0, ',', '.') }}đ</strong> sẽ được trích trực tiếp và <strong>CỘNG THẲNG VÀO SỐ DƯ QUỸ CHUNG WEAMIS</strong>.
                </p>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-2">
                <button type="button" @click="showCompleteModal = false" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-200 font-extrabold text-xs rounded-xl transition cursor-pointer">
                    Hủy bỏ
                </button>
                <form action="{{ route('projects.update', $project) }}" method="POST">
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
                    <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-black text-xs rounded-xl shadow-md transition cursor-pointer">
                        Đồng Ý Hoàn Thành
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Project Confirmation Modal -->
    <div x-show="showDeleteProjectModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="showDeleteProjectModal = false" class="bg-white dark:bg-slate-800 rounded-3xl max-w-md w-full p-6 border border-slate-200 dark:border-slate-700 shadow-2xl space-y-5">
            <div>
                <h4 class="text-base font-black text-slate-900 dark:text-white">Xác Nhận Xóa Dự Án</h4>
                <p class="text-xs font-bold text-slate-400 font-mono">{{ $project->code }} - {{ $project->name }}</p>
            </div>

            <p class="text-xs font-semibold text-slate-600 dark:text-slate-300 leading-relaxed">
                Bạn có chắc chắn muốn xóa dự án này? Dự án sẽ được chuyển vào lịch sử đã xóa và lưu trữ an toàn trong CSDL.
            </p>

            <div class="flex items-center justify-end space-x-3 pt-2">
                <button type="button" @click="showDeleteProjectModal = false" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-200 font-extrabold text-xs rounded-xl transition cursor-pointer">
                    Hủy bỏ
                </button>
                <form action="{{ route('projects.destroy', $project) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-black text-xs rounded-xl shadow-md transition cursor-pointer">
                        Xác Nhận Xóa
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Transaction Confirmation Modal -->
    <div x-show="showDeleteTxModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="showDeleteTxModal = false" class="bg-white dark:bg-slate-800 rounded-3xl max-w-md w-full p-6 border border-slate-200 dark:border-slate-700 shadow-2xl space-y-5">
            <div>
                <h4 class="text-base font-black text-slate-900 dark:text-white">Xác Nhận Xóa Giao Dịch</h4>
                <p class="text-xs font-bold text-slate-400 font-mono" x-text="'Giao dịch #' + deleteTxForm.id"></p>
            </div>

            <p class="text-xs font-semibold text-slate-600 dark:text-slate-300 leading-relaxed">
                Bạn có chắc chắn muốn xóa giao dịch này khỏi dự án? Giao dịch sẽ được lưu trữ an toàn trong CSDL.
            </p>

            <div class="flex items-center justify-end space-x-3 pt-2">
                <button type="button" @click="showDeleteTxModal = false" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-300 font-extrabold text-xs rounded-xl transition cursor-pointer">
                    Hủy bỏ
                </button>
                <form :action="'/transactions/' + deleteTxForm.id" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-black text-xs rounded-xl shadow-md transition cursor-pointer">
                        Xác Nhận Xóa
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
