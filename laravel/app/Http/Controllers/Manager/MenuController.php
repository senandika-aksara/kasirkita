<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manager\MenuRequest;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Menu;
use App\Support\Clock;
use App\Support\Money;
use App\Support\Retry;
use App\Support\Uploads;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('cari'));
        $categoryId = $request->integer('kategori') ?: null;

        $menus = Menu::query()
            ->with('category:id,name')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->orderBy('code')
            ->paginate(10)
            ->withQueryString();

        $categories = Category::query()->ordered()->withCount('menus')->get();

        return view('manager.menus.index', [
            'menus' => $menus,
            'categories' => $categories,
            'filters' => ['cari' => $search, 'kategori' => $categoryId],
            'totalMenus' => Menu::query()->count(),
        ]);
    }

    /** Dipakai pop-up detail menu. */
    public function show(Menu $menu): JsonResponse
    {
        $menu->load(['category:id,name', 'updatedBy:id,name,role']);

        return response()->json([
            'id' => $menu->id,
            'code' => $menu->code,
            'name' => $menu->name,
            'category_id' => $menu->category_id,
            'category' => $menu->category?->name,
            'price' => $menu->price,
            'price_formatted' => Money::format($menu->price),
            'status' => $menu->status->value,
            'status_label' => $menu->status->label(),
            'image' => $menu->imageUrl(),
            'updated_at' => Clock::dateTime($menu->updated_at),
            'updated_by' => $menu->updatedBy
                ? $menu->updatedBy->role->label().' '.$menu->updatedBy->name
                : '—',
            'can_delete' => ! $menu->hasBeenSold(),
            'update_url' => route('manager.menus.update', $menu),
            'delete_url' => route('manager.menus.destroy', $menu),
        ]);
    }

    public function store(MenuRequest $request): RedirectResponse
    {
        $menu = new Menu($request->safe()->except('image'));
        $menu->updated_by = $request->user()->id;

        if ($request->hasFile('image')) {
            $menu->image = Uploads::store($request->file('image'), 'menus');
        }

        // Kode menu dihitung dari kode terakhir, jadi dua manajer yang menyimpan
        // berbarengan bisa mendapat nomor yang sama; ambil nomor berikutnya lalu ulangi.
        Retry::onDuplicate(function () use ($menu) {
            $menu->code = Menu::nextCode();
            $menu->save();
        });

        ActivityLog::record($request->user(), 'Create Menu', 'Menambah menu "'.$menu->name.'" ('.$menu->code.')');

        return back()->with('success', 'Menu "'.$menu->name.'" berhasil ditambahkan dengan kode '.$menu->code.'.');
    }

    public function update(MenuRequest $request, Menu $menu): RedirectResponse
    {
        $menu->fill($request->safe()->except('image'));
        $menu->updated_by = $request->user()->id;

        if ($request->hasFile('image')) {
            // Foto lama dihapus agar tidak menumpuk di penyimpanan.
            Uploads::delete($menu->getOriginal('image'));
            $menu->image = Uploads::store($request->file('image'), 'menus');
        }

        $menu->save();

        ActivityLog::record($request->user(), 'Edit Menu', 'Memperbarui menu "'.$menu->name.'" ('.$menu->code.')');

        return back()->with('success', 'Menu "'.$menu->name.'" berhasil diperbarui.');
    }

    public function destroy(Request $request, Menu $menu): RedirectResponse
    {
        // Menu yang pernah terjual tidak dihapus agar riwayat transaksi tetap utuh.
        if ($menu->hasBeenSold()) {
            return back()->with('error',
                'Menu "'.$menu->name.'" sudah pernah terjual sehingga tidak dapat dihapus. '
                .'Ubah statusnya menjadi "Habis" bila tidak dijual lagi.');
        }

        $name = $menu->name;
        $code = $menu->code;

        Uploads::delete($menu->image);

        $menu->delete();

        ActivityLog::record($request->user(), 'Hapus Menu', 'Menghapus menu "'.$name.'" ('.$code.')', 'Dihapus');

        return redirect()
            ->route('manager.menus.index')
            ->with('success', 'Menu "'.$name.'" ('.$code.') berhasil dihapus.');
    }
}
