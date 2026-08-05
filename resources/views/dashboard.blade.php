@extends('layouts.app')

@section('content')
<div x-data="{ 
    mobileTab: 'entry',
    quickType: 'expense',
    quickDate: '{{ date('Y-m-d') }}',
    quickUserId: '{{ $members->first()->id ?? 1 }}',
    quickAmount: '',
    quickNote: 'Ăn uống',
    selectedCategory: 'eat',
    showAddModal: false,
    showEditModal: false,
    showDeleteModal: false,
    showDistributionModal: false,
    showMemberModal: false,
    selectedTx: null,
    distributeAmount: {{ $fund->balance }},
    rawTransactions: {{ \Illuminate\Support\Js::from($allTransactions) }},

    // History Filters & Pagination State
    txSearchText: '',
    txFilterType: 'all',
    txFilterUserId: 'all',
    txFilterDate: '',
    txPage: 1,
    txPerPage: 10,

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
    },

    expenseCategories: [
        { key: 'eat', name: 'Ăn uống', fullName: 'Ăn uống', icon: '/icons/EatAndDrink.svg', color: 'bg-amber-500' },
        { key: 'daily', name: 'Chi hàng ngày', fullName: 'Chi tiêu hàng ngày', icon: '/icons/DailyExpenses.svg', color: 'bg-emerald-500' },
        { key: 'clothes', name: 'Quần áo', fullName: 'Quần áo', icon: '/icons/Clothes.svg', color: 'bg-blue-500' },
        { key: 'cosmetics', name: 'Mỹ phẩm', fullName: 'Mỹ phẩm', icon: '/icons/Cosmetics.svg', color: 'bg-pink-500' },
        { key: 'exchange', name: 'Phí giao lưu', fullName: 'Phí giao lưu', icon: '/icons/Exchange.svg', color: 'bg-purple-500' },
        { key: 'medical', name: 'Y tế', fullName: 'Y tế', icon: '/icons/Medical.svg', color: 'bg-teal-500' },
        { key: 'education', name: 'Giáo dục', fullName: 'Giáo dục', icon: '/icons/Education.svg', color: 'bg-indigo-500' },
        { key: 'electric', name: 'Tiền điện', fullName: 'Tiền điện', icon: '/icons/Electric.svg', color: 'bg-yellow-500' },
        { key: 'transport', name: 'Đi lại', fullName: 'Đi lại', icon: '/icons/Transport.svg', color: 'bg-orange-500' },
        { key: 'contact', name: 'Phí liên lạc', fullName: 'Phí liên lạc', icon: '/icons/Contact.svg', color: 'bg-cyan-500' },
        { key: 'house', name: 'Tiền nhà', fullName: 'Tiền nhà', icon: '/icons/HouseRent.svg', color: 'bg-rose-500' }
    ],
    incomeCategories: [
        { key: 'salary', name: 'Tiền lương', fullName: 'Tiền lương', icon: '/icons/Salary.svg', color: 'bg-emerald-500' },
        { key: 'bonus', name: 'Tiền thưởng', fullName: 'Tiền thưởng', icon: '/icons/Bonus.svg', color: 'bg-amber-500' },
        { key: 'invest', name: 'Đầu tư', fullName: 'Lợi nhuận đầu tư', icon: '/icons/Invest.svg', color: 'bg-blue-500' },
        { key: 'other_income', name: 'Thu khác', fullName: 'Góp quỹ / Thu khác', icon: '/icons/Exchange.svg', color: 'bg-purple-500' }
    ],
    get activeCategories() {
        return this.quickType === 'expense' ? this.expenseCategories : this.incomeCategories;
    },
    switchQuickType(type) {
        this.quickType = type;
        if (type === 'expense') {
            this.selectCategory(this.expenseCategories[0]);
        } else {
            this.selectCategory(this.incomeCategories[0]);
        }
    },
    get formattedQuickDate() {
        let parts = this.quickDate.split('-');
        if (parts.length !== 3) return this.quickDate;
        let d = new Date(parts[0], parts[1] - 1, parts[2]);
        let day = String(d.getDate()).padStart(2, '0');
        let month = String(d.getMonth() + 1).padStart(2, '0');
        let year = d.getFullYear();
        let dayNames = ['CN', 'Th 2', 'Th 3', 'Th 4', 'Th 5', 'Th 6', 'Th 7'];
        return `${day}/${month}/${year} (${dayNames[d.getDay()]})`;
    },
    prevQuickDay() {
        let parts = this.quickDate.split('-');
        let d = new Date(parts[0], parts[1] - 1, parts[2]);
        d.setDate(d.getDate() - 1);
        let year = d.getFullYear();
        let month = String(d.getMonth() + 1).padStart(2, '0');
        let day = String(d.getDate()).padStart(2, '0');
        this.quickDate = `${year}-${month}-${day}`;
    },
    nextQuickDay() {
        let parts = this.quickDate.split('-');
        let d = new Date(parts[0], parts[1] - 1, parts[2]);
        d.setDate(d.getDate() + 1);
        let year = d.getFullYear();
        let month = String(d.getMonth() + 1).padStart(2, '0');
        let day = String(d.getDate()).padStart(2, '0');
        this.quickDate = `${year}-${month}-${day}`;
    },
    selectCategory(cat) {
        this.selectedCategory = cat.key;
        if (cat.fullName) {
            this.quickNote = cat.fullName;
        }
    },
    get calculatedPayouts() {
        let amount = parseFloat(this.distributeAmount) || 0;
        return [
            @foreach($members as $m)
            { id: {{ $m->id }}, name: '{{ $m->name }}', avatar: '{{ $m->avatar }}', share: {{ $m->share_percentage }}, amount: Math.round(amount * {{ $m->share_percentage }} / 100) },
            @endforeach
        ];
    }
}"
class="pb-20 lg:pb-6">

    <!-- Mobile View Switcher Tabs (hidden on desktop) -->
    <div class="lg:hidden flex items-center space-x-1 p-1 bg-slate-200 dark:bg-slate-700/60 rounded-2xl mb-4">
        <button @click="mobileTab = 'entry'" class="flex-1 py-2 text-xs font-bold rounded-xl transition" :class="mobileTab === 'entry' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 dark:text-slate-400'">Nhập vào</button>
        <button @click="mobileTab = 'stats'" class="flex-1 py-2 text-xs font-bold rounded-xl transition" :class="mobileTab === 'stats' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 dark:text-slate-400'">Thống kê</button>
        <a href="{{ route('report') }}" class="flex-1 py-2 text-xs font-bold rounded-xl transition text-center text-slate-500 dark:text-slate-400">Báo cáo</a>
    </div>

    <!-- Main Container: Desktop Multi-Column Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- CỘT TRÁI (LEFT PANEL): FORM SỔ THU CHI -->
        <div class="lg:col-span-5 xl:col-span-4" :class="mobileTab === 'entry' ? 'block' : 'hidden lg:block'">
            <div class="bg-white dark:bg-slate-800 rounded-3xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700 shadow-md sticky top-20">
                <!-- Header -->
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-700">
                    <div class="flex items-center space-x-2">
                        <h2 class="text-lg sm:text-xl font-black text-slate-900 dark:text-white">Sổ thu chi</h2>
                    </div>
                    <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/40 px-2.5 py-1 rounded-full">Quỹ: {{ number_format($fund->balance, 0, ',', '.') }}đ</span>
                </div>

                <!-- Type Tabs: Chi tiêu vs Thu nhập -->
                <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 dark:bg-slate-700/60 rounded-2xl mb-4">
                    <button type="button" @click="switchQuickType('expense')" 
                            class="py-2 rounded-xl font-bold text-xs sm:text-sm transition-all duration-150 cursor-pointer"
                            :class="quickType === 'expense' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm border-b-2 border-slate-900 dark:border-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900'">
                        Chi tiêu
                    </button>
                    <button type="button" @click="switchQuickType('contribution')" 
                            class="py-2 rounded-xl font-bold text-xs sm:text-sm transition-all duration-150 cursor-pointer"
                            :class="quickType !== 'expense' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm border-b-2 border-slate-900 dark:border-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900'">
                        Thu nhập
                    </button>
                </div>

                <!-- Form Submit -->
                <form action="{{ route('transactions.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3.5" x-data="{ 
                    evidenceMode: 'none', 
                    selectedFileName: '', 
                    selectedFilePreview: '',
                    triggerFilePick() {
                        this.evidenceMode = 'file';
                        this.$refs.evidenceFileInput.click();
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
                        this.$refs.evidenceFileInput.value = '';
                        this.selectedFileName = '';
                        this.selectedFilePreview = '';
                        this.evidenceMode = 'none';
                    }
                }">
                    @csrf
                    <!-- Member Selector -->
                    <div class="flex items-center justify-between text-xs sm:text-sm font-semibold">
                        <span class="text-slate-500 dark:text-slate-400 w-20 flex-shrink-0">Thành viên</span>
                        @if(auth()->user()?->isAdmin())
                            <select name="user_id" x-model="quickUserId" class="flex-1 bg-slate-50 dark:bg-slate-700/60 border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-2 text-xs sm:text-sm font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none">
                                @foreach($members->where('role', '!=', 'admin') as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }}</option>
                                @endforeach
                            </select>
                        @else
                            <div class="flex-1 px-3 py-2 bg-slate-100 dark:bg-slate-700/60 border border-slate-200 dark:border-slate-600 rounded-xl text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-200 flex items-center justify-between">
                                <span>{{ auth()->user()->name }}</span>
                                <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                            </div>
                        @endif
                    </div>

                    <!-- Transaction Type Selector -->
                    <div class="flex items-center justify-between text-xs sm:text-sm font-semibold">
                        <span class="text-slate-500 dark:text-slate-400 w-20 flex-shrink-0">Loại GD</span>
                        <select name="type" x-model="quickType" class="flex-1 bg-slate-50 dark:bg-slate-700/60 border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-2 text-xs sm:text-sm font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none">
                            <option value="expense">Chi tiêu chung</option>
                            <option value="contribution">Góp quỹ</option>
                            <option value="loan">Vay cá nhân</option>
                            <option value="repayment">Trả nợ</option>
                            <option value="withdrawal">Rút lương</option>
                        </select>
                    </div>



                    <!-- Date Picker Row with < and > controls -->
                    <div class="flex items-center justify-between text-xs sm:text-sm font-semibold">
                        <span class="text-slate-500 dark:text-slate-400 w-20 flex-shrink-0">Ngày</span>
                        <div class="flex-1 flex items-center space-x-1.5 bg-slate-50 dark:bg-slate-700/60 border border-slate-200 dark:border-slate-600 rounded-xl px-2 py-1.5">
                            <button type="button" @click="prevQuickDay()" class="p-1 text-slate-400 hover:text-slate-900 dark:hover:text-white transition cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                            </button>
                            <div class="flex-1 text-center font-bold text-xs sm:text-sm text-slate-900 dark:text-white relative cursor-pointer" @click="$refs.nativeDatePicker.showPicker()">
                                <span x-text="formattedQuickDate"></span>
                                <input type="date" x-ref="nativeDatePicker" name="created_at" x-model="quickDate" class="absolute inset-0 opacity-0 cursor-pointer">
                            </div>
                            <button type="button" @click="nextQuickDay()" class="p-1 text-slate-400 hover:text-slate-900 dark:hover:text-white transition cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                        </div>
                    </div>



                    <!-- Evidence Attachment Row -->
                    <div class="flex items-center justify-between text-xs sm:text-sm font-semibold">
                        <span class="text-slate-500 dark:text-slate-400 w-20 flex-shrink-0">Bằng chứng</span>
                        <div class="flex-1 grid grid-cols-3 gap-1.5">
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
                    </div>

                        <!-- Hidden File Input triggered directly by 'Ảnh bill' button -->
                        <input type="file" x-ref="evidenceFileInput" name="evidence_file" accept="image/*,.pdf" class="hidden" @change="onFileSelected($event)">
                        <input type="hidden" name="evidence_type" :value="evidenceMode">

                        <!-- Selected File Preview Container with ✕ Delete Button -->
                        <div x-show="evidenceMode === 'file' && selectedFileName" class="p-2.5 bg-emerald-50/60 dark:bg-emerald-900/20 rounded-2xl border border-emerald-200/80 dark:border-emerald-700/50 flex items-center justify-between transition" x-cloak>
                            <div class="flex items-center space-x-2.5 min-w-0">
                                <template x-if="selectedFilePreview">
                                    <img :src="selectedFilePreview" class="w-9 h-9 object-cover rounded-lg border border-emerald-300 dark:border-emerald-600 shadow-sm flex-shrink-0">
                                </template>
                                <template x-if="!selectedFilePreview">
                                    <span class="w-9 h-9 rounded-lg bg-emerald-100 dark:bg-emerald-800 text-emerald-700 dark:text-emerald-200 font-bold text-xs flex items-center justify-center flex-shrink-0">📄</span>
                                </template>
                                <span class="text-xs font-extrabold text-slate-800 dark:text-slate-200 truncate" x-text="selectedFileName"></span>
                            </div>
                            <button type="button" @click="clearFile()" title="Xóa ảnh đã chọn" class="p-1 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/30 rounded-lg transition font-black text-sm cursor-pointer ml-2 flex-shrink-0">
                                ✕
                            </button>
                        </div>

                        <div x-show="evidenceMode === 'link'" x-cloak class="mt-1.5">
                            <input type="url" name="evidence_link" placeholder="https://momo.vn/transaction/..." class="w-full text-xs px-3 py-2 border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700/60 text-slate-900 dark:text-white">
                        </div>
                        <div x-show="evidenceMode === 'text'" x-cloak class="mt-1.5">
                            <textarea name="evidence_text" rows="2" placeholder="Dán thông tin sao kê trích xuất từ Momo..." class="w-full text-xs px-3 py-2 border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700/60 text-slate-900 dark:text-white"></textarea>
                        </div>

                    <!-- Note Input Row -->
                    <div class="flex items-center justify-between text-xs sm:text-sm font-semibold">
                        <span class="text-slate-500 dark:text-slate-400 w-20 flex-shrink-0">Ghi chú</span>
                        <input type="text" name="description" x-model="quickNote" placeholder="Thêm ghi chú" required
                               class="flex-1 bg-slate-50 dark:bg-slate-700/60 border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-2 text-xs sm:text-sm font-medium text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>

                    <!-- Amount Input Row -->
                    <div class="flex items-center justify-between text-xs sm:text-sm font-semibold">
                        <span class="text-slate-500 dark:text-slate-400 w-20 flex-shrink-0" x-text="quickType === 'expense' ? 'Tiền chi' : 'Tiền thu'"></span>
                        <div class="flex-1 relative">
                            <input type="number" name="amount" x-model="quickAmount" placeholder="0" required min="1000" step="1000"
                                   class="w-full bg-slate-50 dark:bg-slate-700/60 border border-slate-200 dark:border-slate-600 rounded-xl pl-3 pr-8 py-2 text-base font-black text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none">
                            <span class="absolute right-3 top-2.5 text-xs font-bold text-slate-400">đ</span>
                        </div>
                    </div>

                    <!-- Category Title -->
                    <div class="pt-2">
                        <p class="text-xs font-bold text-slate-500 dark:text-slate-400 mb-2">Danh mục</p>
                        
                        <!-- Category Grid -->
                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                            <template x-for="cat in activeCategories" :key="cat.key">
                                <button type="button" @click="selectCategory(cat)"
                                        class="flex flex-col items-center justify-center p-2 rounded-2xl border transition-all duration-150 cursor-pointer min-h-[72px]"
                                        :class="selectedCategory === cat.key ? 'border-2 border-emerald-500 dark:border-emerald-400 bg-emerald-500/10 shadow-sm scale-95' : 'border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-700/30 hover:border-slate-300 dark:hover:border-slate-600'">
                                    <div class="w-6 h-6 mb-1.5 rounded-md transition-colors"
                                         :class="cat.color"
                                         :style="`-webkit-mask: url('${cat.icon}') center/contain no-repeat; mask: url('${cat.icon}') center/contain no-repeat;`">
                                    </div>
                                    <span class="text-[10px] font-bold text-slate-800 dark:text-slate-200 text-center leading-tight line-clamp-2 px-0.5" x-text="cat.name"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button type="submit" 
                                class="w-full py-3 text-white font-extrabold text-xs sm:text-sm rounded-2xl shadow-lg transition-all duration-200 active:scale-98 cursor-pointer flex items-center justify-center space-x-2"
                                :class="quickType === 'expense' ? 'bg-gradient-to-r from-rose-600 to-pink-600 hover:from-rose-700 hover:to-pink-700 shadow-rose-500/25' : 'bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-emerald-500/25'">
                            <span x-text="quickType === 'expense' ? 'Thêm' : 'Thêm'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- CỘT PHẢI (RIGHT PANEL): DASHBOARD OVERVIEW, CHARTS & MEMBER STATS -->
        <div class="lg:col-span-7 xl:col-span-8 space-y-6" :class="mobileTab !== 'entry' ? 'block' : 'hidden lg:block'">

            <!-- 1. Top Stat Cards Row -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4" :class="mobileTab === 'stats' || mobileTab === 'all' ? 'block' : 'hidden lg:grid'">
                <!-- Card 1: Số Dư Quỹ -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm relative overflow-hidden hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider">Số Dư Quỹ</span>
                    </div>
                    <p class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white mt-1.5">
                        {{ number_format($fund->balance, 0, ',', '.') }}<span class="text-base sm:text-lg font-bold">đ</span>
                    </p>
                </div>

                <!-- Card 2: Túi Thần Tài (Tích Lũy) -->
                <div class="bg-gradient-to-br from-amber-500/10 via-amber-500/5 to-transparent dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-amber-300 dark:border-amber-700/60 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] sm:text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Túi Thần Tài</span>
                        <span class="px-1.5 py-0.5 text-[9px] font-black bg-amber-500/20 text-amber-600 dark:text-amber-400 rounded-full">Tích Lũy</span>
                    </div>
                    <p class="text-lg sm:text-2xl font-extrabold text-amber-600 dark:text-amber-400 mt-1.5">
                        +{{ number_format($fund->total_profit, 0, ',', '.') }}<span class="text-sm sm:text-lg font-bold">đ</span>
                    </p>
                </div>

                <!-- Card 3: Tổng Thu -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider">Tổng Thu</span>
                    </div>
                    <p class="text-lg sm:text-2xl font-extrabold text-blue-600 dark:text-blue-400 mt-1.5">
                        +{{ number_format($totalIncome, 0, ',', '.') }}<span class="text-sm sm:text-lg font-bold">đ</span>
                    </p>
                </div>

                <!-- Card 4: Tổng Chi -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider">Tổng Chi</span>
                    </div>
                    <p class="text-lg sm:text-2xl font-extrabold text-rose-600 dark:text-rose-400 mt-1.5">
                        -{{ number_format($totalExpense, 0, ',', '.') }}<span class="text-sm sm:text-lg font-bold">đ</span>
                    </p>
                </div>

                <!-- Card 5: Tổng Cho Vay -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider">Đang Cho Vay</span>
                    </div>
                    <p class="text-lg sm:text-2xl font-extrabold text-purple-600 dark:text-purple-400 mt-1.5">
                        {{ number_format($totalLoans, 0, ',', '.') }}<span class="text-sm sm:text-lg font-bold">đ</span>
                    </p>
                </div>
            </div>

            <!-- ApexCharts Section -->
            <div class="grid grid-cols-1 gap-4 sm:gap-5"
                 :class="mobileTab === 'stats' || mobileTab === 'all' ? 'block' : 'hidden lg:block'"
            >
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm">
                    <div class="flex items-center space-x-3 mb-3 pb-2.5 border-b border-slate-100 dark:border-slate-700">
                        <div class="p-2 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-slate-900 dark:text-white text-sm sm:text-base">Tỷ Lệ Dòng Tiền</h3>
                            <p class="text-[10px] text-slate-400 font-medium">Tổng Quan Thu / Chi / Cho Vay</p>
                        </div>
                    </div>
                    <!-- Custom SVG Donut Chart with Leader/Connector Lines -->
                    @php
                        $donutItems = [];
                        $donutTotal = max(1, $totalIncome + $totalExpense + $totalLoans);
                        $rawDonut = [
                            ['label' => 'Tổng Thu', 'value' => $totalIncome, 'color' => '#10b981'],
                            ['label' => 'Tổng Chi', 'value' => $totalExpense, 'color' => '#f43f5e'],
                            ['label' => 'Đang Vay', 'value' => $totalLoans, 'color' => '#f59e0b'],
                        ];
                        $cx = 220; $cy = 150; $outerR = 92; $innerR = 60;
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
                    <svg viewBox="0 0 440 300" class="w-full max-w-[480px] mx-auto" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                        {{-- Donut Slices --}}
                        @foreach($donutItems as $sl)
                            <path d="{{ $sl['d'] }}" fill="{{ $sl['color'] }}" class="transition-opacity duration-200 hover:opacity-80" />
                        @endforeach

                        {{-- Inner Hole --}}
                        <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $innerR }}" class="fill-white dark:fill-slate-800" />

                        {{-- Center Label --}}
                        <text x="{{ $cx }}" y="{{ $cy - 10 }}" text-anchor="middle" font-size="9" font-weight="700" class="fill-slate-400 uppercase">Tổng Thu</text>
                        <text x="{{ $cx }}" y="{{ $cy + 10 }}" text-anchor="middle" font-size="15" font-weight="800" class="fill-slate-900 dark:fill-white">{{ number_format($fund->balance, 0, ',', '.') }}đ</text>

                        {{-- Connector Lines + Labels --}}
                        @foreach($donutItems as $sl)
                            <polyline points="{{ $sl['lsx'] }},{{ $sl['lsy'] }} {{ $sl['lex'] }},{{ $sl['ley'] }} {{ $sl['lhx'] }},{{ $sl['lhy'] }}" fill="none" stroke="{{ $sl['color'] }}" stroke-width="2" />
                            <circle cx="{{ $sl['lhx'] }}" cy="{{ $sl['lhy'] }}" r="3" fill="{{ $sl['color'] }}" />
                            <text x="{{ $sl['lhx'] + $sl['txOff'] }}" y="{{ $sl['lhy'] - 3 }}" text-anchor="{{ $sl['anchor'] }}" font-size="13" font-weight="800" class="fill-slate-900 dark:fill-white">{{ $sl['pct'] }}%</text>
                            <text x="{{ $sl['lhx'] + $sl['txOff'] }}" y="{{ $sl['lhy'] + 12 }}" text-anchor="{{ $sl['anchor'] }}" font-size="11" font-weight="700" fill="{{ $sl['color'] }}">{{ $sl['label'] }}</text>
                        @endforeach
                    </svg>

                    {{-- Legend --}}
                    <div class="flex items-center justify-center flex-wrap gap-x-5 gap-y-1.5 mt-3 pt-2 border-t border-slate-100 dark:border-slate-700/60">
                        @foreach($rawDonut as $rd)
                            @if($rd['value'] > 0)
                            <div class="flex items-center space-x-1.5">
                                <span class="w-3 h-3 rounded-full" style="background: {{ $rd['color'] }}"></span>
                                <span class="text-xs font-extrabold text-slate-700 dark:text-slate-300">{{ $rd['label'] }}</span>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Admin Pending Requests Alert Bar (Admin Only) -->
            @if(auth()->user()?->isAdmin() && $pendingTransactions->count() > 0)
                <div class="bg-amber-500/10 border-2 border-amber-500/30 rounded-2xl p-3 sm:p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center space-x-2">
                            <span class="flex h-3 w-3 relative">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                            </span>
                            <h3 class="font-extrabold text-amber-800 dark:text-amber-300 text-xs sm:text-sm">
                                Yêu Cầu Chờ Duyệt ({{ $pendingTransactions->count() }})
                            </h3>
                        </div>
                    </div>

                    <div class="space-y-2">
                        @foreach($pendingTransactions as $pending)
                            <div class="bg-white dark:bg-slate-800 p-3 rounded-xl border border-amber-200 dark:border-amber-800/40 flex items-center justify-between text-xs">
                                <div class="flex items-center space-x-2.5 min-w-0">
                                    <div class="w-7 h-7 rounded-full bg-amber-100 dark:bg-amber-900/50 text-amber-800 dark:text-amber-200 font-bold text-[10px] flex items-center justify-center flex-shrink-0 overflow-hidden">
                                        @if($pending->user->avatar && \Illuminate\Support\Str::startsWith($pending->user->avatar, ['http://', 'https://', '/uploads/']))
                                            <img src="{{ $pending->user->avatar }}" alt="{{ $pending->user->name }}" class="w-full h-full object-cover">
                                        @else
                                            {{ $pending->user->avatar ?? substr($pending->user->name, 0, 2) }}
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-slate-900 dark:text-white truncate">
                                            {{ $pending->user->name }}
                                            <span class="text-[10px] text-slate-400 font-normal">({{ $pending->type === 'expense' ? 'Chi' : 'Vay' }})</span>
                                        </p>
                                        <p class="text-[11px] text-slate-500 truncate">"{{ $pending->description }}"</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2 flex-shrink-0 ml-2">
                                    <span class="font-extrabold text-sm text-rose-600 ml-1">-{{ number_format($pending->amount, 0, ',', '.') }}đ</span>
                                    <form action="{{ route('transactions.approve', $pending) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-[10px] shadow transition cursor-pointer">Duyệt</button>
                                    </form>
                                    <form action="{{ route('transactions.reject', $pending) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 bg-slate-200 dark:bg-slate-700 hover:bg-rose-600 hover:text-white text-slate-700 dark:text-slate-300 font-bold rounded-lg text-[10px] transition cursor-pointer">Từ chối</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- 2. Member Breakdown Table & Cards -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm"
                 :class="mobileTab === 'stats' || mobileTab === 'all' ? 'block' : 'hidden lg:block'">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-700">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-slate-900 dark:text-white text-sm sm:text-base">Thống Kê Cá Nhân</h3>
                        </div>
                    </div>
                </div>

                <!-- Desktop Table -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                        <thead class="bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-700/60 dark:to-slate-700/30 text-slate-500 uppercase font-bold text-[10px] tracking-wider">
                            <tr>
                                <th class="py-3 px-4 rounded-l-xl">Thành viên</th>
                                <th class="py-3 px-4">Đã Góp</th>
                                <th class="py-3 px-4">Đã Vay</th>
                                <th class="py-3 px-4">Đã Trả</th>
                                <th class="py-3 px-4 text-right rounded-r-xl">Còn Vay</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                            @foreach($memberStats as $stat)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition">
                                    <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                                        <div class="flex items-center space-x-2.5">
                                            <div class="w-7 h-7 rounded-full bg-slate-800 text-white font-bold text-[11px] flex items-center justify-center flex-shrink-0 overflow-hidden border border-slate-200 dark:border-slate-700">
                                                @if($stat['avatar'] && \Illuminate\Support\Str::startsWith($stat['avatar'], ['http://', 'https://', '/uploads/']))
                                                    <img src="{{ $stat['avatar'] }}" alt="{{ $stat['name'] }}" class="w-full h-full object-cover">
                                                @else
                                                    {{ $stat['avatar'] ?? substr($stat['name'], 0, 2) }}
                                                @endif
                                            </div>
                                            <span>{{ $stat['name'] }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 font-bold text-emerald-600 dark:text-emerald-400">
                                        +{{ number_format($stat['contributions'], 0, ',', '.') }}đ
                                    </td>
                                    <td class="py-3.5 px-4 font-bold text-purple-600 dark:text-purple-400">
                                        {{ number_format($stat['loans'], 0, ',', '.') }}đ
                                    </td>
                                    <td class="py-3.5 px-4 font-bold text-teal-600 dark:text-teal-400">
                                        {{ number_format($stat['repaid'], 0, ',', '.') }}đ
                                    </td>
                                    <td class="py-3.5 px-4 font-bold text-right">
                                        @if($stat['debt'] > 0)
                                            <span class="px-2 py-0.5 bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-300 rounded-full font-extrabold text-[11px]">
                                                {{ number_format($stat['debt'], 0, ',', '.') }}đ
                                            </span>
                                        @else
                                            <span class="text-slate-400">0đ</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Member Cards -->
                <div class="md:hidden space-y-2.5">
                    @foreach($memberStats as $stat)
                        <div class="p-3 bg-slate-50 dark:bg-slate-700/30 rounded-xl border border-slate-100 dark:border-slate-700">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center space-x-2">
                                    <div class="w-7 h-7 rounded-full bg-slate-800 text-white font-bold text-[10px] flex items-center justify-center flex-shrink-0 overflow-hidden">
                                        @if($stat['avatar'] && \Illuminate\Support\Str::startsWith($stat['avatar'], ['http://', 'https://', '/uploads/']))
                                            <img src="{{ $stat['avatar'] }}" alt="{{ $stat['name'] }}" class="w-full h-full object-cover">
                                        @else
                                            {{ $stat['avatar'] ?? substr($stat['name'], 0, 2) }}
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-slate-900 dark:text-white">{{ $stat['name'] }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-4 gap-1.5 text-center pt-2 border-t border-slate-200/60 dark:border-slate-600/40">
                                <div class="bg-white dark:bg-slate-800 p-1.5 rounded-lg">
                                    <p class="text-[9px] text-slate-400 font-bold uppercase">Đã góp</p>
                                    <p class="text-[11px] font-extrabold text-emerald-600">{{ number_format($stat['contributions'], 0, ',', '.') }}</p>
                                </div>
                                <div class="bg-white dark:bg-slate-800 p-1.5 rounded-lg">
                                    <p class="text-[9px] text-slate-400 font-bold uppercase">Đã vay</p>
                                    <p class="text-[11px] font-extrabold text-purple-600">{{ number_format($stat['loans'], 0, ',', '.') }}</p>
                                </div>
                                <div class="bg-white dark:bg-slate-800 p-1.5 rounded-lg">
                                    <p class="text-[9px] text-slate-400 font-bold uppercase">Đã trả</p>
                                    <p class="text-[11px] font-extrabold text-teal-600">{{ number_format($stat['repaid'], 0, ',', '.') }}</p>
                                </div>
                                <div class="bg-white dark:bg-slate-800 p-1.5 rounded-lg">
                                    <p class="text-[9px] text-slate-400 font-bold uppercase">Đang nợ</p>
                                    <p class="text-[11px] font-extrabold {{ $stat['debt'] > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                        {{ $stat['debt'] > 0 ? number_format($stat['debt'], 0, ',', '.') : '0' }}đ
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- 3. Full Transaction History Section (Lịch Sử GD Đầy Đủ) -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm space-y-4"
                 :class="mobileTab === 'history' || mobileTab === 'all' ? 'block' : 'hidden lg:block'">
                
                <!-- Section Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100 dark:border-slate-700">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-slate-900 dark:text-white text-sm sm:text-base">Nhật Ký Thu Chi Chi Tiết</h3>
                            <p class="text-[10px] text-slate-400 font-medium">Toàn bộ lịch sử thu chi toàn hệ thống</p>
                        </div>
                    </div>

                    <span class="text-xs font-bold text-slate-400" x-text="'Hiển thị ' + filteredTransactions.length + ' giao dịch'"></span>
                </div>

                <!-- Live Filters Bar -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5">
                    <!-- Search Input -->
                    <div>
                        <input type="text" x-model="txSearchText" @input="txPage = 1" placeholder="🔍 Tìm ghi chú, số tiền, tên..." 
                               class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-xl text-xs font-bold text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <!-- Type Filter -->
                    <div>
                        <select x-model="txFilterType" @change="txPage = 1" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-xl text-xs font-bold text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="all">Tất cả loại GD</option>
                            <option value="contribution">Góp quỹ</option>
                            <option value="expense">Chi tiêu chung</option>
                            <option value="loan">Vay cá nhân</option>
                            <option value="repayment">Trả nợ</option>
                            <option value="withdrawal">Rút lương</option>
                        </select>
                    </div>

                    <!-- Member Filter -->
                    <div>
                        <select x-model="txFilterUserId" @change="txPage = 1" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-xl text-xs font-bold text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="all">Tất cả thành viên</option>
                            @foreach($members as $m)
                                <option value="{{ $m->id }}">{{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date Filter -->
                    <div>
                        <input type="date" x-model="txFilterDate" @change="txPage = 1" 
                               class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-xl text-xs font-bold text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <!-- Table View -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                        <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-400 font-extrabold text-[10px] uppercase tracking-wider">
                            <tr>
                                <th class="py-2.5 px-3 rounded-l-xl">ID & Ngày</th>
                                <th class="py-2.5 px-3">Thành viên</th>
                                <th class="py-2.5 px-3">Loại GD</th>
                                <th class="py-2.5 px-3">Nội dung ghi chú</th>
                                <th class="py-2.5 px-3 text-right">Số tiền (VNĐ)</th>
                                <th class="py-2.5 px-3 text-center rounded-r-xl">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50 font-semibold">
                            <template x-for="tx in paginatedTransactions" :key="tx.id">
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition">
                                    <!-- ID & Date -->
                                    <td class="py-3 px-3">
                                        <p class="font-extrabold text-slate-900 dark:text-white font-mono text-[11px]" x-text="'#' + tx.id"></p>
                                        <p class="text-[10px] text-slate-400 font-medium" x-text="tx.created_at_formatted"></p>
                                    </td>

                                    <!-- Member -->
                                    <td class="py-3 px-3">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-6 h-6 rounded-full bg-slate-800 text-white font-bold text-[10px] flex items-center justify-center flex-shrink-0 overflow-hidden">
                                                <template x-if="tx.user_avatar && (tx.user_avatar.startsWith('http') || tx.user_avatar.startsWith('/uploads/'))">
                                                    <img :src="tx.user_avatar" :alt="tx.user_name" class="w-full h-full object-cover">
                                                </template>
                                                <template x-if="!tx.user_avatar || (!tx.user_avatar.startsWith('http') && !tx.user_avatar.startsWith('/uploads/'))">
                                                    <span x-text="tx.user_avatar || (tx.user_name ? tx.user_name.substr(0, 2) : 'HV')"></span>
                                                </template>
                                            </div>
                                            <span class="font-bold text-slate-900 dark:text-white" x-text="tx.user_name"></span>
                                        </div>
                                    </td>

                                    <!-- Type Badge -->
                                    <td class="py-3 px-3">
                                        <span class="px-2 py-0.5 text-[10px] font-black rounded-lg"
                                              :class="{
                                                  'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20': tx.type === 'contribution',
                                                  'bg-rose-500/10 text-rose-600 border border-rose-500/20': tx.type === 'expense',
                                                  'bg-purple-500/10 text-purple-600 border border-purple-500/20': tx.type === 'loan',
                                                  'bg-teal-500/10 text-teal-600 border border-teal-500/20': tx.type === 'repayment',
                                                  'bg-indigo-500/10 text-indigo-600 border border-indigo-500/20': tx.type === 'withdrawal'
                                              }"
                                              x-text="tx.type === 'contribution' ? 'Góp quỹ' : (tx.type === 'expense' ? 'Chi tiêu chung' : (tx.type === 'loan' ? 'Vay cá nhân' : (tx.type === 'repayment' ? 'Trả nợ' : 'Rút lương')))">
                                        </span>
                                    </td>

                                    <!-- Description -->
                                    <td class="py-3 px-3">
                                        <p class="font-medium text-slate-800 dark:text-slate-200 line-clamp-1" x-text="tx.description"></p>
                                    </td>

                                    <!-- Amount -->
                                    <td class="py-3 px-3 text-right">
                                        <template x-if="tx.type === 'contribution' || tx.type === 'repayment'">
                                            <span class="font-black text-emerald-600 dark:text-emerald-400 text-xs" x-text="'+' + new Intl.NumberFormat('vi-VN').format(tx.amount) + 'đ'"></span>
                                        </template>
                                        <template x-if="tx.type !== 'contribution' && tx.type !== 'repayment'">
                                            <span class="font-black text-rose-600 dark:text-rose-400 text-xs" x-text="'-' + new Intl.NumberFormat('vi-VN').format(tx.amount) + 'đ'"></span>
                                        </template>
                                    </td>

                                    <!-- Status -->
                                    <td class="py-3 px-3 text-center">
                                        <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 rounded-md text-[10px] font-bold">
                                            ✓ Đã duyệt
                                        </span>
                                    </td>
                                </tr>
                            </template>

                            <template x-if="paginatedTransactions.length === 0">
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400 font-semibold">
                                        Không tìm thấy giao dịch nào phù hợp với bộ lọc.
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Bar -->
                <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-700/60 text-xs font-bold text-slate-500">
                    <span x-text="'Trang ' + txPage + ' / ' + totalTxPages"></span>
                    <div class="flex items-center space-x-1.5">
                        <button type="button" @click="if (txPage > 1) txPage--" :disabled="txPage <= 1" class="px-3 py-1 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-200 rounded-lg transition disabled:opacity-40 cursor-pointer">
                            ◀ Trước
                        </button>
                        <button type="button" @click="if (txPage < totalTxPages) txPage++" :disabled="txPage >= totalTxPages" class="px-3 py-1 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-200 rounded-lg transition disabled:opacity-40 cursor-pointer">
                            Sau ▶
                        </button>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- MODAL: PHÂN CHIA LỢI NHUẬN % -->
    <div x-show="showDistributionModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/60 backdrop-blur-sm" x-cloak x-transition>
        <div @click.away="showDistributionModal = false" class="bg-white dark:bg-slate-800 rounded-t-3xl sm:rounded-3xl p-5 sm:p-6 w-full sm:max-w-lg shadow-2xl border border-slate-100 dark:border-slate-700 max-h-[90vh] overflow-y-auto">
            <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mb-2">📊 Chia Quỹ Theo % Cổ Phần</h3>
            <p class="text-[11px] sm:text-xs text-slate-500 mb-4">Nhập tổng tiền cần chia, hệ thống phân bổ theo tỷ lệ %.</p>

            <form action="{{ route('distributions.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Số tiền đem chia (VNĐ)</label>
                    <input type="number" name="total_amount" x-model="distributeAmount" required step="1000" min="1000" max="{{ $fund->balance }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-base font-extrabold text-emerald-600 focus:ring-2 focus:ring-emerald-500">
                    <p class="text-[10px] text-slate-400 mt-1">Tối đa: {{ number_format($fund->balance, 0, ',', '.') }}đ</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Ghi chú</label>
                    <input type="text" name="note" placeholder="VD: Chia lợi nhuận quý 3..." class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm focus:ring-2 focus:ring-emerald-500">
                </div>

                <!-- Live Preview -->
                <div class="bg-slate-50 dark:bg-slate-700/40 p-3.5 rounded-2xl border border-slate-200/60 dark:border-slate-600/60 space-y-2">
                    <h4 class="text-[10px] sm:text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Bảng phân bổ:</h4>
                    <template x-for="payout in calculatedPayouts" :key="payout.id">
                        <div class="flex items-center justify-between text-xs py-1.5 border-b border-slate-200/40 dark:border-slate-600/40 last:border-0">
                            <div class="flex items-center space-x-2 min-w-0">
                                <div class="w-6 h-6 rounded-full bg-slate-800 text-white font-bold text-[10px] flex items-center justify-center flex-shrink-0 overflow-hidden">
                                    <template x-if="payout.avatar && (payout.avatar.startsWith('http') || payout.avatar.startsWith('/uploads/'))">
                                        <img :src="payout.avatar" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!payout.avatar || (!payout.avatar.startsWith('http') && !payout.avatar.startsWith('/uploads/'))">
                                        <span x-text="payout.avatar || payout.name.substr(0, 2)"></span>
                                    </template>
                                </div>
                                <span class="font-semibold text-slate-800 dark:text-slate-200 text-xs truncate" x-text="payout.name"></span>
                                <span class="text-[10px] text-slate-400 flex-shrink-0">(<span x-text="payout.share"></span>%)</span>
                            </div>
                            <span class="font-extrabold text-emerald-600 dark:text-emerald-400 text-xs flex-shrink-0 ml-2" x-text="new Intl.NumberFormat('vi-VN').format(payout.amount) + 'đ'"></span>
                        </div>
                    </template>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-2">
                    <button type="button" @click="showDistributionModal = false" class="px-4 py-2.5 text-xs font-bold text-slate-500">Hủy</button>
                    <button type="submit" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-slate-950 rounded-xl text-xs font-extrabold shadow-md active:scale-95 transition">Chia Tiền</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: QUẢN LÝ THÀNH VIÊN -->
    <div x-show="showMemberModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/60 backdrop-blur-sm" x-cloak x-transition>
        <div @click.away="showMemberModal = false" class="bg-white dark:bg-slate-800 rounded-t-3xl sm:rounded-3xl p-5 sm:p-6 w-full sm:max-w-lg shadow-2xl border border-slate-100 dark:border-slate-700 max-h-[85vh] overflow-y-auto">
            <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mb-3">👥 Thành Viên & % Cổ Phần</h3>

            <div class="space-y-2.5 mb-5">
                @foreach($members as $m)
                    <form action="{{ route('members.updateShare', $m) }}" method="POST" class="p-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
                        @csrf
                        @method('PUT')
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2.5 min-w-0">
                                <div class="w-7 h-7 rounded-full bg-slate-800 text-white font-bold text-xs flex items-center justify-center flex-shrink-0 overflow-hidden">
                                    @if($m->avatar && \Illuminate\Support\Str::startsWith($m->avatar, ['http://', 'https://', '/uploads/']))
                                        <img src="{{ $m->avatar }}" alt="{{ $m->name }}" class="w-full h-full object-cover">
                                    @else
                                        {{ $m->avatar ?? substr($m->name, 0, 2) }}
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ $m->name }}</p>
                                    <p class="text-[10px] text-slate-400 truncate">{{ $m->email }}</p>
                                </div>
                            </div>

                            <div class="flex items-center space-x-1.5 flex-shrink-0 ml-2">
                                <input type="number" name="share_percentage" value="{{ $m->share_percentage }}" step="0.1" min="0" max="100" class="w-16 px-2 py-1.5 rounded-lg border border-slate-300 dark:border-slate-600 dark:bg-slate-800 text-xs font-bold text-center">
                                <span class="text-xs font-bold text-slate-500">%</span>
                                <button type="submit" class="px-2 py-1.5 bg-emerald-600 text-white rounded-lg text-[10px] font-bold hover:bg-emerald-700">Lưu</button>
                            </div>
                        </div>
                    </form>
                @endforeach
            </div>

            <!-- Form Add Member -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-700">
                <h4 class="text-xs font-extrabold text-slate-900 dark:text-white mb-3">➕ Thêm Thành Viên Mới</h4>
                <form action="{{ route('members.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">Họ và Tên</label>
                        <input type="text" name="name" required placeholder="Nhập họ và tên thành viên..." class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">Địa Chỉ Email</label>
                        <input type="email" name="email" required placeholder="example@weamis.com" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">% Cổ Phần Ban Đầu</label>
                        <div class="flex items-center space-x-2">
                            <div class="relative flex-1">
                                <input type="number" name="share_percentage" required placeholder="Ví dụ: 12.5" step="0.1" min="0" max="100" class="w-full px-3.5 py-2 pr-7 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                                <span class="absolute right-3 top-2 text-xs font-bold text-slate-400">%</span>
                            </div>
                            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-extrabold flex-shrink-0 shadow-md transition cursor-pointer">Thêm</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="flex items-center justify-end pt-4">
                <button type="button" @click="showMemberModal = false" class="px-4 py-2.5 text-xs font-bold text-slate-500">Đóng</button>
            </div>
        </div>
    </div>

</div>
@endsection
