@extends('layouts.app')

@section('content')
<div x-data="{ 
    // Filter & Pagination State
    rawTransactions: {{ \Illuminate\Support\Js::from($allTransactions) }},
    txSearchText: '',
    txFilterType: 'all',
    txFilterUserId: 'all',
    txFilterDate: '',
    txPage: 1,
    txPerPage: 10,
    timeHorizon: 'ALL',

    get filteredTransactions() {
        return this.rawTransactions.filter(tx => {
            if (this.txFilterType !== 'all' && tx.type !== this.txFilterType) return false;
            if (this.txFilterUserId !== 'all' && String(tx.user_id) !== String(this.txFilterUserId)) return false;
            if (this.txFilterDate && (!tx.created_at || !tx.created_at.startsWith(this.txFilterDate))) return false;
            if (this.txSearchText) {
                let q = this.txSearchText.toLowerCase();
                let descMatch = tx.description && tx.description.toLowerCase().includes(q);
                let userMatch = tx.user_name && tx.user_name.toLowerCase().includes(q);
                let amountMatch = String(tx.amount).includes(q);
                if (!descMatch && !userMatch && !amountMatch) return false;
            }
            return true;
        });
    },

    get paginatedTransactions() {
        let start = (this.txPage - 1) * this.txPerPage;
        return this.filteredTransactions.slice(start, start + this.txPerPage);
    },

    get totalTxPages() {
        return Math.ceil(this.filteredTransactions.length / this.txPerPage) || 1;
    }
}"
class="space-y-6">

    <!-- 1. Google Finance Hero Card: Net Portfolio & Key Metrics -->
    <div class="bg-white dark:bg-[#1e1f20] rounded-3xl p-5 sm:p-7 border border-[#dadce0] dark:border-[#282a2c] shadow-sm transition-all">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-[#f1f3f4] dark:border-[#282a2c]">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="text-xs font-bold text-[#5f6368] dark:text-[#9aa0a6] uppercase tracking-wider">Số Dư Quỹ Weamis</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#e6f4ea] text-[#137333] dark:bg-[#072711] dark:text-[#81c995]">
                        Live Balance
                    </span>
                </div>
                <div class="flex items-baseline space-x-3 mt-2">
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-[#202124] dark:text-white tracking-tight font-mono">
                        {{ number_format($fund->balance, 0, ',', '.') }}<span class="text-xl sm:text-2xl font-bold ml-1">₫</span>
                    </h2>
                </div>
            </div>

            <!-- Time Horizon Pill Selector (Google Finance Style) -->
            <div class="flex items-center space-x-1 bg-[#f1f3f4] dark:bg-[#282a2c] p-1 rounded-full self-start md:self-auto text-xs font-bold">
                <template x-for="horizon in ['1D', '1W', '1M', 'YTD', 'ALL']" :key="horizon">
                    <button @click="timeHorizon = horizon" 
                            :class="timeHorizon === horizon ? 'bg-white dark:bg-[#1e1f20] text-[#1a73e8] dark:text-[#8ab4f8] shadow-sm' : 'text-[#5f6368] dark:text-[#9aa0a6] hover:text-[#202124] dark:hover:text-white'"
                            class="px-3 py-1 rounded-full transition-all cursor-pointer"
                            x-text="horizon">
                    </button>
                </template>
            </div>
        </div>

        <!-- Metric Tonal Cards Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mt-6">
            
            <!-- Metric 1: Tổng Thu -->
            <div class="bg-[#f8f9fa] dark:bg-[#282a2c]/50 p-4 rounded-2xl border border-[#e8eaed] dark:border-[#3c4043]/40">
                <span class="text-[11px] font-bold text-[#5f6368] dark:text-[#9aa0a6] uppercase tracking-wider block">Tổng Thu</span>
                <p class="text-lg sm:text-2xl font-extrabold text-[#137333] dark:text-[#81c995] mt-1 font-mono">
                    +{{ number_format($totalIncome, 0, ',', '.') }}<span class="text-xs sm:text-sm font-bold ml-0.5">₫</span>
                </p>
                <span class="text-[10px] text-[#5f6368] dark:text-[#9aa0a6] font-medium mt-1 block">Dòng tiền ghi có</span>
            </div>

            <!-- Metric 2: Tổng Chi -->
            <div class="bg-[#f8f9fa] dark:bg-[#282a2c]/50 p-4 rounded-2xl border border-[#e8eaed] dark:border-[#3c4043]/40">
                <span class="text-[11px] font-bold text-[#5f6368] dark:text-[#9aa0a6] uppercase tracking-wider block">Tổng Chi</span>
                <p class="text-lg sm:text-2xl font-extrabold text-[#c5221f] dark:text-[#f28b82] mt-1 font-mono">
                    -{{ number_format($totalExpense, 0, ',', '.') }}<span class="text-xs sm:text-sm font-bold ml-0.5">₫</span>
                </p>
                <span class="text-[10px] text-[#5f6368] dark:text-[#9aa0a6] font-medium mt-1 block">Hoạt động & chi phí</span>
            </div>

            <!-- Metric 3: Đang Cho Vay -->
            <div class="bg-[#f8f9fa] dark:bg-[#282a2c]/50 p-4 rounded-2xl border border-[#e8eaed] dark:border-[#3c4043]/40">
                <span class="text-[11px] font-bold text-[#5f6368] dark:text-[#9aa0a6] uppercase tracking-wider block">Đang Cho Vay</span>
                <p class="text-lg sm:text-2xl font-extrabold text-[#1a73e8] dark:text-[#8ab4f8] mt-1 font-mono">
                    {{ number_format($totalLoans, 0, ',', '.') }}<span class="text-xs sm:text-sm font-bold ml-0.5">₫</span>
                </p>
                <span class="text-[10px] text-[#5f6368] dark:text-[#9aa0a6] font-medium mt-1 block">Nợ phải thu thành viên</span>
            </div>

            <!-- Metric 4: Hành Động Nhanh -->
            <div class="bg-[#f8f9fa] dark:bg-[#282a2c]/50 p-4 rounded-2xl border border-[#e8eaed] dark:border-[#3c4043]/40 flex flex-col justify-between">
                <span class="text-[11px] font-bold text-[#5f6368] dark:text-[#9aa0a6] uppercase tracking-wider block">Thao Tác</span>
                <div class="flex items-center space-x-2 mt-2">
                    <a href="{{ route('history') }}" class="w-full py-2 bg-[#1a73e8] hover:bg-[#1557b0] text-white rounded-xl font-bold text-xs text-center shadow-sm transition">
                        + Thêm Giao Dịch
                    </a>
                </div>
            </div>

        </div>
    </div>

    <!-- 2. Charts & Financial Visualizations -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Donut Distribution Chart (8 Cols) -->
        <div class="lg:col-span-8 bg-white dark:bg-[#1e1f20] rounded-3xl p-5 sm:p-6 border border-[#dadce0] dark:border-[#282a2c] shadow-sm">
            <div class="flex items-center justify-between pb-4 border-b border-[#f1f3f4] dark:border-[#282a2c] mb-4">
                <div>
                    <h3 class="font-bold text-sm sm:text-base text-[#202124] dark:text-white">Tỷ Lệ Cơ Cấu Dòng Tiền</h3>
                    <p class="text-[11px] text-[#5f6368] dark:text-[#9aa0a6]">Phân bổ tỷ trọng Thu, Chi và Cho Vay</p>
                </div>
            </div>

            @php
                $donutItems = [];
                $donutTotal = max(1, $totalIncome + $totalExpense + $totalLoans);
                $rawDonut = [
                    ['label' => 'Tổng Thu', 'value' => $totalIncome, 'color' => '#137333'],
                    ['label' => 'Tổng Chi', 'value' => $totalExpense, 'color' => '#c5221f'],
                    ['label' => 'Đang Vay', 'value' => $totalLoans, 'color' => '#1a73e8'],
                ];
                $cx = 220; $cy = 150; $outerR = 92; $innerR = 62;
                $cumAngle = -90;
                foreach ($rawDonut as $item) {
                    if ($item['value'] <= 0) continue;
                    $pct = ($item['value'] / $donutTotal) * 100;
                    $angle = ($pct / 100) * 360;
                    $startAngle = $cumAngle;
                    $endAngle = $cumAngle + $angle;
                    $cumAngle = $endAngle;
                    $effAngle = min($angle, 359.99);
                    $effEnd = $startAngle + $effAngle;
                    $sRad = deg2rad($startAngle); $eRad = deg2rad($effEnd);
                    $x1 = $cx + $outerR * cos($sRad); $y1 = $cy + $outerR * sin($sRad);
                    $x2 = $cx + $outerR * cos($eRad); $y2 = $cy + $outerR * sin($eRad);
                    $ix1 = $cx + $innerR * cos($eRad); $iy1 = $cy + $innerR * sin($eRad);
                    $ix2 = $cx + $innerR * cos($sRad); $iy2 = $cy + $innerR * sin($sRad);
                    $la = $effAngle > 180 ? 1 : 0;
                    $d = "M {$x1} {$y1} A {$outerR} {$outerR} 0 {$la} 1 {$x2} {$y2} L {$ix1} {$iy1} A {$innerR} {$innerR} 0 {$la} 0 {$ix2} {$iy2} Z";
                    $midRad = deg2rad(($startAngle + $endAngle) / 2);
                    $lsx = round($cx + $outerR * cos($midRad), 1);
                    $lsy = round($cy + $outerR * sin($midRad), 1);
                    $lex = round($cx + ($outerR + 24) * cos($midRad), 1);
                    $ley = round($cy + ($outerR + 24) * sin($midRad), 1);
                    $isR = $lex >= $cx;
                    $lhx = $isR ? $lex + 28 : $lex - 28;
                    $donutItems[] = [
                        'd' => $d, 'color' => $item['color'], 'label' => $item['label'],
                        'pct' => number_format($pct, 1, ',', '.'),
                        'lsx' => $lsx, 'lsy' => $lsy, 'lex' => $lex, 'ley' => $ley,
                        'lhx' => round($lhx, 1), 'lhy' => round($ley, 1),
                        'anchor' => $isR ? 'start' : 'end', 'txOff' => $isR ? 5 : -5,
                    ];
                }
            @endphp

            <svg viewBox="0 0 440 300" class="w-full max-w-[460px] mx-auto">
                @foreach($donutItems as $sl)
                    <path d="{{ $sl['d'] }}" fill="{{ $sl['color'] }}" class="transition-opacity duration-200 hover:opacity-85" />
                @endforeach
                <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $innerR }}" class="fill-white dark:fill-[#1e1f20]" />
                <text x="{{ $cx }}" y="{{ $cy - 8 }}" text-anchor="middle" font-size="10" font-weight="700" class="fill-[#5f6368] dark:fill-[#9aa0a6] uppercase tracking-wider">Số Dư</text>
                <text x="{{ $cx }}" y="{{ $cy + 12 }}" text-anchor="middle" font-size="14" font-weight="800" class="fill-[#202124] dark:fill-white font-mono">{{ number_format($fund->balance, 0, ',', '.') }}₫</text>

                @foreach($donutItems as $sl)
                    <polyline points="{{ $sl['lsx'] }},{{ $sl['lsy'] }} {{ $sl['lex'] }},{{ $sl['ley'] }} {{ $sl['lhx'] }},{{ $sl['lhy'] }}" fill="none" stroke="{{ $sl['color'] }}" stroke-width="2" />
                    <circle cx="{{ $sl['lhx'] }}" cy="{{ $sl['lhy'] }}" r="3" fill="{{ $sl['color'] }}" />
                    <text x="{{ $sl['lhx'] + $sl['txOff'] }}" y="{{ $sl['lhy'] - 3 }}" text-anchor="{{ $sl['anchor'] }}" font-size="12" font-weight="800" class="fill-[#202124] dark:fill-white font-mono">{{ $sl['pct'] }}%</text>
                    <text x="{{ $sl['lhx'] + $sl['txOff'] }}" y="{{ $sl['lhy'] + 12 }}" text-anchor="{{ $sl['anchor'] }}" font-size="10" font-weight="700" fill="{{ $sl['color'] }}">{{ $sl['label'] }}</text>
                @endforeach
            </svg>

            <!-- Legend Pills -->
            <div class="flex items-center justify-center flex-wrap gap-x-5 gap-y-2 mt-4 pt-3 border-t border-[#f1f3f4] dark:border-[#282a2c]">
                @foreach($rawDonut as $rd)
                    @if($rd['value'] > 0)
                    <div class="flex items-center space-x-1.5 text-xs font-semibold text-[#3c4043] dark:text-[#bdc1c6]">
                        <span class="w-2.5 h-2.5 rounded-full" style="background: {{ $rd['color'] }}"></span>
                        <span>{{ $rd['label'] }}</span>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>

        <!-- Quick Member Cards (4 Cols) -->
        <div class="lg:col-span-4 bg-white dark:bg-[#1e1f20] rounded-3xl p-5 sm:p-6 border border-[#dadce0] dark:border-[#282a2c] shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-[#f1f3f4] dark:border-[#282a2c] mb-3">
                    <h3 class="font-bold text-sm sm:text-base text-[#202124] dark:text-white">Thành Viên Quỹ</h3>
                    <a href="{{ route('analytics.networth') }}" class="text-xs font-bold text-[#1a73e8] dark:text-[#8ab4f8] hover:underline">Xem Net Worth →</a>
                </div>

                <div class="space-y-2.5 max-h-[300px] overflow-y-auto pr-1">
                    @foreach($members as $m)
                        <div class="p-2.5 rounded-2xl bg-[#f8f9fa] dark:bg-[#282a2c]/50 flex items-center justify-between border border-[#e8eaed]/60 dark:border-[#3c4043]/30">
                            <div class="flex items-center space-x-2.5 min-w-0">
                                <div class="w-7 h-7 rounded-full bg-[#1a73e8] text-white font-bold text-xs flex items-center justify-center flex-shrink-0 overflow-hidden">
                                    @if($m->avatar && \Illuminate\Support\Str::startsWith($m->avatar, ['http://', 'https://', '/uploads/']))
                                        <img src="{{ $m->avatar }}" alt="{{ $m->name }}" class="w-full h-full object-cover">
                                    @else
                                        {{ $m->avatar ?? substr($m->name, 0, 2) }}
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-[#202124] dark:text-white truncate">{{ $m->name }}</p>
                                    <p class="text-[10px] text-[#5f6368] dark:text-[#9aa0a6] truncate">{{ $m->email }}</p>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#e8f0fe] text-[#1a73e8] dark:bg-[#002b4d] dark:text-[#8ab4f8]">
                                {{ number_format($m->share_percentage, 0) }}%
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="pt-4 border-t border-[#f1f3f4] dark:border-[#282a2c] mt-4">
                <a href="{{ route('projects.index') }}" class="block text-center py-2 bg-[#f1f3f4] dark:bg-[#282a2c] hover:bg-[#e8eaed] dark:hover:bg-[#303134] text-[#3c4043] dark:text-[#e3e3e3] rounded-full text-xs font-bold transition">
                    Khảo Sát Danh Sách Dự Án
                </a>
            </div>
        </div>

    </div>

    <!-- 3. Recent Transactions Feed (Google Material Style List) -->
    <div class="bg-white dark:bg-[#1e1f20] rounded-3xl p-5 sm:p-6 border border-[#dadce0] dark:border-[#282a2c] shadow-sm space-y-4">
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-[#f1f3f4] dark:border-[#282a2c]">
            <div>
                <h3 class="font-bold text-sm sm:text-base text-[#202124] dark:text-white">Giao Dịch Gần Đây</h3>
                <p class="text-[11px] text-[#5f6368] dark:text-[#9aa0a6]">Nhật ký chi tiêu và đóng góp quỹ mới nhất</p>
            </div>

            <div class="flex items-center space-x-2">
                <!-- Search Box -->
                <div class="relative">
                    <input type="text" x-model="txSearchText" placeholder="Lọc nội dung, thành viên..." class="pl-8 pr-3 py-1.5 bg-[#f1f3f4] dark:bg-[#282a2c] rounded-full text-xs text-[#202124] dark:text-white border border-transparent focus:border-[#1a73e8] outline-none transition w-44 sm:w-56">
                    <svg class="w-3.5 h-3.5 text-[#5f6368] absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>

                <a href="{{ route('history') }}" class="px-3 py-1.5 bg-[#f1f3f4] dark:bg-[#282a2c] hover:bg-[#e8eaed] text-[#1a73e8] dark:text-[#8ab4f8] rounded-full text-xs font-bold transition">
                    Toàn Bộ Lịch Sử →
                </a>
            </div>
        </div>

        <!-- Google List Feed -->
        <div class="divide-y divide-[#f1f3f4] dark:divide-[#282a2c]">
            <template x-for="tx in paginatedTransactions" :key="tx.id">
                <div class="py-3 px-2 flex items-center justify-between hover:bg-[#f8f9fa] dark:hover:bg-[#282a2c]/40 rounded-2xl transition">
                    
                    <!-- Left: Avatar + Title & Date -->
                    <div class="flex items-center space-x-3 min-w-0 pr-3">
                        <div class="w-9 h-9 rounded-2xl flex items-center justify-center text-sm flex-shrink-0 font-bold"
                             :class="tx.type === 'contribution' || tx.type === 'repayment' ? 'bg-[#e6f4ea] text-[#137333] dark:bg-[#072711] dark:text-[#81c995]' : 'bg-[#fce8e6] text-[#c5221f] dark:bg-[#3c1211] dark:text-[#f28b82]'">
                            <span x-text="tx.type === 'contribution' ? '📥' : (tx.type === 'repayment' ? '🔄' : '📤')"></span>
                        </div>
                        <div class="min-w-0">
                            <p class="font-bold text-xs sm:text-sm text-[#202124] dark:text-white truncate" x-text="tx.description"></p>
                            <div class="flex items-center space-x-2 text-[10px] text-[#5f6368] dark:text-[#9aa0a6] mt-0.5">
                                <span class="font-semibold" x-text="tx.user_name"></span>
                                <span>•</span>
                                <span x-text="tx.created_at_formatted"></span>
                                <span>•</span>
                                <span class="font-mono" x-text="'#' + tx.id"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Amount Badge -->
                    <div class="text-right flex-shrink-0">
                        <template x-if="tx.type === 'contribution' || tx.type === 'repayment' || tx.type === 'adjustment'">
                            <span class="font-extrabold text-xs sm:text-sm text-[#137333] dark:text-[#81c995] font-mono" x-text="'+' + new Intl.NumberFormat('vi-VN').format(tx.amount) + '₫'"></span>
                        </template>
                        <template x-if="tx.type !== 'contribution' && tx.type !== 'repayment' && tx.type !== 'adjustment'">
                            <span class="font-extrabold text-xs sm:text-sm text-[#c5221f] dark:text-[#f28b82] font-mono" x-text="'-' + new Intl.NumberFormat('vi-VN').format(tx.amount) + '₫'"></span>
                        </template>
                    </div>

                </div>
            </template>

            <template x-if="paginatedTransactions.length === 0">
                <div class="py-8 text-center text-xs text-[#5f6368] dark:text-[#9aa0a6]">
                    Không có giao dịch nào khớp với bộ lọc.
                </div>
            </template>
        </div>

        <!-- Pagination Bar -->
        <div class="flex items-center justify-between pt-3 border-t border-[#f1f3f4] dark:border-[#282a2c] text-xs font-semibold text-[#5f6368] dark:text-[#9aa0a6]">
            <span x-text="'Trang ' + txPage + ' / ' + totalTxPages"></span>
            <div class="flex items-center space-x-1.5">
                <button type="button" @click="if (txPage > 1) txPage--" :disabled="txPage <= 1" class="px-3.5 py-1.5 rounded-full bg-[#f1f3f4] dark:bg-[#282a2c] hover:bg-[#e8eaed] text-[#3c4043] dark:text-white disabled:opacity-40 transition cursor-pointer">
                    ◀ Trước
                </button>
                <button type="button" @click="if (txPage < totalTxPages) txPage++" :disabled="txPage >= totalTxPages" class="px-3.5 py-1.5 rounded-full bg-[#f1f3f4] dark:bg-[#282a2c] hover:bg-[#e8eaed] text-[#3c4043] dark:text-white disabled:opacity-40 transition cursor-pointer">
                    Sau ▶
                </button>
            </div>
        </div>

    </div>

</div>
@endsection
