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

    expenseCategories: [
        { key: 'eat', name: 'Ăn uống', fullName: 'Ăn uống', icon: '/icons/EatAndDrink.svg', color: 'bg-amber-500' },
        { key: 'daily', name: 'Chi tiêu h...', fullName: 'Chi tiêu hàng ngày', icon: '/icons/DailyExpenses.svg', color: 'bg-emerald-500' },
        { key: 'clothes', name: 'Quần áo', fullName: 'Quần áo', icon: '/icons/Clothes.svg', color: 'bg-blue-500' },
        { key: 'cosmetics', name: 'Mỹ phẩm', fullName: 'Mỹ phẩm', icon: '/icons/Cosmetics.svg', color: 'bg-pink-500' },
        { key: 'exchange', name: 'Phí giao l...', fullName: 'Phí giao lưu', icon: '/icons/Exchange.svg', color: 'bg-purple-500' },
        { key: 'medical', name: 'Y tế', fullName: 'Y tế', icon: '/icons/Medical.svg', color: 'bg-teal-500' },
        { key: 'education', name: 'Giáo dục', fullName: 'Giáo dục', icon: '/icons/Education.svg', color: 'bg-indigo-500' },
        { key: 'electric', name: 'Tiền điện', fullName: 'Tiền điện', icon: '/icons/Electric.svg', color: 'bg-yellow-500' },
        { key: 'transport', name: 'Đi lại', fullName: 'Đi lại', icon: '/icons/Transport.svg', color: 'bg-orange-500' },
        { key: 'contact', name: 'Phí liên lạc', fullName: 'Phí liên lạc', icon: '/icons/Contact.svg', color: 'bg-cyan-500' },
        { key: 'house', name: 'Tiền nhà', fullName: 'Tiền nhà', icon: '/icons/HouseRent.svg', color: 'bg-rose-500' },
        { key: 'other', name: 'Chỉnh sửa', fullName: '', icon: '/icons/Edit.svg', color: 'bg-slate-400' }
    ],
    incomeCategories: [
        { key: 'salary', name: 'Tiền lương', fullName: 'Tiền lương', icon: '/icons/Salary.svg', color: 'bg-emerald-500' },
        { key: 'bonus', name: 'Tiền thưởng', fullName: 'Tiền thưởng', icon: '/icons/Bonus.svg', color: 'bg-amber-500' },
        { key: 'invest', name: 'Đầu tư', fullName: 'Lợi nhuận đầu tư', icon: '/icons/Invest.svg', color: 'bg-blue-500' },
        { key: 'other_income', name: 'Thu khác', fullName: 'Góp quỹ / Thu khác', icon: '/icons/Exchange.svg', color: 'bg-purple-500' },
        { key: 'other', name: 'Chỉnh sửa', fullName: '', icon: '/icons/Edit.svg', color: 'bg-slate-400' }
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
        <button @click="mobileTab = 'entry'" class="flex-1 py-2 text-xs font-bold rounded-xl transition" :class="mobileTab === 'entry' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 dark:text-slate-400'">✏️ Nhập vào</button>
        <button @click="mobileTab = 'stats'" class="flex-1 py-2 text-xs font-bold rounded-xl transition" :class="mobileTab === 'stats' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 dark:text-slate-400'">📊 Thống kê</button>
        <a href="{{ route('report') }}" class="flex-1 py-2 text-xs font-bold rounded-xl transition text-center text-slate-500 dark:text-slate-400">📈 Báo cáo ➔</a>
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
                    <input type="hidden" name="type" :value="quickType">

                    <!-- Member Selector -->
                    <div class="flex items-center justify-between text-xs sm:text-sm font-semibold">
                        <span class="text-slate-500 dark:text-slate-400 w-20 flex-shrink-0">Thành viên</span>
                        <select name="user_id" x-model="quickUserId" class="flex-1 bg-slate-50 dark:bg-slate-700/60 border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-2 text-xs sm:text-sm font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none">
                            @foreach($members->where('role', '!=', 'admin') as $m)
                                <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->share_percentage }}%)</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Project Selector -->
                    <div class="flex items-center justify-between text-xs sm:text-sm font-semibold">
                        <span class="text-slate-500 dark:text-slate-400 w-20 flex-shrink-0">Dự án</span>
                        <select name="project_id" class="flex-1 bg-slate-50 dark:bg-slate-700/60 border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-2 text-xs sm:text-sm font-bold text-indigo-600 dark:text-indigo-400 focus:ring-2 focus:ring-emerald-500 outline-none">
                            <option value="">-- Không gắn dự án (Quỹ chung) --</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}">📁 {{ $p->name }} ({{ $p->code }})</option>
                            @endforeach
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

                    <!-- Responsible & Claimant User Selectors -->
                    <div class="grid grid-cols-2 gap-2 text-xs font-semibold">
                        <div>
                            <span class="text-slate-500 dark:text-slate-400 block mb-1">Người trách nhiệm</span>
                            <select name="responsible_user_id" class="w-full bg-slate-50 dark:bg-slate-700/60 border border-slate-200 dark:border-slate-600 rounded-xl px-2 py-1.5 text-xs font-medium text-slate-900 dark:text-white outline-none">
                                <option value="">-- Trách nhiệm --</option>
                                @foreach($members->where('role', '!=', 'admin') as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <span class="text-slate-500 dark:text-slate-400 block mb-1">Người đòi/nhận tiền</span>
                            <select name="claimant_user_id" class="w-full bg-slate-50 dark:bg-slate-700/60 border border-slate-200 dark:border-slate-600 rounded-xl px-2 py-1.5 text-xs font-medium text-slate-900 dark:text-white outline-none">
                                <option value="">-- Đòi tiền --</option>
                                @foreach($members->where('role', '!=', 'admin') as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Evidence Attachment Row -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs font-semibold">
                            <span class="text-slate-500 dark:text-slate-400">Bằng chứng</span>
                            <div class="flex space-x-1">
                                <button type="button" @click="triggerFilePick()" class="px-2.5 py-1 rounded-lg text-xs font-bold transition flex items-center space-x-1 cursor-pointer" :class="evidenceMode === 'file' && selectedFileName ? 'bg-emerald-600 text-white shadow-sm' : 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-300 hover:bg-emerald-100'">
                                    <span>🖼️ Ảnh bill</span>
                                </button>
                                <button type="button" @click="evidenceMode = 'link'" class="px-2.5 py-1 rounded-lg text-xs font-bold transition flex items-center space-x-1 cursor-pointer" :class="evidenceMode === 'link' ? 'bg-blue-600 text-white shadow-sm' : 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300 hover:bg-blue-100'">
                                    <span>🔗 Link</span>
                                </button>
                                <button type="button" @click="evidenceMode = 'text'" class="px-2.5 py-1 rounded-lg text-xs font-bold transition flex items-center space-x-1 cursor-pointer" :class="evidenceMode === 'text' ? 'bg-amber-600 text-white shadow-sm' : 'bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-300 hover:bg-amber-100'">
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

                        <div x-show="evidenceMode === 'link'" x-cloak>
                            <input type="url" name="evidence_link" placeholder="https://momo.vn/transaction/..." class="w-full text-xs px-3 py-2 border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700/60 text-slate-900 dark:text-white">
                        </div>
                        <div x-show="evidenceMode === 'text'" x-cloak>
                            <textarea name="evidence_text" rows="2" placeholder="Dán thông tin sao kê trích xuất từ Momo..." class="w-full text-xs px-3 py-2 border border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700/60 text-slate-900 dark:text-white"></textarea>
                        </div>
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
                        <button type="submit" class="w-full py-3 bg-slate-800 dark:bg-slate-700 hover:bg-slate-900 dark:hover:bg-slate-600 text-white font-extrabold text-xs sm:text-sm rounded-2xl shadow-md transition-all duration-150 active:scale-98 cursor-pointer flex items-center justify-center space-x-2">
                            <span x-text="quickType === 'expense' ? 'Nhập khoản Tiền chi' : 'Nhập khoản Thu nhập'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- CỘT PHẢI (RIGHT PANEL): DASHBOARD OVERVIEW, CHARTS & MEMBER STATS -->
        <div class="lg:col-span-7 xl:col-span-8 space-y-6" :class="mobileTab !== 'entry' ? 'block' : 'hidden lg:block'">

            <!-- Top Desktop Action Buttons -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                        Tổng quan
                    </h2>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('report') }}" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs rounded-xl shadow-sm transition flex items-center space-x-1.5 cursor-pointer">
                        <span>📈 Xem Báo Cáo</span>
                    </a>
                    @if(auth()->user()?->isAdmin())
                        <button @click="showDistributionModal = true" class="px-3.5 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-extrabold text-xs rounded-xl shadow-sm transition flex items-center space-x-1.5 cursor-pointer">
                            <span>📊 Chia % Quỹ</span>
                        </button>
                    @endif
                </div>
            </div>

            <!-- 1. Top Stat Cards Row -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4" :class="mobileTab === 'stats' || mobileTab === 'all' ? 'block' : 'hidden lg:grid'">
                <!-- Card 1: Số Dư Quỹ -->
                <div class="col-span-2 sm:col-span-1 bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm relative overflow-hidden hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider">Số Dư Quỹ</span>
                        <span class="p-1.5 sm:p-2 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-300">💵</span>
                    </div>
                    <p class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white mt-1.5">
                        {{ number_format($fund->balance, 0, ',', '.') }}<span class="text-base sm:text-lg font-bold">đ</span>
                    </p>
                </div>

                <!-- Card 2: Tổng Thu -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider">Tổng Thu</span>
                        <span class="p-1.5 sm:p-2 rounded-xl bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300">📥</span>
                    </div>
                    <p class="text-lg sm:text-2xl font-extrabold text-blue-600 dark:text-blue-400 mt-1.5">
                        +{{ number_format($totalIncome, 0, ',', '.') }}<span class="text-sm sm:text-lg font-bold">đ</span>
                    </p>
                </div>

                <!-- Card 3: Tổng Chi -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider">Tổng Chi</span>
                        <span class="p-1.5 sm:p-2 rounded-xl bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-300">📤</span>
                    </div>
                    <p class="text-lg sm:text-2xl font-extrabold text-rose-600 dark:text-rose-400 mt-1.5">
                        -{{ number_format($totalExpense, 0, ',', '.') }}<span class="text-sm sm:text-lg font-bold">đ</span>
                    </p>
                </div>

                <!-- Card 4: Tổng Cho Vay -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider">Đang Cho Vay</span>
                        <span class="p-1.5 sm:p-2 rounded-xl bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-300">🤝</span>
                    </div>
                    <p class="text-lg sm:text-2xl font-extrabold text-amber-600 dark:text-amber-400 mt-1.5">
                        {{ number_format($totalLoans, 0, ',', '.') }}<span class="text-sm sm:text-lg font-bold">đ</span>
                    </p>
                </div>
            </div>

            <!-- ApexCharts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-5"
                 :class="mobileTab === 'stats' || mobileTab === 'all' ? 'grid' : 'hidden lg:grid'"
                 x-data="{
                isDark: document.documentElement.classList.contains('dark'),
                donutChart: null,
                barChart: null,
                updateTheme() {
                    this.isDark = document.documentElement.classList.contains('dark');
                    const textColor = this.isDark ? '#94a3b8' : '#64748b';
                    const valueColor = this.isDark ? '#f8fafc' : '#0f172a';
                    const foreColor = this.isDark ? '#f1f5f9' : '#1e293b';
                    const bgColor = this.isDark ? '#1e293b' : '#ffffff';
                    const gridColor = this.isDark ? '#334155' : '#e2e8f0';

                    if (this.donutChart) {
                        this.donutChart.updateOptions({
                            chart: { foreColor: foreColor },
                            stroke: { colors: [bgColor] },
                            legend: { labels: { colors: foreColor } },
                            tooltip: { theme: this.isDark ? 'dark' : 'light' }
                        });
                    }

                    if (this.barChart) {
                        this.barChart.updateOptions({
                            chart: { foreColor: foreColor },
                            xaxis: { labels: { style: { colors: foreColor } } },
                            yaxis: { labels: { style: { colors: foreColor } } },
                            dataLabels: { style: { colors: [this.isDark ? '#ffffff' : '#0f172a'] } },
                            grid: { borderColor: gridColor },
                            tooltip: { theme: this.isDark ? 'dark' : 'light' }
                        });
                    }
                },
                initCharts() {
                    this.isDark = document.documentElement.classList.contains('dark');
                    const textColor = this.isDark ? '#94a3b8' : '#64748b';
                    const valueColor = this.isDark ? '#f8fafc' : '#0f172a';
                    const foreColor = this.isDark ? '#f1f5f9' : '#1e293b';
                    const bgColor = this.isDark ? '#1e293b' : '#ffffff';
                    const gridColor = this.isDark ? '#334155' : '#e2e8f0';

                    // Donut chart is rendered as custom SVG (no ApexCharts needed)

                    // 2. Bar Chart: Member Shares
                    const memberNames = [@foreach($members as $m)'{{ $m->name }}',@endforeach];
                    const memberShares = [@foreach($members as $m){{ $m->share_percentage }},@endforeach];

                    this.barChart = new ApexCharts(this.$refs.barChart, {
                        chart: { type: 'bar', height: 260, background: 'transparent', fontFamily: 'Plus Jakarta Sans, sans-serif', toolbar: { show: false }, foreColor: foreColor },
                        series: [{ name: 'Cổ phần (%)', data: memberShares }],
                        xaxis: {
                            categories: memberNames,
                            labels: { style: { fontSize: '10px', fontWeight: 600, colors: foreColor }, formatter: (val) => val + '%' }
                        },
                        yaxis: { labels: { style: { fontSize: '10px', fontWeight: 700, colors: foreColor } }, max: Math.max(...memberShares) + 5 },
                        plotOptions: { bar: { horizontal: true, borderRadius: 6, barHeight: '60%', distributed: true } },
                        colors: ['#6366f1', '#8b5cf6', '#a78bfa', '#c084fc', '#e879f9', '#f472b6', '#fb7185', '#f87171'],
                        dataLabels: {
                            enabled: true,
                            formatter: (val) => val + '%',
                            style: { fontSize: '11px', fontWeight: 800, colors: [this.isDark ? '#ffffff' : '#0f172a'] },
                            dropShadow: { enabled: true, top: 1, left: 1, blur: 2, opacity: 0.2 }
                        },
                        legend: { show: false },
                        grid: { borderColor: gridColor },
                        tooltip: { y: { formatter: (val) => val + '% cổ phần' }, theme: this.isDark ? 'dark' : 'light' }
                    });
                    this.barChart.render();

                    const observer = new MutationObserver(() => { this.updateTheme(); });
                    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
                }
             }"
                 x-init="$nextTick(() => initCharts())"
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

                <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm">
                    <div class="flex items-center space-x-3 mb-3 pb-2.5 border-b border-slate-100 dark:border-slate-700">
                        <div class="p-2 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-slate-900 dark:text-white text-sm sm:text-base">Cổ Phần Thành Viên</h3>
                            <p class="text-[10px] text-slate-400 font-medium">Tỷ lệ % góp vốn của từng thành viên</p>
                        </div>
                    </div>
                    <div x-ref="barChart"></div>
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
                            <p class="text-[10px] text-slate-400 font-medium">Chi tiết cổ phần & tài chính từng thành viên</p>
                        </div>
                    </div>
                </div>

                <!-- Desktop Table -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                        <thead class="bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-700/60 dark:to-slate-700/30 text-slate-500 uppercase font-bold text-[10px] tracking-wider">
                            <tr>
                                <th class="py-3 px-4 rounded-l-xl">Thành viên</th>
                                <th class="py-3 px-4">Cổ phần</th>
                                <th class="py-3 px-4">Đã Góp</th>
                                <th class="py-3 px-4">Đã Vay</th>
                                <th class="py-3 px-4">Đã Trả</th>
                                <th class="py-3 px-4">Dư Nợ Vay</th>
                                <th class="py-3 px-4 text-right rounded-r-xl">Dự Nhận Chia</th>
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
                                    <td class="py-3.5 px-4 font-extrabold text-blue-600 dark:text-blue-400">
                                        {{ $stat['share_percentage'] }}%
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
                                    <td class="py-3.5 px-4 font-bold">
                                        @if($stat['debt'] > 0)
                                            <span class="px-2 py-0.5 bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-300 rounded-full font-extrabold text-[11px]">
                                                {{ number_format($stat['debt'], 0, ',', '.') }}đ
                                            </span>
                                        @else
                                            <span class="text-slate-400">0đ</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 font-extrabold text-right text-emerald-600 dark:text-emerald-400 text-sm">
                                        {{ number_format($stat['estimated_share_amount'], 0, ',', '.') }}đ
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
                                        <p class="text-[10px] font-bold text-blue-600 dark:text-blue-400">Cổ phần: {{ $stat['share_percentage'] }}%</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] text-slate-400 font-bold uppercase">Dự nhận chia</p>
                                    <p class="text-xs font-extrabold text-emerald-600 dark:text-emerald-400">{{ number_format($stat['estimated_share_amount'], 0, ',', '.') }}đ</p>
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

            <!-- 3. Recent 5 Transactions Section -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm"
                 :class="mobileTab === 'history' || mobileTab === 'all' ? 'block' : 'hidden lg:block'">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-700">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-slate-900 dark:text-white text-sm sm:text-base">Giao Dịch Gần Đây</h3>
                            <p class="text-[10px] text-slate-400 font-medium">5 giao dịch mới nhất trong hệ thống</p>
                        </div>
                    </div>

                    <a href="{{ route('history') }}" class="px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 hover:bg-indigo-600 hover:text-white text-indigo-600 dark:text-indigo-300 rounded-xl text-xs font-bold transition flex items-center space-x-1 cursor-pointer">
                        <span>Xem tất cả ➔</span>
                    </a>
                </div>

                <!-- Recent items list -->
                <div class="space-y-2.5">
                    <template x-for="tx in rawTransactions.slice(0, 5)" :key="tx.id">
                        <div class="p-3 bg-slate-50 dark:bg-slate-700/30 rounded-xl border border-slate-100 dark:border-slate-700 flex items-center justify-between text-xs hover:shadow-sm transition">
                            <div class="flex items-center space-x-3 min-w-0">
                                <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-600 text-slate-800 dark:text-slate-200 font-bold text-xs flex items-center justify-center flex-shrink-0 overflow-hidden">
                                    <template x-if="tx.user_avatar && (tx.user_avatar.startsWith('http') || tx.user_avatar.startsWith('/uploads/'))">
                                        <img :src="tx.user_avatar" :alt="tx.user_name" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!tx.user_avatar || (!tx.user_avatar.startsWith('http') && !tx.user_avatar.startsWith('/uploads/'))">
                                        <span x-text="tx.user_avatar || (tx.user_name ? tx.user_name.substr(0, 2) : 'HV')"></span>
                                    </template>
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center space-x-2">
                                        <span class="font-bold text-slate-900 dark:text-white truncate" x-text="tx.user_name"></span>
                                        <span class="text-[10px] text-slate-400" x-text="tx.created_at_formatted"></span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate" x-text="tx.description"></p>
                                </div>
                            </div>

                            <div class="text-right flex-shrink-0 ml-3">
                                <template x-if="tx.type === 'contribution' || tx.type === 'repayment'">
                                    <span class="font-extrabold text-emerald-600 dark:text-emerald-400 text-xs sm:text-sm" x-text="'+' + new Intl.NumberFormat('vi-VN').format(tx.amount) + 'đ'"></span>
                                </template>
                                <template x-if="tx.type !== 'contribution' && tx.type !== 'repayment'">
                                    <span class="font-extrabold text-slate-900 dark:text-slate-100 text-xs sm:text-sm" x-text="'-' + new Intl.NumberFormat('vi-VN').format(tx.amount) + 'đ'"></span>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="mt-4 text-center">
                    <a href="{{ route('history') }}" class="inline-block w-full py-2.5 bg-slate-100 dark:bg-slate-700/60 hover:bg-indigo-600 hover:text-white text-slate-700 dark:text-slate-300 font-extrabold text-xs rounded-xl transition">
                        📜 Xem Toàn Bộ Lịch Sử Giao Dịch ➔
                    </a>
                </div>
            </div>

        </div>

    </div>

    <!-- MOBILE FIXED BOTTOM NAVIGATION BAR -->
    <div class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 dark:bg-slate-800/95 backdrop-blur-md border-t border-slate-200 dark:border-slate-700 shadow-2xl safe-bottom px-2 py-1.5">
        <div class="grid grid-cols-5 gap-1 text-center">
            <button @click="mobileTab = 'entry'" class="flex flex-col items-center justify-center py-1 rounded-xl transition cursor-pointer" :class="mobileTab === 'entry' ? 'text-emerald-600 dark:text-emerald-400 font-extrabold' : 'text-slate-400 hover:text-slate-600'">
                <div class="w-5 h-5 mb-0.5 bg-current" style="-webkit-mask: url('/icons/Edit.svg') center/contain no-repeat; mask: url('/icons/Edit.svg') center/contain no-repeat;"></div>
                <span class="text-[10px]">Nhập vào</span>
            </button>

            <a href="{{ route('report') }}" class="flex flex-col items-center justify-center py-1 rounded-xl text-slate-400 hover:text-indigo-600 transition cursor-pointer">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path></svg>
                <span class="text-[10px]">Báo cáo</span>
            </a>

            <a href="{{ route('history') }}" class="flex flex-col items-center justify-center py-1 rounded-xl text-slate-400 hover:text-emerald-600 transition cursor-pointer">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                <span class="text-[10px]">Lịch sử</span>
            </a>

            <button @click="showDistributionModal = true" class="flex flex-col items-center justify-center py-1 rounded-xl text-slate-400 hover:text-amber-500 transition cursor-pointer">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="text-[10px]">Chia %</span>
            </button>

            <button @click="showMemberModal = true" class="flex flex-col items-center justify-center py-1 rounded-xl text-slate-400 hover:text-blue-500 transition cursor-pointer">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <span class="text-[10px]">Thành viên</span>
            </button>
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
