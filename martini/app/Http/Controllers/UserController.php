<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(User::class);
        View::composer('user-management/user', function($view)
        {
            $view->with('perms', Permission::GetPermissionList());
        });
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $showDisabled = $request->input('showDisabled', false);
        if ($showDisabled) return view('user-management/index',['users' => User::paginate(10),'showDisabled' => true]);
        else return view('user-management/index',['users' => User::where("disabled",false)->paginate(10),'showDisabled' => false]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('user-management/user',['user' => new User, 'isNew' => true]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make(Str::random(40)),
        ]);

        event(new Registered($user));
        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );
        return $this->show($user);
    }

    /**
     * Display the specified resource.
     *
     * @param  User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return view('user-management/user',['user' => $user, 'isNew' => false]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        return $this->show($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);
        $input = $request->all();
        $user->name = $input['name'];
        $user->email = $input['email'];
        $user->disabled = array_key_exists("disabled",$input);
        if (isset($input['perms']) && is_array($input['perms']) && count($input['perms']) > 0)
        {
            //throw new Exception(json_encode([$input['perms'],Permission::all()]));
            foreach(Permission::all() as $perm)
            {                
                if (array_key_exists($perm->name,$input['perms']))
                {
                    $user->assignPermission($perm);
                }
                else
                {
                    $user->unassignPermission($perm);
                }
            }
        }
        $user->touch();
        $user->save();
        return $this->show($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }
}
?>