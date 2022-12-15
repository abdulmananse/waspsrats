<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\IndustryRequest;
use App\Models\Industry;

use Session;
use DataTables;
use Auth;

class IndustryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $industries = Industry::query();
            $user = Auth::user();
            
            return Datatables::of($industries)
                ->addColumn('is_active', function ($industry) {
                    return getStatusBadge($industry->is_active);
                })
                ->addColumn('action', function ($industry) use ($user) {
                    $action = '<span style="overflow: visible; position: relative; width: 130px;">';
                    //if($user->hasrole('Super Admin') || $user->can('Edit Divisions'))
                        $action .= '<a href="'. route('industries.edit', $industry->uuid) .'" data-edit="true" class="text-primary me-2" data-toggle="tooltip" title="'.__('Edit Industry').'"><i class="feather icon-edit"></i></a>';
                    //if($user->hasrole('Super Admin') || $user->can('Delete Divisions'))
                        $action .= '<a href="'. route('industries.destroy', $industry->uuid) .'" class="text-danger btn-delete" data-toggle="tooltip" title="'.__('Delete Industry').'"><i class="feather icon-trash-2"></i></a>';
                      
                    $action .= '</span>';    
                    return $action;
                })
                ->editColumn('id', 'ID: {{$id}}')
                ->rawColumns(['is_active', 'action'])
                ->make(true);
        }

        return view('industries.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('industries.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  App\Http\Requests\IndustryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(IndustryRequest $request)
    {
        Industry::create($request->validated());

        Session::flash('success', __('Industry successfully added!'));
        return redirect()->route('industries.index');
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
     * @param  App\Models\Industry;
     * @return \Illuminate\Http\Response
     */
    public function edit(Industry $industry)
    {
        return view('industries.edit', compact('industry'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  App\Http\Requests\IndustryRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Industry $industry, IndustryRequest $request)
    {
        $industry->update($request->validated());
        
        Session::flash('success', __('Industry successfully updated!'));
        return redirect()->route('industries.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  App\Models\Industry
     * @return \Illuminate\Http\Response
     */
    public function destroy(Industry $industry)
    {
        if ($industry) {
            $industry->delete();
            return response()->json([
                'message' => __('Industry deleted!')
            ], $this->successStatus);
        }

        return response()->json([
            'message' => __('Industry not exist against this id')
        ], $this->errorStatus);
    }
}
