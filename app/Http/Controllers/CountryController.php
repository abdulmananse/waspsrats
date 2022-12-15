<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\CountryRequest;
use App\Models\Country;

use Session;
use DataTables;
use Auth;

class CountryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $countries = Country::query();
            $user = Auth::user();
            
            return Datatables::of($countries)
                ->addColumn('is_active', function ($country) {
                    return getStatusBadge($country->is_active);
                })
                ->addColumn('action', function ($country) use ($user) {
                    $action = '<span style="overflow: visible; position: relative; width: 130px;">';
                    //if($user->hasrole('Super Admin') || $user->can('Edit Divisions'))
                        $action .= '<a href="'. route('countries.edit', $country->uuid) .'" data-edit="true" class="text-primary me-2" data-toggle="tooltip" title="'.__('Edit Country').'"><i class="feather icon-edit"></i></a>';
                    //if($user->hasrole('Super Admin') || $user->can('Delete Divisions'))
                        $action .= '<a href="'. route('countries.destroy', $country->uuid) .'" class="text-danger btn-delete" data-toggle="tooltip" title="'.__('Delete Country').'"><i class="feather icon-trash-2"></i></a>';
                      
                    $action .= '</span>';    
                    return $action;
                })
                ->editColumn('id', 'ID: {{$id}}')
                ->rawColumns(['is_active', 'action'])
                ->make(true);
        }

        return view('countries.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('countries.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  App\Http\Requests\CountryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CountryRequest $request)
    {
        Country::create($request->validated());

        Session::flash('success', __('Country successfully added!'));
        return redirect()->route('countries.index');
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
     * @param  App\Models\Country;
     * @return \Illuminate\Http\Response
     */
    public function edit(Country $country)
    {
        return view('countries.edit', compact('country'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  App\Http\Requests\CountryRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Country $country, CountryRequest $request)
    {
        $country->update($request->validated());
        
        Session::flash('success', __('Country successfully updated!'));
        return redirect()->route('countries.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  App\Models\Industry
     * @return \Illuminate\Http\Response
     */
    public function destroy(Country $country)
    {
        if ($country) {
            $country->delete();
            return response()->json([
                'message' => __('Country deleted!')
            ], $this->successStatus);
        }

        return response()->json([
            'message' => __('Country not exist against this id')
        ], $this->errorStatus);
    }
}
