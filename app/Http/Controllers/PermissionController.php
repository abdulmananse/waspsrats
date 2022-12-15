<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\PermissionRequest;
use App\Models\Permission;
use Session;
use DataTables;
use Auth;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $permissions = Permission::query();
            $user = Auth::user();
            
            return Datatables::of($permissions)
                ->addColumn('action', function ($permission) use ($user) {
                    $action = '<span style="overflow: visible; position: relative; width: 130px;">';
                    //if($user->hasrole('Super Admin') || $user->can('Edit Divisions'))
                        $action .= '<a href="'. route('permissions.edit', $permission->uuid) .'" data-edit="true" class="text-primary me-2" data-toggle="tooltip" title="'.__('Edit Permission').'"><i class="feather icon-edit"></i></a>';
                    //if($user->hasrole('Super Admin') || $user->can('Delete Divisions'))
                        $action .= '<a href="'. route('permissions.destroy', $permission->uuid) .'" class="text-danger btn-delete" data-toggle="tooltip" title="'.__('Delete Permission').'"><i class="feather icon-trash-2"></i></a>';
                      
                    $action .= '</span>';    
                    return $action;
                })
                ->editColumn('id', 'ID: {{$id}}')
                ->rawColumns(['is_active', 'action'])
                ->make(true);
        }

        return view('acl.permissions.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('acl.permissions.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  App\Http\Requests\PermissionRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PermissionRequest $request)
    {
        $request->merge(['guard_name' => 'web']);
        Permission::create($request->all());

        Session::flash('success', __('Permission successfully added!'));
        return redirect()->route('permissions.index');
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
     * @param  App\Models\Permission;
     * @return \Illuminate\Http\Response
     */
    public function edit(Permission $permission)
    {
        return view('acl.permissions.edit', compact('permission'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  App\Http\Requests\PermissionRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Permission $permission, PermissionRequest $request)
    {
        $permission->update($request->validated());
        
        Session::flash('success', __('Permission successfully updated!'));
        return redirect()->route('permissions.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  App\Models\Permission
     * @return \Illuminate\Http\Response
     */
    public function destroy(Permission $permission)
    {
        if ($permission) {
            $permission->delete();
            return response()->json([
                'message' => __('Permission deleted!')
            ], $this->successStatus);
        }

        return response()->json([
            'message' => __('Permission not exist against this id')
        ], $this->errorStatus);
    }
}
