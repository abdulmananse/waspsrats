<x-app-layout>
    @php
        $action = true;
        // $action = (auth()->user()->hasrole('Super Admin') || auth()->user()->can('Edit Divisions') || auth()->user()->can('Delete Divisions'))?true:false;
        // $createAction = (auth()->user()->hasrole('Super Admin') || auth()->user()->can('Create Divisions'))?true:false;
    @endphp

    <!-- [ Main Content ] start -->
    <div class="pcoded-main-container">
        <div class="pcoded-wrapper">
            <div class="pcoded-content">
                <div class="pcoded-inner-content">
                    
                    <!-- [ breadcrumb ] start -->
                    <x-breadcrumb title="Users" :button="['name' => 'Add', 'allow' => true, 'link' => route('users.create')]" />
                    <!-- [ breadcrumb ] end -->
                    
                    <div class="main-body">
                        <div class="page-wrapper">
                            <!-- [ Main Content ] start -->
                            <div class="row">
                                <!-- [ basic-table ] start -->
                                <div class="col-xl-12">
                                    <div class="card">
                                        <div class="card-block table-border-style">
                                            <x-table action="false" :keys="['Name', 'Username', 'Email', 'License No']" />
                                        </div>
                                    </div>
                                </div>
                                <!-- [ basic-table ] end -->
                            </div>
                            <!-- [ Main Content ] end -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->

    @include('layouts.dataTablesFiles')

    @push('scripts')
        <script type="text/javascript">
        $("document").ready(function () {
            var datatable_url = route('users.ajax');
            var datatable_columns = [
                {data: 'name'},
                {data: 'username'},
                {data: 'email'},
                {data: 'license_no'},
                @if($action)
                {data: 'action', width: '15%', orderable: false, searchable: false}
                @endif
                ];

                create_datatables(datatable_url,datatable_columns);
          });
        </script>
    @endpush


</x-app-layout>
