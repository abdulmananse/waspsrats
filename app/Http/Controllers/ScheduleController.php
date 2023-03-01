<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\RoleRequest;
use App\Http\Requests\ScheduleRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\ScheduleGroup;
use App\Models\Schedule;
use App\Models\User;
use Session;
use DataTables;
use Auth;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $schedules = Schedule::with('user');
            $user = Auth::user();
            
            return Datatables::of($schedules)
                ->addColumn('action', function ($schedule) use ($user) {

                    $action = '<td><div class="overlay-edit">';

                    if ($user->can('Schedule Update')) {
                        $action .= '<a href="'.route('schedules.edit', $schedule->uuid).'" class="btn btn-icon btn-secondary"><i class="feather icon-edit-2"></i></a>';
                    }

                    if ($user->can('Schedule Delete')) {    
                        $action .= '<a href="'.route('schedules.destroy', $schedule->uuid).'" class="btn btn-icon btn-danger btn-delete"><i class="feather icon-trash-2"></i></a>';
                    }
                    $action .= '</div></td>';

                    return $action;
                })
                ->editColumn('id', 'ID: {{$id}}')
                ->rawColumns(['is_active', 'action'])
                ->make(true);
        }

        return view('schedule.schedules.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $groups = ScheduleGroup::pluck('name', 'id');
        $users = User::pluck('name', 'id');
        return view('schedule.schedules.create', get_defined_vars());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  App\Http\Requests\ScheduleRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ScheduleRequest $request)
    {
        $schedule = Schedule::create($request->validated());
        if ($schedule) {
            foreach($request->group_ids as $groupId) {
                $schedule->schedule_groups()->create(['schedule_group_id' => $groupId]);
            }
        }

        Session::flash('success', __('Schedule successfully added!'));
        return redirect()->route('schedules.index');
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
     * @param  \App\Models\Schedule $schedule
     * @return \Illuminate\Http\Response
     */
    public function edit(Schedule $schedule)
    {
        $groups = ScheduleGroup::pluck('name', 'id');
        $users = User::pluck('name', 'id');
        $groupIds = $schedule->schedule_groups()->pluck('schedule_group_id');
        
        return view('schedule.schedules.edit', get_defined_vars());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\ScheduleRequest  $request
     * @param  \App\Models\Schedule $schedule
     * @return \Illuminate\Http\Response
     */
    public function update(Schedule $schedule, ScheduleRequest $request)
    {
        $schedule->update($request->validated());
        
        $schedule->schedule_groups()->delete();
        foreach($request->group_ids as $groupId) {
            $schedule->schedule_groups()->create(['schedule_group_id' => $groupId]);
        }

        Session::flash('success', __('Schedule successfully updated!'));
        return redirect()->route('schedules.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Schedule $schedule
     * @return \Illuminate\Http\Response
     */
    public function destroy(Schedule $schedule)
    {
        if ($schedule) {
            $schedule->delete();
            return response()->json([
                'message' => __('Schedule deleted!')
            ], $this->successStatus);
        }

        return response()->json([
            'message' => __('Schedule not exist against this id')
        ], $this->errorStatus);
    }
}
