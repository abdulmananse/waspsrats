<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\TimezoneRequest;
use App\Models\Timezone;

use Session;
use DataTables;
use Auth;

class TimezoneController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $timezones = Timezone::query();
            $user = Auth::user();
            
            return Datatables::of($timezones)
                ->addColumn('is_active', function ($timezone) {
                    return getStatusBadge($timezone->is_active);
                })
                ->addColumn('action', function ($timezone) use ($user) {
                    $action = '<span style="overflow: visible; position: relative; width: 130px;">';
                    //if($user->hasrole('Super Admin') || $user->can('Edit Divisions'))
                        $action .= '<a href="'. route('timezones.edit', $timezone->uuid) .'" data-edit="true" class="text-primary me-2" data-toggle="tooltip" title="'.__('Edit Timezone').'"><i class="feather icon-edit"></i></a>';
                    //if($user->hasrole('Super Admin') || $user->can('Delete Divisions'))
                        $action .= '<a href="'. route('timezones.destroy', $timezone->uuid) .'" class="text-danger btn-delete" data-toggle="tooltip" title="'.__('Delete Timezone').'"><i class="feather icon-trash-2"></i></a>';
                      
                    $action .= '</span>';    
                    return $action;
                })
                ->editColumn('id', 'ID: {{$id}}')
                ->rawColumns(['is_active', 'action'])
                ->make(true);
        }

        return view('timezones.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('timezones.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  App\Http\Requests\TimezoneRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(TimezoneRequest $request)
    {
        Timezone::create($request->validated());

        Session::flash('success', __('Timezone successfully added!'));
        return redirect()->route('timezones.index');
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
     * @param  App\Models\Timezone;
     * @return \Illuminate\Http\Response
     */
    public function edit(Timezone $timezone)
    {
        return view('timezones.edit', compact('timezone'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  App\Http\Requests\TimezoneRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Timezone $timezone, TimezoneRequest $request)
    {
        $timezone->update($request->validated());
        
        Session::flash('success', __('Timezone successfully updated!'));
        return redirect()->route('timezones.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  App\Models\Industry
     * @return \Illuminate\Http\Response
     */
    public function destroy(Timezone $timezone)
    {
        if ($timezone) {
            $timezone->delete();
            return response()->json([
                'message' => __('Timezone deleted!')
            ], $this->successStatus);
        }

        return response()->json([
            'message' => __('Timezone not exist against this id')
        ], $this->errorStatus);
    }
}
