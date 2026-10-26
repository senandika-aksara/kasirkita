<?php

namespace App\Http\Controllers\Manager;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Manager\UserRequest;
use App\Models\ActivityLog;
use App\Models\User;
use App\Support\Clock;
use App\Support\SqlDialect;
use App\Support\Uploads;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('cari'));

        $users = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('username', 'like', '%'.$search.'%');
                });
            })
            ->orderByRaw(SqlDialect::orderByValues('role', ['superadmin', 'manager', 'kasir']))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('manager.users.index', [
            'users' => $users,
            'search' => $search,
            'roles' => $this->assignableRoles($request->user()),
        ]);
    }

    /** Dipakai pop-up detail & form ubah pengguna. */
    public function show(User $user): JsonResponse
    {
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'role' => $user->role->value,
            'role_label' => $user->role->label(),
            'status' => $user->status->value,
            'status_label' => $user->status->label(),
            'photo' => $user->photoUrl(),
            'initials' => $user->initials(),
            'created_at' => Clock::dateTime($user->created_at),
            'can_delete' => $this->canDelete($user),
            'update_url' => route('manager.users.update', $user),
            'delete_url' => route('manager.users.destroy', $user),
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $user = new User($request->safe()->except(['photo', 'password']));
        $user->password = $request->input('password');

        if ($request->hasFile('photo')) {
            $user->photo = Uploads::store($request->file('photo'), 'users');
        }

        $user->save();

        ActivityLog::record(
            $request->user(),
            'Create User',
            'Menambah pengguna "'.$user->name.'" ('.$user->role->label().')',
        );

        return back()->with('success', 'Pengguna "'.$user->name.'" berhasil ditambahkan.');
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        // Pengguna tidak boleh menonaktifkan atau menurunkan peran dirinya sendiri.
        if ($user->is($request->user())) {
            if ($request->input('status') !== $user->status->value) {
                return back()->with('error', 'Anda tidak dapat mengubah status akun Anda sendiri.');
            }

            if ($request->input('role') !== $user->role->value) {
                return back()->with('error', 'Anda tidak dapat mengubah level akses akun Anda sendiri.');
            }
        }

        if ($user->isSuperadmin() && ! $request->user()->isSuperadmin()) {
            return back()->with('error', 'Hanya superadmin yang dapat mengubah akun superadmin.');
        }

        $user->fill($request->safe()->except(['photo', 'password']));

        if ($request->filled('password')) {
            $user->password = $request->input('password');
        }

        if ($request->hasFile('photo')) {
            // Foto lama dihapus agar tidak menumpuk di penyimpanan.
            Uploads::delete($user->getOriginal('photo'));
            $user->photo = Uploads::store($request->file('photo'), 'users');
        }

        $user->save();

        ActivityLog::record($request->user(), 'Edit User', 'Memperbarui pengguna "'.$user->name.'"');

        return back()->with('success', 'Data pengguna "'.$user->name.'" berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ($user->isSuperadmin() && ! $request->user()->isSuperadmin()) {
            return back()->with('error', 'Hanya superadmin yang dapat menghapus akun superadmin.');
        }

        // Kasir yang sudah punya transaksi tidak dihapus agar laporan tetap utuh.
        if ($user->transactions()->exists()) {
            return back()->with('error',
                'Pengguna "'.$user->name.'" sudah memiliki transaksi sehingga tidak dapat dihapus. '
                .'Ubah statusnya menjadi "Non-Aktif" agar tidak bisa login.');
        }

        $name = $user->name;

        Uploads::delete($user->photo);

        $user->delete();

        ActivityLog::record($request->user(), 'Hapus User', 'Menghapus pengguna "'.$name.'"', 'Dihapus');

        return redirect()
            ->route('manager.users.index')
            ->with('success', 'Pengguna "'.$name.'" berhasil dihapus.');
    }

    private function canDelete(User $user): bool
    {
        return ! $user->is(request()->user())
            && ! $user->transactions()->exists()
            && (! $user->isSuperadmin() || request()->user()->isSuperadmin());
    }

    /** @return array<int, UserRole> */
    private function assignableRoles(User $actor): array
    {
        return $actor->isSuperadmin()
            ? [UserRole::Superadmin, UserRole::Manager, UserRole::Cashier]
            : [UserRole::Manager, UserRole::Cashier];
    }
}
