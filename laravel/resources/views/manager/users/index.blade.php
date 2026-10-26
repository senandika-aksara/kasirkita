<x-layouts.app title="Kelola Pengguna" heading="Kelola Pengguna">

    <div x-data="userManager()">

        <x-card padding="p-0">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line-2 p-5">
                <h2 class="text-base font-bold tracking-tight">Kelola Data Pengguna Sistem</h2>
                <x-button type="button" variant="nav" size="lg" @click="openCreate()">
                    <x-icon name="plus" class="h-5 w-5" />
                    Tambah Pengguna Baru
                </x-button>
            </div>

            <form method="GET" class="flex flex-wrap items-end gap-3 border-b border-line-2 bg-surface-2 p-4">
                <div class="min-w-56 flex-1">
                    <x-input label="Cari pengguna" name="cari" type="search"
                             value="{{ $search }}" placeholder="Contoh: Nurul Azizah" />
                </div>
                <x-button type="submit" variant="nav" size="md">
                    <x-icon name="search" class="h-4 w-4" />
                    Cari
                </x-button>
                <x-button :href="route('manager.users.index')" variant="secondary" size="md">Reset</x-button>
            </form>

            @if ($users->isEmpty())
                <x-empty title="Pengguna tidak ditemukan"
                         message="Tidak ada pengguna yang cocok dengan pencarian." />
            @else
                <div class="overflow-x-auto scrollbar-slim">
                    <table class="w-full min-w-[820px] text-sm">
                        <caption class="sr-only">Daftar pengguna sistem</caption>
                        <thead>
                            <tr class="border-b border-line bg-surface-2 text-left text-[11px] font-bold uppercase tracking-wider text-muted">
                                <th scope="col" class="px-4 py-3">Foto</th>
                                <th scope="col" class="px-4 py-3">Nama Lengkap</th>
                                <th scope="col" class="px-4 py-3">Username</th>
                                <th scope="col" class="px-4 py-3">Level Akses</th>
                                <th scope="col" class="px-4 py-3">Status</th>
                                <th scope="col" class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line-2">
                            @foreach ($users as $user)
                                <tr class="transition hover:bg-surface-2">
                                    <td class="px-4 py-2"><x-avatar :user="$user" class="h-11 w-11" /></td>
                                    <th scope="row" class="px-4 py-2 text-left font-semibold">
                                        {{ $user->name }}
                                        @if ($user->is(auth()->user()))
                                            <span class="ml-1 rounded bg-nav-100 px-1.5 py-0.5 text-[10px] font-bold uppercase text-nav-700">
                                                Anda
                                            </span>
                                        @endif
                                    </th>
                                    <td class="px-4 py-2 text-ink-2">{{ $user->username }}</td>
                                    <td class="px-4 py-2">
                                        <span class="inline-flex rounded-md border px-2 py-1 text-xs font-bold uppercase {{ $user->role->badgeClass() }}">
                                            {{ $user->role->label() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2"><x-status-badge :status="$user->status" /></td>
                                    <td class="px-4 py-2">
                                        <div class="flex flex-wrap justify-end gap-1.5">
                                            <x-button type="button" variant="soft" size="sm"
                                                      @click="openDetail('{{ route('manager.users.show', $user) }}')">
                                                <x-icon name="search" class="h-4 w-4" />
                                                Detail
                                            </x-button>
                                            <x-button type="button" variant="soft-brand" size="sm"
                                                      @click="openEdit('{{ route('manager.users.show', $user) }}')">
                                                <x-icon name="pencil" class="h-4 w-4" />
                                                Ubah
                                            </x-button>
                                            <x-button type="button" variant="outline-danger" size="sm"
                                                      @click="openDelete('{{ route('manager.users.show', $user) }}')">
                                                <x-icon name="trash" class="h-4 w-4" />
                                                Hapus
                                            </x-button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($users->hasPages())
                    <div class="border-t border-line-2 p-4">{{ $users->links() }}</div>
                @endif
            @endif
        </x-card>

        {{-- ============ POP-UP TAMBAH / UBAH PENGGUNA ============ --}}
        <x-modal name="form-pengguna" maxWidth="max-w-2xl">
            <x-slot:heading>
                <span x-text="editing ? 'Ubah Pengguna' : 'Tambah Pengguna Baru'">Tambah Pengguna Baru</span>
            </x-slot:heading>

            <form method="POST"
                  :action="editing ? form.update_url : '{{ route('manager.users.store') }}'"
                  enctype="multipart/form-data"
                  @submit="submitting = true"
                  class="space-y-4">
                @csrf
                <template x-if="editing">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="grid gap-4 sm:grid-cols-[150px_minmax(0,1fr)]">
                    <div class="space-y-2">
                        <span class="block text-xs font-semibold text-ink-2">Foto Pengguna</span>

                        <label for="user-photo"
                               class="flex aspect-square w-full cursor-pointer items-center justify-center overflow-hidden rounded-lg border-2 border-dashed border-line bg-bg transition hover:border-nav-600">
                            <template x-if="preview">
                                <img :src="preview" alt="Pratinjau foto pengguna" class="h-full w-full object-cover">
                            </template>
                            <template x-if="!preview">
                                <x-icon name="user" class="h-12 w-12 text-line" />
                            </template>
                        </label>

                        <input id="user-photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp"
                               class="block w-full text-xs text-ink-2 file:mr-2 file:min-h-10 file:cursor-pointer file:rounded-lg file:border-0 file:bg-nav-600 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-nav-700"
                               @change="showPreview($event)">
                        <p class="text-xs text-muted">JPG/PNG, maks. 1 MB.</p>
                    </div>

                    <div class="space-y-3">
                        <div class="space-y-1">
                            <label for="user-name" class="block text-xs font-semibold text-ink-2">
                                Nama Lengkap
                            </label>
                            <input id="user-name" name="name" type="text" required maxlength="100" autofocus
                                   x-model="form.name" placeholder="Contoh: Rizki Setiawan"
                                   class="block min-h-11 w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm focus:outline-2 focus:outline-nav-600">
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="space-y-1">
                                <label for="user-username" class="block text-xs font-semibold text-ink-2">
                                    Username
                                </label>
                                <input id="user-username" name="username" type="text" required maxlength="50"
                                       x-model="form.username" placeholder="rizki.ksr" autocomplete="off"
                                       class="block min-h-11 w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm focus:outline-2 focus:outline-nav-600">
                            </div>

                            <div class="space-y-1">
                                <label for="user-role" class="block text-xs font-semibold text-ink-2">
                                    Level Akses
                                </label>
                                <select id="user-role" name="role" required x-model="form.role"
                                        class="block min-h-11 w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm focus:outline-2 focus:outline-nav-600">
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->value }}">{{ Str::upper($role->label()) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <fieldset>
                            <legend class="mb-1 block text-xs font-semibold text-ink-2">Status</legend>
                            <div class="flex gap-4">
                                <label class="flex min-h-11 cursor-pointer items-center gap-2 text-sm font-semibold">
                                    <input type="radio" name="status" value="aktif" x-model="form.status"
                                           class="h-4 w-4 border-line text-brand-600 focus:ring-brand-600">
                                    Aktif
                                </label>
                                <label class="flex min-h-11 cursor-pointer items-center gap-2 text-sm font-semibold">
                                    <input type="radio" name="status" value="non_aktif" x-model="form.status"
                                           class="h-4 w-4 border-line text-warn-700 focus:ring-warn-700">
                                    Non-Aktif
                                </label>
                            </div>
                        </fieldset>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="space-y-1">
                                <label for="user-password" class="block text-xs font-semibold text-ink-2">
                                    Password
                                </label>
                                <input id="user-password" name="password" type="password" minlength="8"
                                       :required="!editing" autocomplete="new-password"
                                       :placeholder="editing ? 'Kosongkan bila tidak diubah' : 'Minimal 8 karakter'"
                                       class="block min-h-11 w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm focus:outline-2 focus:outline-nav-600">
                            </div>

                            <div class="space-y-1">
                                <label for="user-password-confirm" class="block text-xs font-semibold text-ink-2">
                                    Ulangi Password
                                </label>
                                <input id="user-password-confirm" name="password_confirmation" type="password" minlength="8"
                                       :required="!editing" autocomplete="new-password"
                                       placeholder="Ketik ulang password"
                                       class="block min-h-11 w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm focus:outline-2 focus:outline-nav-600">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-nav-600/25 bg-nav-100 px-4 py-3 text-sm text-nav-800">
                    <p class="font-bold uppercase tracking-wide">Informasi Level Akses</p>
                    <ul class="mt-1 list-inside list-disc space-y-0.5">
                        <li><strong>Superadmin</strong> — akses penuh termasuk mengelola seluruh akun pengguna.</li>
                        <li><strong>Manajer</strong> — dashboard, riwayat transaksi, kelola menu dan pengguna.</li>
                        <li><strong>Kasir</strong> — hanya mencatat transaksi dan menandainya lunas.</li>
                    </ul>
                </div>

                <div class="flex justify-end gap-2">
                    <x-button type="button" variant="outline-danger" @click="$dispatch('close-modal')">
                        <x-icon name="x" class="h-4 w-4" />
                        Batal
                    </x-button>
                    <x-button type="submit" variant="primary" x-bind:disabled="submitting">
                        <span x-text="submitting ? 'Menyimpan…' : 'Simpan Pengguna'">Simpan Pengguna</span>
                    </x-button>
                </div>
            </form>
        </x-modal>

        {{-- ============ POP-UP DETAIL PENGGUNA ============ --}}
        <x-modal name="detail-pengguna" maxWidth="max-w-lg">
            <x-slot:heading>
                <span x-text="detail ? 'Detail Pengguna: ' + detail.name : 'Memuat…'">Detail Pengguna</span>
            </x-slot:heading>

            <template x-if="detail">
                <div class="flex flex-col items-center gap-4 sm:flex-row sm:items-start">
                    <div class="flex h-28 w-28 shrink-0 items-center justify-center overflow-hidden rounded-full border border-line bg-nav-100">
                        <template x-if="detail.photo">
                            <img :src="detail.photo" :alt="'Foto ' + detail.name" class="h-full w-full object-cover">
                        </template>
                        <template x-if="!detail.photo">
                            <span class="text-3xl font-extrabold text-nav-700" x-text="detail.initials"></span>
                        </template>
                    </div>

                    <dl class="flex-1 space-y-2 text-sm">
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-muted">Nama Lengkap</dt>
                            <dd class="font-bold" x-text="detail.name"></dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-muted">Username</dt>
                            <dd class="font-semibold" x-text="detail.username"></dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-muted">Level Akses</dt>
                            <dd class="font-semibold" x-text="detail.role_label"></dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-muted">Status</dt>
                            <dd>
                                <span class="inline-flex rounded-md border px-2 py-1 text-xs font-bold uppercase"
                                      :class="detail.status === 'aktif'
                                          ? 'border-brand-700/20 bg-brand-100 text-brand-700'
                                          : 'border-warn-700/20 bg-warn-100 text-warn-700'"
                                      x-text="detail.status_label"></span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-muted">Terdaftar Sejak</dt>
                            <dd class="tabular" x-text="detail.created_at"></dd>
                        </div>
                    </dl>
                </div>
            </template>

            <x-slot:footer>
                <x-button type="button" variant="secondary" @click="$dispatch('close-modal')">Tutup</x-button>
                <x-button type="button" variant="primary" @click="openEditFrom(detail)">
                    <x-icon name="pencil" class="h-4 w-4" />
                    Ubah
                </x-button>
            </x-slot:footer>
        </x-modal>

        {{-- ============ POP-UP KONFIRMASI HAPUS ============ --}}
        <x-modal name="hapus-pengguna" maxWidth="max-w-lg" headerClass="bg-danger-100 text-danger-700">
            <x-slot:heading>Konfirmasi Hapus Pengguna</x-slot:heading>

            <template x-if="detail">
                <div class="space-y-4 text-center">
                    <p class="text-lg font-semibold">Apakah Anda yakin ingin menghapus pengguna:</p>
                    <p class="text-2xl font-extrabold">
                        <span x-text="detail.name"></span>
                        <span class="block text-base font-semibold text-muted" x-text="detail.username + ' · ' + detail.role_label"></span>
                    </p>

                    <template x-if="detail.can_delete">
                        <p class="flex items-center justify-center gap-2 text-base font-bold text-danger-700">
                            <x-icon name="warning" class="h-5 w-5" />
                            Tindakan ini tidak dapat dibatalkan.
                        </p>
                    </template>

                    <template x-if="!detail.can_delete">
                        <p class="rounded-lg border border-warn-700/30 bg-warn-100 px-4 py-3 text-left text-sm text-warn-700">
                            Pengguna ini <strong>tidak dapat dihapus</strong> — akun Anda sendiri, akun superadmin,
                            atau sudah memiliki transaksi tercatat. Ubah statusnya menjadi
                            <strong>Non-Aktif</strong> agar tidak bisa login.
                        </p>
                    </template>
                </div>
            </template>

            <x-slot:footer>
                <x-button type="button" variant="secondary" @click="$dispatch('close-modal')">
                    <x-icon name="x" class="h-4 w-4" />
                    Batal
                </x-button>
                <form method="POST" :action="detail?.delete_url" x-cloak x-show="detail?.can_delete" @submit="submitting = true">
                    @csrf
                    @method('DELETE')
                    <x-button type="submit" variant="danger" x-bind:disabled="submitting">
                        <x-icon name="trash" class="h-4 w-4" />
                        Hapus
                    </x-button>
                </form>
            </x-slot:footer>
        </x-modal>
    </div>

    <script>
        function userManager() {
            const blankForm = () => ({
                name: '',
                username: '',
                role: '{{ $roles[count($roles) - 1]->value }}',
                status: 'aktif',
                update_url: '',
            });

            return {
                editing: false,
                submitting: false,
                preview: null,
                detail: null,
                form: blankForm(),

                openCreate() {
                    this.editing = false;
                    this.submitting = false;
                    this.preview = null;
                    this.form = blankForm();
                    this.$dispatch('open-modal', { name: 'form-pengguna' });
                },

                async openEdit(url) {
                    const user = await this.fetchUser(url);
                    if (user) this.openEditFrom(user);
                },

                openEditFrom(user) {
                    if (!user) return;

                    // Tutup pop-up detail dulu agar tidak menumpuk di atas form.
                    this.$dispatch('close-modal');

                    this.editing = true;
                    this.submitting = false;
                    this.preview = user.photo;
                    this.form = {
                        name: user.name,
                        username: user.username,
                        role: user.role,
                        status: user.status,
                        update_url: user.update_url,
                    };
                    this.$dispatch('open-modal', { name: 'form-pengguna' });
                },

                async openDetail(url) {
                    this.detail = null;
                    this.$dispatch('open-modal', { name: 'detail-pengguna' });
                    this.detail = await this.fetchUser(url);
                },

                async openDelete(url) {
                    this.detail = null;
                    this.submitting = false;
                    this.$dispatch('open-modal', { name: 'hapus-pengguna' });
                    this.detail = await this.fetchUser(url);
                },

                async fetchUser(url) {
                    try {
                        const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
                        if (!response.ok) throw new Error(response.statusText);

                        return await response.json();
                    } catch (error) {
                        console.error('Gagal memuat data pengguna:', error);

                        return null;
                    }
                },

                showPreview(event) {
                    const file = event.target.files?.[0];
                    this.preview = file ? URL.createObjectURL(file) : this.preview;
                },
            };
        }
    </script>

</x-layouts.app>
