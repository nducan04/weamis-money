@extends('layouts.app')

@section('content')
<div x-data="{ 
    showCalendar: false,
    showEditModal: false,
    showDeleteModal: false,
    showFilters: false,
    activeEvidence: null,
    selectedTx: null,
    filterSearch: '',
    filterMemberId: '',
    filterType: '',
    filterDateFrom: '',
    filterDateTo: '',
    sortOrder: 'desc',
    currentPage: 1,
    perPage: 15,
    currentUserId: {{ auth()->id() }},
    isCurrentUserAdmin: {{ auth()->user()?->isAdmin() ? 'true' : 'false' }},
    canEditTx(tx) {
        if (!tx) return false;
        return this.isCurrentUserAdmin || tx.user_id === this.currentUserId;
    },
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
    },
    calMonthYear: new Date().getFullYear() + '-' + String(new Date().getMonth() + 1).padStart(2, '0'),
    showCalModal: false,
    selectedCalDay: null,
    get calendarDays() {
        if (!this.calMonthYear) return [];
        let parts = this.calMonthYear.split('-');
        let year = parseInt(parts[0]);
        let month = parseInt(parts[1]) - 1;

        let daysInMonth = new Date(year, month + 1, 0).getDate();
        let firstDay = new Date(year, month, 1).getDay(); // 0 is Sunday
        firstDay = firstDay === 0 ? 6 : firstDay - 1; // convert to 0=Mon, 6=Sun

        let days = [];
        // Previous month days
        let prevMonthDays = new Date(year, month, 0).getDate();
        for(let i = firstDay - 1; i >= 0; i--) {
            days.push({ day: prevMonthDays - i, isCurrentMonth: false });
        }
        
        for(let i = 1; i <= daysInMonth; i++) {
            let dStr = year + '-' + String(month + 1).padStart(2, '0') + '-' + String(i).padStart(2, '0');
            // Filter approved transactions for this day
            let dailyTxs = this.rawTransactions.filter(tx => tx.created_at && tx.created_at.startsWith(dStr) && tx.status === 'approved');
            
            let income = 0;
            let expense = 0;
            let users = {};
            
            dailyTxs.forEach(tx => {
                if(tx.type === 'contribution' || tx.type === 'repayment') {
                    income += tx.amount;
                } else {
                    expense += tx.amount;
                }
                
                if(!users[tx.user_name]) { users[tx.user_name] = { income: 0, expense: 0, avatar: tx.user_avatar }; }
                if(tx.type === 'contribution' || tx.type === 'repayment') {
                    users[tx.user_name].income += tx.amount;
                } else {
                    users[tx.user_name].expense += tx.amount;
                }
            });
            
            days.push({
                day: i,
                dateStr: dStr,
                income: income,
                expense: expense,
                users: users,
                txCount: dailyTxs.length,
                isCurrentMonth: true
            });
        }

        // Next month days to complete grid
        let nextMonthDay = 1;
        while(days.length % 7 !== 0) {
            days.push({ day: nextMonthDay++, isCurrentMonth: false });
        }

        return days;
    }
}"
x-init="$watch('filterSearch', () => currentPage = 1); $watch('filterMemberId', () => currentPage = 1); $watch('filterType', () => currentPage = 1); $watch('filterDateFrom', () => currentPage = 1); $watch('filterDateTo', () => currentPage = 1); $watch('sortOrder', () => currentPage = 1)"
class="pb-20 lg:pb-6">

    <!-- Page Header & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center space-x-2">
            <button @click="showCalendar = !showCalendar" class="px-3.5 py-2.5 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700 font-extrabold text-xs rounded-xl shadow-sm transition flex items-center space-x-2 cursor-pointer">
                <span>📅 Lịch Thu Chi Nhật Ký</span>
                <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-black" x-text="showCalendar ? '▲ Thu gọn' : '▼ Mở rộng'"></span>
            </button>
        </div>
    </div>

    <!-- Collapsible Calendar Section -->
    <div x-show="showCalendar" x-cloak x-transition class="bg-white dark:bg-slate-800 rounded-3xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700 shadow-md mb-6">
        <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100 dark:border-slate-700">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-1.5">
                <span>🗓️ Lịch Thu Chi Nhóm Thống Kê Theo Ngày</span>
            </h3>
            <div class="flex items-center gap-2">
                <input type="month" x-model="calMonthYear" class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 text-slate-900 dark:text-white cursor-pointer">
            </div>
        </div>

        <div class="grid grid-cols-7 gap-1 sm:gap-2 text-center text-xs font-bold text-slate-700 dark:text-slate-300 mb-2 uppercase tracking-wider">
            <div>H</div>
            <div>B</div>
            <div>T</div>
            <div>N</div>
            <div>S</div>
            <div>B</div>
            <div>C</div>
        </div>

        <div class="grid grid-cols-7 gap-1 sm:gap-2">
            <template x-for="(dayObj, i) in calendarDays" :key="i">
                <div class="min-h-[55px] sm:min-h-[65px] border border-slate-100 dark:border-slate-700 rounded-xl p-1 sm:p-1.5 bg-slate-50/50 dark:bg-slate-800/50 flex flex-col justify-between transition"
                    :class="{ 'opacity-50 grayscale': !dayObj.isCurrentMonth, 'hover:shadow-md hover:border-emerald-300 dark:hover:border-emerald-700 cursor-pointer bg-white dark:bg-slate-700': dayObj.isCurrentMonth, 'ring-2 ring-emerald-500 shadow-md': dayObj.isCurrentMonth && new Date().toISOString().startsWith(dayObj.dateStr) }"
                    @click="if(dayObj.isCurrentMonth && dayObj.txCount > 0) { selectedCalDay = dayObj; showCalModal = true; }">
                    
                    <div>
                        <p class="text-right font-black text-xs" 
                           :class="{ 'text-emerald-600': dayObj.isCurrentMonth && new Date().toISOString().startsWith(dayObj.dateStr), 'text-slate-700 dark:text-slate-200': dayObj.isCurrentMonth && !new Date().toISOString().startsWith(dayObj.dateStr), 'text-slate-400 dark:text-slate-500': !dayObj.isCurrentMonth }"
                           x-text="dayObj.day"></p>
                    </div>

                    <div x-show="dayObj.isCurrentMonth && dayObj.txCount > 0" class="flex flex-col gap-0.5 mt-0.5 text-right">
                        <!-- Thu nhập màu xanh -->
                        <template x-if="dayObj.income > 0">
                            <span class="text-[9px] font-black text-emerald-600 dark:text-emerald-400 truncate" x-text="'+' + (dayObj.income >= 1000000 ? (dayObj.income/1000000).toFixed(1) + 'tr' : (dayObj.income/1000).toFixed(0) + 'k')"></span>
                        </template>
                        <!-- Chi tiêu màu đỏ -->
                        <template x-if="dayObj.expense > 0">
                            <span class="text-[9px] font-black text-rose-600 dark:text-rose-400 truncate" x-text="'-' + (dayObj.expense >= 1000000 ? (dayObj.expense/1000000).toFixed(1) + 'tr' : (dayObj.expense/1000).toFixed(0) + 'k')"></span>
                        </template>
                    </div>
                </div>
            </template>
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
                <input type="text" x-model="filterSearch" placeholder="Tìm kiếm nội dung..." class="w-full md:w-56 px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-medium focus:ring-2 focus:ring-emerald-500 text-slate-900 dark:text-white">
                
                <select x-model="filterMemberId" class="w-full sm:w-auto px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 text-slate-900 dark:text-white cursor-pointer">
                    <option value="">Tất cả thành viên</option>
                    @foreach($members->where('role', '!=', 'admin') as $m)
                        <option value="{{ $m->id }}">{{ $m->name }}</option>
                    @endforeach
                </select>

                <select x-model="filterType" class="w-full sm:w-auto px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 text-slate-900 dark:text-white cursor-pointer">
                    <option value="">Tất cả loại GD</option>
                    <option value="contribution">Góp quỹ</option>
                    <option value="expense">Chi tiêu</option>
                    <option value="loan">Vay cá nhân</option>
                    <option value="repayment">Trả nợ vay</option>
                    <option value="withdrawal">Rút lương</option>
                    <option value="distribution">Chia tiền</option>
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
                            <th class="py-3.5 px-4 font-bold text-slate-400" x-text="(currentPage - 1) * perPage + index + 1"></th>
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
                                    <div>
                                        <span x-text="tx.user_name"></span>
                                        <template x-if="tx.project_name">
                                            <span class="block text-[10px] text-indigo-600 dark:text-indigo-400 font-extrabold" x-text="'📁 ' + tx.project_name"></span>
                                        </template>
                                    </div>
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
                                <template x-if="tx.type === 'withdrawal'">
                                    <span class="px-2.5 py-1 bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300 font-extrabold rounded-lg text-xs">Rút lương</span>
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
                            <td class="py-4 px-4 font-medium text-slate-800 dark:text-slate-200 max-w-sm break-words whitespace-normal leading-relaxed">
                                <span x-text="tx.description"></span>
                                <div class="flex flex-wrap gap-1 mt-1">
                                    <template x-if="tx.responsible_user_name">
                                        <span class="px-1.5 py-0.5 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300 font-bold rounded text-[10px]" x-text="'TN: ' + tx.responsible_user_name"></span>
                                    </template>
                                    <template x-if="tx.claimant_user_name">
                                        <span class="px-1.5 py-0.5 bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-300 font-bold rounded text-[10px]" x-text="'Đòi: ' + tx.claimant_user_name"></span>
                                    </template>
                                    <template x-if="tx.evidence_type === 'file' && tx.evidence_value">
                                        <button @click="activeEvidence = { type: 'image', value: tx.evidence_value }" class="px-1.5 py-0.5 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 font-bold rounded text-[10px] cursor-pointer">🖼️ Bill</button>
                                    </template>
                                    <template x-if="tx.evidence_type === 'link' && tx.evidence_value">
                                        <a :href="tx.evidence_value" target="_blank" class="px-1.5 py-0.5 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-bold rounded text-[10px]">🔗 Link</a>
                                    </template>
                                    <template x-if="tx.evidence_type === 'text' && tx.evidence_value">
                                        <button @click="activeEvidence = { type: 'text', value: tx.evidence_value }" class="px-1.5 py-0.5 bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 font-bold rounded text-[10px] cursor-pointer">📝 Momo</button>
                                    </template>
                                </div>
                            </td>
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
                                <template x-if="canEditTx(tx)">
                                    <div class="inline-flex items-center space-x-1.5">
                                        <button @click="selectedTx = tx; showEditModal = true" class="px-3 py-1.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-lg font-bold transition cursor-pointer">✏️ Sửa</button>
                                        <button @click="selectedTx = tx; showDeleteModal = true" class="px-3 py-1.5 bg-rose-50 dark:bg-rose-900/30 hover:bg-rose-600 hover:text-white text-rose-600 font-bold rounded-lg transition cursor-pointer">🗑️ Xóa</button>
                                    </div>
                                </template>
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
                        <template x-if="tx.type === 'withdrawal'">
                            <span class="px-2 py-0.5 bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300 font-bold rounded text-[10px] flex-shrink-0">Rút lương</span>
                        </template>
                        <template x-if="tx.type === 'distribution'">
                            <span class="px-2 py-0.5 bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 font-bold rounded text-[10px] flex-shrink-0">Chia %</span>
                        </template>
                        <template x-if="tx.project_name">
                            <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 font-bold rounded text-[10px] flex-shrink-0" x-text="'📂 ' + tx.project_name"></span>
                        </template>
                        <template x-if="tx.billing_cycle">
                            <span class="px-2 py-0.5 bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300 font-bold rounded text-[10px] flex-shrink-0" x-text="'📅 ' + tx.billing_cycle"></span>
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
                            <template x-if="canEditTx(tx)">
                                <div class="flex items-center space-x-2">
                                    <button @click="selectedTx = tx; showEditModal = true" class="px-3 py-1.5 bg-slate-200 dark:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-lg text-xs font-bold active:scale-95 transition cursor-pointer">✏️ Sửa</button>
                                    <button @click="selectedTx = tx; showDeleteModal = true" class="px-3 py-1.5 bg-rose-100 dark:bg-rose-900/30 text-rose-600 rounded-lg text-xs font-bold active:scale-95 transition cursor-pointer">🗑️ Xóa</button>
                                </div>
                            </template>
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
                        <template x-if="isCurrentUserAdmin">
                            <select name="user_id" x-model="selectedTx.user_id" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-medium">
                                @foreach($members->where('role', '!=', 'admin') as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }}</option>
                                @endforeach
                            </select>
                        </template>
                        <template x-if="!isCurrentUserAdmin">
                            <div class="px-3 py-2.5 bg-slate-100 dark:bg-slate-700 rounded-xl text-sm font-bold text-slate-800 dark:text-slate-200">
                                <span x-text="selectedTx.user_name"></span>
                                <input type="hidden" name="user_id" :value="selectedTx.user_id">
                            </div>
                        </template>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Loại Giao Dịch</label>
                        <select name="type" x-model="selectedTx.type" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-medium">
                            <option value="contribution">Góp quỹ</option>
                            <option value="expense">Chi tiêu chung</option>
                            <option value="loan">Vay cá nhân</option>
                            <option value="repayment">Trả nợ vay</option>
                            <option value="withdrawal">Rút lương</option>
                            <option value="distribution">Chia tiền</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Số tiền (VNĐ)</label>
                        <input type="number" name="amount" x-model="selectedTx.amount" required step="1000" min="1000" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-bold">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Gán Dự Án</label>
                        <select name="project_id" x-model="selectedTx.project_id" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-medium">
                            <option value="">-- Không gắn dự án --</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Chu Kỳ Thu Phí / Khoảng Thời Gian</label>
                        <input type="text" name="billing_cycle" x-model="selectedTx.billing_cycle" placeholder="VD: Tháng 05/2026, Quý 2/2026..." class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-medium">
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

    <!-- MODAL: CHI TIẾT NGÀY TRÊN LỊCH -->
    <div x-show="showCalModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/60 backdrop-blur-sm" x-cloak x-transition>
        <div @click.away="showCalModal = false" class="bg-white dark:bg-slate-800 rounded-t-3xl sm:rounded-3xl p-5 sm:p-6 w-full sm:max-w-md shadow-2xl border border-slate-100 dark:border-slate-700 max-h-[85vh] flex flex-col">
            <div class="flex items-center justify-between mb-4 flex-shrink-0">
                <h3 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white">
                    Chi tiết ngày <span class="text-emerald-600" x-text="selectedCalDay ? (selectedCalDay.day + '/' + (calMonthYear ? calMonthYear.split('-')[1] : '') + '/' + (calMonthYear ? calMonthYear.split('-')[0] : '')) : ''"></span>
                </h3>
                <button @click="showCalModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white">
                    ✕
                </button>
            </div>
            
            <div class="overflow-y-auto pr-1 flex-1 space-y-4">
                <template x-if="selectedCalDay">
                    <div>
                        <!-- Summary -->
                        <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700/50 rounded-2xl mb-4 border border-slate-100 dark:border-slate-700">
                            <div class="text-center w-1/2 border-r border-slate-200 dark:border-slate-600">
                                <p class="text-[10px] uppercase font-bold text-slate-400 mb-0.5">Tổng Thu</p>
                                <p class="text-sm font-black text-emerald-600 dark:text-emerald-400" x-text="'+' + new Intl.NumberFormat('vi-VN').format(selectedCalDay.income)"></p>
                            </div>
                            <div class="text-center w-1/2">
                                <p class="text-[10px] uppercase font-bold text-slate-400 mb-0.5">Tổng Chi</p>
                                <p class="text-sm font-black text-rose-600 dark:text-rose-400" x-text="'-' + new Intl.NumberFormat('vi-VN').format(selectedCalDay.expense)"></p>
                            </div>
                        </div>

                        <!-- Per User Breakdown -->
                        <div class="space-y-3">
                            <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Chi tiết từng tài khoản</h4>
                            <template x-for="(userData, userName) in selectedCalDay.users" :key="userName">
                                <div class="flex items-center justify-between p-3 border border-slate-100 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800">
                                    <div class="flex items-center space-x-2.5">
                                        <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200 font-bold text-xs flex items-center justify-center flex-shrink-0 overflow-hidden">
                                            <template x-if="userData.avatar && (userData.avatar.startsWith('http') || userData.avatar.startsWith('/uploads/'))">
                                                <img :src="userData.avatar" :alt="userName" class="w-full h-full object-cover">
                                            </template>
                                            <template x-if="!userData.avatar || (!userData.avatar.startsWith('http') && !userData.avatar.startsWith('/uploads/'))">
                                                <span x-text="userData.avatar || (userName ? userName.substr(0, 2) : 'HV')"></span>
                                            </template>
                                        </div>
                                        <p class="text-sm font-bold text-slate-800 dark:text-white" x-text="userName"></p>
                                    </div>
                                    <div class="text-right">
                                        <template x-if="userData.income > 0">
                                            <p class="text-xs font-black text-emerald-600 dark:text-emerald-400" x-text="'+' + new Intl.NumberFormat('vi-VN').format(userData.income)"></p>
                                        </template>
                                        <template x-if="userData.expense > 0">
                                            <p class="text-xs font-black text-rose-600 dark:text-rose-400" x-text="'-' + new Intl.NumberFormat('vi-VN').format(userData.expense)"></p>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
            
            <div class="pt-4 mt-2 border-t border-slate-100 dark:border-slate-700 flex-shrink-0 text-center">
                <button @click="showCalModal = false" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-white rounded-xl text-xs font-bold transition w-full">Đóng</button>
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

</div>

@endsection
