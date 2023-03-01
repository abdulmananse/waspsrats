<x-app-layout>
    <!-- [ Main Content ] start -->
    <div class="pcoded-main-container">
        <div class="pcoded-content">
            <!-- [ breadcrumb ] start -->
            <x-breadcrumb title="Schedules" :button="['name' => 'Add', 'allow' => true, 'link' => route('schedules.create')]" />
            <!-- [ breadcrumb ] end -->
            <!-- [ Main Content ] start -->
            <div class="row">
                <!-- product profit end -->

                <div class="col-xl-12 col-md-12">
                    <div class="card user-profile-list">
                        <div class="card-body-dd theme-tbl">
                            <x-table action="false" :keys="['Name', 'Nickname', 'Assigned To', 'Start', 'End', '']" />
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
            $("document").ready(function() {
                var datatable_url = route('schedules.ajax');
                var datatable_columns = [{
                        data: 'name'
                    },
                    {
                        data: 'nickname'
                    },
                    {
                        data: 'user.name'
                    },
                    {
                        data: 'start'
                    },
                    {
                        data: 'end'
                    },
                    {
                        data: 'action',
                        width: '15%',
                        orderable: false,
                        searchable: false
                    }
                ];

                create_datatables(datatable_url, datatable_columns);
            });
        </script>
    @endpush


</x-app-layout>
