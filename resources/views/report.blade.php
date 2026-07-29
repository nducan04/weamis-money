@extends('layouts.app')

@section('content')
<div x-data="{ 
    reportFreq: 'monthly', // 'monthly' or 'yearly'
    reportYear: {{ date('Y') }},
    reportMonth: {{ date('n') - 1 }}, // 0-indexed (0 = Jan, 6 = Jul)
    reportCategoryType: 'expense', // 'expense' or 'income'
    rawTransactions: {{ \Illuminate\Support\Js::from($allTransactions) }},

    expenseCategories: [
        { key: 'eat', name: 'Ăn uống', fullName: 'Ăn uống', icon: '/icons/EatAndDrink.svg', color: 'bg-amber-500', hex: '#f59e0b' },
        { key: 'daily', name: 'Chi tiêu h...', fullName: 'Chi tiêu hàng ngày', icon: '/icons/DailyExpenses.svg', color: 'bg-emerald-500', hex: '#10b981' },
        { key: 'clothes', name: 'Quần áo', fullName: 'Quần áo', icon: '/icons/Clothes.svg', color: 'bg-blue-500', hex: '#3b82f6' },
        { key: 'cosmetics', name: 'Mỹ phẩm', fullName: 'Mỹ phẩm', icon: '/icons/Cosmetics.svg', color: 'bg-pink-500', hex: '#ec4899' },
        { key: 'exchange', name: 'Phí giao l...', fullName: 'Phí giao lưu', icon: '/icons/Exchange.svg', color: 'bg-purple-500', hex: '#a855f7' },
        { key: 'medical', name: 'Y tế', fullName: 'Y tế', icon: '/icons/Medical.svg', color: 'bg-teal-500', hex: '#14b8a6' },
        { key: 'education', name: 'Giáo dục', fullName: 'Giáo dục', icon: '/icons/Education.svg', color: 'bg-indigo-500', hex: '#6366f1' },
        { key: 'electric', name: 'Tiền điện', fullName: 'Tiền điện', icon: '/icons/Electric.svg', color: 'bg-yellow-500', hex: '#eab308' },
        { key: 'transport', name: 'Đi lại', fullName: 'Đi lại', icon: '/icons/Transport.svg', color: 'bg-orange-500', hex: '#f97316' },
        { key: 'contact', name: 'Phí liên lạc', fullName: 'Phí liên lạc', icon: '/icons/Contact.svg', color: 'bg-cyan-500', hex: '#06b6d4' },
        { key: 'house', name: 'Tiền nhà', fullName: 'Tiền nhà', icon: '/icons/HouseRent.svg', color: 'bg-rose-500', hex: '#f43f5e' },
        { key: 'other', name: 'Chỉnh sửa', fullName: '', icon: '/icons/Edit.svg', color: 'bg-slate-400', hex: '#94a3b8' }
    ],
    incomeCategories: [
        { key: 'salary', name: 'Tiền lương', fullName: 'Tiền lương', icon: '/icons/Salary.svg', color: 'bg-emerald-500', hex: '#10b981' },
        { key: 'bonus', name: 'Tiền thưởng', fullName: 'Tiền thưởng', icon: '/icons/Bonus.svg', color: 'bg-amber-500', hex: '#f59e0b' },
        { key: 'invest', name: 'Đầu tư', fullName: 'Lợi nhuận đầu tư', icon: '/icons/Invest.svg', color: 'bg-blue-500', hex: '#3b82f6' },
        { key: 'other_income', name: 'Thu khác', fullName: 'Góp quỹ / Thu khác', icon: '/icons/Exchange.svg', color: 'bg-purple-500', hex: '#a855f7' },
        { key: 'other', name: 'Chỉnh sửa', fullName: '', icon: '/icons/Edit.svg', color: 'bg-slate-400', hex: '#94a3b8' }
    ],

    get reportDateLabel() {
        if (this.reportFreq === 'monthly') {
            let m = String(this.reportMonth + 1).padStart(2, '0');
            let lastDay = new Date(this.reportYear, this.reportMonth + 1, 0).getDate();
            return `${m}/${this.reportYear} (01/${m} - ${lastDay}/${m})`;
        } else {
            return `Năm ${this.reportYear} (01/01 - 31/12)`;
        }
    },
    prevReportPeriod() {
        if (this.reportFreq === 'monthly') {
            if (this.reportMonth === 0) {
                this.reportMonth = 11;
                this.reportYear--;
            } else {
                this.reportMonth--;
            }
        } else {
            this.reportYear--;
        }
    },
    nextReportPeriod() {
        if (this.reportFreq === 'monthly') {
            if (this.reportMonth === 11) {
                this.reportMonth = 0;
                this.reportYear++;
            } else {
                this.reportMonth++;
            }
        } else {
            this.reportYear++;
        }
    },
    get reportTransactions() {
        return this.rawTransactions.filter(tx => {
            if (tx.status !== 'approved') return false;
            if (!tx.created_at) return false;
            let d = new Date(tx.created_at);
            if (d.getFullYear() !== this.reportYear) return false;
            if (this.reportFreq === 'monthly' && d.getMonth() !== this.reportMonth) return false;
            return true;
        });
    },
    get periodExpenseTotal() {
        return this.reportTransactions
            .filter(tx => tx.type === 'expense' || tx.type === 'loan')
            .reduce((sum, tx) => sum + (parseFloat(tx.amount) || 0), 0);
    },
    get periodIncomeTotal() {
        return this.reportTransactions
            .filter(tx => tx.type === 'contribution' || tx.type === 'repayment')
            .reduce((sum, tx) => sum + (parseFloat(tx.amount) || 0), 0);
    },
    get periodNetTotal() {
        return this.periodIncomeTotal - this.periodExpenseTotal;
    },
    get reportCategoryBreakdown() {
        let cats = this.reportCategoryType === 'expense' ? this.expenseCategories : this.incomeCategories;
        let total = this.reportCategoryType === 'expense' ? this.periodExpenseTotal : this.periodIncomeTotal;
        
        let results = cats.map(cat => {
            let catTotal = 0;
            if (this.reportCategoryType === 'expense') {
                catTotal = this.reportTransactions
                    .filter(tx => (tx.type === 'expense' || tx.type === 'loan') && (
                        (tx.description && tx.description.toLowerCase().includes(cat.fullName.toLowerCase())) ||
                        (tx.description && cat.name !== 'Chỉnh sửa' && tx.description.toLowerCase().includes(cat.name.toLowerCase()))
                    ))
                    .reduce((sum, tx) => sum + (parseFloat(tx.amount) || 0), 0);
            } else {
                catTotal = this.reportTransactions
                    .filter(tx => (tx.type === 'contribution' || tx.type === 'repayment') && (
                        (tx.description && tx.description.toLowerCase().includes(cat.fullName.toLowerCase())) ||
                        (tx.description && cat.name !== 'Chỉnh sửa' && tx.description.toLowerCase().includes(cat.name.toLowerCase()))
                    ))
                    .reduce((sum, tx) => sum + (parseFloat(tx.amount) || 0), 0);
            }
            return {
                ...cat,
                amount: catTotal,
                percentage: total > 0 ? (catTotal / total) * 100 : 0
            };
        }).filter(item => item.amount > 0);

        let matchedSum = results.reduce((sum, item) => sum + item.amount, 0);
        if (total > matchedSum) {
            let remainder = total - matchedSum;
            let otherCat = cats.find(c => c.key === 'other') || cats[cats.length - 1];
            let existingOther = results.find(r => r.key === otherCat.key);
            if (existingOther) {
                existingOther.amount += remainder;
                existingOther.percentage = (existingOther.amount / total) * 100;
            } else {
                results.push({
                    ...otherCat,
                    name: otherCat.name || 'Khác',
                    amount: remainder,
                    percentage: (remainder / total) * 100
                });
            }
        }

        return results.sort((a, b) => b.amount - a.amount);
    },

    // Helper SVG Donut Slices generator for 100% Vector Sharpness
    get svgDonutPaths() {
        let list = this.reportCategoryBreakdown;
        if (list.length === 0) return [];
        let cumulative = 0;
        return list.map(item => {
            let startPct = cumulative;
            let endPct = cumulative + item.percentage;
            cumulative = endPct;

            // Compute SVG arc coordinates (r=90, cx=120, cy=120)
            let startAngle = (startPct / 100) * 360 - 90;
            let endAngle = (endPct / 100) * 360 - 90;

            // Handle 100% single slice case gracefully
            if (endAngle - startAngle >= 359.99) {
                endAngle = startAngle + 359.99;
            }

            let startRad = (startAngle * Math.PI) / 180;
            let endRad = (endAngle * Math.PI) / 180;

            let x1 = 120 + 75 * Math.cos(startRad);
            let y1 = 120 + 75 * Math.sin(startRad);
            let x2 = 120 + 75 * Math.cos(endRad);
            let y2 = 120 + 75 * Math.sin(endRad);

            let largeArc = endAngle - startAngle > 180 ? 1 : 0;

            let d = `M 120 120 L ${x1} ${y1} A 75 75 0 ${largeArc} 1 ${x2} ${y2} Z`;
            return {
                d: d,
                color: item.hex,
                name: item.name,
                percentage: item.percentage
            };
        });
    }
}"
class="pb-20 lg:pb-6">

    <!-- Page Header & Period Selector -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 sm:p-6 border border-slate-200/80 dark:border-slate-700 shadow-md mb-6">
        
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-700">
            <div>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white flex items-center space-x-2">
                    <span>📊 Báo Cáo Thu Chi</span>
                </h2>
                <p class="text-xs text-slate-400 font-medium">Thống kê chi tiết dòng tiền theo chu kỳ & danh mục</p>
            </div>

            <!-- Frequency Tabs (Hàng tháng / Hàng năm) -->
            <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-700/60 rounded-2xl self-start md:self-auto">
                <button type="button" @click="reportFreq = 'monthly'" 
                        class="px-5 py-2 rounded-xl font-black text-xs sm:text-sm transition-all duration-150 cursor-pointer"
                        :class="reportFreq === 'monthly' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900'">
                    Hàng tháng
                </button>
                <button type="button" @click="reportFreq = 'yearly'" 
                        class="px-5 py-2 rounded-xl font-black text-xs sm:text-sm transition-all duration-150 cursor-pointer"
                        :class="reportFreq === 'yearly' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900'">
                    Hàng năm
                </button>
            </div>
        </div>

        <!-- Date Range Navigator Row (< 07/2026 (01/07 - 31/07) >) -->
        <div class="mt-4 flex items-center justify-between bg-slate-50 dark:bg-slate-700/50 border border-slate-200/80 dark:border-slate-600/60 rounded-2xl px-4 py-2.5">
            <button type="button" @click="prevReportPeriod()" class="p-2 text-slate-400 hover:text-slate-900 dark:hover:text-white transition cursor-pointer active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
            </button>
            <span class="font-extrabold text-sm sm:text-base text-slate-900 dark:text-white tracking-wide" x-text="reportDateLabel"></span>
            <button type="button" @click="nextReportPeriod()" class="p-2 text-slate-400 hover:text-slate-900 dark:hover:text-white transition cursor-pointer active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
            </button>
        </div>

        <!-- Financial Summary Card (Chi tiêu | Thu nhập | Thu chi) -->
        <div class="mt-4 bg-slate-900 dark:bg-slate-900/90 text-white rounded-2xl p-4 sm:p-6 shadow-lg border border-slate-800">
            <div class="grid grid-cols-2 gap-4 pb-4 border-b border-slate-800 text-center">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Chi tiêu</p>
                    <p class="text-lg sm:text-2xl font-black text-rose-400 mt-1" x-text="'-' + new Intl.NumberFormat('vi-VN').format(periodExpenseTotal) + 'đ'"></p>
                </div>
                <div class="border-l border-slate-800">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Thu nhập</p>
                    <p class="text-lg sm:text-2xl font-black text-emerald-400 mt-1" x-text="'+' + new Intl.NumberFormat('vi-VN').format(periodIncomeTotal) + 'đ'"></p>
                </div>
            </div>
            <div class="pt-3 text-center">
                <span class="text-xs sm:text-sm font-bold text-slate-400">Thu chi: </span>
                <span class="text-lg sm:text-2xl font-black ml-1.5" 
                      :class="periodNetTotal >= 0 ? 'text-emerald-400' : 'text-rose-400'"
                      x-text="(periodNetTotal >= 0 ? '+' : '') + new Intl.NumberFormat('vi-VN').format(periodNetTotal) + 'đ'"></span>
            </div>
        </div>

        <!-- Mode Tabs: Chi tiêu vs Thu nhập -->
        <div class="mt-5 grid grid-cols-2 gap-2 p-1 bg-slate-100 dark:bg-slate-700/60 rounded-2xl">
            <button type="button" @click="reportCategoryType = 'expense'" 
                    class="py-2.5 rounded-xl font-extrabold text-xs sm:text-sm transition-all duration-150 cursor-pointer"
                    :class="reportCategoryType === 'expense' ? 'bg-white dark:bg-slate-800 text-rose-600 dark:text-rose-400 shadow-sm border-b-2 border-rose-500' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900'">
                Chi tiêu
            </button>
            <button type="button" @click="reportCategoryType = 'income'" 
                    class="py-2.5 rounded-xl font-extrabold text-xs sm:text-sm transition-all duration-150 cursor-pointer"
                    :class="reportCategoryType === 'income' ? 'bg-white dark:bg-slate-800 text-emerald-600 dark:text-emerald-400 shadow-sm border-b-2 border-emerald-500' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900'">
                Thu nhập
            </button>
        </div>
    </div>

    <!-- Main 2-Column Content: Desktop Split (Left: Vector Donut & Indicator Line, Right: Category Breakdown) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- BÊN TRÁI: BIỂU ĐỒ DONUT chuẩn VECTOR SVG + ĐƯỜNG GẠCH CHỈ BÁO % VÀ TÊN DANH MỤC -->
        <div class="lg:col-span-5 bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-700 shadow-md flex flex-col items-center justify-center min-h-[380px]">
            
            <h3 class="text-sm font-extrabold text-slate-500 uppercase tracking-wider mb-4">Biểu Đồ Phân Bổ Tỷ Lệ</h3>

            <!-- SVG Vector Donut Chart -->
            <div class="relative w-60 h-60 flex items-center justify-center">
                <svg viewBox="0 0 24 24" class="w-full h-full transform -rotate-90">
                    <template x-for="(slice, idx) in svgDonutPaths" :key="idx">
                        <path :d="slice.d" :fill="slice.color" class="transition-all duration-300 hover:opacity-90"></path>
                    </template>

                    <template x-if="svgDonutPaths.length === 0">
                        <circle cx="120" cy="120" r="75" fill="#cbd5e1"></circle>
                    </template>

                    <!-- Inner Hole for Donut Effect -->
                    <circle cx="120" cy="120" r="48" class="fill-white dark:fill-slate-800"></circle>
                </svg>

                <!-- Center Amount Label -->
                <div class="absolute inset-0 flex flex-col items-center justify-center text-center p-4 pointer-events-none">
                    <span class="text-[10px] font-bold text-slate-400 uppercase">Tổng tiền</span>
                    <span class="text-sm font-black text-slate-900 dark:text-white" x-text="new Intl.NumberFormat('vi-VN').format(reportCategoryType === 'expense' ? periodExpenseTotal : periodIncomeTotal) + 'đ'"></span>
                </div>
            </div>

            <!-- Đường Gạch Chỉ Báo Callout Indicator Line (% + Tên Danh Mục) Đúng Theo Ảnh Mẫu -->
            <template x-if="reportCategoryBreakdown.length > 0">
                <div class="mt-4 flex items-center justify-center">
                    <div class="flex items-start space-x-1.5">
                        <svg class="w-7 h-7 text-amber-500 dark:text-amber-400 flex-shrink-0" viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M 14 2 L 14 18 L 32 18"></path>
                        </svg>
                        <div class="text-left font-sans">
                            <p class="text-xs font-black text-slate-900 dark:text-white leading-tight" x-text="reportCategoryBreakdown[0].percentage.toFixed(1) + ' %'"></p>
                            <p class="text-xs font-extrabold text-slate-700 dark:text-slate-200" x-text="reportCategoryBreakdown[0].name"></p>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- BÊN PHẢI: BẢNG DANH SÁCH DANH MỤC CHI TIẾT -->
        <div class="lg:col-span-7 bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-700 shadow-md">
            
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100 dark:border-slate-700">
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Chi Tiết Theo Danh Mục</h3>
                <span class="text-xs font-bold text-slate-400" x-text="reportCategoryBreakdown.length + ' danh mục phát sinh'"></span>
            </div>

            <div class="space-y-3">
                <template x-for="item in reportCategoryBreakdown" :key="item.key">
                    <div class="p-3.5 bg-slate-50 dark:bg-slate-700/40 rounded-2xl border border-slate-200/60 dark:border-slate-600/60 flex items-center justify-between hover:shadow-md transition">
                        <div class="flex items-center space-x-3.5 min-w-0">
                            <div class="w-9 h-9 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-sm"
                                 :class="item.color"
                                 :style="`-webkit-mask: url('${item.icon}') center/contain no-repeat; mask: url('${item.icon}') center/contain no-repeat;`">
                            </div>
                            <div class="min-w-0">
                                <p class="font-extrabold text-xs sm:text-sm text-slate-900 dark:text-white truncate" x-text="item.fullName || item.name"></p>
                                <p class="text-[10px] text-slate-400 font-medium">Tỷ lệ trong tổng thu chi</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3 flex-shrink-0 ml-2">
                            <span class="font-black text-xs sm:text-sm text-slate-900 dark:text-white" x-text="new Intl.NumberFormat('vi-VN').format(item.amount) + 'đ'"></span>
                            <span class="text-xs font-extrabold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/40 px-2.5 py-1 rounded-xl" x-text="item.percentage.toFixed(1) + '%'"></span>
                        </div>
                    </div>
                </template>

                <template x-if="reportCategoryBreakdown.length === 0">
                    <div class="py-12 text-center text-slate-400">
                        <p class="text-xs font-medium">Không có dữ liệu thu chi trong khoảng thời gian này.</p>
                    </div>
                </template>
            </div>
        </div>

    </div>

</div>
@endsection
