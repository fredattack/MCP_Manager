<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Services\UserManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserManagementController extends Controller
{
    public function __construct(
        protected UserManagementService $userService
    ) {}

    public function index(Request $request): Response
    {
        $query = User::query()
            ->with(['mcpServer', 'activityLogs' => fn ($q) => $q->latest()->limit(5)])
            ->withCount('integrationAccounts');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->get('role')) {
            $query->where('role', $role);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('is_locked')) {
            $query->where('is_locked', $request->boolean('is_locked'));
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $users = $query->paginate($request->integer('per_page', 15))->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role', 'is_active', 'is_locked', 'sort_by', 'sort_order']),
            'roles' => UserRole::options(),
            'can' => [
                'create' => $request->user()?->hasPermission('users.create') ?? false,
                'edit' => $request->user()?->hasPermission('users.edit') ?? false,
                'delete' => $request->user()?->hasPermission('users.delete') ?? false,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Users/Create', [
            'roles' => UserRole::options(),
        ]);
    }

    public function store(CreateUserRequest $request): JsonResponse
    {
        $user = $this->userService->createUser(
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user->load('mcpServer', 'integrationAccounts'),
        ], 201);
    }

    public function show(User $user): Response
    {
        $user->load([
            'mcpServer',
            'integrationAccounts',
            'activityLogs.performedBy',
            'tokens' => fn ($q) => $q->latest(),
        ]);

        return Inertia::render('Admin/Users/Show', [
            'user' => $user,
            'can' => [
                'edit' => request()->user()?->hasPermission('users.edit') ?? false,
                'delete' => request()->user()?->hasPermission('users.delete') ?? false,
            ],
        ]);
    }

    public function edit(User $user): Response
    {
        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'roles' => UserRole::options(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $user = $this->userService->updateUser(
            $user,
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user->fresh()->load('mcpServer', 'integrationAccounts'),
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($user->id === $request->user()?->id) {
            return response()->json([
                'message' => 'You cannot delete your own account',
            ], 403);
        }

        $this->userService->deleteUser($user, $request->user());

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }

    public function generateCredentials(User $user): JsonResponse
    {
        $credentials = $this->userService->generateCredentials(
            $user,
            request()->user()
        );

        return response()->json([
            'message' => 'Credentials generated successfully',
            'credentials' => $credentials,
        ]);
    }

    public function resetPassword(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $password = $this->userService->resetPassword(
            $user,
            $validated['password'] ?? null,
            $request->user()
        );

        return response()->json([
            'message' => 'Password reset successfully',
            'password' => $password,
        ]);
    }

    public function lock(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $user->lock($validated['reason'], $request->user());

        return response()->json([
            'message' => 'User locked successfully',
            'user' => $user->fresh(),
        ]);
    }

    public function unlock(User $user): JsonResponse
    {
        $user->unlock(request()->user());

        return response()->json([
            'message' => 'User unlocked successfully',
            'user' => $user->fresh(),
        ]);
    }

    public function changeRole(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'role' => ['required', Rule::enum(UserRole::class)],
        ]);

        $user = $this->userService->changeRole(
            $user,
            UserRole::from($validated['role']),
            $request->user()
        );

        return response()->json([
            'message' => 'Role changed successfully',
            'user' => $user->fresh(),
        ]);
    }

    public function updatePermissions(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string'],
        ]);

        $user = $this->userService->updatePermissions(
            $user,
            $validated['permissions'],
            $request->user()
        );

        return response()->json([
            'message' => 'Permissions updated successfully',
            'user' => $user->fresh(),
        ]);
    }

    public function activityLog(User $user): JsonResponse
    {
        $logs = $user->activityLogs()
            ->with('performedBy')
            ->latest()
            ->paginate(50);

        return response()->json($logs);
    }
}
