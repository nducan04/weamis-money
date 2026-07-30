@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 sm:p-6 border border-slate-200/80 dark:border-slate-700 shadow-md flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center space-x-2">
                <span>📊</span>
                <span>Thống Kê Tài Sản Ròng & Mạng Lưới Hợp Tác</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold mt-1">Phân tích giá trị tích lũy tài chính và mối quan hệ hợp tác làm chung dự án của các thành viên.</p>
        </div>
    </div>

    <!-- 1. Explanation & Formula Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-3xl p-5 sm:p-6 text-white shadow-xl border border-indigo-900/50 space-y-3">
        <div class="flex items-center space-x-2 text-indigo-400 font-extrabold text-xs uppercase tracking-wider">
            <span>💡 Giải Thích Công Thức Tính Tài Sản Ròng (Net Worth)</span>
        </div>
        <div class="text-sm font-semibold text-slate-200 leading-relaxed space-y-1.5">
            <p class="text-base font-extrabold text-white">
                Tài Sản Ròng = <span class="text-emerald-400">(Thu Nhập Từ Dự Án + Đã Đóng Góp Quỹ)</span> - <span class="text-rose-400">Khoản Đang Vay</span>
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs pt-2 border-t border-indigo-800/40">
                <div class="bg-indigo-900/40 p-3 rounded-2xl border border-indigo-700/40">
                    <p class="font-extrabold text-emerald-300">💎 Thu Nhập Từ Dự Án</p>
                    <p class="text-[11px] text-slate-300 mt-1">Tổng tiền ước tính thành viên được nhận từ tất cả các dự án đã/đang tham gia (sau khi đã trừ % Trích Về Quỹ Chung).</p>
                </div>
                <div class="bg-indigo-900/40 p-3 rounded-2xl border border-indigo-700/40">
                    <p class="font-extrabold text-blue-300">💵 Đã Đóng Góp Quỹ</p>
                    <p class="text-[11px] text-slate-300 mt-1">Tổng số tiền thành viên đã nộp trực tiếp vào Quỹ Chung của team.</p>
                </div>
                <div class="bg-indigo-900/40 p-3 rounded-2xl border border-indigo-700/40">
                    <p class="font-extrabold text-rose-300">🔻 Khoản Đang Vay/Nợ</p>
                    <p class="text-[11px] text-slate-300 mt-1">Số tiền thành viên hiện tại đang nợ/vay chưa hoàn trả lại cho Quỹ.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Ranked Net Worth Member Cards -->
    <div class="space-y-3">
        <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center space-x-2">
            <span>🏆</span>
            <span>Bảng Xếp Hạng Tài Sản Ròng Thành Viên</span>
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($netWorthData as $rank => $nw)
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm relative overflow-hidden flex flex-col justify-between hover:shadow-lg transition-all duration-200">
                    
                    <!-- Rank Badge -->
                    <div class="flex items-center justify-between mb-3">
                        <span class="px-2.5 py-1 text-[11px] font-black rounded-xl {{ $rank === 0 ? 'bg-amber-400 text-amber-950 shadow-sm' : ($rank === 1 ? 'bg-slate-200 text-slate-800' : ($rank === 2 ? 'bg-amber-700/20 text-amber-600' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300')) }}">
                            {{ $rank === 0 ? '👑 TOP 1' : ($rank === 1 ? '🥈 TOP 2' : ($rank === 2 ? '🥉 TOP 3' : '#' . ($rank + 1))) }}
                        </span>
                        <span class="text-xs font-bold text-slate-400">@ {{ $nw['username'] }}</span>
                    </div>

                    <!-- Member Avatar & Name -->
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-slate-900 text-white font-black text-sm flex items-center justify-center flex-shrink-0 overflow-hidden ring-2 ring-emerald-500/30">
                            @if($nw['avatar'] && \Illuminate\Support\Str::startsWith($nw['avatar'], ['http://', 'https://', '/uploads/']))
                                <img src="{{ $nw['avatar'] }}" alt="{{ $nw['name'] }}" class="w-full h-full object-cover">
                            @else
                                {{ substr($nw['name'], 0, 2) }}
                            @endif
                        </div>
                        <div>
                            <h4 class="font-extrabold text-base text-slate-900 dark:text-white leading-snug">{{ $nw['name'] }}</h4>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tài Sản Ròng</p>
                        </div>
                    </div>

                    <!-- Net Worth Value -->
                    <div class="bg-slate-50 dark:bg-slate-700/40 p-3.5 rounded-2xl border border-slate-100 dark:border-slate-700/60 mb-3 text-center">
                        <p class="text-2xl font-black tracking-tight {{ $nw['net_worth'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            {{ number_format($nw['net_worth'], 0, ',', '.') }}<span class="text-base font-extrabold">đ</span>
                        </p>
                    </div>

                    <!-- Breakdown Detail List -->
                    <div class="space-y-1.5 text-xs border-t border-slate-100 dark:border-slate-700 pt-3">
                        <div class="flex justify-between items-center text-slate-600 dark:text-slate-300">
                            <span class="font-medium">💎 Dự án:</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">+{{ number_format($nw['project_earnings'], 0, ',', '.') }}đ</span>
                        </div>
                        <div class="flex justify-between items-center text-slate-600 dark:text-slate-300">
                            <span class="font-medium">💵 Nộp quỹ:</span>
                            <span class="font-bold text-indigo-600 dark:text-indigo-400">+{{ number_format($nw['contributions'], 0, ',', '.') }}đ</span>
                        </div>
                        <div class="flex justify-between items-center text-slate-600 dark:text-slate-300">
                            <span class="font-medium">🔻 Nợ quỹ:</span>
                            <span class="font-bold text-rose-500">-{{ number_format($nw['loans'], 0, ',', '.') }}đ</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- 3. Vis.js Collaboration Network Graph -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 sm:p-6 border border-slate-200/80 dark:border-slate-700 shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 pb-3 border-b border-slate-100 dark:border-slate-700">
            <div>
                <h3 class="font-black text-slate-900 dark:text-white text-base sm:text-lg flex items-center space-x-2">
                    <span>🕸️</span>
                    <span>Đồ Thị Mạng Lưới Tương Quan Hợp Tác (Collaboration Graph)</span>
                </h3>
                <p class="text-xs text-slate-400 font-medium">Trực quan hóa mối quan hệ hợp tác làm chung dự án & % phân chia cổ phần giữa các thành viên.</p>
            </div>
            
            <!-- Graph Legend & Controls -->
            <div class="flex flex-wrap items-center gap-2">
                <div class="flex items-center space-x-2 bg-slate-100 dark:bg-slate-700/60 px-3 py-1.5 rounded-xl text-xs font-bold">
                    <span class="flex items-center space-x-1"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span> <span class="text-slate-700 dark:text-slate-300">Thành viên</span></span>
                    <span class="text-slate-300 dark:text-slate-600">|</span>
                    <span class="flex items-center space-x-1"><span class="w-2.5 h-2.5 rounded-md bg-amber-500 inline-block"></span> <span class="text-slate-700 dark:text-slate-300">Dự án</span></span>
                </div>
                <button id="btn-reset-view" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-sm transition cursor-pointer flex items-center space-x-1">
                    <span>🔍 Reset Zoom</span>
                </button>
            </div>
        </div>

        <div id="network-graph" class="w-full h-[550px] bg-slate-900 rounded-2xl border border-slate-700/60 shadow-inner relative overflow-hidden"></div>
    </div>

