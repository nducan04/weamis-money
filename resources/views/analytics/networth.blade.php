@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- 1. Ranked Net Worth Member Cards -->
    <div class="space-y-3">
        <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center space-x-2">
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

                    <!-- Member Avatar & Name (Safe Multi-byte UTF-8 Initials) -->
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-slate-900 text-white font-black text-sm flex items-center justify-center flex-shrink-0 overflow-hidden ring-2 ring-emerald-500/30">
                            @if($nw['avatar'] && \Illuminate\Support\Str::startsWith($nw['avatar'], ['http://', 'https://', '/uploads/']))
                                <img src="{{ $nw['avatar'] }}" alt="{{ $nw['name'] }}" class="w-full h-full object-cover">
                            @else
                                @php
                                    $words = explode(' ', trim($nw['name']));
                                    $initials = count($words) >= 2 
                                        ? mb_substr($words[0], 0, 1, 'UTF-8') . mb_substr(end($words), 0, 1, 'UTF-8')
                                        : mb_substr($nw['name'], 0, 2, 'UTF-8');
                                    $initials = mb_strtoupper($initials, 'UTF-8');
                                @endphp
                                {{ $initials }}
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

    <!-- 3. Vis.js Collaboration Network Graph & Sidebar Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6" x-data="{ graphFilter: 'all' }">
        
        <!-- Left Column: Vis.js Canvas (8 cols) -->
        <div class="lg:col-span-8 bg-white dark:bg-slate-800 rounded-3xl p-5 sm:p-6 border border-slate-200/80 dark:border-slate-700 shadow-md flex flex-col justify-between">
            <div>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 pb-3 border-b border-slate-100 dark:border-slate-700">
                    <div>
                        <h3 class="font-black text-slate-900 dark:text-white text-base sm:text-lg flex items-center space-x-2">
                            <span>Đồ Thị Mạng Lưới Tương Quan Hợp Tác</span>
                        </h3>
                        <p class="text-xs text-slate-400 font-medium">Click hoặc rê chuột vào 1 nút để làm nổi bật duy nhất các kết nối liên quan.</p>
                    </div>
                    
                    <!-- Filter & Zoom Controls -->
                    <div class="flex items-center space-x-2">
                        <button id="btn-reset-view" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-sm transition cursor-pointer flex items-center space-x-1">
                            <span>Reset Zoom</span>
                        </button>
                    </div>
                </div>

                <div id="network-graph" class="w-full h-[520px] bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700/60 shadow-inner relative overflow-hidden"></div>
            </div>

            <!-- Legend & Interactive Focus Tip -->
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-700/80 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs font-bold text-slate-600 dark:text-slate-300">
                <div class="flex items-center space-x-4">
                    <span class="flex items-center space-x-1.5"><span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span> <span>Thành viên</span></span>
                    <span class="flex items-center space-x-1.5"><span class="w-3 h-3 rounded-md bg-amber-500 inline-block"></span> <span>Dự án</span></span>
                    <span class="flex items-center space-x-1.5"><span class="w-6 h-0.5 bg-emerald-500 inline-block"></span> <span>Cổ phần (%)</span></span>
                </div>
                <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-extrabold flex items-center space-x-1">
                    <span>💡 Tap/Click vào 1 nút để bật Chế Độ Focus</span>
                </span>
            </div>
        </div>

        <!-- Right Column: Collaboration Stats Sidebar (4 cols) -->
        <div class="lg:col-span-4 space-y-6">
            
            <!-- Top Collaborating Pairs Card -->
            <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 sm:p-6 border border-slate-200/80 dark:border-slate-700 shadow-md space-y-4">
                <div class="pb-3 border-b border-slate-100 dark:border-slate-700">
                    <h4 class="font-black text-slate-900 dark:text-white text-base flex items-center space-x-2">
                        <span>Top Cặp Đôi Hợp Tác Chặt Chẽ</span>
                    </h4>
                    <p class="text-xs text-slate-400 font-medium mt-0.5">Xếp hạng các cặp thành viên đồng hành nhiều dự án nhất</p>
                </div>

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
                <div class="pb-3 border-b border-slate-100 dark:border-slate-700">
                    <h4 class="font-black text-slate-900 dark:text-white text-base flex items-center space-x-2">
                        <span>Tổng Quan Mạng Lưới Dự Án</span>
                    </h4>
                    <p class="text-xs text-slate-400 font-medium mt-0.5">Phân bổ nhân sự & quy mô cổ phần</p>
                </div>

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

