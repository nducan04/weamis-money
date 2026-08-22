@extends('layouts.app')

@section('content')
<div x-data="reportPage" class="space-y-6 pb-20 lg:pb-6">

    <!-- 1. Header & Period Filter Controls -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 sm:p-6 border border-slate-200/80 dark:border-slate-700 shadow-md">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-700">
            <div>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white flex items-center space-x-2">
                    <span>Báo Cáo Thu Chi & Phân Tích Dòng Tiền</span>
                </h2>
            </div>

            <!-- Action Controls -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Filter Mode Selector -->
                <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-700/60 rounded-2xl">
                    <button type="button" @click="reportFreq = 'monthly'" 
                            class="px-3.5 py-1.5 rounded-xl font-extrabold text-xs transition cursor-pointer"
                            :class="reportFreq === 'monthly' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900'">
                        Hàng tháng
                    </button>
                    <button type="button" @click="reportFreq = 'yearly'" 
                            class="px-3.5 py-1.5 rounded-xl font-extrabold text-xs transition cursor-pointer"
                            :class="reportFreq === 'yearly' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900'">
                        Hàng năm
                    </button>
                    <button type="button" @click="reportFreq = 'custom'" 
                            class="px-3.5 py-1.5 rounded-xl font-extrabold text-xs transition cursor-pointer"
                            :class="reportFreq === 'custom' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900'">
                        Tự chọn
                    </button>
                </div>

                <!-- Export Button -->
                <button type="button" @click="exportToExcel()" 
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-sm transition cursor-pointer flex items-center space-x-1.5">
                    <span>Xuất Excel/CSV</span>
                </button>
            </div>
        </div>

        <!-- Period Navigator -->
        <div class="pt-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <template x-if="reportFreq !== 'custom'">
                <div class="flex items-center space-x-3">
                    <button type="button" @click="prevReportPeriod()" class="p-2 bg-slate-100 dark:bg-slate-700/60 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition cursor-pointer">
                        <svg class="w-4 h-4 text-slate-600 dark:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                    </button>
                    <span class="font-extrabold text-sm sm:text-base text-slate-800 dark:text-slate-200" x-text="reportDateLabel"></span>
                    <button type="button" @click="nextReportPeriod()" class="p-2 bg-slate-100 dark:bg-slate-700/60 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition cursor-pointer">
                        <svg class="w-4 h-4 text-slate-600 dark:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                </div>
            </template>

            <template x-if="reportFreq === 'custom'">
                <div class="flex items-center space-x-2 text-xs font-bold text-slate-600 dark:text-slate-300">
                    <span>Từ:</span>
                    <input type="date" x-model="customStartDate" class="px-2.5 py-1.5 bg-slate-100 dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600">
                    <span>đến:</span>
                    <input type="date" x-model="customEndDate" class="px-2.5 py-1.5 bg-slate-100 dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600">
                </div>
            </template>
        </div>
    </div>

    <!-- 2. Summary Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Income Metric -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-extrabold uppercase text-emerald-600 dark:text-emerald-400">Tổng Thu Nhập</span>
            </div>
            <p class="text-2xl font-black tracking-tight text-emerald-600 dark:text-emerald-400" x-text="new Intl.NumberFormat('vi-VN').format(periodIncomeTotal) + 'đ'"></p>
            <p class="text-[11px] text-slate-400 font-semibold mt-1">Bao gồm nộp quỹ & thu từ dự án</p>
        </div>

        <!-- Expense Metric -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-extrabold uppercase text-rose-600 dark:text-rose-400">Tổng Chi Tiêu</span>
            </div>
            <p class="text-2xl font-black tracking-tight text-rose-600 dark:text-rose-400" x-text="new Intl.NumberFormat('vi-VN').format(periodExpenseTotal) + 'đ'"></p>
            <p class="text-[11px] text-slate-400 font-semibold mt-1">Bao gồm chi tiêu & khoản cho vay</p>
        </div>

        <!-- Net Total Metric -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-extrabold uppercase text-indigo-600 dark:text-indigo-400">Dòng Tiền Ròng</span>
            </div>
            <p class="text-2xl font-black tracking-tight" :class="periodNetTotal >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'" x-text="new Intl.NumberFormat('vi-VN').format(periodNetTotal) + 'đ'"></p>
            <p class="text-[11px] text-slate-400 font-semibold mt-1">Thu nhập trừ tổng chi tiêu</p>
        </div>

        <!-- Active Projects Count Metric -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-extrabold uppercase text-amber-600 dark:text-amber-400">Dự Án Phát Sinh</span>
            </div>
            <p class="text-2xl font-black tracking-tight text-slate-900 dark:text-white" x-text="projectBreakdown.length + ' Dự án'"></p>
            <p class="text-[11px] text-slate-400 font-semibold mt-1">Dự án có giao dịch thu/chi</p>
        </div>
    </div>

    <!-- 3. Dual Bar Chart (Income vs Expense ApexChart) -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 sm:p-6 border border-slate-200/80 dark:border-slate-700 shadow-md space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
            <div>
                <h3 class="font-black text-slate-900 dark:text-white text-base sm:text-lg flex items-center space-x-2">
                    <span>Biểu Đồ So Sánh Thu Nhập vs Chi Tiêu</span>
                </h3>
            </div>
        </div>

        <div id="income-expense-bar-chart" class="w-full h-80"></div>
    </div>

    <!-- 4. Project Financial Breakdown Table -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 sm:p-6 border border-slate-200/80 dark:border-slate-700 shadow-md space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
            <div>
                <h3 class="font-black text-slate-900 dark:text-white text-base sm:text-lg flex items-center space-x-2">
                    <span>Báo Cáo Thu Chi & Doanh Thu Phân Rã Theo Dự Án</span>
                </h3>
                <p class="text-xs text-slate-400 font-medium">Chi tiết dòng tiền, % Trích Về Quỹ Chung và chia lợi nhuận thành viên cho từng dự án</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-700/50 text-[11px] uppercase font-black text-slate-400 border-b border-slate-200 dark:border-slate-700">
                        <th class="py-3 px-4">Dự Án</th>
                        <th class="py-3 px-4 text-right">Tổng Thu</th>
                        <th class="py-3 px-4 text-right">Tổng Chi</th>
                        <th class="py-3 px-4 text-right">Trích Quỹ Chung</th>
                        <th class="py-3 px-4 text-right">Lợi Nhuận Chia Thành Viên</th>
                        <th class="py-3 px-4">Trạng Thái</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200">
                    <template x-for="p in projectBreakdown" :key="p.id">
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition">
                            <td class="py-3 px-4 font-black">
                                <div class="flex items-center space-x-2">
                                    <div>
                                        <p class="font-extrabold text-slate-900 dark:text-white" x-text="p.name"></p>
                                        <p class="text-[10px] text-slate-400 font-mono" x-text="'#' + p.code"></p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-right font-extrabold text-emerald-600 dark:text-emerald-400" x-text="new Intl.NumberFormat('vi-VN').format(p.income) + 'đ'"></td>
                            <td class="py-3 px-4 text-right font-extrabold text-rose-600 dark:text-rose-400" x-text="new Intl.NumberFormat('vi-VN').format(p.expense) + 'đ'"></td>
                            <td class="py-3 px-4 text-right font-extrabold text-indigo-600 dark:text-indigo-400">
                                <span x-text="new Intl.NumberFormat('vi-VN').format(p.fundCut) + 'đ'"></span>
                                <span class="text-[10px] font-bold text-slate-400 block" x-text="'(' + p.weamis_fund_percentage + '%)'"></span>
                            </td>
                            <td class="py-3 px-4 text-right font-black text-amber-600 dark:text-amber-400" x-text="new Intl.NumberFormat('vi-VN').format(p.netDistributable) + 'đ'"></td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-1 text-[10px] font-extrabold rounded-lg uppercase" 
                                      :class="p.status === 'completed' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300'" 
                                      x-text="p.status === 'completed' ? 'Đã xong' : 'Đang làm'"></span>
                            </td>
                        </tr>
                    </template>
                    <template x-if="projectBreakdown.length === 0">
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-400 text-xs font-semibold">Chưa có giao dịch phát sinh cho dự án trong khoảng thời gian này</td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Alpine.js Store & ApexCharts Initialization -->
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('reportPage', () => ({
            reportFreq: 'monthly',
            reportYear: {{ date('Y') }},
            reportMonth: {{ date('n') - 1 }},
            customStartDate: '{{ date('Y-m-01') }}',
            customEndDate: '{{ date('Y-m-t') }}',
            reportCategoryType: 'expense',
            rawTransactions: @json($allTransactions),
            rawProjects: @json($projectsData ?? []),

            expenseCategories: [
                { key: 'eat', name: 'Ăn uống', fullName: 'Ăn uống', icon: '/icons/EatAndDrink.svg', color: 'bg-amber-500', hex: '#f59e0b' },
                { key: 'daily', name: 'Chi hàng ngày', fullName: 'Chi hàng ngày', icon: '/icons/DailyExpenses.svg', color: 'bg-emerald-500', hex: '#10b981' },
                { key: 'clothes', name: 'Quần áo', fullName: 'Quần áo', icon: '/icons/Clothes.svg', color: 'bg-blue-500', hex: '#3b82f6' },
                { key: 'cosmetics', name: 'Mỹ phẩm', fullName: 'Mỹ phẩm', icon: '/icons/Cosmetics.svg', color: 'bg-pink-500', hex: '#ec4899' },
                { key: 'exchange', name: 'Phí giao lưu', fullName: 'Phí giao lưu', icon: '/icons/Exchange.svg', color: 'bg-purple-500', hex: '#a855f7' },
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
                    return `Tháng ${m}/${this.reportYear} (01/${m} - ${lastDay}/${m})`;
                } else if (this.reportFreq === 'yearly') {
                    return `Năm ${this.reportYear} (01/01 - 31/12)`;
                } else {
                    return `Từ ${this.customStartDate} đến ${this.customEndDate}`;
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
                } else if (this.reportFreq === 'yearly') {
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
                } else if (this.reportFreq === 'yearly') {
                    this.reportYear++;
                }
            },

            get reportTransactions() {
                return this.rawTransactions.filter(tx => {
                    if (tx.status !== 'approved') return false;
                    if (!tx.created_at) return false;
                    let d = new Date(tx.created_at);
                    
                    if (this.reportFreq === 'monthly') {
                        return d.getFullYear() === this.reportYear && d.getMonth() === this.reportMonth;
                    } else if (this.reportFreq === 'yearly') {
                        return d.getFullYear() === this.reportYear;
                    } else {
                        let start = new Date(this.customStartDate + 'T00:00:00');
                        let end = new Date(this.customEndDate + 'T23:59:59');
                        return d >= start && d <= end;
                    }
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

            get projectBreakdown() {
                return this.rawProjects.map(p => {
                    let income = 0;
                    let expense = 0;
                    let txCount = 0;

                    this.reportTransactions.forEach(tx => {
                        let matchesProject = false;
                        let projectIncomeAmt = 0;
                        let projectExpenseAmt = 0;

                        if (tx.project_id === p.id) {
                            matchesProject = true;
                            if (tx.type === 'contribution' || tx.type === 'repayment') projectIncomeAmt = tx.amount;
                            if (tx.type === 'expense' || tx.type === 'loan') projectExpenseAmt = tx.amount;
                        } else if (tx.splits && tx.splits.length > 0) {
                            tx.splits.forEach(sp => {
                                if (sp.owner_type === 'App\\Models\\Project' && sp.owner_id === p.id) {
                                    matchesProject = true;
                                    if (tx.type === 'contribution' || tx.type === 'repayment') projectIncomeAmt += sp.amount;
                                    if (tx.type === 'expense' || tx.type === 'loan') projectExpenseAmt += sp.amount;
                                }
                            });
                        }

                        if (matchesProject) {
                            income += projectIncomeAmt;
                            expense += projectExpenseAmt;
                            txCount++;
                        }
                    });

                    let fundCut = (income * p.weamis_fund_percentage) / 100;
                    let netDistributable = Math.max(0, income - fundCut - expense);
                    
                    return {
                        ...p,
                        income: income,
                        expense: expense,
                        fundCut: fundCut,
                        netDistributable: netDistributable,
                        txCount: txCount
                    };
                }).filter(p => p.income > 0 || p.expense > 0);
            },

            exportToExcel() {
                let headers = ['ID', 'Ngay tao', 'Loai GD', 'Thanh vien', 'Du an', 'So tien (VND)', 'Noi dung', 'Trang thai'];
                let rows = this.reportTransactions.map(tx => [
                    tx.id,
                    tx.created_at_formatted || tx.created_at,
                    tx.type === 'contribution' ? 'Nop quy' : (tx.type === 'repayment' ? 'Tra no' : (tx.type === 'loan' ? 'Vay no' : 'Chi tieu')),
                    `"${tx.user_name || ''}"`,
                    `"${tx.project_name || 'N/A'}"`,
                    tx.amount,
                    `"${(tx.description || '').replace(/"/g, '""')}"`,
                    tx.status
                ]);

                let csvContent = '\uFEFF' + [headers.join(','), ...rows.map(r => r.join(','))].join('\n');
                let blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                let url = URL.createObjectURL(blob);
                let link = document.createElement('a');
                link.setAttribute('href', url);
                link.setAttribute('download', `Bao_Cao_Thu_Chi_${this.reportYear}_${this.reportMonth + 1}.csv`);
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        }));
    });

    document.addEventListener('DOMContentLoaded', function () {
        const rawTxs = @json($allTransactions);
        
        const months = ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'];
        const currentYear = new Date().getFullYear();

        let incomeData = new Array(12).fill(0);
        let expenseData = new Array(12).fill(0);

        rawTxs.forEach(tx => {
            if (tx.status !== 'approved' || !tx.created_at) return;
            let d = new Date(tx.created_at);
            if (d.getFullYear() === currentYear) {
                let m = d.getMonth();
                let amt = parseFloat(tx.amount) || 0;
                if (tx.type === 'contribution' || tx.type === 'repayment') {
                    incomeData[m] += amt;
                } else if (tx.type === 'expense' || tx.type === 'loan') {
                    expenseData[m] += amt;
                }
            }
        });

        const options = {
            chart: {
                type: 'bar',
                height: 320,
                toolbar: { show: false },
                fontFamily: 'Plus Jakarta Sans, sans-serif'
            },
            series: [
                { name: 'Thu Nhập', data: incomeData },
                { name: 'Chi Tiêu', data: expenseData }
            ],
            colors: ['#10b981', '#f43f5e'],
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    borderRadius: 6
                }
            },
            dataLabels: { enabled: false },
            stroke: { show: true, width: 2, colors: ['transparent'] },
            xaxis: {
                categories: months,
                labels: { style: { colors: '#94a3b8', fontWeight: 700 } }
            },
            yaxis: {
                labels: {
                    style: { colors: '#94a3b8', fontWeight: 700 },
                    formatter: (val) => new Intl.NumberFormat('vi-VN').format(val) + 'đ'
                }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right',
                labels: { colors: '#64748b' }
            },
            tooltip: {
                y: {
                    formatter: (val) => new Intl.NumberFormat('vi-VN').format(val) + 'đ'
                }
            }
        };

        const chart = new ApexCharts(document.querySelector("#income-expense-bar-chart"), options);
        chart.render();
    });
</script>
@endsection
