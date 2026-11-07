<x-layouts.app title="Daftar Menu" heading="Daftar Menu">

    <div x-data="menuManager()" class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_280px]">

        <x-card padding="p-0" class="min-w-0">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line-2 p-5">
                <h2 class="text-base font-bold tracking-tight">Daftar Menu Kuliner</h2>
                <x-button type="button" variant="primary" size="lg" @click="openCreate()">
                    <x-icon name="plus" class="h-5 w-5" />
                    Tambah Menu
                </x-button>
            </div>

            <form method="GET" class="grid gap-3 border-b border-line-2 bg-surface-2 p-4 sm:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_auto]">
                <x-input label="Cari menu" name="cari" type="search"
                         value="{{ $filters['cari'] }}" placeholder="Contoh: Nasi Goreng" />

                <x-select label="Kategori" name="kategori">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected($filters['kategori'] == $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </x-select>

                <div class="flex flex-wrap items-end gap-2">
                    <x-button type="submit" variant="nav" size="md">
                        <x-icon name="search" class="h-4 w-4" />
                        Cari
                    </x-button>
                    <x-button :href="route('manager.menus.index')" variant="secondary" size="md">Reset</x-button>
                </div>
            </form>

            @if ($menus->isEmpty())
                <x-empty title="Menu tidak ditemukan"
                         message="Tidak ada menu yang cocok dengan pencarian. Coba kata kunci lain atau tambahkan menu baru." />
            @else
                <div class="overflow-x-auto scrollbar-slim">
                    <table class="w-full min-w-[820px] text-sm">
                        <caption class="sr-only">Daftar menu kuliner</caption>
                        <thead>
                            <tr class="border-b border-line bg-surface-2 text-left text-[11px] font-bold uppercase tracking-wider text-muted">
                                <th scope="col" class="px-4 py-3">Foto</th>
                                <th scope="col" class="px-4 py-3">Nama Menu</th>
                                <th scope="col" class="px-4 py-3">Kategori</th>
                                <th scope="col" class="px-4 py-3 text-right">Harga</th>
                                <th scope="col" class="px-4 py-3">Status</th>
                                <th scope="col" class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line-2">
                            @foreach ($menus as $menu)
                                <tr class="transition hover:bg-surface-2">
                                    <td class="px-4 py-2">
                                        @if ($menu->imageUrl())
                                            <img src="{{ $menu->imageUrl() }}" alt="Foto {{ $menu->name }}"
                                                 class="h-12 w-16 rounded border border-line object-cover">
                                        @else
                                            <span class="flex h-12 w-16 items-center justify-center rounded border border-line bg-bg text-muted">
                                                <x-icon name="camera" class="h-5 w-5" />
                                            </span>
                                        @endif
                                    </td>
                                    <th scope="row" class="px-4 py-2 text-left font-semibold">
                                        {{ $menu->name }}
                                        <span class="block text-xs font-normal text-muted">{{ $menu->code }}</span>
                                    </th>
                                    <td class="px-4 py-2">{{ $menu->category?->name ?? '—' }}</td>
                                    <td class="px-4 py-2 text-right font-bold whitespace-nowrap">
                                        <x-money :value="$menu->price" />
                                    </td>
                                    <td class="px-4 py-2"><x-status-badge :status="$menu->status" /></td>
                                    <td class="px-4 py-2">
                                        <div class="flex flex-wrap justify-end gap-1.5">
                                            <x-button type="button" variant="soft" size="sm"
                                                      @click="openDetail('{{ route('manager.menus.show', $menu) }}')">
                                                <x-icon name="search" class="h-4 w-4" />
                                                Detail
                                            </x-button>
                                            <x-button type="button" variant="soft-brand" size="sm"
                                                      @click="openEdit('{{ route('manager.menus.show', $menu) }}')">
                                                <x-icon name="pencil" class="h-4 w-4" />
                                                Ubah
                                            </x-button>
                                            <x-button type="button" variant="outline-danger" size="sm"
                                                      @click="openDelete('{{ route('manager.menus.show', $menu) }}')">
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

                @if ($menus->hasPages())
                    <div class="border-t border-line-2 p-4">{{ $menus->links() }}</div>
                @endif
            @endif
        </x-card>

        {{-- Ringkasan jumlah menu per kategori --}}
        <x-card title="Ringkasan Menu" padding="p-0" class="h-fit">
            <dl class="divide-y divide-line-2">
                <div class="flex items-center justify-between px-4 py-2.5">
                    <dt class="text-sm font-bold">Total Menu</dt>
                    <dd class="tabular text-lg font-extrabold text-nav-700">{{ $totalMenus }}</dd>
                </div>
                @foreach ($categories as $category)
                    <div class="flex items-center justify-between bg-bg/50 px-4 py-2.5">
                        <dt class="text-sm font-medium text-ink-2">{{ $category->name }}</dt>
                        <dd class="tabular text-base font-bold">{{ $category->menus_count }}</dd>
                    </div>
                @endforeach
            </dl>
        </x-card>

        {{-- ============ POP-UP TAMBAH / UBAH MENU ============ --}}
        <x-modal name="form-menu" maxWidth="max-w-3xl">
            <x-slot:heading>
                <span x-text="editing ? 'Ubah Menu' : 'Tambah Menu Baru'">Tambah Menu Baru</span>
            </x-slot:heading>

            <form method="POST"
                  :action="editing ? form.update_url : '{{ route('manager.menus.store') }}'"
                  enctype="multipart/form-data"
                  @submit="submitting = true"
                  class="grid gap-4 sm:grid-cols-2">
                @csrf
                <template x-if="editing">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="space-y-4">
                    <div class="space-y-1">
                        <label for="menu-name" class="block text-xs font-semibold text-ink-2">
                            Nama Menu
                        </label>
                        <input id="menu-name" name="name" type="text" required maxlength="100" autofocus
                               x-model="form.name"
                               placeholder="Contoh: Nasi Goreng Spesial"
                               class="block min-h-11 w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm focus:outline-2 focus:outline-nav-600">
                    </div>

                    <div class="space-y-1">
                        <label for="menu-category" class="block text-xs font-semibold text-ink-2">
                            Kategori Menu
                        </label>
                        <select id="menu-category" name="category_id" required x-model="form.category_id"
                                class="block min-h-11 w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm focus:outline-2 focus:outline-nav-600">
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ Str::upper($category->name) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label for="menu-price" class="block text-xs font-semibold text-ink-2">
                            Harga (Rp)
                        </label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm font-bold text-muted">Rp</span>
                            <input id="menu-price" name="price" type="text" inputmode="numeric" required
                                   x-model="form.price"
                                   placeholder="35000"
                                   class="tabular block min-h-11 w-full rounded-lg border border-line bg-surface py-2 pr-3 pl-9 text-sm font-bold focus:outline-2 focus:outline-nav-600">
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label for="menu-status" class="block text-xs font-semibold text-ink-2">
                            Status
                        </label>
                        <select id="menu-status" name="status" required x-model="form.status"
                                class="block min-h-11 w-full rounded-lg border border-line bg-surface px-3 py-2 text-sm focus:outline-2 focus:outline-nav-600">
                            <option value="tersedia">TERSEDIA</option>
                            <option value="habis">HABIS</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-2">
                    <span class="block text-xs font-semibold text-ink-2">Unggah Foto Menu</span>

                    <label for="menu-image"
                           class="flex aspect-4/3 w-full cursor-pointer items-center justify-center overflow-hidden rounded-lg border-2 border-dashed border-line bg-bg transition hover:border-nav-600">
                        <template x-if="preview">
                            <img :src="preview" alt="Pratinjau foto menu" class="h-full w-full object-cover">
                        </template>
                        <template x-if="!preview">
                            <span class="flex flex-col items-center gap-1 text-muted">
                                <x-icon name="camera" class="h-10 w-10" />
                                <span class="text-xs font-semibold">Klik untuk pilih file</span>
                            </span>
                        </template>
                    </label>

                    <input id="menu-image" name="image" type="file" accept="image/jpeg,image/png,image/webp"
                           class="block w-full text-xs text-ink-2 file:mr-2 file:min-h-11 file:cursor-pointer file:rounded-lg file:border-0 file:bg-nav-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-nav-700"
                           @change="showPreview($event)">

                    <p class="text-xs text-muted">Format JPG, PNG, atau WEBP. Ukuran maksimal 1 MB.</p>
                </div>

                <div class="flex justify-end gap-2 sm:col-span-2">
                    <x-button type="button" variant="outline-danger" @click="$dispatch('close-modal')">Batal</x-button>
                    <x-button type="submit" variant="primary" x-bind:disabled="submitting">
                        <span x-text="submitting ? 'Menyimpan…' : 'Simpan'">Simpan</span>
                    </x-button>
                </div>
            </form>
        </x-modal>

        {{-- ============ POP-UP DETAIL MENU ============ --}}
        <x-modal name="detail-menu" maxWidth="max-w-2xl">
            <x-slot:heading>
                <span x-text="detail ? 'Detail Menu: ' + detail.name : 'Memuat…'">Detail Menu</span>
            </x-slot:heading>

            <template x-if="detail">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="flex aspect-4/3 items-center justify-center overflow-hidden rounded-xl border border-line bg-surface-2">
                        <template x-if="detail.image">
                            <img :src="detail.image" :alt="'Foto ' + detail.name" class="h-full w-full object-cover">
                        </template>
                        <template x-if="!detail.image">
                            <x-icon name="camera" class="h-12 w-12 text-line" />
                        </template>
                    </div>

                    <dl class="space-y-2 text-sm">
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-muted">Menu ID</dt>
                            <dd class="tabular font-bold" x-text="detail.code"></dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-muted">Nama Menu</dt>
                            <dd class="font-bold" x-text="detail.name"></dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-muted">Kategori</dt>
                            <dd class="font-semibold" x-text="detail.category"></dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-muted">Harga Jual</dt>
                            <dd class="tabular text-lg font-extrabold text-brand-600" x-text="detail.price_formatted"></dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-muted">Status</dt>
                            <dd>
                                <span class="inline-flex rounded-md border px-2 py-1 text-xs font-bold uppercase"
                                      :class="detail.status === 'tersedia'
                                          ? 'border-brand-700/20 bg-brand-100 text-brand-700'
                                          : 'border-warn-700/20 bg-warn-100 text-warn-700'"
                                      x-text="detail.status_label"></span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-muted">Terakhir Diedit</dt>
                            <dd class="tabular" x-text="detail.updated_at"></dd>
                            <dd class="text-xs text-muted" x-text="'oleh ' + detail.updated_by"></dd>
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
        <x-modal name="hapus-menu" maxWidth="max-w-lg" headerClass="bg-danger-100 text-danger-700">
            <x-slot:heading>Konfirmasi Hapus Menu</x-slot:heading>

            <template x-if="detail">
                <div class="space-y-4 text-center">
                    <p class="text-lg font-semibold">Apakah Anda yakin ingin menghapus menu:</p>
                    <p class="text-2xl font-extrabold">
                        <span x-text="detail.code"></span> | <span x-text="detail.name"></span>
                    </p>

                    <template x-if="detail.can_delete">
                        <p class="flex items-center justify-center gap-2 text-base font-bold text-danger-700">
                            <x-icon name="warning" class="h-5 w-5" />
                            Tindakan ini tidak dapat dibatalkan.
                        </p>
                    </template>

                    <template x-if="!detail.can_delete">
                        <p class="rounded-lg border border-warn-700/30 bg-warn-100 px-4 py-3 text-left text-sm text-warn-700">
                            Menu ini sudah pernah terjual, sehingga <strong>tidak dapat dihapus</strong> agar riwayat
                            transaksi tetap utuh. Ubah statusnya menjadi <strong>Habis</strong> bila tidak dijual lagi.
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
        function menuManager() {
            const blankForm = () => ({
                name: '',
                category_id: '{{ $categories->first()?->id }}',
                price: '',
                status: 'tersedia',
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
                    this.form = blankForm();
                    this.preview = null;
                    this.submitting = false;
                    this.$dispatch('open-modal', { name: 'form-menu' });
                },

                async openEdit(url) {
                    const menu = await this.fetchMenu(url);
                    if (menu) this.openEditFrom(menu);
                },

                openEditFrom(menu) {
                    if (!menu) return;

                    // Tutup pop-up detail dulu agar tidak menumpuk di atas form.
                    this.$dispatch('close-modal');

                    this.editing = true;
                    this.submitting = false;
                    this.preview = menu.image;
                    this.form = {
                        name: menu.name,
                        category_id: String(menu.category_id),
                        price: String(menu.price),
                        status: menu.status,
                        update_url: menu.update_url,
                    };
                    this.$dispatch('open-modal', { name: 'form-menu' });
                },

                async openDetail(url) {
                    this.detail = null;
                    this.$dispatch('open-modal', { name: 'detail-menu' });
                    this.detail = await this.fetchMenu(url);
                },

                async openDelete(url) {
                    this.detail = null;
                    this.submitting = false;
                    this.$dispatch('open-modal', { name: 'hapus-menu' });
                    this.detail = await this.fetchMenu(url);
                },

                async fetchMenu(url) {
                    try {
                        const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
                        if (!response.ok) throw new Error(response.statusText);

                        return await response.json();
                    } catch (error) {
                        console.error('Gagal memuat data menu:', error);

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