<!-- Vis.js Network JS -->
<script src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('network-graph');

        const rawNodes = @json($nodes);
        const rawEdges = @json($edges);

        const isDarkMode = document.documentElement.classList.contains('dark');

        const processedNodes = rawNodes.map(node => {
            if (node.group === 'member') {
                return {
                    ...node,
                    size: 44,
                    font: {
                        size: 15,
                        color: isDarkMode ? '#ffffff' : '#0f172a',
                        face: 'Plus Jakarta Sans',
                        bold: 'true',
                        strokeWidth: 2,
                        strokeColor: isDarkMode ? '#090d16' : '#ffffff'
                    }
                };
            }
            return node;
        });

        const processedEdges = rawEdges.map(edge => {
            return {
                ...edge,
                font: {
                    ...edge.font,
                    size: 13,
                    strokeWidth: 2,
                    strokeColor: isDarkMode ? '#090d16' : '#ffffff',
                    color: '#10b981'
                }
            };
        });

        const nodesDataSet = new vis.DataSet(processedNodes);
        const edgesDataSet = new vis.DataSet(processedEdges);

        const data = { nodes: nodesDataSet, edges: edgesDataSet };
        const options = {
            nodes: {
                borderWidth: 3,
                size: 42,
                shadow: {
                    enabled: true,
                    color: 'rgba(0,0,0,0.15)',
                    size: 10,
                    x: 2,
                    y: 2
                }
            },
            edges: {
                width: 2.5,
                smooth: { type: 'continuous', roundness: 0.5 }, // Curved lines to avoid crossing overlapping mess
                shadow: { enabled: true, color: 'rgba(0,0,0,0.08)', size: 4, x: 1, y: 1 }
            },
            physics: {
                solver: 'forceAtlas2Based',
                forceAtlas2Based: {
                    gravitationalConstant: -280,
                    centralGravity: 0.015,
                    springLength: 150,
                    springConstant: 0.05,
                    damping: 0.4,
                    avoidOverlap: 1 // Guarantee nodes NEVER overlap each other
                },
                maxVelocity: 40,
                minVelocity: 0.1,
                stabilization: { iterations: 200 }
            },
            interaction: {
                hover: true,
                tooltipDelay: 100,
                zoomView: true,
                dragView: true
            }
        };

        const network = new vis.Network(container, data, options);

        // --- INTERACTIVE FOCUS MODE (Anti-Cluttering on Hover/Click) ---
        let highlightActive = false;

        network.on('click', function (params) {
            if (params.nodes.length > 0) {
                highlightActive = true;
                const selectedNodeId = params.nodes[0];

                const connectedNodes = network.getConnectedNodes(selectedNodeId);
                connectedNodes.push(selectedNodeId);

                const connectedEdges = network.getConnectedEdges(selectedNodeId);

                // Highlight connected nodes & edges, dim unrelated ones
                const allNodes = nodesDataSet.get();
                const nodeUpdates = allNodes.map(node => {
                    const isConnected = connectedNodes.includes(node.id);
                    return {
                        id: node.id,
                        opacity: isConnected ? 1 : 0.15
                    };
                });
                nodesDataSet.update(nodeUpdates);

                const allEdges = edgesDataSet.get();
                const edgeUpdates = allEdges.map(edge => {
                    const isConnected = connectedEdges.includes(edge.id);
                    return {
                        id: edge.id,
                        color: { opacity: isConnected ? 1 : 0.08 },
                        width: isConnected ? 4 : 1
                    };
                });
                edgesDataSet.update(edgeUpdates);

            } else if (highlightActive) {
                // Clicked on background: Reset all nodes & edges to normal
                resetHighlight();
            }
        });

        function resetHighlight() {
            highlightActive = false;
            const allNodes = nodesDataSet.get();
            nodesDataSet.update(allNodes.map(n => ({ id: n.id, opacity: 1 })));

            const allEdges = edgesDataSet.get();
            edgesDataSet.update(allEdges.map(e => ({ id: e.id, color: { opacity: 1 }, width: 2.5 })));
        }

        network.once('stabilizationIterationsDone', function () {
            network.fit({
                animation: {
                    duration: 600,
                    easingFunction: 'easeInOutQuad'
                }
            });
        });

        document.getElementById('btn-reset-view').addEventListener('click', function() {
            resetHighlight();
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
