<x-layouts.app title="Transaksi Baru" heading="Transaksi Baru">

    <form method="POST"
          action="{{ route('cashier.transactions.store') }}"
          x-data="pos({{ Js::from($menus) }})"
          @submit="submitting = true"
          class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)_minmax(0,0.85fr)]">
        @csrf

        {{-- Baris item dikirim sebagai input tersembunyi agar total dihitung ulang di server --}}
        <template x-for="(line, index) in cart" :key="line.id">
            {{-- display:none wajib: tanpa ini tiap baris keranjang jadi sel grid kosong dan menggeser kolom --}}
            <span class="hidden">
                <input type="hidden" :name="`items[${index}][menu_id]`" :value="line.id">
                <input type="hidden" :name="`items[${index}][qty]`" :value="line.qty">
            </span>
        </template>
        <input type="hidden" name="payment_status" :value="paid ? 'lunas' : 'belum_bayar'">

        {{-- ============ KOLOM 1: KATALOG MENU ============ --}}
        <x-card padding="p-3" class="flex min-w-0 flex-col lg:max-h-[calc(100vh-7rem)]">
            <div class="relative mb-3">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-muted">
                    <x-icon name="search" class="h-5 w-5" />
                </span>
                <label for="cari-menu" class="sr-only">Cari menu</label>
                <input id="cari-menu" type="search" x-model="search"
                       placeholder="Cari menu (contoh: Nasi Goreng)"
                       class="block min-h-11 w-full rounded-lg border border-line bg-surface py-2 pr-3 pl-10 text-sm focus:outline-2 focus:outline-nav-600">
            </div>

            <div class="mb-3 flex gap-1 overflow-x-auto border-b border-line scrollbar-slim" role="tablist" aria-label="Kategori menu">
                <button type="button" role="tab"
                        @click="category = 0"
                        :aria-selected="category === 0"
                        :class="category === 0 ? 'border-nav-700 text-nav-700' : 'border-transparent text-muted hover:text-ink-2'"
                        class="min-h-11 shrink-0 border-b-2 px-3 text-sm font-semibold transition">
                    Semua
                </button>
                @foreach ($categories as $category)
                    <button type="button" role="tab"
                            @click="category = {{ $category->id }}"
                            :aria-selected="category === {{ $category->id }}"
                            :class="category === {{ $category->id }} ? 'border-nav-700 text-nav-700' : 'border-transparent text-muted hover:text-ink-2'"
                            class="min-h-11 shrink-0 border-b-2 px-3 text-sm font-semibold transition">
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>

            <div class="grid flex-1 grid-cols-2 gap-2 overflow-y-auto pr-1 scrollbar-slim">
                <template x-for="menu in filteredMenus" :key="menu.id">
                    <button type="button"
                            @click="add(menu)"
                            :disabled="!menu.available"
                            :class="menu.available
                                ? 'border-line bg-surface hover:border-brand-600 hover:shadow-md'
                                : 'cursor-not-allowed border-line bg-bg opacity-60'"
                            class="flex flex-col overflow-hidden rounded-lg border text-left transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600">
                        <span class="relative flex aspect-4/3 w-full items-center justify-center bg-bg">
                            <template x-if="menu.image">
                                <img :src="menu.image" :alt="menu.name" class="h-full w-full object-cover">
                            </template>
                            <template x-if="!menu.image">
                                <x-icon name="camera" class="h-8 w-8 text-line" />
                            </template>
                            <template x-if="!menu.available">
                                <span class="absolute top-1 right-1 rounded border border-warn-700/30 bg-warn-100 px-1.5 py-0.5 text-[10px] font-bold uppercase text-warn-700">
                                    Habis
                                </span>
                            </template>
                        </span>
                        <span class="flex flex-1 flex-col gap-0.5 p-2">
                            <span class="text-[13px] font-semibold leading-snug text-ink" x-text="menu.name"></span>
                            <span class="tabular text-sm font-extrabold text-brand-600" x-text="rupiah(menu.price)"></span>
                        </span>
                    </button>
                </template>

                <template x-if="filteredMenus.length === 0">
                    <p class="col-span-2 py-10 text-center text-sm text-muted">
                        Menu tidak ditemukan. Coba kata kunci lain.
                    </p>
                </template>
            </div>
        </x-card>

        {{-- ============ KOLOM 2: DATA TRANSAKSI + DAFTAR PESANAN ============ --}}
        <div class="flex min-w-0 flex-col gap-3 lg:max-h-[calc(100vh-7rem)]">

            <x-card padding="p-3">
                <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,1.3fr)]">
                    <x-select label="No. Meja" name="table_number">
                        <option value="">Bawa pulang</option>
                        @for ($i = 1; $i <= 20; $i++)
                            <option value="{{ $i }}" @selected(old('table_number') == $i)>Meja {{ $i }}</option>
                        @endfor
                    </x-select>

                    <div class="space-y-1">
                        <span class="block text-xs font-semibold text-ink-2">Tanggal</span>
                        <p class="tabular flex min-h-11 items-center rounded-xl border border-line bg-surface-2 px-3 text-sm text-ink-2">
                            {{ now()->format('d/m/Y') }}
                        </p>
                    </div>

                    <div class="space-y-1">
                        <span class="block text-xs font-semibold text-ink-2">ID Transaksi</span>
                        <p class="tabular flex min-h-11 items-center rounded-xl border border-line bg-surface-2 px-3 text-sm font-bold text-ink">
                            {{ $nextInvoice }}
                        </p>
                    </div>
                </div>
                <p class="mt-2 text-xs text-muted">Tanggal dan ID transaksi diisi otomatis oleh sistem saat disimpan.</p>
            </x-card>

            <x-card title="Daftar Pesanan" padding="p-0" class="flex min-h-0 flex-1 flex-col">
                <div class="min-h-40 flex-1 divide-y divide-line-2 overflow-y-auto scrollbar-slim">
                    <template x-for="(line, index) in cart" :key="line.id">
                        <div class="flex items-start gap-2 p-3 sm:gap-3">
                            <span class="tabular mt-1 hidden w-6 shrink-0 text-xs font-bold text-muted sm:block" x-text="`[${index + 1}]`"></span>

                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-ink" x-text="line.name"></p>
                                <p class="tabular text-xs text-muted" x-text="rupiah(line.price) + ' / porsi'"></p>
                            </div>

                            <div class="flex shrink-0 items-center gap-1">
                                <button type="button" @click="decrease(line)"
                                        :disabled="line.qty <= 1"
                                        class="flex h-9 w-9 items-center justify-center rounded-md border border-line text-lg font-bold text-ink-2 hover:bg-surface-2 disabled:opacity-40"
                                        :aria-label="`Kurangi ${line.name}`">&minus;</button>
                                <span class="tabular w-9 text-center text-sm font-bold" x-text="line.qty"></span>
                                <button type="button" @click="increase(line)"
                                        :disabled="line.qty >= 99"
                                        class="flex h-9 w-9 items-center justify-center rounded-md border border-line text-lg font-bold text-ink-2 hover:bg-surface-2 disabled:opacity-40"
                                        :aria-label="`Tambah ${line.name}`">+</button>
                            </div>

                            <div class="w-20 shrink-0 text-right sm:w-24">
                                <p class="tabular text-sm font-extrabold text-ink" x-text="rupiah(line.price * line.qty)"></p>
                                <button type="button" @click="remove(line)"
                                        class="text-xs font-semibold text-danger-700 hover:underline"
                                        :aria-label="`Hapus ${line.name}`">Hapus</button>
                            </div>
                        </div>
                    </template>

                    <template x-if="cart.length === 0">
                        <div class="flex h-full flex-col items-center justify-center gap-2 p-8 text-center">
                            <x-icon name="inbox" class="h-10 w-10 text-line" />
                            <p class="text-sm font-semibold text-ink-2">Belum ada menu dipilih</p>
                            <p class="text-sm text-muted">Klik menu di sebelah kiri untuk menambahkan ke pesanan.</p>
                        </div>
                    </template>
                </div>

                @error('items')
                    <p class="border-t border-line bg-danger-100 px-3 py-2 text-xs font-medium text-danger-700">{{ $message }}</p>
                @enderror
                @error('items.0.menu_id')
                    <p class="border-t border-line bg-danger-100 px-3 py-2 text-xs font-medium text-danger-700">{{ $message }}</p>
                @enderror
            </x-card>
        </div>

        {{-- ============ KOLOM 3: RINGKASAN & PEMBAYARAN ============ --}}
        <div class="flex min-w-0 flex-col gap-3">

            <x-card title="Transaksi Terbaru" padding="p-3">
                @forelse ($latestTransactions as $latest)
                    <a href="{{ route('cashier.transactions.show', $latest) }}"
                       class="flex min-h-11 items-center justify-between gap-2 border-b border-line py-1.5 text-sm last:border-0 hover:text-nav-700">
                        <span class="tabular font-semibold">
                            {{ $latest->invoice_number }}
                            <span class="font-normal text-muted">
                                ({{ $latest->table_number ? 'Meja '.$latest->table_number : 'Bawa pulang' }})
                            </span>
                        </span>
                        <span @class([
                            'shrink-0 text-xs font-bold uppercase',
                            'text-brand-600' => $latest->isPaid(),
                            'text-danger-700' => ! $latest->isPaid(),
                        ])>{{ $latest->payment_status->label() }}</span>
                    </a>
                @empty
                    <p class="py-2 text-sm text-muted">Belum ada transaksi hari ini.</p>
                @endforelse
            </x-card>

            <div class="rounded-xl border border-nav-600/30 bg-nav-100 p-4">
                <p class="text-[11px] font-bold uppercase tracking-wider text-nav-700">Total Transaksi</p>
                <p class="tabular mt-1 text-4xl font-extrabold text-nav-800" x-text="rupiah(total)">Rp0</p>
                <p class="tabular mt-1 text-xs text-nav-700" x-text="`${itemCount} porsi dalam ${cart.length} baris menu`"></p>

                <hr class="my-3 border-nav-600/20">

                <fieldset>
                    <legend class="mb-2 text-[11px] font-bold uppercase tracking-wider text-nav-700">Status Bayar</legend>
                    <div class="grid grid-cols-2 gap-0 overflow-hidden rounded-lg border border-nav-600/40">
                        <button type="button" @click="paid = false"
                                :class="!paid ? 'bg-danger-600 text-white' : 'bg-surface text-ink-2 hover:bg-surface-2'"
                                :aria-pressed="!paid"
                                class="min-h-11 px-2 text-sm font-bold uppercase transition">
                            Belum Bayar
                        </button>
                        <button type="button" @click="paid = true"
                                :class="paid ? 'bg-nav-700 text-white' : 'bg-surface text-ink-2 hover:bg-surface-2'"
                                :aria-pressed="paid"
                                class="min-h-11 px-2 text-sm font-bold uppercase transition">
                            Sudah Bayar
                        </button>
                    </div>
                </fieldset>

                <div x-cloak x-show="paid" x-transition class="mt-3 space-y-2">
                    <label for="pay_amount" class="block text-[11px] font-bold uppercase tracking-wider text-nav-700">
                        Jumlah Uang
                    </label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm font-bold text-muted">Rp</span>
                        <input id="pay_amount" name="pay_amount" type="text" inputmode="numeric"
                               x-model="payInput"
                               placeholder="Kosongkan bila uang pas"
                               class="tabular block min-h-11 w-full rounded-lg border border-line bg-surface py-2 pr-3 pl-9 text-right text-base font-bold focus:outline-2 focus:outline-nav-600">
                    </div>

                    <div class="flex flex-wrap gap-1">
                        <button type="button" @click="payInput = String(total)"
                                class="min-h-9 rounded-md border border-nav-600/40 bg-surface px-2 text-xs font-bold text-nav-700 hover:bg-nav-100">
                            Uang pas
                        </button>
                        <template x-for="note in quickNotes" :key="note">
                            <button type="button" @click="payInput = String(note)"
                                    class="tabular min-h-9 rounded-md border border-nav-600/40 bg-surface px-2 text-xs font-bold text-nav-700 hover:bg-nav-100"
                                    x-text="rupiah(note)"></button>
                        </template>
                    </div>

                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-nav-700">Uang Kembalian</p>
                        <p class="tabular mt-1 rounded-lg border border-line bg-surface px-3 py-2 text-right text-xl font-extrabold"
                           :class="shortfall > 0 ? 'text-danger-700' : 'text-brand-600'"
                           x-text="shortfall > 0 ? 'Kurang ' + rupiah(shortfall) : rupiah(change)">Rp0</p>
                    </div>

                    <p x-cloak x-show="shortfall > 0"
                       class="rounded-md bg-danger-100 px-3 py-2 text-xs font-medium text-danger-700">
                        Uang yang dibayar kurang dari total transaksi.
                    </p>

                    @error('pay_amount')
                        <p class="rounded-md bg-danger-100 px-3 py-2 text-xs font-medium text-danger-700">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="space-y-2">
                <x-button type="button" variant="secondary" size="lg" class="w-full"
                          @click="reset()"
                          x-bind:disabled="cart.length === 0">
                    Hapus Transaksi
                </x-button>

                <x-button type="submit" variant="primary" size="lg" class="w-full text-lg"
                          x-bind:disabled="cart.length === 0 || shortfall > 0 || submitting">
                    <span x-text="submitting ? 'Menyimpan…' : 'Simpan & Proses'">Simpan &amp; Proses</span>
                </x-button>
            </div>
        </div>
    </form>

    <script>
        function pos(menus) {
            return {
                menus,
                cart: [],
                search: '',
                category: 0,
                paid: true,
                payInput: '',
                submitting: false,
                quickNotes: [20000, 50000, 100000],

                get filteredMenus() {
                    const keyword = this.search.trim().toLowerCase();

                    return this.menus.filter((menu) => {
                        const byCategory = this.category === 0 || menu.category_id === this.category;
                        const byKeyword = keyword === '' || menu.name.toLowerCase().includes(keyword);

                        return byCategory && byKeyword;
                    });
                },

                get total() {
                    return this.cart.reduce((sum, line) => sum + line.price * line.qty, 0);
                },

                get itemCount() {
                    return this.cart.reduce((sum, line) => sum + line.qty, 0);
                },

                get payValue() {
                    const digits = String(this.payInput).replace(/\D/g, '');

                    return digits === '' ? this.total : Number(digits);
                },

                get change() {
                    return Math.max(0, this.payValue - this.total);
                },

                get shortfall() {
                    if (!this.paid || this.cart.length === 0) return 0;

                    return Math.max(0, this.total - this.payValue);
                },

                add(menu) {
                    if (!menu.available) return;

                    const line = this.cart.find((item) => item.id === menu.id);

                    if (line) {
                        this.increase(line);
                        return;
                    }

                    if (this.cart.length >= 30) return;

                    this.cart.push({ id: menu.id, name: menu.name, price: menu.price, qty: 1 });
                },

                increase(line) {
                    if (line.qty < 99) line.qty++;
                },

                decrease(line) {
                    // Pada qty 1 tombol kurang dinonaktifkan; item hanya hilang lewat "Hapus".
                    if (line.qty > 1) line.qty--;
                },

                remove(line) {
                    this.cart = this.cart.filter((item) => item.id !== line.id);
                },

                reset() {
                    this.cart = [];
                    this.payInput = '';
                },
            };
        }
    </script>

</x-layouts.app>
