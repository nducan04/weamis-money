@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    showCreateModal: {{ $errors->any() ? 'true' : 'false' }},
    weamisFundPct: 0,
    memberShares: {
        @foreach($members->where('role', '!=', 'admin') as $m)
            'm_{{ $m->id }}': 0,
        @endforeach
    },
    get sumShares() {
        return Object.values(this.memberShares).reduce((acc, val) => acc + (parseFloat(val) || 0), 0);
    },
    get totalPct() {
        return (parseFloat(this.weamisFundPct) || 0) + this.sumShares;
    }
}">

    <!-- Header Section -->
    <div class="flex items-center justify-end">
        <button @click="showCreateModal = true" class="px-4 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-extrabold text-xs sm:text-sm rounded-xl shadow-md transition-all duration-200 flex items-center space-x-2 cursor-pointer">
            <span>Tạo Dự Án Mới</span>
        </button>
    </div>

    <!-- Summary Metrics Header -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        @php
            $totalProjectsCount = $projects->count();
            $activeProjectsCount = $projects->where('status', 'active')->count();
            $totalProjectsIncome = $projects->sum(function($p) {
                return $p->transactions->where('status', 'approved')->whereIn('type', ['contribution', 'repayment'])->sum('amount');
            });
            $totalFundCutAll = $projects->sum(function($p) {
                $inc = $p->transactions->where('status', 'approved')->whereIn('type', ['contribution', 'repayment'])->sum('amount');
                return ($inc * $p->weamis_fund_percentage) / 100;
            });
        @endphp
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200/80 dark:border-slate-700 shadow-sm flex items-center space-x-3">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tổng Số Dự Án</p>
                <p class="text-lg font-black text-slate-900 dark:text-white">{{ $totalProjectsCount }} <span class="text-xs font-semibold text-slate-400">({{ $activeProjectsCount }} đang chạy)</span></p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200/80 dark:border-slate-700 shadow-sm flex items-center space-x-3">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Doanh Thu Dự Án</p>
                <p class="text-lg font-black text-indigo-600 dark:text-indigo-400">+{{ number_format($totalProjectsIncome, 0, ',', '.') }}đ</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200/80 dark:border-slate-700 shadow-sm flex items-center space-x-3">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Trích Về Quỹ Chung</p>
                <p class="text-lg font-black text-amber-600 dark:text-amber-400">{{ number_format($totalFundCutAll, 0, ',', '.') }}đ</p>
            </div>
        </div>
    </div>

    <!-- Projects Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($projects as $p)
            @php
                $pIncome = $p->transactions->where('status', 'approved')->whereIn('type', ['contribution', 'repayment'])->sum('amount');
                $pExpense = $p->transactions->where('status', 'approved')->where('type', 'expense')->sum('amount');
                $fundCut = ($pIncome * $p->weamis_fund_percentage) / 100;
                $distributable = max(0, $pIncome - $fundCut);
            @endphp
            <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm hover:shadow-md transition-all duration-200 flex flex-col justify-between">
                <div>
                    <!-- Code Badge & Status -->
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center space-x-2">
                            <span class="px-2.5 py-1 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-black text-xs rounded-lg uppercase tracking-wider">
                                {{ $p->code }}
                            </span>
                            @if($p->release_date)
                                <span class="px-2 py-0.5 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-bold text-[10px] rounded-lg">
                                    Go-live: {{ $p->release_date->format('d/m/Y') }}
                                </span>
                            @endif
                        </div>
                        <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-full {{ $p->status === 'active' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300' }}">
                            {{ $p->status === 'active' ? 'Đang chạy' : ($p->status === 'completed' ? 'Hoàn thành' : 'Hủy') }}
                        </span>
                    </div>

                    <h3 class="font-black text-lg text-slate-900 dark:text-white mb-1.5">{{ $p->name }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 mb-4 font-medium">{{ $p->description ?: 'Không có mô tả dự án' }}</p>

                    <!-- Financial Summary -->
                    <div class="grid grid-cols-2 gap-2 bg-slate-50 dark:bg-slate-700/40 p-3 rounded-2xl mb-4 border border-slate-100 dark:border-slate-700/60 text-xs">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Tổng Thu</p>
                            <p class="font-extrabold text-emerald-600 dark:text-emerald-400 text-sm">+{{ number_format($pIncome, 0, ',', '.') }}đ</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Quỹ Weamis ({{ number_format($p->weamis_fund_percentage, 0) }}%)</p>
                            <p class="font-extrabold text-amber-600 dark:text-amber-400 text-sm">{{ number_format($fundCut, 0, ',', '.') }}đ</p>
                        </div>
                    </div>

                    <!-- Members avatars & shares -->
                    <div class="space-y-1.5 mb-4">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Thành viên tham gia ({{ $p->members->count() }}):</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($p->members as $m)
                                <span class="px-2 py-1 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-[11px] font-bold flex items-center space-x-1">
                                    <span>{{ $m->name }}</span>
                                    <span class="text-indigo-600 dark:text-indigo-400">({{ number_format($m->pivot->share_percentage, 0) }}%)</span>
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Footer Action Link -->
                <div class="pt-3 border-t border-slate-100 dark:border-slate-700 flex items-center justify-between">
                    <span class="text-xs text-slate-400 font-medium">Lead: <strong class="text-slate-700 dark:text-slate-200">{{ $p->lead ? $p->lead->name : 'N/A' }}</strong></span>
                    <a href="{{ route('projects.show', $p) }}" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl transition flex items-center space-x-1 cursor-pointer">
                        <span>Chi tiết</span>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white dark:bg-slate-800 rounded-3xl p-10 text-center border border-slate-200 dark:border-slate-700">
                <p class="text-base font-bold text-slate-500">Chưa có dự án nào được tạo.</p>
                <button @click="showCreateModal = true" class="mt-3 px-4 py-2 bg-emerald-600 text-white font-bold text-xs rounded-xl">Tạo Dự Án Đầu Tiên</button>
            </div>
        @endforelse
    </div>

    <!-- CREATE PROJECT MODAL -->
    <div x-show="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-cloak x-transition>
        <div @click.away="showCreateModal = false" class="bg-white dark:bg-slate-800 rounded-3xl p-5 sm:p-6 w-full max-w-lg shadow-2xl border border-slate-100 dark:border-slate-700 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-100 dark:border-slate-700">
                <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
            </div>

            <form action="{{ route('projects.store') }}" method="POST" class="space-y-4">
                @csrf
                @if($errors->any())
                    <div class="p-3 bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800 rounded-xl text-xs font-bold text-rose-600 dark:text-rose-400 space-y-1">
                        @foreach($errors->all() as $err)
                            <p>• {{ $err }}</p>
                        @endforeach
                    </div>
                @endif
                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Tên dự án</label>
                        <input type="text" name="name" placeholder="VD: Everbloom" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs sm:text-sm font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Tên viết tắt</label>
                        <input type="text" name="code" placeholder="EVB" required class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-black text-slate-900 dark:text-white uppercase focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Mô tả dự án</label>
                    <textarea name="description" rows="2" placeholder="Tóm tắt dự án..." class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-medium text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Quản lý dự án</label>
                        <select name="lead_user_id" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none">
                            <option value="">-- Chọn --</option>
                            @foreach($members->where('role', '!=', 'admin') as $m)
                                <option value="{{ $m->id }}">{{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Ngày release</label>
                        <input type="date" name="release_date" class="w-full px-2 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-semibold text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none">
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

                        @foreach($members->where('role', '!=', 'admin') as $index => $m)
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
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-xl">Hủy</button>
                    <button type="submit" :disabled="totalPct > 100" :class="totalPct > 100 ? 'opacity-50 cursor-not-allowed bg-slate-400' : 'bg-emerald-600 hover:bg-emerald-700'" class="px-5 py-2 text-white font-extrabold text-xs rounded-xl shadow-md transition">Tạo Dự Án</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
