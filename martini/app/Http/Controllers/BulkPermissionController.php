<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;

class BulkPermissionController extends Controller
{
    private static int $defaultPaginate = 25;

    /**
     * The default query to populate the list of users on the User Index/Search page
     *
     * @return Builder
     */
    protected function baseQuery()
    {
        return User::with('permissions')->where([["disabled",false],['is_hidden',false]]);
    }
    /**
     * Handle the incoming request.
     *
     * @return \Illuminate\Http\Response
     */
    public function view()
    {
        return view(
            'user-management.bulk', [
                'users'         => $this->baseQuery()->paginate($this::$defaultPaginate),
                'permissions'   => Permission::where("name","<>","superadmin")->get(),
            ]
        );
    }
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request)
    {
        foreach($request->input("perms") as $user_id => $permissionsToAssign)
        {
            $user = User::with("permissions")->find($user_id);
            foreach ($user->permissions as $existingPerm)
            {
                $user->unassignPermission($existingPerm);
            }
            foreach ($permissionsToAssign as $assign_id => $discard)
            {
                $user->assignPermission(Permission::find($assign_id));
            }
        }
        return redirect('bulkpermissions',302,['page' => $request->page])->with(['message' => 'Permissions Updated']);
    }
}
