@extends('layouts.app')

@section('content')
<div x-data="{ 
    showCalendar: false,
    showAddModal: false,
    showEditModal: false,
    showDeleteModal: false,
    showSplitModal: false,
    showFilters: false,
    quickType: 'expense',
    quickAmount: '',
    quickNote: '',
    quickDate: (() => { let d = new Date(); return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0'); })(),
    quickTime: (() => { let d = new Date(); return String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0'); })(),
    get formattedQuickDate() {
        if (!this.quickDate) return '';
        let d = new Date(this.quickDate);
        let days = ['CN', 'Th 2', 'Th 3', 'Th 4', 'Th 5', 'Th 6', 'Th 7'];
        return d.getDate().toString().padStart(2, '0') + '/' + (d.getMonth() + 1).toString().padStart(2, '0') + '/' + d.getFullYear() + ' (' + days[d.getDay()] + ')';
    },
    prevQuickDay() {
        let d = new Date(this.quickDate);
        d.setDate(d.getDate() - 1);
        this.quickDate = d.toISOString().substring(0, 10);
    },
    nextQuickDay() {
        let d = new Date(this.quickDate);
        d.setDate(d.getDate() + 1);
        this.quickDate = d.toISOString().substring(0, 10);
    },
    activeEvidence: null,
    selectedTx: null,
    splitRows: [],
    openSplitModal(tx) {
        this.selectedTx = tx;
        let half1 = Math.floor(tx.amount / 2);
        let half2 = Math.ceil(tx.amount / 2);
        this.splitRows = [
            { to_account_id: '', amount: half1, displayAmount: half1 > 0 ? half1.toLocaleString('vi-VN') : '', memo: '' },
            { to_account_id: '', amount: half2, displayAmount: half2 > 0 ? half2.toLocaleString('vi-VN') : '', memo: '' }
        ];
        this.showSplitModal = true;
    },
    addSplitRow() {
        this.splitRows.push({ to_account_id: '', amount: 0, displayAmount: '', memo: '' });
    },
    removeSplitRow(idx) {
        if (this.splitRows.length > 2) {
            this.splitRows.splice(idx, 1);
        }
    },
    get splitTotal() {
        return this.splitRows.reduce((sum, r) => sum + (parseInt(r.amount, 10) || 0), 0);
    },
    filterSearch: '',
    filterMemberId: '',
    filterType: '',
    filterDateFrom: '',
    filterDateTo: '',
    sortOrder: 'desc',
    currentPage: 1,
    perPage: 15,
    currentUserId: {{ auth()->id() ? auth()->id() : 'null' }},
    isCurrentUserAdmin: {{ auth()->user()?->isAdmin() ? 'true' : 'false' }},
    canEditTx(tx) {
        if (!tx || !this.currentUserId) return false;
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
        let prevMonthDays = new Date(year, month, 0).getDate();
        for(let i = firstDay - 1; i >= 0; i--) {
            days.push({ day: prevMonthDays - i, isCurrentMonth: false });
        }
        
        for(let i = 1; i <= daysInMonth; i++) {
            let dStr = year + '-' + String(month + 1).padStart(2, '0') + '-' + String(i).padStart(2, '0');
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

        let nextMonthDay = 1;
        while(days.length % 7 !== 0) {
            days.push({ day: nextMonthDay++, isCurrentMonth: false });
        }

        return days;
    }
}"
x-init="$watch('filterSearch', () => currentPage = 1); $watch('filterMemberId', () => currentPage = 1); $watch('filterType', () => currentPage = 1); $watch('filterDateFrom', () => currentPage = 1); $watch('filterDateTo', () => currentPage = 1); $watch('sortOrder', () => currentPage = 1)"
class="pb-12 sm:pb-8">

    <!-- Main Container Card -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-4 sm:p-6 border border-slate-200/80 dark:border-slate-700 shadow-md">
        
        <!-- Single Horizontal Action & Filter Bar -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-5 pb-5 border-b border-slate-100 dark:border-slate-700">
            <!-- Left Side: Filters & Total Count -->
            <div class="flex flex-col sm:flex-row sm:flex-wrap items-center gap-2.5 w-full md:w-auto">
                <input type="text" x-model="filterSearch" placeholder="Tìm kiếm nội dung..." class="w-full sm:w-48 px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-medium focus:ring-2 focus:ring-emerald-500 text-slate-900 dark:text-white">
                
                <select x-model="filterMemberId" class="w-full sm:w-auto px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 text-slate-900 dark:text-white cursor-pointer">
                    <option value="">Tất cả thành viên</option>
                    @foreach($members->where('role', '!=', 'admin') as $m)
                        <option value="{{ $m->id }}">{{ $m->name }}</option>
                    @endforeach
                </select>

                <select x-model="sortOrder" class="w-full sm:w-auto px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 text-slate-900 dark:text-white cursor-pointer">
                    <option value="desc">Mới nhất ➔ Cũ nhất</option>
                    <option value="asc">Cũ nhất ➔ Mới nhất</option>
                </select>

                <p class="text-xs text-slate-500 font-bold whitespace-nowrap self-start sm:self-center">
                    Tổng số: <span class="text-emerald-600 dark:text-emerald-400 font-extrabold" x-text="filteredTransactions.length"></span> giao dịch
                    <template x-if="filterSearch || filterMemberId || sortOrder !== 'desc'">
                        <span class="text-indigo-500 font-bold"> (đang lọc)</span>
                    </template>
                </p>

                <template x-if="filterSearch || filterMemberId || sortOrder !== 'desc'">
                    <button @click="resetFilters()" class="px-2.5 py-1.5 text-xs font-bold text-rose-600 hover:text-rose-700 hover:underline cursor-pointer">
                        <span>✕ Xóa lọc</span>
                    </button>
                </template>
            </div>

            <!-- Right Side: Thêm Giao Dịch Button (Only for Logged In Users) -->
            @auth
            <button @click="showAddModal = true" class="w-full md:w-auto px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white rounded-xl text-xs sm:text-sm font-extrabold shadow-md shadow-emerald-500/20 active:scale-95 transition flex items-center justify-center space-x-2 cursor-pointer flex-shrink-0">
                <span>Thêm Giao Dịch</span>
            </button>
            @endauth
        </div>

        <!-- Desktop Table -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                <thead class="bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-700/60 dark:to-slate-700/30 text-slate-500 uppercase font-bold text-[10px] tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4 rounded-l-xl">Mã ID</th>
                        <th class="py-3.5 px-4">Thời Gian</th>
                        <th class="py-3.5 px-4">Thành Viên</th>
                        <th class="py-3.5 px-4">Số Tiền</th>
                        <th class="py-3.5 px-4">Nội Dung</th>
                        <th class="py-3.5 px-4">Luồng Tiền</th>
                        <th class="py-3.5 px-4 text-right rounded-r-xl">Hành Động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                    <template x-for="(tx, index) in paginatedTransactions" :key="tx.id">
                        <tr class="hover:bg-indigo-50/40 dark:hover:bg-indigo-900/10 transition-colors duration-150">
                            <td class="py-3.5 px-4 font-mono font-black text-indigo-600 dark:text-indigo-400" x-text="tx.id"></td>
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
                                        <template x-if="tx.is_fund_only">
                                            <span class="inline-flex items-center px-1.5 py-0.5 mt-0.5 bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300 font-extrabold text-[10px] rounded shadow-xs border border-amber-300/60 dark:border-amber-700/50">
                                                🏦 Vào Quỹ
                                            </span>
                                        </template>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4 font-black text-sm whitespace-nowrap">
                                <template x-if="tx.type === 'contribution' || tx.type === 'repayment' || tx.type === 'adjustment'">
                                    <span class="text-emerald-600 dark:text-emerald-400" x-text="'+' + new Intl.NumberFormat('vi-VN').format(tx.amount) + 'đ'"></span>
                                </template>
                                <template x-if="tx.type !== 'contribution' && tx.type !== 'repayment' && tx.type !== 'adjustment'">
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

                            <!-- Luồng Tiền (Double-Entry Flow) -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                <template x-if="tx.is_split">
                                    <div class="flex flex-col items-start gap-1">
                                        <span class="px-2 py-0.5 bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 font-extrabold rounded-md text-[10px]">Đã tách</span>
                                        <div class="flex flex-col gap-0.5 mt-0.5">
                                            <template x-for="(s, sIdx) in tx.splits" :key="sIdx">
                                                <div class="flex items-center gap-1 text-[10px] text-slate-600 dark:text-slate-300 font-medium">
                                                    <span class="font-bold text-emerald-600 dark:text-emerald-400" x-text="new Intl.NumberFormat('vi-VN').format(s.amount) + 'đ'"></span>
                                                    <span class="text-indigo-500 font-bold">→</span>
                                                    <span class="font-bold text-indigo-600 dark:text-indigo-400 truncate max-w-[130px]" x-text="s.to_account_name.replace('Dự án ', '').replace('Ví ', '')"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="!tx.is_split && tx.from_account_name && tx.to_account_name">
                                    <div class="flex flex-col items-start gap-0.5">
                                        <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 truncate max-w-[120px]" x-text="tx.from_account_name.replace('Ví ', '')"></span>
                                        <span class="text-[10px] font-black text-indigo-500">↓</span>
                                        <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 truncate max-w-[120px]" x-text="tx.to_account_name.replace('Ví ', '')"></span>
                                    </div>
                                </template>
                                <template x-if="!tx.is_split && (!tx.from_account_name || !tx.to_account_name)">
                                    <span class="text-[10px] text-slate-400">—</span>
                                </template>
                            </td>

                            <td class="py-4 px-4 text-right whitespace-nowrap space-x-1.5">
                                 <template x-if="canEditTx(tx)">
                                    <div class="inline-flex items-center space-x-1.5">
                                        <button @click.prevent="openSplitModal(tx)" class="px-2.5 py-1.5 bg-amber-50 dark:bg-amber-900/30 hover:bg-amber-500 hover:text-white text-amber-600 dark:text-amber-400 rounded-lg font-extrabold text-xs transition cursor-pointer" title="Tách giao dịch này thành nhiều dự án/ví">Tách</button>
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
                                <div class="flex items-center space-x-1">
                                    <span class="px-1.5 py-0.5 bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300 font-mono font-extrabold text-[10px] rounded flex-shrink-0" x-text="'ID: ' + tx.id"></span>
                                    <p class="text-xs font-bold text-slate-900 dark:text-white truncate" x-text="tx.user_name"></p>
                                </div>
                                <p class="text-[10px] text-slate-400" x-text="tx.created_at_formatted"></p>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0 ml-2">
                            <template x-if="tx.type === 'contribution' || tx.type === 'repayment' || tx.type === 'adjustment'">
                                <p class="text-base font-black text-emerald-600 dark:text-emerald-400" x-text="'+' + new Intl.NumberFormat('vi-VN').format(tx.amount) + 'đ'"></p>
                            </template>
                            <template x-if="tx.type !== 'contribution' && tx.type !== 'repayment' && tx.type !== 'adjustment'">
                                <p class="text-base font-black text-slate-900 dark:text-slate-100" x-text="'-' + new Intl.NumberFormat('vi-VN').format(tx.amount) + 'đ'"></p>
                            </template>
                        </div>
                    </div>

                    <div class="flex items-start space-x-2 mb-3">
                        <template x-if="tx.type === 'contribution'">
                            <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 font-bold rounded text-[10px] flex-shrink-0">Góp quỹ</span>
                        </template>
                        <template x-if="tx.type === 'expense'">
                            <span class="px-2 py-0.5 bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 font-bold rounded text-[10px] flex-shrink-0">Chi tiêu chung</span>
                        </template>
                        <template x-if="tx.type === 'loan'">
                            <span class="px-2 py-0.5 bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300 font-bold rounded text-[10px] flex-shrink-0">Vay cá nhân</span>
                        </template>
                        <template x-if="tx.type === 'repayment'">
                            <span class="px-2 py-0.5 bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 font-bold rounded text-[10px] flex-shrink-0">Trả nợ</span>
                        </template>
                        <template x-if="tx.type === 'withdrawal' || tx.type === 'distribution'">
                            <span class="px-2 py-0.5 bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300 font-bold rounded text-[10px] flex-shrink-0">Rút lương</span>
                        </template>
                        <template x-if="tx.type === 'adjustment'">
                            <span class="px-2 py-0.5 bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-300 font-bold rounded text-[10px] flex-shrink-0">Điều chỉnh</span>
                        </template>
                        <template x-if="tx.is_fund_only">
                            <span class="px-2 py-0.5 bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 font-extrabold rounded text-[10px] flex-shrink-0 border border-amber-300/60 dark:border-amber-700/50">🏦 Vào Quỹ</span>
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
                            <template x-if="tx.is_split">
                                <div class="flex flex-col items-start gap-0.5">
                                    <span class="px-1.5 py-0.5 bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 font-extrabold rounded text-[9px]">Đã tách</span>
                                    <template x-for="(s, sIdx) in tx.splits" :key="sIdx">
                                        <span class="text-[9px] font-bold text-indigo-600 dark:text-indigo-400" x-text="new Intl.NumberFormat('vi-VN').format(s.amount) + 'đ → ' + s.to_account_name.replace('Dự án ', '').replace('Ví ', '')"></span>
                                    </template>
                                </div>
                            </template>
                            <template x-if="!tx.is_split && tx.from_account_name && tx.to_account_name">
                                <div class="flex items-center gap-1 text-[10px]">
                                    <span class="font-bold text-slate-500 dark:text-slate-400 truncate max-w-[80px]" x-text="tx.from_account_name.replace('Ví ', '')"></span>
                                    <span class="font-black text-indigo-500">→</span>
                                    <span class="font-bold text-indigo-600 dark:text-indigo-400 truncate max-w-[80px]" x-text="tx.to_account_name.replace('Ví ', '')"></span>
                                </div>
                            </template>
                        </div>
                        <div class="flex items-center space-x-2">
                            <template x-if="canEditTx(tx)">
                                <div class="flex items-center space-x-2">
                                    <button @click.prevent="openSplitModal(tx)" class="px-2.5 py-1.5 bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 rounded-lg text-xs font-extrabold active:scale-95 transition cursor-pointer">Tách</button>
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

    <!-- MODAL: THÊM GIAO DỊCH MỚI -->
    <div x-show="showAddModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/60 backdrop-blur-sm" x-cloak x-transition>
        <div @click.away="showAddModal = false" class="bg-white dark:bg-slate-800 rounded-t-3xl sm:rounded-3xl p-5 sm:p-6 w-full sm:max-w-md shadow-2xl border border-slate-100 dark:border-slate-700 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700 mb-4">
                <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <span>Thêm Giao Dịch Mới</span>
                </h3>
                <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-xl font-bold cursor-pointer">✕</button>
            </div>

            <!-- Type Switcher Tabs (Chi tiêu vs Thu nhập) -->
            <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 dark:bg-slate-700/60 rounded-2xl mb-4">
                <button type="button" @click="quickType = 'expense'" 
                        class="py-2 rounded-xl font-bold text-xs sm:text-sm transition-all duration-150 cursor-pointer"
                        :class="quickType === 'expense' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm border-b-2 border-slate-900 dark:border-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900'">
                    Chi tiêu
                </button>
                <button type="button" @click="quickType = 'contribution'" 
                        class="py-2 rounded-xl font-bold text-xs sm:text-sm transition-all duration-150 cursor-pointer"
                        :class="quickType !== 'expense' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm border-b-2 border-slate-900 dark:border-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900'">
                    Thu nhập
                </button>
            </div>

            <form action="{{ route('transactions.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4" x-data="{ 
                evidenceMode: 'none', 
                selectedFileName: '', 
                selectedFilePreview: '',
                triggerFilePick() {
                    this.evidenceMode = 'file';
                    this.$refs.evidenceFileInputModal.click();
                },
                onFileSelected(e) {
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
                clearFile() {
                    this.$refs.evidenceFileInputModal.value = '';
                    this.selectedFileName = '';
                    this.selectedFilePreview = '';
                    this.evidenceMode = 'none';
                }
            }">
                @csrf
                <!-- Automatically set user_id to logged-in user -->
                <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                <input type="hidden" name="type" :value="quickType">

                <!-- Date & Time Picker Row -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Thời gian giao dịch</label>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="col-span-2 flex items-center space-x-1.5 bg-slate-50 dark:bg-slate-700/60 border border-slate-200 dark:border-slate-600 rounded-xl px-2 py-1.5">
                            <button type="button" @click="prevQuickDay()" class="p-1 text-slate-400 hover:text-slate-900 dark:hover:text-white transition cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                            </button>
                            <div class="flex-1 text-center font-bold text-xs sm:text-sm text-slate-900 dark:text-white relative cursor-pointer" @click="$refs.nativeDatePickerModal.showPicker()">
                                <span x-text="formattedQuickDate"></span>
                                <input type="date" x-ref="nativeDatePickerModal" x-model="quickDate" class="absolute inset-0 opacity-0 cursor-pointer">
                            </div>
                            <button type="button" @click="nextQuickDay()" class="p-1 text-slate-400 hover:text-slate-900 dark:hover:text-white transition cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                        </div>
                        <div class="col-span-1 flex items-center bg-slate-50 dark:bg-slate-700/60 border border-slate-200 dark:border-slate-600 rounded-xl px-2 py-1.5" title="Chọn giờ & phút">
                            <input type="time" x-model="quickTime" class="w-full text-center text-xs font-extrabold bg-transparent text-slate-900 dark:text-white border-none outline-none focus:ring-0 p-0 cursor-pointer">
                        </div>
                    </div>
                    <input type="hidden" name="created_at" :value="quickDate && quickTime ? (quickDate + ' ' + quickTime + ':00') : quickDate">
                </div>

                <!-- Evidence Attachment Row -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Bằng chứng</label>
                    <div class="grid grid-cols-3 gap-1.5">
                        <button type="button" @click="triggerFilePick()" class="w-full py-2 rounded-xl text-xs font-extrabold transition-all flex items-center justify-center space-x-1 cursor-pointer active:scale-95 shadow-xs text-center" :class="evidenceMode === 'file' && selectedFileName ? 'bg-emerald-600 text-white ring-2 ring-emerald-500 shadow-sm' : 'bg-slate-50 dark:bg-slate-700/60 text-emerald-600 dark:text-emerald-400 border border-slate-200 dark:border-slate-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/30'">
                            <span>🖼️ Bill</span>
                        </button>
                        <button type="button" @click="evidenceMode = evidenceMode === 'link' ? 'none' : 'link'" class="w-full py-2 rounded-xl text-xs font-extrabold transition-all flex items-center justify-center space-x-1 cursor-pointer active:scale-95 shadow-xs text-center" :class="evidenceMode === 'link' ? 'bg-blue-600 text-white ring-2 ring-blue-500 shadow-sm' : 'bg-slate-50 dark:bg-slate-700/60 text-blue-600 dark:text-blue-400 border border-slate-200 dark:border-slate-600 hover:bg-blue-50 dark:hover:bg-blue-900/30'">
                            <span>🔗 Link</span>
                        </button>
                        <button type="button" @click="evidenceMode = evidenceMode === 'text' ? 'none' : 'text'" class="w-full py-2 rounded-xl text-xs font-extrabold transition-all flex items-center justify-center space-x-1 cursor-pointer active:scale-95 shadow-xs text-center" :class="evidenceMode === 'text' ? 'bg-amber-600 text-white ring-2 ring-amber-500 shadow-sm' : 'bg-slate-50 dark:bg-slate-700/60 text-amber-600 dark:text-amber-400 border border-slate-200 dark:border-slate-600 hover:bg-amber-50 dark:hover:bg-amber-900/30'">
                            <span>📝 Momo</span>
                        </button>
                    </div>

                    <input type="file" x-ref="evidenceFileInputModal" name="evidence_file" accept="image/*,.pdf" class="hidden" @change="onFileSelected($event)">
                    <input type="hidden" name="evidence_type" :value="evidenceMode">

                    <div x-show="evidenceMode === 'file' && selectedFileName" class="p-2.5 bg-emerald-50/60 dark:bg-emerald-900/20 rounded-2xl border border-emerald-200/80 dark:border-emerald-700/50 flex items-center justify-between transition mt-2" x-cloak>
                        <div class="flex items-center space-x-2.5 min-w-0">
                            <template x-if="selectedFilePreview">
                                <img :src="selectedFilePreview" class="w-9 h-9 object-cover rounded-lg border border-emerald-300 dark:border-emerald-600 shadow-sm flex-shrink-0">
                            </template>
                            <template x-if="!selectedFilePreview">
                                <span class="w-9 h-9 rounded-lg bg-emerald-100 dark:bg-emerald-800 text-emerald-700 dark:text-emerald-200 font-bold text-xs flex items-center justify-center flex-shrink-0">📄</span>
                            </template>
                            <span class="text-xs font-extrabold text-slate-800 dark:text-slate-200 truncate" x-text="selectedFileName"></span>
                        </div>
                        <button type="button" @click="clearFile()" title="Xóa ảnh đã chọn" class="p-1 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/30 rounded-lg transition font-black text-sm cursor-pointer ml-2 flex-shrink-0">✕</button>
                    </div>

                    <div x-show="evidenceMode === 'link'" x-cloak class="mt-1.5">
                        <input type="url" name="evidence_link" placeholder="https://momo.vn/transaction/..." class="w-full text-xs px-3 py-2 border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700/60 text-slate-900 dark:text-white">
                    </div>
                    <div x-show="evidenceMode === 'text'" x-cloak class="mt-1.5">
                        <textarea name="evidence_text" rows="2" placeholder="Dán thông tin sao kê trích xuất từ Momo..." class="w-full text-xs px-3 py-2 border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700/60 text-slate-900 dark:text-white"></textarea>
                    </div>
                </div>

                <!-- Note Input Row -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Ghi chú nội dung</label>
                    <input type="text" name="description" x-model="quickNote" placeholder="Thêm ghi chú" required
                           class="w-full bg-slate-50 dark:bg-slate-700/60 border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-2 text-xs sm:text-sm font-medium text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>

                <!-- Amount Input Row -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1" x-text="quickType === 'expense' ? 'Số tiền chi' : 'Số tiền thu'"></label>
                    <div class="relative">
                        <input type="number" name="amount" x-model="quickAmount" placeholder="0" required min="1" step="any"
                               class="w-full bg-slate-50 dark:bg-slate-700/60 border border-slate-200 dark:border-slate-600 rounded-xl pl-3 pr-8 py-2 text-base font-black text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none">
                        <span class="absolute right-3 top-2.5 text-xs font-bold text-slate-400">đ</span>
                    </div>
                </div>

                <!-- On Behalf Of Member Row (Only visible for Income) -->
                <div x-show="quickType !== 'expense'" x-cloak>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        <span>Nộp thay cho thành viên</span>
                        <span class="text-[10px] text-slate-400 font-normal">(tùy chọn)</span>
                    </label>
                    <select name="responsible_user_id" class="w-full bg-slate-50 dark:bg-slate-700/60 border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-2 text-xs sm:text-sm font-bold text-slate-900 dark:text-white cursor-pointer">
                        <option value="">-- Chính mình (Không nộp hộ) --</option>
                        @foreach($members->where('role', '!=', 'admin') as $m)
                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Fund Only Toggle -->
                <div class="p-3 bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-700/50 rounded-2xl transition hover:border-amber-400/80">
                    <label class="flex items-start space-x-3 cursor-pointer">
                        <input type="checkbox" name="is_fund_only" value="1" class="mt-0.5 w-4 h-4 rounded text-amber-600 focus:ring-amber-500 border-amber-300 dark:border-amber-600 cursor-pointer">
                        <div class="text-xs">
                            <span class="font-extrabold text-slate-900 dark:text-white flex items-center gap-1.5">
                                <span>Tính vào quỹ</span>
                                <span class="text-[10px] font-bold text-amber-700 dark:text-amber-300 bg-amber-200/60 dark:bg-amber-900/50 px-1.5 py-0.5 rounded">(không tính vào Net & Gross)</span>
                            </span>
                        </div>
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" 
                            class="w-full py-3 text-white font-extrabold text-xs sm:text-sm rounded-2xl shadow-lg transition-all duration-200 active:scale-98 cursor-pointer flex items-center justify-center space-x-2"
                            :class="quickType === 'expense' ? 'bg-gradient-to-r from-rose-600 to-pink-600 hover:from-rose-700 hover:to-pink-700 shadow-rose-500/25' : 'bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-emerald-500/25'">
                        <span>Thêm giao dịch</span>
                    </button>
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

                    <div x-show="selectedTx.type !== 'expense'" x-cloak>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            <span>Nộp thay cho thành viên</span>
                            <span class="text-[10px] text-slate-400 font-normal">(tùy chọn)</span>
                        </label>
                        <select name="responsible_user_id" x-model="selectedTx.responsible_user_id" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-medium">
                            <option value="">-- Chính mình (Không nộp hộ) --</option>
                            @foreach($members->where('role', '!=', 'admin') as $m)
                                <option value="{{ $m->id }}">{{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Loại Giao Dịch</label>
                        <select name="type" x-model="selectedTx.type" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-bold">
                            <option value="expense">Chi tiêu (-)</option>
                            <option value="contribution">Thu nhập (+)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Số tiền (VNĐ)</label>
                        <input type="number" name="amount" x-model="selectedTx.amount" required step="any" min="1" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-bold">
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

                    <!-- Fund Only Toggle in Edit Modal -->
                    <div class="p-3 bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-700/50 rounded-2xl">
                        <label class="flex items-start space-x-3 cursor-pointer">
                            <input type="checkbox" name="is_fund_only" value="1" x-model="selectedTx.is_fund_only" class="mt-0.5 w-4 h-4 rounded text-amber-600 focus:ring-amber-500 border-amber-300 dark:border-amber-600 cursor-pointer">
                            <div class="text-xs">
                                <span class="font-extrabold text-slate-900 dark:text-white flex items-center gap-1.5">
                                    <span>Tính vào quỹ</span>
                                    <span class="text-[10px] font-bold text-amber-700 dark:text-amber-300 bg-amber-200/60 dark:bg-amber-900/50 px-1.5 py-0.5 rounded">(không tính vào Net & Gross)</span>
                                </span>
                            </div>
                        </label>
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

    <!-- SPLIT TRANSACTION MODAL -->
    <div x-show="showSplitModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 backdrop-blur-sm p-4" x-cloak x-transition>
        <div @click.away="showSplitModal = false" class="bg-white dark:bg-slate-800 rounded-3xl p-6 w-full max-w-2xl shadow-2xl border border-slate-100 dark:border-slate-700 max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-700">
                <div>
                    <h3 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <span>Tách Giao Dịch #<span x-text="selectedTx?.id"></span></span>
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5" x-text="'Giao dịch gốc: ' + (selectedTx ? new Intl.NumberFormat('vi-VN').format(selectedTx.amount) + 'đ (' + selectedTx.description + ')' : '')"></p>
                </div>
                <button @click="showSplitModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-xl font-bold cursor-pointer">✕</button>
            </div>

            <form x-bind:action="'/transactions/' + (selectedTx ? selectedTx.id : '') + '/split'" method="POST" class="mt-4 flex-1 flex flex-col min-h-0 space-y-4">
                @csrf
                <div class="overflow-y-auto space-y-3 pr-1 flex-1">
                    <template x-for="(row, idx) in splitRows" :key="idx">
                        <div class="p-3.5 bg-slate-50 dark:bg-slate-700/40 rounded-2xl border border-slate-200/80 dark:border-slate-600/60 flex items-center gap-3">
                            <div class="flex-1">
                                <label class="block text-[10px] font-extrabold uppercase text-slate-400 mb-1" x-text="'Dòng ' + (idx + 1) + ': Dự án nhận'"></label>
                                <select :name="'splits[' + idx + '][to_account_id]'" x-model="row.to_account_id" required class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-2 text-xs font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                                    <option value="">-- Chọn Dự án --</option>
                                    @foreach($accounts->where('type', 'project') as $acc)
                                        <option value="{{ $acc->id }}">{{ str_replace('Dự án ', '', $acc->name) }}</option>
                                    @endforeach
                                    @foreach($accounts->where('type', 'fund') as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->name }} (Quỹ chung)</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="w-44">
                                <label class="block text-[10px] font-extrabold uppercase text-slate-400 mb-1">Số tiền (VNĐ)</label>
                                <div class="relative">
                                    <input type="text"
                                           :value="row.displayAmount"
                                           @input="let clean = $event.target.value.replace(/\D/g, ''); let num = parseInt(clean, 10) || 0; row.amount = num; row.displayAmount = num > 0 ? num.toLocaleString('vi-VN') : '';"
                                           placeholder="0"
                                           required
                                           class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-2 pr-7 text-xs font-black text-emerald-600 dark:text-emerald-400 focus:ring-2 focus:ring-emerald-500">
                                    <input type="hidden" :name="'splits[' + idx + '][amount]'" :value="row.amount">
                                    <span class="absolute right-2.5 top-2 text-xs font-bold text-slate-400 pointer-events-none">đ</span>
                                </div>
                            </div>

                            <div class="flex-1">
                                <label class="block text-[10px] font-extrabold uppercase text-slate-400 mb-1">Diễn giải / Ghi chú</label>
                                <input type="text" :name="'splits[' + idx + '][memo]'" x-model="row.memo" placeholder="Vd: Phân bổ cho EVB" class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white">
                            </div>

                            <template x-if="splitRows.length > 2">
                                <button type="button" @click="removeSplitRow(idx)" class="mt-4 p-2 text-rose-500 hover:bg-rose-50 rounded-xl transition text-xs font-bold cursor-pointer" title="Xóa dòng này">✕</button>
                            </template>
                        </div>
                    </template>

                    <button type="button" @click="addSplitRow()" class="w-full py-2.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-700/50 hover:bg-indigo-100 rounded-2xl text-xs font-extrabold transition cursor-pointer">
                        + Thêm dòng phân bổ mới
                    </button>
                </div>

                <!-- Validation Footer -->
                <div class="pt-3 border-t border-slate-100 dark:border-slate-700 flex items-center justify-between">
                    <div>
                        <span class="text-xs text-slate-500">Tổng tiền đã tách: </span>
                        <span class="text-sm font-black" :class="selectedTx && Math.abs(splitTotal - selectedTx.amount) < 1 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'" x-text="new Intl.NumberFormat('vi-VN').format(splitTotal) + 'đ'"></span>
                        <span class="text-xs text-slate-400" x-text="' / ' + (selectedTx ? new Intl.NumberFormat('vi-VN').format(selectedTx.amount) + 'đ' : '')"></span>
                    </div>

                    <div class="flex items-center space-x-2">
                        <button type="button" @click="showSplitModal = false" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold cursor-pointer">Hủy</button>
                        <button type="submit" :disabled="selectedTx && Math.abs(splitTotal - selectedTx.amount) >= 1" class="px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl text-xs font-bold disabled:opacity-50 transition cursor-pointer">Xác Nhận Tách</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>

@endsection
