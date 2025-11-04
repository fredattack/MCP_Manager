<?php

namespace App\Http\Controllers\Api\Mcp;

use App\Http\Controllers\Controller;
use App\Models\OrganizationMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetAuthenticatedUserController extends Controller
{
    /**
     * Get authenticated user information
     *
     * The ValidateMcpServerToken middleware handles authentication
     * and attaches the user to the request. This endpoint simply
     * returns the authenticated user's information.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $userToken = $request->attributes->get('mcp_token');

        $organizations = OrganizationMember::where('user_id', $user->id)
            ->with(['organization:id,name,slug,status'])
            ->get()
            ->map(fn (OrganizationMember $member) => [
                'id' => $member->organization->id,
                'name' => $member->organization->name,
                'slug' => $member->organization->slug,
                'role' => $member->role->value,
                'permissions' => $member->role->permissions(),
                'is_active' => $member->organization->isActive(),
            ])
            ->toArray();

        $permissions = $this->getUserPermissions($user);

        return response()->json([
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'permissions' => $permissions,
            'organizations' => $organizations,
            'token_scopes' => $userToken->scopes ?? [],
        ]);
    }

    private function getUserPermissions($user): array
    {
        $permissions = ['read:own', 'write:own'];

        $memberships = OrganizationMember::where('user_id', $user->id)->get();

        foreach ($memberships as $membership) {
            $permissions = array_merge($permissions, $membership->role->permissions());
        }

        return array_unique($permissions);
    }
}
