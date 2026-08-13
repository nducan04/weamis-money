@extends('layouts.app')

@section('content')
<div class="space-y-6"
     x-data="{
        viewMode: 'net',
        grossData: {{ \Illuminate\Support\Js::from($grossData) }},
        netData: {{ \Illuminate\Support\Js::from($netData) }},
        treasuryCash: {{ $treasuryCash }},
        get activeData() { return this.viewMode === 'gross' ? this.grossData : this.netData; },
        formatMoney(v) {
            let abs = Math.abs(v);
            let formatted = abs.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            return (v >= 0 ? '+' : '-') + formatted;
        },
        rankLabel(idx) {
            if (idx === 0) return '👑 TOP 1';
            if (idx === 1) return '🥈 TOP 2';
            if (idx === 2) return '🥉 TOP 3';
            return '#' + (idx + 1);
        },
        rankClass(idx) {
            if (idx === 0) return 'bg-amber-400 text-amber-950';
            if (idx === 1) return 'bg-slate-200 text-slate-800';
            if (idx === 2) return 'bg-amber-700/20 text-amber-600';
            return 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300';
        }
     }">

    <!-- 1. Toggle & Member Cards Section -->
    <div class="space-y-4">
        <!-- Section Header with Toggle -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white uppercase tracking-wider">
                <span x-text="viewMode === 'net' ? 'Tài Sản Ròng Các Thành Viên' : 'Vốn Cống Hiến Các Thành Viên'"></span>
            </h3>

            <!-- Pill Toggle -->
            <div class="flex items-center bg-slate-100 dark:bg-slate-700/60 rounded-xl p-1 border border-slate-200 dark:border-slate-600">
                <button @click="viewMode = 'net'"
                        :class="viewMode === 'net' ? 'bg-white dark:bg-slate-800 text-emerald-700 dark:text-emerald-400 shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700'"
                        class="px-4 py-1.5 rounded-lg text-xs font-extrabold transition-all duration-200 cursor-pointer">
                    Net
                </button>
                <button @click="viewMode = 'gross'"
                        :class="viewMode === 'gross' ? 'bg-white dark:bg-slate-800 text-indigo-700 dark:text-indigo-400 shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700'"
                        class="px-4 py-1.5 rounded-lg text-xs font-extrabold transition-all duration-200 cursor-pointer">
                    Gross
                </button>
            </div>
        </div>

        <!-- Member Cards Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
            <template x-for="(member, idx) in activeData" :key="member.username + viewMode">
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200/80 dark:border-slate-700 shadow-sm relative overflow-hidden hover:shadow-lg transition-all duration-200">
                    
                    <!-- Rank Badge -->
                    <div class="flex items-center justify-between mb-2.5">
                        <span :class="rankClass(idx)" class="px-2 py-0.5 text-[10px] font-black rounded-lg" x-text="rankLabel(idx)"></span>
                    </div>

                    <!-- Name -->
                    <h4 class="font-extrabold text-sm text-slate-900 dark:text-white leading-snug mb-2.5" x-text="member.name"></h4>

                    <!-- Value -->
                    <p class="text-xl sm:text-2xl font-black tracking-tight"
                       :class="member.value >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                        <span x-text="formatMoney(member.value)"></span><span class="text-sm font-extrabold">đ</span>
                    </p>

                    <!-- Equity % (only in Gross mode, only for positive members) -->
                    <template x-if="viewMode === 'gross' && member.equity && member.equity !== '--'">
                        <p class="mt-1.5 text-xs font-bold text-indigo-600 dark:text-indigo-400">
                            Cổ phần: <span x-text="member.equity"></span>
                        </p>
                    </template>
                </div>
            </template>
        </div>
    </div>

    <!-- 2. Vis.js Collaboration Network Graph & Sidebar Grid Layout -->
    <div class="pt-4 border-t border-slate-200/80 dark:border-slate-700/80 space-y-3">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Column: Vis.js Canvas (8 cols) -->
            <div class="lg:col-span-8 bg-white dark:bg-slate-800 rounded-3xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700 shadow-md flex flex-col justify-between relative overflow-hidden">
                <div class="relative w-full">
                    <!-- Overlay Control Bar -->
                    <div class="absolute top-3 right-3 z-10 flex items-center space-x-1.5 bg-white/90 dark:bg-slate-800/90 backdrop-blur p-1.5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
                        <button id="btn-zoom-in" title="Phóng to" class="w-7 h-7 bg-slate-100 dark:bg-slate-700 hover:bg-emerald-500 hover:text-white text-slate-700 dark:text-slate-200 rounded-lg text-xs font-bold transition flex items-center justify-center cursor-pointer">＋</button>
                        <button id="btn-zoom-out" title="Thu nhỏ" class="w-7 h-7 bg-slate-100 dark:bg-slate-700 hover:bg-emerald-500 hover:text-white text-slate-700 dark:text-slate-200 rounded-lg text-xs font-bold transition flex items-center justify-center cursor-pointer">－</button>
                        <button id="btn-reset-view" title="Căn giữa mặc định" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-[11px] font-extrabold shadow-sm transition cursor-pointer">Căn giữa</button>
                    </div>

                    <div id="network-graph" class="w-full h-[420px] sm:h-[580px] bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700/60 shadow-inner relative overflow-hidden"></div>
                </div>
            </div>

            <!-- Right Column: Collaboration Stats Sidebar (4 cols) -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Top Collaborating Pairs Card -->
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 sm:p-6 border border-slate-200/80 dark:border-slate-700 shadow-md space-y-4">
                    <div class="space-y-3">
                        @forelse($topPairs as $idx => $pair)
                            <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 dark:bg-slate-700/40 border border-slate-100 dark:border-slate-700/60">
                                <div class="flex items-center space-x-3">
                                    <span class="w-6 h-6 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-black text-xs flex items-center justify-center">
                                        #{{ $idx + 1 }}
                                    </span>
                                    <div>
                                        <p class="font-extrabold text-xs text-slate-900 dark:text-white">
                                            {{ $pair['m1']->name }} & {{ $pair['m2']->name }}
                                        </p>
                                        <p class="text-[10px] text-slate-400 font-semibold">Đồng hành trong công việc</p>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 rounded-xl text-xs font-black">
                                    {{ $pair['count'] }} Dự án
                                </span>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 text-center py-4 font-medium">Chưa có dữ liệu cặp đôi hợp tác</p>
                        @endforelse
                    </div>
                </div>

                <!-- Active Projects Distribution Summary -->
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 sm:p-6 border border-slate-200/80 dark:border-slate-700 shadow-md space-y-4">
                    <div class="space-y-2.5 text-xs font-semibold">
                        <div class="flex justify-between items-center p-3 rounded-2xl bg-slate-50 dark:bg-slate-700/40">
                            <span class="text-slate-600 dark:text-slate-300">Tổng số thành viên:</span>
                            <span class="font-black text-slate-900 dark:text-white">{{ count($members) }} Thành viên</span>
                        </div>
                        <div class="flex justify-between items-center p-3 rounded-2xl bg-slate-50 dark:bg-slate-700/40">
                            <span class="text-slate-600 dark:text-slate-300">Tổng số dự án:</span>
                            <span class="font-black text-slate-900 dark:text-white">{{ count($projects) }} Dự án</span>
                        </div>
                        <div class="flex justify-between items-center p-3 rounded-2xl bg-slate-50 dark:bg-slate-700/40">
                            <span class="text-slate-600 dark:text-slate-300">Số mối liên kết cổ phần:</span>
                            <span class="font-black text-emerald-600 dark:text-emerald-400">{{ count($edges) }} Kết nối</span>
                        </div>
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
                    size: 46,
                    font: {
                        size: 16,
                        color: isDarkMode ? '#f8fafc' : '#0f172a',
                        face: 'Plus Jakarta Sans',
                        bold: 'true',
                        strokeWidth: 4,
                        strokeColor: isDarkMode ? '#090d16' : '#ffffff'
                    }
                };
            }
            return {
                ...node,
                font: {
                    ...node.font,
                    size: 13,
                    bold: 'true',
                    color: '#0f172a'
                }
            };
        });

        const processedEdges = rawEdges.map(edge => {
            return {
                ...edge,
                width: 3,
                font: {
                    ...edge.font,
                    size: 14,
                    bold: 'true',
                    color: isDarkMode ? '#34d399' : '#047857',
                    strokeWidth: 4,
                    strokeColor: isDarkMode ? '#0f172a' : '#ffffff'
                }
            };
        });

        const data = {
            nodes: new vis.DataSet(processedNodes),
            edges: new vis.DataSet(processedEdges)
        };

        const options = {
            nodes: {
                borderWidthSelected: 3,
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
                    gravitationalConstant: -6000,
                    centralGravity: 0.3,
                    springLength: 140,
                    springConstant: 0.04,
                    avoidOverlap: 0.5
                },
                maxVelocity: 30,
                minVelocity: 0.75,
                solver: 'barnesHut',
                stabilization: {
                    enabled: true,
                    iterations: 150,
                    updateInterval: 50
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

        // Turn off physics after stabilization to freeze CPU usage at 0% on mobile
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
