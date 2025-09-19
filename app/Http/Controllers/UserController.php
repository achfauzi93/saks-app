<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller implements HasMiddleware
{

    public static function middleware()
    {
        return [
            new Middleware('permission:view-users', only: ['index', 'show']),
            new Middleware('permission:create-users', only: ['create', 'store']),
            new Middleware('permission:edit-users', only: ['edit', 'update']),
            new Middleware('permission:delete-users', only: ['destroy']),
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $searchTerm = $request->input('search');
        $users = User::with('roles')
            ->where('id', '!=', $request->user()->id)
            ->search($searchTerm)
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('users/Index', [
            'users'     => UserResource::collection($users),
            'searchTerm' => $request->only('search'),
            'perPage'   => $perPage
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all();
        return Inertia::render('users/Create', [
            'roles' => $roles
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {
        try {
            $validated = $request->validated();

            // Buat user baru
            $user = User::create($validated);

            // Sinkronisasi role (pastikan 'role' sudah valid)
            $user->syncRoles($validated['role']);

            return redirect()->route('users.index');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $user = User::with('roles')->findOrFail($user->id);
        return Inertia::render('users/Edit', [
            'user' => $user,
            'roles' => Role::all()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, User $user)
    {
        try {
            $validated = $request->validated();

            // Jika password kosong, hapus dari array supaya tidak diupdate
            if (empty($validated['password'])) {
                unset($validated['password']);
            } else {
                // Jika ada password, hash dulu sebelum update
                $validated['password'] = bcrypt($validated['password']);
            }

            // Update user dengan data yang sudah divalidasi dan diproses
            $user->update($validated);

            // Sinkronisasi role (pastikan 'role' ada di $validated)
            if (isset($validated['role'])) {
                $user->syncRoles($validated['role']);
            }

            return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
        } catch (\Exception $e) {
            // \Log::error('Gagal memperbarui user: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, User $user)
    {
        if ($user->id == $request->user()->id) {
            return redirect()->route('users.index')->withErrors(['error' => 'Anda tidak dapat menghapus diri sendiri.']);
        }

        $user->delete();
        return redirect()->route('users.index');
    }
}