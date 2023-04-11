<x-app-layout>
    <div class="pcoded-main-container">
        <div class="pcoded-content">
            <x-breadcrumb title="{{ $customer->name }} Jobs" :button="['name' => 'Add', 'allow' => true, 'link' => route('customers.create')]" />

            <div class="row">
                <div class="col-xl-12 col-md-12">
                    <div class="card user-profile-list">
                        <div class="card-body-dd theme-tbl">
                            <x-table action="false" :keys="['Service Name', 'From', 'To', 'Status', '']" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.dataTablesFiles')

    @push('scripts')
        <script type="text/javascript">
            $("document").ready(function() {
                var datatable_url = route('customers.jobs.ajax', [{{ $customer->id }}]);
                var datatable_columns = [{
                        data: 'service.name'
                    },
                    {
                        data: 'from',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'to',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'is_active',
                        width: '5%',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        width: '5%',
                        orderable: false,
                        searchable: false
                    }
                ];

                create_datatables(datatable_url, datatable_columns);
            });
        </script>
    @endpush


</x-app-layout>
