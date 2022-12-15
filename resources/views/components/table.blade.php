<div>
    <table class="datatable table table-bordered table-hover table-checkable" id="datatable" style="margin-top: 13px !important">
        <thead>
            <tr>
                @if($checkbox == true)
                <th><input type="checkbox" id="id_check_all" class="group-checkable"/></th>
                @endif
                @foreach($keys as $key => $value)
                <th>{{ $value }}</th>
                @endforeach
                @if($action)
                <th>Action</th>
                @endif
            </tr>
        </thead>
        <tbody>
            <tr><td colspan="{{ ($action)?(count($keys)+1):count($keys) }}" align="center">No matching records found</td></tr>
        </tbody>
        <tfoot>
        </tfoot>
    </table>
</div>