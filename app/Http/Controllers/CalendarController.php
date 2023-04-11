<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\CountryRequest;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Job;
use App\Models\Service;
use Session;
use DataTables;
use Auth;

class CalendarController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $customers = Customer::all()->pluck('name', 'id');
        $services = Service::pluck('name', 'id');
        $jobs = Job::with('service', 'customer')->get();

        $events = [];
        foreach($jobs as $job){
            $events[] = getEventObject($job);
        }
        $events = json_encode($events);
        
        return view('calendars.index', get_defined_vars());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('calendars.create');
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
