<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use Session;
use DataTables;
use Auth;


class JobController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $customerId)
    {
        if ($request->ajax()) {
            $jobs = Job::with('service')->where('customer_id', $customerId);
            $user = Auth::user();
            
            return Datatables::of($jobs)
                ->addColumn('is_active', function ($job) {
                    return getStatusBadge($job->status);
                })
                ->addColumn('from', function ($job) {
                    return $job->from_date->format('d/m/Y') . ' ' . $job->from_time->format('h:i a');
                })
                ->addColumn('to', function ($job) {
                    return $job->to_date->format('d/m/Y') . ' ' . $job->to_time->format('h:i a');
                })
                ->editColumn('id', 'ID: {{$id}}')
                ->rawColumns(['is_active', 'action'])
                ->make(true);
        }

        $customer = Customer::uuid($customerId)->first();
        return view('customers.jobs', get_defined_vars());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        exit('create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required',
            'service_id' => 'required',
            'from' => 'required',
            'to' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendResponse(false, $validator->errors()->first(), [], 403);
        }

        $requestData = $request->all();
        $from = explode('T', $request->from);
        $requestData['from_date'] = $from[0];
        if (isset($from[1])) {
            $fromTime = explode('+', $from[1]);
            $requestData['from_time'] = $fromTime[0];
        } else {
            $requestData['from_time'] = '00:00:00';
        }

        $to = explode('T', $request->to);
        $requestData['to_date'] = $to[0];
        if (isset($to[1])) {
            $toTime = explode('+', $to[1]);
            $requestData['to_time'] = $toTime[0];
        } else {
            $requestData['to_time'] = '23:59:59';
        }

        $job = Job::with('service', 'customer')->create($requestData);
        $event = getEventObject($job);

        return $this->sendResponse(true, 'Job successfully created', ['event' => $event]);
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
     * @param  App\Models\Company;
     * @return \Illuminate\Http\Response
     */
    public function edit(Company $company)
    {
        return view('companies.edit', compact('company'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  App\Http\Requests\CompanyRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Company $company, CompanyRequest $request)
    {
        $company->update($request->validated());
        
        Session::flash('success', __('Company successfully updated!'));
        return redirect()->route('companies.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  App\Models\Company
     * @return \Illuminate\Http\Response
     */
    public function destroy(Company $company)
    {
        if ($company) {
            $company->delete();
            return response()->json([
                'message' => __('Company deleted!')
            ], $this->successStatus);
        }

        return response()->json([
            'message' => __('Company not exist against this id')
        ], $this->errorStatus);
    }
}
