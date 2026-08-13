@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ 
    showCreateModal: false, 
    editMember: null, 
    showResetModal: null, 
    showDeleteMemberModal: null
}">

    <!-- Header Section -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 sm:p-6 border border-slate-200/80 dark:border-slate-700 shadow-md flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center space-x-2">
                <span>Quản Lý Tài Khoản Thành Viên</span>
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 font-medium mt-1">
                Xem toàn bộ danh sách tài khoản, tên đăng nhập, reset mật khẩu và phân quyền hệ thống.
            </p>
        </div>
        <button type="button" @click="showCreateModal = true" class="px-4 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-extrabold text-xs sm:text-sm rounded-xl shadow-md transition-all duration-200 flex items-center space-x-2 cursor-pointer">
            <span>Thêm Tài Khoản Mới</span>
        </button>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200/80 dark:border-slate-700 shadow-sm flex items-center space-x-3">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tổng Số Tài Khoản</p>
                <p class="text-lg font-black text-slate-900 dark:text-white">{{ $members->count() }} Thành viên</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200/80 dark:border-slate-700 shadow-sm flex items-center space-x-3">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tài Khoản Admin</p>
                <p class="text-lg font-black text-amber-600 dark:text-amber-400">{{ $members->where('role', 'admin')->count() }} Quản trị viên</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200/80 dark:border-slate-700 shadow-sm flex items-center space-x-3">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Thành Viên Thường</p>
                <p class="text-lg font-black text-indigo-600 dark:text-indigo-400">{{ $members->where('role', 'member')->count() }} Thành viên</p>
            </div>
        </div>
    </div>

    <!-- Members Table Card -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200/80 dark:border-slate-700 shadow-md">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 text-[11px] font-black uppercase text-slate-400">
                        <th class="pb-3 px-3">Thành viên</th>
                        <th class="pb-3 px-3">Tài khoản</th>
                        <th class="pb-3 px-3">Email</th>
                        <th class="pb-3 px-3 text-center">Vai trò</th>
                        <th class="pb-3 px-3 text-right">Còn nợ</th>
                        <th class="pb-3 px-3 text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 text-xs font-semibold">
                    @foreach($members as $m)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition">
                            <!-- Member Name & Avatar -->
                            <td class="py-3.5 px-3">
                                <div class="flex items-center space-x-3">
                                    <div class="w-9 h-9 rounded-full bg-emerald-700 text-white font-black text-xs flex items-center justify-center flex-shrink-0 overflow-hidden shadow-sm">
                                        @if($m->avatar && \Illuminate\Support\Str::startsWith($m->avatar, ['http://', 'https://', '/uploads/']))
                                            <img src="{{ $m->avatar }}" alt="{{ $m->name }}" class="w-full h-full object-cover">
                                        @else
                                            {{ $m->avatar ?? substr($m->name, 0, 2) }}
                                        @endif
                                    </div>
                                    <div>
                                        <p class="font-extrabold text-slate-900 dark:text-white text-sm">{{ $m->name }}</p>
                                        <p class="text-[10px] text-slate-400">ID #{{ $m->id }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Username Badge -->
                            <td class="py-3.5 px-3">
                                <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-700 text-emerald-600 dark:text-emerald-400 font-black text-xs rounded-lg font-mono border border-slate-200 dark:border-slate-600">
                                    {{ $m->username ?: 'chưa đặt' }}
                                </span>
                            </td>

                            <!-- Email -->
                            <td class="py-3.5 px-3 text-slate-600 dark:text-slate-300">
                                {{ $m->email }}
                            </td>

                            <!-- Role Badge -->
                            <td class="py-3.5 px-3 text-center">
                                @if($m->role === 'admin')
                                    <span class="px-2.5 py-1 bg-amber-500/10 text-amber-600 dark:text-amber-400 font-extrabold text-[11px] rounded-xl border border-amber-500/30">
                                        👑 Admin
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300 font-extrabold text-[11px] rounded-xl">
                                        👤 Member
                                    </span>
                                @endif
                            </td>

                            <!-- Current Debt -->
                            <td class="py-3.5 px-3 text-right font-black {{ $m->current_debt > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-400' }}">
                                {{ number_format($m->current_debt, 0, ',', '.') }}đ
                            </td>

                            <!-- Actions -->
                            <td class="py-3.5 px-3">
                                <div class="flex items-center justify-center space-x-1.5">
                                    <!-- Edit Button -->
                                    <button type="button" @click.stop="editMember = {{ json_encode(['id' => $m->id, 'name' => $m->name, 'username' => $m->username ?: '', 'email' => $m->email, 'role' => $m->role]) }}" class="px-2.5 py-1 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-200 font-bold text-xs rounded-lg transition cursor-pointer">
                                        ✏️ Sửa
                                    </button>

                                    <!-- Reset Password Button -->
                                    <button type="button" @click.stop="showResetModal = {{ json_encode(['id' => $m->id, 'name' => $m->name]) }}" class="px-2.5 py-1 bg-amber-500/10 text-amber-600 dark:text-amber-400 hover:bg-amber-500 hover:text-white font-bold text-xs rounded-lg transition cursor-pointer">
                                        🔑 Reset Mật Khẩu
                                    </button>

                                    <!-- Delete Button -->
                                    @if($m->id !== auth()->id())
                                        <button type="button" @click.stop="showDeleteMemberModal = {{ json_encode(['id' => $m->id, 'name' => $m->name]) }}" class="px-2 py-1 bg-rose-50 dark:bg-rose-900/30 text-rose-600 hover:bg-rose-600 hover:text-white font-bold text-xs rounded-lg transition cursor-pointer" title="Xóa tài khoản">
                                            🗑️
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- CREATE MEMBER MODAL -->
    <div x-show="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-cloak x-transition>
        <div @click.away="showCreateModal = false" class="bg-white dark:bg-slate-800 rounded-3xl p-6 w-full max-w-md shadow-2xl border border-slate-100 dark:border-slate-700">
            <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-100 dark:border-slate-700">
                <h3 class="text-lg font-black text-slate-900 dark:text-white">➕ Thêm Tài Khoản Thành Viên Mới</h3>
                <button type="button" @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
            </div>

            <form action="{{ route('members.store') }}" method="POST" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Họ và Tên</label>
                    <input type="text" name="name" required placeholder="VD: Trần Văn Nam" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-bold text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Username (Tên đăng nhập)</label>
                        <input type="text" name="username" required placeholder="tvn" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-mono font-bold text-emerald-600 dark:text-emerald-400 outline-none focus:ring-2 focus:ring-emerald-500 lowercase">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Mật khẩu khởi tạo</label>
                        <input type="text" name="password" value="1234" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-bold text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Email</label>
                    <input type="email" name="email" required placeholder="nam.tv@weamis.com" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-bold text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Vai Trò</label>
                    <select name="role" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-bold text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="member">👤 Member (Thành viên)</option>
                        <option value="admin">👑 Admin (Quản trị)</option>
                    </select>
                </div>

                <div class="pt-3 flex justify-end space-x-2">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-xl">Hủy</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-md">Tạo Tài Khoản</button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT MEMBER MODAL -->
    <template x-if="editMember">
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
            <div @click.away="editMember = null" class="bg-white dark:bg-slate-800 rounded-3xl p-6 w-full max-w-md shadow-2xl border border-slate-100 dark:border-slate-700">
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-100 dark:border-slate-700">
                    <h3 class="text-lg font-black text-slate-900 dark:text-white">✏️ Cập Nhật Thông Tin: <span class="text-emerald-600" x-text="editMember.name"></span></h3>
                    <button type="button" @click="editMember = null" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
                </div>

                <form :action="'/members/' + editMember.id" method="POST" class="space-y-3.5">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Họ và Tên</label>
                        <input type="text" name="name" x-model="editMember.name" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-bold text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Username (Tên đăng nhập)</label>
                            <input type="text" name="username" x-model="editMember.username" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-mono font-bold text-emerald-600 dark:text-emerald-400 outline-none focus:ring-2 focus:ring-emerald-500 lowercase">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Email</label>
                            <input type="email" name="email" x-model="editMember.email" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-bold text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Vai Trò</label>
                        <select name="role" x-model="editMember.role" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-xs font-bold text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="member">👤 Member (Thành viên)</option>
                            <option value="admin">👑 Admin (Quản trị)</option>
                        </select>
                    </div>

                    <div class="pt-3 flex justify-end space-x-2">
                        <button type="button" @click="editMember = null" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-xl">Hủy</button>
                        <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-md">Lưu Thay Đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- RESET PASSWORD MODAL -->
    <template x-if="showResetModal">
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
            <div @click.away="showResetModal = null" class="bg-white dark:bg-slate-800 rounded-3xl p-6 w-full max-w-md shadow-2xl border border-slate-100 dark:border-slate-700">
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-100 dark:border-slate-700">
                    <h3 class="text-lg font-black text-slate-900 dark:text-white">🔑 Reset Mật Khẩu Cho: <span class="text-amber-500" x-text="showResetModal.name"></span></h3>
                    <button type="button" @click="showResetModal = null" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
                </div>

                <form :action="'/members/' + showResetModal.id + '/reset-password'" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nhập Mật Khẩu Mới Cho Thành Viên</label>
                        <input type="text" name="password" value="1234" required placeholder="1234" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-700 text-sm font-bold text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-amber-500">
                        <p class="text-[10px] text-slate-400 mt-1 font-medium">Gợi ý: Đặt mặc định là <code class="text-amber-500 font-bold">1234</code> để thành viên dễ đăng nhập lại.</p>
                    </div>

                    <div class="pt-2 flex justify-end space-x-2">
                        <button type="button" @click="showResetModal = null" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-xl">Hủy</button>
                        <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white font-extrabold text-xs rounded-xl shadow-md">Xác Nhận Reset Mật Khẩu</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- Delete Member Confirmation Modal -->
    <template x-if="showDeleteMemberModal">
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div @click.away="showDeleteMemberModal = null" class="bg-white dark:bg-slate-800 rounded-3xl max-w-md w-full p-6 border border-slate-200 dark:border-slate-700 shadow-2xl space-y-5">
                <div class="flex items-center space-x-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-rose-500/10 text-rose-600 dark:text-rose-400 font-black text-2xl flex items-center justify-center flex-shrink-0">
                        🗑️
                    </div>
                    <div>
                        <h4 class="text-base font-black text-slate-900 dark:text-white">Xác Nhận Xóa Tài Khoản</h4>
                        <p class="text-xs font-bold text-slate-400" x-text="showDeleteMemberModal.name"></p>
                    </div>
                </div>

                <p class="text-xs font-semibold text-slate-600 dark:text-slate-300 leading-relaxed">
                    Bạn có chắc chắn muốn xóa tài khoản thành viên này? Hành động này sẽ cập nhật và lưu trữ trong CSDL.
                </p>

                <div class="flex items-center justify-end space-x-3 pt-2">
                    <button type="button" @click="showDeleteMemberModal = null" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-300 font-extrabold text-xs rounded-xl transition cursor-pointer">Hủy bỏ</button>
                    <form :action="'/members/' + showDeleteMemberModal.id" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-black text-xs rounded-xl shadow-md transition cursor-pointer">🗑️ Xác Nhận Xóa</button>
                    </form>
                </div>
            </div>
        </div>
    </template>

</div>
@endsection
