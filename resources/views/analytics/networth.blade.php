@extends('layouts.app')

@section('content')
<div class="space-y-6"
     x-data="{
        viewMode: 'net',
        grossData: {{ \Illuminate\Support\Js::from($grossData) }},
        netData: {{ \Illuminate\Support\Js::from($netData) }},
        treasuryCash: {{ $treasuryCash }},
        searchQuery: '',
        get activeData() { 
            let list = this.viewMode === 'gross' ? this.grossData : this.netData;
            if (!this.searchQuery) return list;
            let q = this.searchQuery.toLowerCase();
            return list.filter(m => (m.name && m.name.toLowerCase().includes(q)) || (m.username && m.username.toLowerCase().includes(q)));
        },
        get positiveTotal() {
            let list = this.viewMode === 'gross' ? this.grossData : this.netData;
            return list.filter(m => m.value > 0).reduce((sum, m) => sum + m.value, 0);
        },
        get negativeTotal() {
            let list = this.viewMode === 'gross' ? this.grossData : this.netData;
            return list.filter(m => m.value < 0).reduce((sum, m) => sum + m.value, 0);
        },
        formatMoney(v) {
            let num = Math.round(Number(v) || 0);
            let abs = Math.abs(num);
            let formatted = abs.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            return (num >= 0 ? '+' : '-') + formatted;
        },
        rankBadge(idx) {
            if (idx === 0) return { label: 'TOP 1', class: 'bg-[#fef7e0] text-[#b06000] dark:bg-[#3c2a00] dark:text-[#fdd663]' };
            if (idx === 1) return { label: 'TOP 2', class: 'bg-[#f1f3f4] text-[#3c4043] dark:bg-[#303134] dark:text-[#e3e3e3]' };
            if (idx === 2) return { label: 'TOP 3', class: 'bg-[#fce8e6] text-[#c5221f] dark:bg-[#3c1211] dark:text-[#f28b82]' };
            return { label: '#' + (idx + 1), class: 'bg-[#f1f3f4] text-[#5f6368] dark:bg-[#282a2c] dark:text-[#9aa0a6]' };
        }
     }">

    <!-- 1. Google Finance Hero Card: Net Worth Overview -->
    <div class="bg-white dark:bg-[#1e1f20] rounded-3xl p-5 sm:p-7 border border-[#dadce0] dark:border-[#282a2c] shadow-sm transition-all">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-[#f1f3f4] dark:border-[#282a2c]">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="text-xs font-bold text-[#5f6368] dark:text-[#9aa0a6] uppercase tracking-wider">Phân Tích Cấu Trúc Tài Sản</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#e8f0fe] text-[#1a73e8] dark:bg-[#002b4d] dark:text-[#8ab4f8]">
                        Equity Engine
                    </span>
                </div>
                <div class="flex items-baseline space-x-3 mt-2">
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-[#202124] dark:text-white tracking-tight">
                        <span x-text="viewMode === 'net' ? 'Tài Sản Ròng Thành Viên (Net)' : 'Vốn Cống Hiến Lũy Kế (Gross)'"></span>
                    </h2>
                </div>
                <p class="text-xs text-[#5f6368] dark:text-[#9aa0a6] mt-1 font-medium">
                    <span x-text="viewMode === 'net' ? 'Số tiền thực tế thành viên được rút hoặc cần bù vào quỹ theo sổ cái kép' : 'Tổng giá trị công sức cống hiến & tỷ lệ cổ phần sweat-equity của từng thành viên'"></span>
                </p>
            </div>

            <!-- Material 3 Pill Mode Selector -->
            <div class="flex items-center space-x-1 bg-[#f1f3f4] dark:bg-[#282a2c] p-1 rounded-full self-start md:self-auto text-xs font-bold">
                <button @click="viewMode = 'net'" 
                        :class="viewMode === 'net' ? 'bg-white dark:bg-[#1e1f20] text-[#1a73e8] dark:text-[#8ab4f8] shadow-sm' : 'text-[#5f6368] dark:text-[#9aa0a6] hover:text-[#202124] dark:hover:text-white'"
                        class="px-4 py-1.5 rounded-full transition-all cursor-pointer">
                    Net (Tài Sản Ròng)
                </button>
                <button @click="viewMode = 'gross'" 
                        :class="viewMode === 'gross' ? 'bg-white dark:bg-[#1e1f20] text-[#1a73e8] dark:text-[#8ab4f8] shadow-sm' : 'text-[#5f6368] dark:text-[#9aa0a6] hover:text-[#202124] dark:hover:text-white'"
                        class="px-4 py-1.5 rounded-full transition-all cursor-pointer">
                    Gross (Cổ Phần Cống Hiến)
                </button>
            </div>
        </div>

        <!-- Metric Tonal Cards Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mt-6">
            <!-- Metric 1: Tiền Mặt Sẵn Có Trong Quỹ -->
            <div class="bg-[#f8f9fa] dark:bg-[#282a2c]/50 p-4 rounded-2xl border border-[#e8eaed] dark:border-[#3c4043]/40">
                <span class="text-[11px] font-bold text-[#5f6368] dark:text-[#9aa0a6] uppercase tracking-wider block">Tiền Mặt Quỹ MoMo</span>
                <p class="text-lg sm:text-2xl font-extrabold text-[#1a73e8] dark:text-[#8ab4f8] mt-1 font-mono">
                    {{ number_format($treasuryCash, 0, ',', '.') }}<span class="text-xs sm:text-sm font-bold ml-0.5">₫</span>
                </p>
                <span class="text-[10px] text-[#5f6368] dark:text-[#9aa0a6] font-medium mt-1 block">Khả năng thanh toán tức thời</span>
            </div>

            <!-- Metric 2: Tổng Vốn Cố Định Dương -->
            <div class="bg-[#f8f9fa] dark:bg-[#282a2c]/50 p-4 rounded-2xl border border-[#e8eaed] dark:border-[#3c4043]/40">
                <span class="text-[11px] font-bold text-[#5f6368] dark:text-[#9aa0a6] uppercase tracking-wider block">Tổng Dư Có (+)</span>
                <p class="text-lg sm:text-2xl font-extrabold text-[#137333] dark:text-[#81c995] mt-1 font-mono">
                    <span x-text="formatMoney(positiveTotal)"></span><span class="text-xs sm:text-sm font-bold ml-0.5">₫</span>
                </p>
                <span class="text-[10px] text-[#5f6368] dark:text-[#9aa0a6] font-medium mt-1 block">Tài sản tích lũy của nhóm</span>
            </div>

            <!-- Metric 3: Tổng Dư Nợ Âm -->
            <div class="bg-[#f8f9fa] dark:bg-[#282a2c]/50 p-4 rounded-2xl border border-[#e8eaed] dark:border-[#3c4043]/40">
                <span class="text-[11px] font-bold text-[#5f6368] dark:text-[#9aa0a6] uppercase tracking-wider block">Tổng Nợ Treo (-)</span>
                <p class="text-lg sm:text-2xl font-extrabold text-[#c5221f] dark:text-[#f28b82] mt-1 font-mono">
                    <span x-text="formatMoney(negativeTotal)"></span><span class="text-xs sm:text-sm font-bold ml-0.5">₫</span>
                </p>
                <span class="text-[10px] text-[#5f6368] dark:text-[#9aa0a6] font-medium mt-1 block">Khoản cần hoàn ứng vào quỹ</span>
            </div>

            <!-- Metric 4: Số Thành Viên & Dự Án -->
            <div class="bg-[#f8f9fa] dark:bg-[#282a2c]/50 p-4 rounded-2xl border border-[#e8eaed] dark:border-[#3c4043]/40 flex flex-col justify-between">
                <span class="text-[11px] font-bold text-[#5f6368] dark:text-[#9aa0a6] uppercase tracking-wider block">Mạng Lưới</span>
                <div class="flex items-center justify-between mt-1">
                    <div>
                        <span class="text-lg sm:text-2xl font-extrabold text-[#202124] dark:text-white font-mono">{{ count($members) }}</span>
                        <span class="text-[11px] text-[#5f6368] dark:text-[#9aa0a6] block font-medium">Thành viên</span>
                    </div>
                    <div class="text-right">
                        <span class="text-lg sm:text-2xl font-extrabold text-[#202124] dark:text-white font-mono">{{ count($projects) }}</span>
                        <span class="text-[11px] text-[#5f6368] dark:text-[#9aa0a6] block font-medium">Dự án số</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Member Cards Grid Section -->
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="font-bold text-sm sm:text-base text-[#202124] dark:text-white">Bảng Xếp Hạng & Định Giá Thành Viên</h3>
                <p class="text-[11px] text-[#5f6368] dark:text-[#9aa0a6]">Danh sách chi tiết theo thứ tự đóng góp tài sản từ cao xuống thấp</p>
            </div>

            <!-- Quick Filter Input -->
            <div class="relative">
                <input type="text" x-model="searchQuery" placeholder="Tìm tên thành viên..." class="pl-8 pr-3 py-1.5 bg-white dark:bg-[#1e1f20] rounded-full text-xs text-[#202124] dark:text-white border border-[#dadce0] dark:border-[#282a2c] focus:border-[#1a73e8] outline-none transition w-48 sm:w-56 shadow-sm">
                <svg class="w-3.5 h-3.5 text-[#5f6368] absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
            <template x-for="(member, idx) in activeData" :key="member.username + viewMode">
                <div class="bg-white dark:bg-[#1e1f20] rounded-3xl p-5 border border-[#dadce0] dark:border-[#282a2c] shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                    <div>
                        <!-- Header: Rank Badge & Equity Pill -->
                        <div class="flex items-center justify-between mb-3">
                            <span :class="rankBadge(idx).class" class="px-2.5 py-0.5 text-[10px] font-extrabold rounded-full font-mono" x-text="rankBadge(idx).label"></span>
                            
                            <template x-if="viewMode === 'gross' && member.equity && member.equity !== '--'">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#e8f0fe] text-[#1a73e8] dark:bg-[#002b4d] dark:text-[#8ab4f8]">
                                    Cổ phần: <span x-text="member.equity"></span>
                                </span>
                            </template>
                        </div>

                        <!-- User Profile Info -->
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-10 h-10 rounded-full bg-[#1a73e8] text-white font-bold text-xs flex items-center justify-center flex-shrink-0 overflow-hidden shadow-sm">
                                <template x-if="member.avatar && (member.avatar.startsWith('http') || member.avatar.startsWith('/uploads/'))">
                                    <img :src="member.avatar" :alt="member.name" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!member.avatar || (!member.avatar.startsWith('http') && !member.avatar.startsWith('/uploads/'))">
                                    <span x-text="member.name.substring(0, 2).toUpperCase()"></span>
                                </template>
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-bold text-sm text-[#202124] dark:text-white truncate" x-text="member.name"></h4>
                                <p class="text-[10px] text-[#5f6368] dark:text-[#9aa0a6] truncate font-mono" x-text="'@' + member.username"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Net / Gross Value -->
                    <div class="pt-3 border-t border-[#f1f3f4] dark:border-[#282a2c]">
                        <span class="text-[10px] font-bold text-[#5f6368] dark:text-[#9aa0a6] uppercase tracking-wider block">
                            <span x-text="viewMode === 'net' ? 'Tài Sản Ròng (Net)' : 'Vốn Cống Hiến (Gross)'"></span>
                        </span>
                        <div class="flex items-baseline space-x-1 mt-1">
                            <p class="text-xl sm:text-2xl font-extrabold tracking-tight font-mono"
                               :class="member.value >= 0 ? 'text-[#137333] dark:text-[#81c995]' : 'text-[#c5221f] dark:text-[#f28b82]'">
                                <span x-text="formatMoney(member.value)"></span><span class="text-xs sm:text-sm font-bold ml-0.5">₫</span>
                            </p>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- 3. Collaboration Network Graph & Top Pairs -->
    <div class="bg-white dark:bg-[#1e1f20] rounded-3xl p-5 sm:p-6 border border-[#dadce0] dark:border-[#282a2c] shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-[#f1f3f4] dark:border-[#282a2c]">
            <div>
                <h3 class="font-bold text-sm sm:text-base text-[#202124] dark:text-white">Mạng Lưới Liên Kết & Hợp Tác Dự Án</h3>
                <p class="text-[11px] text-[#5f6368] dark:text-[#9aa0a6]">Biểu đồ topology thể hiện tương tác cổ phần và các cặp thành viên đồng hành nhiều nhất</p>
            </div>
            
            <!-- Quick Network Controls -->
            <div class="flex items-center space-x-1.5 self-start sm:self-auto">
                <button id="btn-zoom-in" title="Phóng to" class="w-8 h-8 bg-[#f1f3f4] dark:bg-[#282a2c] hover:bg-[#1a73e8] hover:text-white text-[#3c4043] dark:text-white rounded-full text-xs font-bold transition flex items-center justify-center cursor-pointer">＋</button>
                <button id="btn-zoom-out" title="Thu nhỏ" class="w-8 h-8 bg-[#f1f3f4] dark:bg-[#282a2c] hover:bg-[#1a73e8] hover:text-white text-[#3c4043] dark:text-white rounded-full text-xs font-bold transition flex items-center justify-center cursor-pointer">－</button>
                <button id="btn-reset-view" title="Căn giữa mặc định" class="px-3 py-1.5 bg-[#f1f3f4] dark:bg-[#282a2c] hover:bg-[#e8eaed] text-[#1a73e8] dark:text-[#8ab4f8] rounded-full text-xs font-bold transition cursor-pointer">Căn giữa</button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left: Vis.js Canvas (8 Cols) -->
            <div class="lg:col-span-8">
                <div id="network-graph" class="w-full h-[450px] sm:h-[550px] bg-[#f8f9fa] dark:bg-[#131314] rounded-2xl border border-[#dadce0] dark:border-[#282a2c] shadow-inner relative overflow-hidden"></div>
                <div class="flex items-center justify-between text-[11px] text-[#5f6368] dark:text-[#9aa0a6] mt-2 px-1">
                    <span>💡 Kéo thả hoặc lăn chuột để tương tác với các node</span>
                    <div class="flex items-center space-x-3">
                        <span class="inline-flex items-center space-x-1">
                            <span class="w-2 h-2 rounded-full bg-[#1a73e8]"></span>
                            <span>Thành viên</span>
                        </span>
                        <span class="inline-flex items-center space-x-1">
                            <span class="w-2 h-2 rounded-full bg-[#f29900]"></span>
                            <span>Dự án</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Right: Collaboration Stats & Top Pairs (4 Cols) -->
            <div class="lg:col-span-4 space-y-4">
                <div class="p-4 rounded-2xl bg-[#f8f9fa] dark:bg-[#282a2c]/50 border border-[#e8eaed] dark:border-[#3c4043]/40">
                    <span class="text-[11px] font-bold text-[#5f6368] dark:text-[#9aa0a6] uppercase tracking-wider block mb-3">Top Đồng Hành Dự Án</span>
                    
                    <div class="space-y-2.5">
                        @forelse($topPairs as $idx => $pair)
                            <div class="flex items-center justify-between p-2.5 rounded-xl bg-white dark:bg-[#1e1f20] border border-[#dadce0]/60 dark:border-[#282a2c]">
                                <div class="flex items-center space-x-2.5 min-w-0 pr-2">
                                    <span class="w-5 h-5 rounded-full bg-[#e8f0fe] text-[#1a73e8] dark:bg-[#002b4d] dark:text-[#8ab4f8] font-mono font-bold text-[10px] flex items-center justify-center flex-shrink-0">
                                        #{{ $idx + 1 }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="font-bold text-xs text-[#202124] dark:text-white truncate">
                                            {{ $pair['m1']->name }} & {{ $pair['m2']->name }}
                                        </p>
                                        <span class="text-[10px] text-[#5f6368] dark:text-[#9aa0a6]">Cộng tác</span>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 bg-[#e6f4ea] text-[#137333] dark:bg-[#072711] dark:text-[#81c995] rounded-full text-[10px] font-bold flex-shrink-0">
                                    {{ $pair['count'] }} dự án
                                </span>
                            </div>
                        @empty
                            <p class="text-xs text-[#5f6368] dark:text-[#9aa0a6] text-center py-4 font-medium">Chưa có dữ liệu cặp đôi hợp tác</p>
                        @endforelse
                    </div>
                </div>

                <!-- Summary Counts -->
                <div class="p-4 rounded-2xl bg-[#f8f9fa] dark:bg-[#282a2c]/50 border border-[#e8eaed] dark:border-[#3c4043]/40 space-y-2 text-xs font-semibold">
                    <div class="flex justify-between items-center py-1">
                        <span class="text-[#5f6368] dark:text-[#9aa0a6]">Tổng số kết nối cổ phần:</span>
                        <span class="font-extrabold text-[#1a73e8] dark:text-[#8ab4f8] font-mono">{{ count($edges) }} liên kết</span>
                    </div>
                    <div class="flex justify-between items-center py-1 border-t border-[#f1f3f4] dark:border-[#282a2c]">
                        <span class="text-[#5f6368] dark:text-[#9aa0a6]">Số dự án đang hoạt động:</span>
                        <span class="font-extrabold text-[#202124] dark:text-white font-mono">{{ count($projects) }} dự án</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Vis.js Network JS -->
<script src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('network-graph');
        if (!container) return;

        const rawNodes = @json($nodes);
        const rawEdges = @json($edges);

        const isDarkMode = document.documentElement.classList.contains('dark');

        const processedNodes = rawNodes.map(node => {
            if (node.group === 'member') {
                return {
                    ...node,
                    size: 42,
                    font: {
                        size: 13,
                        color: isDarkMode ? '#e3e3e3' : '#202124',
                        face: '"Google Sans", "Plus Jakarta Sans", Roboto, sans-serif',
                        bold: 'true',
                        strokeWidth: 3,
                        strokeColor: isDarkMode ? '#131314' : '#ffffff'
                    }
                };
            }
            return {
                ...node,
                font: {
                    ...node.font,
                    size: 12,
                    bold: 'true',
                    face: '"Google Sans", "Plus Jakarta Sans", Roboto, sans-serif',
                    color: '#202124'
                }
            };
        });

        const processedEdges = rawEdges.map(edge => {
            return {
                ...edge,
                width: 2.5,
                font: {
                    ...edge.font,
                    size: 11,
                    bold: 'true',
                    face: '"Roboto Mono", monospace',
                    color: isDarkMode ? '#81c995' : '#137333',
                    strokeWidth: 3,
                    strokeColor: isDarkMode ? '#131314' : '#ffffff'
                }
            };
        });

        const data = {
            nodes: new vis.DataSet(processedNodes),
            edges: new vis.DataSet(processedEdges)
        };

        const options = {
            nodes: {
                borderWidthSelected: 2.5,
                shadow: false
            },
            edges: {
                smooth: {
                    type: 'continuous',
                    roundness: 0.2
                },
                shadow: false
            },
            physics: {
                barnesHut: {
                    gravitationalConstant: -5000,
                    centralGravity: 0.3,
                    springLength: 130,
                    springConstant: 0.04,
                    avoidOverlap: 0.5
                },
                maxVelocity: 30,
                minVelocity: 0.75,
                solver: 'barnesHut',
                stabilization: {
                    enabled: true,
                    iterations: 120,
                    updateInterval: 40
                }
            },
            interaction: {
                hover: false,
                tooltipDelay: 300,
                zoomView: true,
                dragView: true
            }
        };

        const network = new vis.Network(container, data, options);

        // Freeze physics on finish to maintain 0% CPU consumption
        network.once("stabilizationIterationsDone", function () {
            network.setOptions({ physics: { enabled: false } });
        });

        document.getElementById('btn-reset-view')?.addEventListener('click', function () {
            network.fit({ animation: { duration: 300, easingFunction: 'easeInOutQuad' } });
        });
        document.getElementById('btn-zoom-in')?.addEventListener('click', function () {
            let scale = network.getScale();
            network.moveTo({ scale: scale * 1.25, animation: { duration: 200 } });
        });
        document.getElementById('btn-zoom-out')?.addEventListener('click', function () {
            let scale = network.getScale();
            network.moveTo({ scale: scale * 0.8, animation: { duration: 200 } });
        });
    });
</script>
@endsection
