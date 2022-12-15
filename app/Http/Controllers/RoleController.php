<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\RoleRequest;
use App\Models\Role;
use App\Models\RoleGroup;
use Session;
use DataTables;
use Auth;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $roles = Role::with('group');
            $user = Auth::user();
            
            return Datatables::of($roles)
                ->addColumn('action', function ($role) use ($user) {
                    $action = '<span style="overflow: visible; position: relative; width: 130px;">';
                    //if($user->hasrole('Super Admin') || $user->can('Edit Divisions'))
                        $action .= '<a href="'. route('roles.edit', $role->uuid) .'" data-edit="true" class="text-primary me-2" data-toggle="tooltip" title="'.__('Edit Role').'"><i class="feather icon-edit"></i></a>';
                    //if($user->hasrole('Super Admin') || $user->can('Delete Divisions'))
                        $action .= '<a href="'. route('roles.destroy', $role->uuid) .'" class="text-danger btn-delete" data-toggle="tooltip" title="'.__('Delete Role').'"><i class="feather icon-trash-2"></i></a>';
                      
                    $action .= '</span>';    
                    return $action;
                })
                ->editColumn('id', 'ID: {{$id}}')
                ->rawColumns(['is_active', 'action'])
                ->make(true);
        }

        return view('acl.roles.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $groups = RoleGroup::pluck('name', 'id');
        return view('acl.roles.create', compact('groups'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  App\Http\Requests\RoleRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RoleRequest $request)
    {
        $request->merge(['guard_name' => 'web']);
        Role::create($request->all());

        Session::flash('success', __('Role successfully added!'));
        return redirect()->route('roles.index');
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
     * @param  App\Models\Role;
     * @return \Illuminate\Http\Response
     */
    public function edit(Role $role)
    {
        $groups = RoleGroup::pluck('name', 'id');
        return view('acl.roles.edit', compact('role', 'groups'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  App\Http\Requests\RoleRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Role $role, RoleRequest $request)
    {
        $role->update($request->validated());
        
        Session::flash('success', __('Role successfully updated!'));
        return redirect()->route('roles.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  App\Models\Role
     * @return \Illuminate\Http\Response
     */
    public function destroy(Role $role)
    {
        if ($role) {
            $role->delete();
            return response()->json([
                'message' => __('Role deleted!')
            ], $this->successStatus);
        }

        return response()->json([
            'message' => __('Role not exist against this id')
        ], $this->errorStatus);
    }
}
