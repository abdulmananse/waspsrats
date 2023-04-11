<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use Session;
use DataTables;
use Auth;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $customers = Customer::query();
            $user = Auth::user();
            
            return Datatables::of($customers)
                ->addColumn('name', function ($customer) {
                    return $customer->name;
                })
                ->addColumn('is_active', function ($customer) {
                    return getStatusBadge($customer->is_active);
                })
                ->addColumn('action', function ($customer) use ($user) {

                    $action = '<td><div class="overlay-edit">';

                    if ($user->can('Customer Jobs Index')) {
                        $action .= '<a href="'.route('customers.jobs', $customer->uuid).'" class="btn btn-icon btn-secondary" title="Jobs"><i class="feather icon-watch"></i></a>';
                    }
                    
                    if ($user->can('Customer Contacts Index')) {
                        $action .= '<a href="'.route('customer-contacts.index', ['id' => $customer->uuid]).'" class="btn btn-icon btn-success" title="Contacts"><i class="feather icon-credit-card"></i></a>';
                    }
                    
                    if ($user->can('Customers Update')) {
                        $action .= '<a href="'.route('customers.edit', $customer->uuid).'" class="btn btn-icon btn-secondary"><i class="feather icon-edit-2"></i></a>';
                    }

                    if ($user->can('Customers Delete')) {    
                        $action .= '<a href="'.route('customers.destroy', $customer->uuid).'" class="btn btn-icon btn-danger btn-delete"><i class="feather icon-trash-2"></i></a>';
                    }
                    $action .= '</div></td>';

                    return $action;
                })
                ->editColumn('id', 'ID: {{$id}}')
                ->rawColumns(['is_active', 'action'])
                ->make(true);
        }

        return view('customers.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\CustomerRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CustomerRequest $request)
    {
        Customer::create($request->validated());

        Session::flash('success', __('Customer successfully added!'));
        return redirect()->route('customers.index');
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
     * @param  \App\Models\Customer $customer
     * @return \Illuminate\Http\Response
     */
    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\CustomerRequest  $request
     * @param  \App\Models\Customer $customer
     * @return \Illuminate\Http\Response
     */
    public function update(Customer $customer, CustomerRequest $request)
    {
        $customer->update($request->validated());
        
        Session::flash('success', __('Customer successfully updated!'));
        return redirect()->route('customers.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Customer $customer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Customer $customer)
    {
        if ($customer) {
            $customer->delete();
            return response()->json([
                'message' => __('Customer deleted!')
            ], $this->successStatus);
        }

        return response()->json([
            'message' => __('Customer not exist against this id')
        ], $this->errorStatus);
    }
}
