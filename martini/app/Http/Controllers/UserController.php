<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\PermissionsGroup;
use App\Models\User;
use App\Models\UserPermission;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

/**
 *
 */
class UserController extends Controller
{
    private static int $defaultPaginate = 25;

    /**
     * The default query to populate the list of users on the User Index/Search page
     *
     * @return Builder
     */
    protected function baseQuery()
    {
        return User::withCount('permissions');
    }

    /**
     * Function which checks whether a SHA1 password is valid
     * @param string $plainPassword The password the user entered you wish to validate
     * @param string $hashedPassword The password that is hashed in the database
     * @return bool The result of the operation
     */
    protected function checkSha1Hash(string $plainPassword, string $hashedPassword)
    {
        return sha1($plainPassword) === $hashedPassword;
    }

    public function __construct()
    {
        $this->authorizeResource(User::class);
        View::composer('user-management/user', function ($view) {
            $view->with('permissions', PermissionsGroup::with('permissions')->where('id','<>',"0")->get());
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

        return view(
            'user-management.index', [
                'users' => $showDisabled ? $this->baseQuery()->paginate($this::$defaultPaginate)
                    : $this->baseQuery()->where('disabled', false)->paginate($this::$defaultPaginate),
                        'search_term' => '',
                'show_disabled' => $showDisabled
            ]
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('user-management/user', ['user' => new User, 'isNew' => true]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
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
        
        if (isset($request->perms) && is_array($request->perms) && count($request->perms) > 0) {
            foreach (Permission::all() as $perm) {
                if (array_key_exists($perm->name, $request->perms)) {
                    $user->assignPermission($perm);
                }
            }
        }

        event(new Registered($user));
        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );
        return redirect()->route('users.show',['user'=>$user]);
    }

    /**
     * Display the specified resource.
     *
     * @param User $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return view('user-management/user',
            ['user' => $user,
                'permissions' => PermissionsGroup::with('permissions')->where('id','<>',"0")->get(),
                'isNew' => false]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param User $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        return $this->show($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param User $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['sometimes','string'],
            'new_password' => ['sometimes','string', Rules\Password::defaults()],
            'confirm_password' => ['sometimes', 'string', 'nullable', Rules\Password::defaults()]
        ]);
        $input = $request->all();

        //Autofill doesn't seem to fill in the confirm password, so we use this to check whether user wants
        //to change their password.
        if((isset($input['new_password']) && isset($input['confirm_password'])) && (Auth::user()->id === $user->id || Auth::user()->can('admin')))
        {
            //Use correct password check for each Hash method
            if (Auth::user()->id === $user->id)
            {
                switch($user->hash_method){
                    case 'BCRYPT':
                        if(!Auth::guard('web')->validate([
                            'email' => $user->email,
                            'password' => $input['password']
                        ])){
                            return redirect()->back()->withErrors(__('auth.password'));
                        };
                        break;
                    case 'SHA1':
                        if(!$this->checkSha1Hash($input['password'], $user->password))
                        {
                            return redirect()->back()->withErrors(__('auth.password'));
                        }
                        break;
                }
            }
            if($input['confirm_password'] === $input['new_password'])
            {
                $user->password = Hash::make($input['confirm_password']);
                $user->hash_method = 'BCRYPT';
            }else{
                return redirect()->back()->withErrors(__('auth.confirm'));
            }
        }

        $user->name = $input['name'];
        $user->email = $input['email'];
        $user->disabled = array_key_exists("disabled", $input);
        if (isset($input['perms']) && is_array($input['perms']) && count($input['perms']) > 0) {
            foreach (Permission::all() as $perm) {
                if (array_key_exists($perm->name, $input['perms'])) {
                    $user->assignPermission($perm);
                } else {
                    $user->unassignPermission($perm);
                }
            }
        }
        $user->touch();
        $user->save();

        return redirect(route('users.index'))->with(['message' => "Successfully updated $user->name's account"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }

    /**
     * GET method to search users in the system from the Users Index page
     * @param Request $request
     * @return View
     */
    public function search(Request $request)
    {
        $showDisabled = $request->input('showDisabled', false);
        $searchTerm = $request->get('search');
        return view(
            'user-management.index', [
                'users' => $showDisabled ? User::where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $searchTerm . '%')
                    ->withCount('permissions')
                    ->paginate($this::$defaultPaginate)
                    ->appends(request()->query())
                    : User::where('name', 'like', '%' . $searchTerm . '%')
                        ->orWhere('email', 'like', '%' . $searchTerm . '%')
                        ->withCount('permissions')
                        ->paginate($this::$defaultPaginate)
                        ->appends(request()->query()),
                'show_disabled' => true,
                'search_term' => $searchTerm
            ]
        );
    }

    /**
     * POST route to send out a forgotten password email for a specific user
     * @param Request $request
     * @return void
     */
    public function resetPassword(User $user)
    {
        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
                $user->toArray()

        );

        return redirect()->back()->with(['message' => __('passwords.sent')]);
    }
}

?>
