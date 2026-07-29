@extends('layouts.app')

@section('content')
<div x-data="{ 
    showAddModal: false,
    showEditModal: false,
    showDeleteModal: false,
    showFilters: false,
    selectedTx: null,
    filterSearch: '',
    filterMemberId: '',
    filterType: '',
    filterDateFrom: '',
    filterDateTo: '',
    sortOrder: 'desc',
    currentPage: 1,
    perPage: 15,
    rawTransactions: {{ \Illuminate\Support\Js::from($allTransactions) }},
    get filteredTransactions() {
        let search = this.filterSearch.toLowerCase().trim();
        let memberId = this.filterMemberId;
        let type = this.filterType;
        let dateFrom = this.filterDateFrom;
        let dateTo = this.filterDateTo;

        let filtered = this.rawTransactions.filter(tx => {
            let matchSearch = !search || (tx.description && tx.description.toLowerCase().includes(search));
            let matchMember = !memberId || tx.user_id == memberId;
            let matchType = !type || tx.type == type;

            let txDate = tx.created_at ? tx.created_at.substring(0, 10) : '';
            let matchFrom = !dateFrom || (txDate && txDate >= dateFrom);
            let matchTo = !dateTo || (txDate && txDate <= dateTo);

            return matchSearch && matchMember && matchType && matchFrom && matchTo;
        });

        let order = this.sortOrder;
        return filtered.sort((a, b) => {
            let dateA = a.created_at ? new Date(a.created_at).getTime() : 0;
            let dateB = b.created_at ? new Date(b.created_at).getTime() : 0;
            if (dateA === dateB) {
                return order === 'desc' ? b.id - a.id : a.id - b.id;
            }
            return order === 'desc' ? dateB - dateA : dateA - dateB;
        });
    },
    get totalPages() {
        return Math.max(1, Math.ceil(this.filteredTransactions.length / this.perPage));
    },
    get paginatedTransactions() {
        let start = (this.currentPage - 1) * this.perPage;
        return this.filteredTransactions.slice(start, start + this.perPage);
    },
    get pageNumbers() {
        let pages = [];
        let tp = this.totalPages;
        let cp = this.currentPage;
        if (tp <= 7) {
            for (let i = 1; i <= tp; i++) pages.push(i);
        } else {
            pages.push(1);
            if (cp > 3) pages.push('...');
            for (let i = Math.max(2, cp - 1); i <= Math.min(tp - 1, cp + 1); i++) pages.push(i);
            if (cp < tp - 2) pages.push('...');
            pages.push(tp);
        }
        return pages;
    },
    goToPage(n) { if (n >= 1 && n <= this.totalPages) this.currentPage = n; },
    prevPage() { if (this.currentPage > 1) this.currentPage--; },
    nextPage() { if (this.currentPage < this.totalPages) this.currentPage++; },
    resetFilters() {
        this.filterSearch = '';
        this.filterMemberId = '';
        this.filterType = '';
        this.filterDateFrom = '';
        this.filterDateTo = '';
        this.sortOrder = 'desc';
        this.currentPage = 1;
    }
}"
x-init="$watch('filterSearch', () => currentPage = 1); $watch('filterMemberId', () => currentPage = 1); $watch('filterType', () => currentPage = 1); $watch('filterDateFrom', () => currentPage = 1); $watch('filterDateTo', () => currentPage = 1); $watch('sortOrder', () => currentPage = 1)"
class="pb-20 lg:pb-6">

    <!-- Page Header & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white flex items-center space-x-2">
                <span>📜 Lịch Sử Giao Dịch</span>
            </h2>
            <p class="text-xs text-slate-400 font-medium">Toàn bộ lịch sử thu, chi, vay và trả nợ nhóm</p>
        </div>

        <div class="flex items-center space-x-2">
            <button @click="showAddModal = true" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-md transition flex items-center space-x-1.5 cursor-pointer active:scale-95">
                <span>➕ Thêm Giao Dịch Mới</span>
            </button>
        </div>
    </div>

    <!-- Main Container Card -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-4 sm:p-6 border border-slate-200/80 dark:border-slate-700 shadow-md">
        
        <!-- Reactive Filter Bar: Zero Reload, Instant Filter -->
        <div class="flex flex-col gap-3 mb-5 pb-5 border-b border-slate-100 dark:border-slate-700">
            <div class="flex items-center justify-between">
                <p class="text-xs text-slate-500 font-bold">
                    Tổng số: <span class="text-emerald-600 dark:text-emerald-400 font-extrabold" x-text="filteredTransactions.length"></span> giao dịch
                    <template x-if="filterSearch || filterMemberId || filterType || filterDateFrom || filterDateTo || sortOrder !== 'desc'">
                        <span class="text-indigo-500 font-bold"> (đang lọc)</span>
                    </template>
                </p>

                <!-- Mobile filter toggle -->
                <button @click="showFilters = !showFilters" class="md:hidden px-3 py-1.5 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold flex items-center space-x-1 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    <span>Bộ Lọc</span>
                </button>
            </div>

            <div class="gap-2 grid-cols-1 sm:grid-cols-2 md:flex md:flex-wrap md:items-center" :class="showFilters ? 'grid' : 'hidden md:flex'">
                <input type="text" x-model="filterSearch" placeholder="🔍 Tìm kiếm nội dung..." class="w-full md:w-56 px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-medium focus:ring-2 focus:ring-emerald-500 text-slate-900 dark:text-white">
                
                <select x-model="filterMemberId" class="w-full sm:w-auto px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 text-slate-900 dark:text-white cursor-pointer">
                    <option value="">Tất cả thành viên</option>
                    @foreach($members as $m)
                        <option value="{{ $m->id }}">{{ $m->name }}</option>
                    @endforeach
                </select>

                <select x-model="filterType" class="w-full sm:w-auto px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 text-slate-900 dark:text-white cursor-pointer">
                    <option value="">Tất cả loại GD</option>
                    <option value="contribution">Góp quỹ (Thu)</option>
                    <option value="expense">Chi tiêu (Chi)</option>
                    <option value="loan">Vay cá nhân</option>
                    <option value="repayment">Trả nợ vay</option>
                    <option value="distribution">Chia tiền %</option>
                </select>

                <!-- Lọc Theo Ngày Tháng Năm -->
                <div class="flex items-center space-x-1.5 w-full sm:w-auto bg-slate-50 dark:bg-slate-700/50 p-1 rounded-xl border border-slate-200 dark:border-slate-600">
                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 pl-1.5">📅</span>
                    <input type="date" x-model="filterDateFrom" title="Từ ngày" class="px-2 py-1 rounded-lg border-0 bg-white dark:bg-slate-800 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 text-slate-900 dark:text-white cursor-pointer">
                    <span class="text-slate-400 text-xs font-bold">➔</span>
                    <input type="date" x-model="filterDateTo" title="Đến ngày" class="px-2 py-1 rounded-lg border-0 bg-white dark:bg-slate-800 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 text-slate-900 dark:text-white cursor-pointer">
                </div>

                <!-- Sắp Xếp Ngày Giao Dịch -->
                <select x-model="sortOrder" class="w-full sm:w-auto px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 text-slate-900 dark:text-white cursor-pointer">
                    <option value="desc">⬇️ Mới nhất ➔ Cũ nhất</option>
                    <option value="asc">⬆️ Cũ nhất ➔ Mới nhất</option>
                </select>

                <template x-if="filterSearch || filterMemberId || filterType || filterDateFrom || filterDateTo || sortOrder !== 'desc'">
                    <button @click="resetFilters()" class="px-3 py-2 text-xs font-bold text-rose-600 hover:text-rose-700 hover:underline flex items-center space-x-1 cursor-pointer">
                        <span>✕ Xóa lọc</span>
                    </button>
                </template>
            </div>
        </div>

        <!-- Desktop Table -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                <thead class="bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-700/60 dark:to-slate-700/30 text-slate-500 uppercase font-bold text-[10px] tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4 rounded-l-xl">STT</th>
                        <th class="py-3.5 px-4">Thời Gian</th>
                        <th class="py-3.5 px-4">Thành Viên</th>
                        <th class="py-3.5 px-4">Loại GD</th>
                        <th class="py-3.5 px-4">Số Tiền</th>
                        <th class="py-3.5 px-4">Nội Dung</th>
                        <th class="py-3.5 px-4">Trạng Thái</th>
                        <th class="py-3.5 px-4 text-right rounded-r-xl">Hành Động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                    <template x-for="(tx, index) in paginatedTransactions" :key="tx.id">
                        <tr class="hover:bg-indigo-50/40 dark:hover:bg-indigo-900/10 transition-colors duration-150">
                            <td class="py-4 px-4 font-bold text-slate-400" x-text="sortOrder === 'asc' ? ((currentPage - 1) * perPage + index + 1) : (filteredTransactions.length - ((currentPage - 1) * perPage + index))"></td>
                            <td class="py-4 px-4 whitespace-nowrap text-slate-500 font-semibold" x-text="tx.created_at_formatted"></td>
                            <td class="py-4 px-4 font-bold text-slate-900 dark:text-white">
                                <div class="flex items-center space-x-2.5">
                                    <div class="w-7 h-7 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200 font-bold text-xs flex items-center justify-center flex-shrink-0 overflow-hidden">
                                        <template x-if="tx.user_avatar && (tx.user_avatar.startsWith('http') || tx.user_avatar.startsWith('/uploads/'))">
                                            <img :src="tx.user_avatar" :alt="tx.user_name" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!tx.user_avatar || (!tx.user_avatar.startsWith('http') && !tx.user_avatar.startsWith('/uploads/'))">
                                            <span x-text="tx.user_avatar || (tx.user_name ? tx.user_name.substr(0, 2) : 'HV')"></span>
                                        </template>
                                    </div>
                                    <span x-text="tx.user_name"></span>
                                </div>
                            </td>
                            <td class="py-4 px-4 whitespace-nowrap">
                                <template x-if="tx.type === 'contribution'">
                                    <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 font-extrabold rounded-lg text-xs">Góp quỹ</span>
                                </template>
                                <template x-if="tx.type === 'expense'">
                                    <span class="px-2.5 py-1 bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 font-extrabold rounded-lg text-xs">Chi tiêu</span>
                                </template>
                                <template x-if="tx.type === 'loan'">
                                    <span class="px-2.5 py-1 bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300 font-extrabold rounded-lg text-xs">Vay</span>
                                </template>
                                <template x-if="tx.type === 'repayment'">
                                    <span class="px-2.5 py-1 bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 font-extrabold rounded-lg text-xs">Trả nợ</span>
                                </template>
                                <template x-if="tx.type === 'distribution'">
                                    <span class="px-2.5 py-1 bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 font-extrabold rounded-lg text-xs">Chia %</span>
                                </template>
                            </td>
                            <td class="py-4 px-4 font-black text-sm whitespace-nowrap">
                                <template x-if="tx.type === 'contribution' || tx.type === 'repayment'">
                                    <span class="text-emerald-600 dark:text-emerald-400" x-text="'+' + new Intl.NumberFormat('vi-VN').format(tx.amount) + 'đ'"></span>
                                </template>
                                <template x-if="tx.type !== 'contribution' && tx.type !== 'repayment'">
                                    <span class="text-slate-900 dark:text-slate-100" x-text="'-' + new Intl.NumberFormat('vi-VN').format(tx.amount) + 'đ'"></span>
                                </template>
                            </td>
                            <td class="py-4 px-4 font-medium text-slate-800 dark:text-slate-200 max-w-sm break-words whitespace-normal leading-relaxed" :title="tx.description" x-text="tx.description"></td>
                            <td class="py-4 px-4 whitespace-nowrap">
                                <template x-if="tx.status === 'approved'">
                                    <span class="text-emerald-600 font-bold">✓ Đã duyệt</span>
                                </template>
                                <template x-if="tx.status === 'pending'">
                                    <span class="text-amber-600 font-bold">⏳ Chờ duyệt</span>
                                </template>
                                <template x-if="tx.status === 'rejected'">
                                    <span class="text-slate-400 font-bold line-through">Từ chối</span>
                                </template>
                            </td>
                            <td class="py-4 px-4 text-right whitespace-nowrap space-x-1.5">
                                <button @click="selectedTx = tx; showEditModal = true" class="px-3 py-1.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-lg font-bold transition cursor-pointer">✏️ Sửa</button>
                                <button @click="selectedTx = tx; showDeleteModal = true" class="px-3 py-1.5 bg-rose-50 dark:bg-rose-900/30 hover:bg-rose-600 hover:text-white text-rose-600 font-bold rounded-lg transition cursor-pointer">🗑️ Xóa</button>
                            </td>
                        </tr>
                    </template>

                    <template x-if="filteredTransactions.length === 0">
                        <tr>
                            <td colspan="8" class="text-center py-12 text-slate-400">Không tìm thấy giao dịch nào phù hợp.</td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="lg:hidden space-y-3">
            <template x-for="(tx, index) in paginatedTransactions" :key="tx.id">
                <div class="p-4 bg-slate-50 dark:bg-slate-700/30 rounded-2xl border border-slate-100 dark:border-slate-700 hover:shadow-md transition">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center space-x-2.5 min-w-0">
                            <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-600 text-slate-800 dark:text-slate-200 font-bold text-xs flex items-center justify-center flex-shrink-0 overflow-hidden">
                                <template x-if="tx.user_avatar && (tx.user_avatar.startsWith('http') || tx.user_avatar.startsWith('/uploads/'))">
                                    <img :src="tx.user_avatar" :alt="tx.user_name" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!tx.user_avatar || (!tx.user_avatar.startsWith('http') && !tx.user_avatar.startsWith('/uploads/'))">
                                    <span x-text="tx.user_avatar || (tx.user_name ? tx.user_name.substr(0, 2) : 'HV')"></span>
                                </template>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-slate-900 dark:text-white truncate" x-text="tx.user_name"></p>
                                <p class="text-[10px] text-slate-400" x-text="tx.created_at_formatted"></p>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0 ml-2">
                            <template x-if="tx.type === 'contribution' || tx.type === 'repayment'">
                                <p class="text-base font-black text-emerald-600 dark:text-emerald-400" x-text="'+' + new Intl.NumberFormat('vi-VN').format(tx.amount) + 'đ'"></p>
                            </template>
                            <template x-if="tx.type !== 'contribution' && tx.type !== 'repayment'">
                                <p class="text-base font-black text-slate-900 dark:text-slate-100" x-text="'-' + new Intl.NumberFormat('vi-VN').format(tx.amount) + 'đ'"></p>
                            </template>
                        </div>
                    </div>

                    <div class="flex items-start space-x-2 mb-3">
                        <template x-if="tx.type === 'contribution'">
                            <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 font-bold rounded text-[10px] flex-shrink-0">Góp quỹ</span>
                        </template>
                        <template x-if="tx.type === 'expense'">
                            <span class="px-2 py-0.5 bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 font-bold rounded text-[10px] flex-shrink-0">Chi tiêu</span>
                        </template>
                        <template x-if="tx.type === 'loan'">
                            <span class="px-2 py-0.5 bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300 font-bold rounded text-[10px] flex-shrink-0">Vay</span>
                        </template>
                        <template x-if="tx.type === 'repayment'">
                            <span class="px-2 py-0.5 bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 font-bold rounded text-[10px] flex-shrink-0">Trả nợ</span>
                        </template>
                        <template x-if="tx.type === 'distribution'">
                            <span class="px-2 py-0.5 bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 font-bold rounded text-[10px] flex-shrink-0">Chia %</span>
                        </template>
                        <p class="text-xs text-slate-700 dark:text-slate-200 font-medium leading-relaxed" x-text="tx.description"></p>
                    </div>

                    <div class="flex items-center justify-between pt-2.5 border-t border-slate-200/60 dark:border-slate-600/40">
                        <div>
                            <template x-if="tx.status === 'approved'">
                                <span class="text-xs text-emerald-600 font-bold">✓ Đã duyệt</span>
                            </template>
                            <template x-if="tx.status === 'pending'">
                                <span class="text-xs text-amber-600 font-bold">⏳ Chờ duyệt</span>
                            </template>
                            <template x-if="tx.status === 'rejected'">
                                <span class="text-xs text-slate-400 font-bold line-through">Từ chối</span>
                            </template>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button @click="selectedTx = tx; showEditModal = true" class="px-3 py-1.5 bg-slate-200 dark:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-lg text-xs font-bold active:scale-95 transition cursor-pointer">✏️ Sửa</button>
                            <button @click="selectedTx = tx; showDeleteModal = true" class="px-3 py-1.5 bg-rose-100 dark:bg-rose-900/30 text-rose-600 rounded-lg text-xs font-bold active:scale-95 transition cursor-pointer">🗑️ Xóa</button>
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="filteredTransactions.length === 0">
                <p class="text-center py-12 text-xs text-slate-400">Không tìm thấy giao dịch nào phù hợp.</p>
            </template>
        </div>

        <!-- Alpine.js Client-Side Pagination -->
        <template x-if="totalPages > 1">
            <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-700">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                    <p class="text-xs text-slate-400 font-medium">
                        Hiển thị <span class="font-bold text-slate-600 dark:text-slate-300" x-text="((currentPage - 1) * perPage + 1)"></span>–<span class="font-bold text-slate-600 dark:text-slate-300" x-text="Math.min(currentPage * perPage, filteredTransactions.length)"></span> / <span class="font-bold text-slate-600 dark:text-slate-300" x-text="filteredTransactions.length"></span> giao dịch
                    </p>

                    <div class="flex items-center space-x-1">
                        <button @click="prevPage()" :disabled="currentPage <= 1" class="px-3 py-2 rounded-xl text-xs font-bold transition cursor-pointer" :class="currentPage <= 1 ? 'text-slate-300 dark:text-slate-600 cursor-not-allowed' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700'">
                            ◀ Trái
                        </button>

                        <template x-for="page in pageNumbers" :key="page">
                            <button
                                @click="typeof page === 'number' && goToPage(page)"
                                class="min-w-[36px] h-9 rounded-xl text-xs font-bold transition"
                                :class="{
                                    'bg-gradient-to-r from-emerald-500 to-teal-600 text-white shadow-md shadow-emerald-500/25': page === currentPage,
                                    'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 cursor-pointer': page !== currentPage && typeof page === 'number',
                                    'text-slate-400 cursor-default': typeof page !== 'number'
                                }"
                                x-text="page"
                            ></button>
                        </template>

                        <button @click="nextPage()" :disabled="currentPage >= totalPages" class="px-3 py-2 rounded-xl text-xs font-bold transition cursor-pointer" :class="currentPage >= totalPages ? 'text-slate-300 dark:text-slate-600 cursor-not-allowed' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700'">
                            Phải ▶
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- MODAL: THÊM GIAO DỊCH MỚI -->
    <div x-show="showAddModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/60 backdrop-blur-sm" x-cloak x-transition>
        <div @click.away="showAddModal = false" class="bg-white dark:bg-slate-800 rounded-t-3xl sm:rounded-3xl p-5 sm:p-6 w-full sm:max-w-md shadow-2xl border border-slate-100 dark:border-slate-700 max-h-[90vh] overflow-y-auto">
            <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mb-4">➕ Thêm Giao Dịch Thu Chi</h3>

            <form action="{{ route('transactions.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Thành viên</label>
                    <select name="user_id" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-medium focus:ring-2 focus:ring-emerald-500">
                        @foreach($members as $m)
                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Phân loại</label>
                    <select name="type" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-medium focus:ring-2 focus:ring-emerald-500">
                        <option value="contribution">🟢 Góp Quỹ (Thu vào)</option>
                        <option value="expense">🔴 Chi Tiêu Nhóm (Chi ra)</option>
                        <option value="loan">🟣 Vay Cá Nhân</option>
                        <option value="repayment">🔵 Trả Nợ Vay</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Số tiền (VNĐ)</label>
                    <input type="number" name="amount" required step="1000" min="1000" placeholder="VD: 500000" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-bold focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nội dung / Lí do</label>
                    <input type="text" name="description" required placeholder="VD: Tiền mua nước uống họp team..." class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm focus:ring-2 focus:ring-emerald-500">
                </div>

                <div class="flex items-center justify-end space-x-3 pt-2">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2.5 text-xs font-bold text-slate-500">Hủy</button>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 text-white rounded-xl text-xs font-bold shadow-md hover:bg-emerald-700 active:scale-95 transition">Lưu Giao Dịch</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: CHỈNH SỬA GIAO DỊCH -->
    <div x-show="showEditModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/60 backdrop-blur-sm" x-cloak x-transition>
        <div @click.away="showEditModal = false" class="bg-white dark:bg-slate-800 rounded-t-3xl sm:rounded-3xl p-5 sm:p-6 w-full sm:max-w-md shadow-2xl border border-slate-100 dark:border-slate-700 max-h-[90vh] overflow-y-auto">
            <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mb-4">✏️ Chỉnh Sửa Giao Dịch</h3>

            <template x-if="selectedTx">
                <form :action="`/transactions/${selectedTx.id}`" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Thành viên</label>
                        <select name="user_id" x-model="selectedTx.user_id" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-medium">
                            @foreach($members as $m)
                                <option value="{{ $m->id }}">{{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Loại Giao Dịch</label>
                        <select name="type" x-model="selectedTx.type" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-medium">
                            <option value="contribution">Góp quỹ (Thu)</option>
                            <option value="expense">Chi tiêu chung (Chi)</option>
                            <option value="loan">Vay cá nhân</option>
                            <option value="repayment">Trả nợ vay</option>
                            <option value="distribution">Chia tiền %</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Số tiền (VNĐ)</label>
                        <input type="number" name="amount" x-model="selectedTx.amount" required step="1000" min="1000" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-bold">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nội dung</label>
                        <input type="text" name="description" x-model="selectedTx.description" required class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm">
                    </div>

                    <div class="p-3 bg-amber-50 dark:bg-amber-900/30 rounded-xl text-[11px] text-amber-800 dark:text-amber-200">
                        ⚠️ Sửa giao dịch đã duyệt sẽ tự động điều chỉnh lại Số dư quỹ và Dư nợ cá nhân.
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-2">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2.5 text-xs font-bold text-slate-500">Hủy</button>
                        <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700 active:scale-95 transition">Cập Nhật</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    <!-- MODAL: XÓA GIAO DỊCH -->
    <div x-show="showDeleteModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/60 backdrop-blur-sm" x-cloak x-transition>
        <div @click.away="showDeleteModal = false" class="bg-white dark:bg-slate-800 rounded-t-3xl sm:rounded-3xl p-5 sm:p-6 w-full sm:max-w-sm shadow-2xl border border-slate-100 dark:border-slate-700">
            <h3 class="text-base sm:text-lg font-bold text-rose-600 mb-2">🗑️ Xác Nhận Xóa</h3>
            
            <template x-if="selectedTx">
                <div class="space-y-3">
                    <p class="text-xs text-slate-600 dark:text-slate-300">
                        Bạn có chắc chắn muốn xóa giao dịch: <strong x-text="selectedTx.description"></strong> (<span x-text="new Intl.NumberFormat('vi-VN').format(selectedTx.amount) + 'đ'"></span>)?
                    </p>
                    <p class="text-[11px] text-slate-400">
                        Hệ thống sẽ hoàn tác tác động đối với số dư quỹ.
                    </p>

                    <form :action="`/transactions/${selectedTx.id}`" method="POST" class="flex items-center justify-end space-x-2 pt-3">
                        @csrf
                        @method('DELETE')
                        <button type="button" @click="showDeleteModal = false" class="px-4 py-2.5 text-xs font-bold text-slate-500">Hủy</button>
                        <button type="submit" class="px-5 py-2.5 bg-rose-600 text-white rounded-xl text-xs font-bold hover:bg-rose-700 active:scale-95 transition">Xóa</button>
                    </form>
                </div>
            </template>
        </div>
    </div>

</div>
@endsection