</div>

<!-- Vis.js Network JS -->
<script src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('network-graph');

        const rawNodes = @json($nodes);
        const rawEdges = @json($edges);

        const nodes = new vis.DataSet(rawNodes);
        const edges = new vis.DataSet(rawEdges);

        const data = { nodes: nodes, edges: edges };
        const options = {
            nodes: {
                borderWidth: 3,
                size: 34,
                font: { 
                    size: 13, 
                    color: '#ffffff', 
                    face: 'Plus Jakarta Sans',
                    strokeWidth: 4,
                    strokeColor: '#0f172a'
                },
                shadow: {
                    enabled: true,
                    color: 'rgba(0,0,0,0.5)',
                    size: 10,
                    x: 3,
                    y: 3
                }
            },
            edges: {
                width: 2.5,
                smooth: { type: 'cubicBezier', forceDirection: 'none', roundness: 0.4 },
                shadow: { enabled: true, color: 'rgba(0,0,0,0.3)', size: 5, x: 2, y: 2 }
            },
            physics: {
                solver: 'forceAtlas2Based',
                forceAtlas2Based: {
                    gravitationalConstant: -220,
                    centralGravity: 0.015,
                    springLength: 180,
                    springConstant: 0.05,
                    damping: 0.4
                },
                maxVelocity: 50,
                minVelocity: 0.1,
                stabilization: { iterations: 150 }
            },
            interaction: {
                hover: true,
                tooltipDelay: 100,
                zoomView: true,
                dragView: true
            }
        };

        const network = new vis.Network(container, data, options);

        document.getElementById('btn-reset-view').addEventListener('click', function() {
            network.fit({
                animation: {
                    duration: 600,
                    easingFunction: 'easeInOutQuad'
                }
            });
        });
    });
</script>
@endsection
