<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

use App\Http\Requests\UserRequest;
use App\Models\Role;
use App\Models\User;
use Session;
use DataTables;
use Auth;
use DB;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $users = User::query();
            $authUser = Auth::user();
            
            return Datatables::of($users)
                ->addColumn('action', function ($user) use ($authUser) {
                    $action = '<span style="overflow: visible; position: relative; width: 130px;">';
                    //if($user->hasrole('Super Admin') || $user->can('Edit Divisions'))
                        $action .= '<a href="'. route('users.edit', $user->uuid) .'" data-edit="true" class="text-primary me-2" data-toggle="tooltip" title="'.__('Edit Role').'"><i class="feather icon-edit"></i></a>';
                    //if($user->hasrole('Super Admin') || $user->can('Delete Divisions'))
                    if($user->id > 1)
                        $action .= '<a href="'. route('users.destroy', $user->uuid) .'" class="text-danger btn-delete" data-toggle="tooltip" title="'.__('Delete Role').'"><i class="feather icon-trash-2"></i></a>';
                      
                    $action .= '</span>';    
                    return $action;
                })
                ->editColumn('id', 'ID: {{$id}}')
                ->rawColumns(['is_active', 'action'])
                ->make(true);
        }

        return view('users.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::pluck('name', 'id');
        return view('users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  App\Http\Requests\UserRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserRequest $request)
    {
        $request->merge([
            'name' => $request->first_name . ' ' . $request->last_name,
            'password' => Hash::make($request->password)
        ]);
        $user = User::create($request->all());

        if ($user) {
            $user = User::uuid($user->uuid)->first();
            $role = Role::find($request->role_id);
            $user->assignRole($role);
        }
        

        Session::flash('success', __('User successfully added!'));
        return redirect()->route('users.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  App\Models\User;
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        $role = $user->roles->first();
        
        $roles = Role::pluck('name', 'id');
        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  App\Http\Requests\UserRequest  $request
     * @param  App\Models\User
     * @return \Illuminate\Http\Response
     */
    public function update(User $user, UserRequest $request)
    {
        $data['name'] = $request->first_name . ' ' . $request->last_name;

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        } else {
            $request->request->remove('password');
        }

        $request->merge($data);

        if ($user) {
            DB::table('model_has_roles')->where('model_id', $user->id)->delete();
            $role = Role::find($request->role_id);
            $user->assignRole($role);
            $user->update($request->all());
            
            Session::flash('success', __('Role successfully updated!'));
            return redirect()->route('users.index');
        }
        
        Session::flash('error', __('User not successfully updat!'));
        return redirect()->route('users.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  App\Models\User
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        if ($user) {
            DB::table('model_has_roles')->where('model_id', $user->id)->delete();
            $user->delete();
            return response()->json([
                'message' => __('User deleted!')
            ], $this->successStatus);
        }

        return response()->json([
            'message' => __('User not exist against this id')
        ], $this->errorStatus);
    }


    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function changePassword()
    {
        return view('users.change-password');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  App\Http\Requests\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(Request $request)
    {
        $this->validate($request, [
            'password' => ['required', 'confirmed', Rules\Password::defaults()]
        ]);

        $user = User::find(Auth::id());    
        if ($user) {
            $user->update(['password' => Hash::make($request->password)]);

            Session::flash('success', __('Password successfully updated!'));
            return redirect()->route('change-password');
        }
        
        Session::flash('error', __('User not successfully updat!'));
        return redirect()->route('change-password');
    }
}
