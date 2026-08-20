<div class="table-wrapper m-0">
    <table class="fl-table table table-hover p-0 m-0" id="clientTable">
        <tr>
            <th class="text-center">Module</th>
            <th class="text-center">Read</th>
            <th class="text-center">Insert</th>
            <th class="text-center">Update</th>
            <th class="text-center">Delete</th>
            <th class="text-center">Read/Write</th>
        </tr>
        @foreach($matrixRoles as $matrixRole)
        @php
            $moduleKey = strtolower($matrixRole['module']);
        @endphp
        <tr>
            <td>{{ $matrixRole['module_label'] ?? $matrixRole['module'] }}</td>
            <td class="text-center">
                <input type="checkbox" class="access-matrix-checkbox" data-module="{{ $matrixRole['module'] }}" {{ $matrixRole['read_only'] == 1 ? 'checked' : '' }} name="{{ $moduleKey }}_read_only" value="1" />
            </td>
            <td class="text-center">
                <input type="checkbox" class="access-matrix-checkbox" data-module="{{ $matrixRole['module'] }}" {{ $matrixRole['write_only'] == 1 ? 'checked' : '' }} name="{{ $moduleKey }}_write_only" value="1" />
            </td>
            <td class="text-center">
                <input type="checkbox" class="access-matrix-checkbox" data-module="{{ $matrixRole['module'] }}" {{ $matrixRole['update_only'] == 1 ? 'checked' : '' }} name="{{ $moduleKey }}_update_only" value="1" />
            </td>
            <td class="text-center">
                <input type="checkbox" class="access-matrix-checkbox" data-module="{{ $matrixRole['module'] }}" {{ $matrixRole['delete_only'] == 1 ? 'checked' : '' }} name="{{ $moduleKey }}_delete_only" value="1" />
            </td>
            <td class="text-center">
                <input type="checkbox" class="access-matrix-checkbox" data-module="{{ $matrixRole['module'] }}" {{ $matrixRole['read_write_only'] == 1 ? 'checked' : '' }} name="{{ $moduleKey }}_read_write_only" value="1" />
            </td>
        </tr>
        @endforeach
    </table>
</div>
