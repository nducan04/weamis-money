@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 sm:p-6 border border-slate-200/80 dark:border-slate-700 shadow-md">
        <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center space-x-2">
            <span>📈</span>
            <span>Tài Sản Ròng (Net Worth) & Đồ Thị Mạng Lưới Tương Quan</span>
        </h2>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 font-medium mt-1">
            Theo dõi tích lũy tài sản ròng cá nhân và mạng lưới quan hệ hợp tác dự án giữa các thành viên.
        </p>
    </div>

    <!-- 1. Net Worth Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($netWorthData as $nw)
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200/80 dark:border-slate-700 shadow-sm relative overflow-hidden">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-10 h-10 rounded-full bg-slate-800 text-white font-black text-xs flex items-center justify-center flex-shrink-0 overflow-hidden">
                        @if($nw['avatar'] && \Illuminate\Support\Str::startsWith($nw['avatar'], ['http://', 'https://', '/uploads/']))
                            <img src="{{ $nw['avatar'] }}" alt="{{ $nw['name'] }}" class="w-full h-full object-cover">
                        @else
                            {{ $nw['avatar'] ?? substr($nw['name'], 0, 2) }}
                        @endif
                    </div>
                    <div>
                        <h4 class="font-extrabold text-sm text-slate-900 dark:text-white">{{ $nw['name'] }}</h4>
                        <p class="text-[10px] text-slate-400 font-bold uppercase">Tài sản ròng dự kiến</p>
                    </div>
                </div>

                <div class="space-y-1">
                    <p class="text-xl font-black {{ $nw['net_worth'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                        {{ number_format($nw['net_worth'], 0, ',', '.') }}đ
                    </p>
                    <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 pt-2 border-t border-slate-100 dark:border-slate-700">
                        <span>Đã thu dự án: <strong class="text-indigo-600 dark:text-indigo-400">{{ number_format($nw['project_earnings'], 0, ',', '.') }}đ</strong></span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- 2. Vis.js Collaboration Network Graph -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 sm:p-6 border border-slate-200/80 dark:border-slate-700 shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 pb-3 border-b border-slate-100 dark:border-slate-700">
            <div>
                <h3 class="font-black text-slate-900 dark:text-white text-base sm:text-lg flex items-center space-x-2">
                    <span>🕸️</span>
                    <span>Đồ Thị Mạng Lưới Tương Quan Hợp Tác (Collaboration Graph)</span>
                </h3>
                <p class="text-xs text-slate-400 font-medium">Trực quan hóa mối quan hệ hợp tác làm chung dự án & % phân chia giữa các thành viên.</p>
            </div>
            
            <!-- Graph Legend & Controls -->
            <div class="flex items-center space-x-2">
                <div class="flex items-center space-x-2 bg-slate-100 dark:bg-slate-700/60 px-3 py-1.5 rounded-xl text-xs font-bold">
                    <span class="flex items-center space-x-1"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span> <span class="text-slate-700 dark:text-slate-300">Thành viên</span></span>
                    <span class="text-slate-300 dark:text-slate-600">|</span>
                    <span class="flex items-center space-x-1"><span class="w-2.5 h-2.5 rounded-md bg-amber-500 inline-block"></span> <span class="text-slate-700 dark:text-slate-300">Dự án</span></span>
                </div>
                <button id="btn-reset-view" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-sm transition cursor-pointer flex items-center space-x-1">
                    <span>🔍 Zoom Chuẩn</span>
                </button>
            </div>
        </div>

        <div id="network-graph" class="w-full h-[520px] bg-slate-900 rounded-2xl border border-slate-700/60 shadow-inner relative overflow-hidden"></div>
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
                size: 32,
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
