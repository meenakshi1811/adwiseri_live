@extends('admin.layout.main')

@section('main-section')
        <div class="col-lg-10 column-client">
            <div class="client-dashboard">
                <div class="client-btn d-flex mb-2 ">
                    <form class="form-inline d-flex justify-content-between w-100 align-items-center">
                        <h3 class="text-primary text-center flex-grow-1 text-center m-0">Error Log</h3>
                        <a href="{{ route('error_logs_export') }}" class="btn btn-primary">
                            <i class="fa-solid fa-download"></i> Download
                        </a>
                    </form>
                </div>
                <div class="table-wrapper">
                    <table class="table table-hover table-bordered fl-table" id="errorLogTable">
                        <thead>
                        <tr>
                            <th class="text-center">ID</th>
                            <th class="text-center">Error Type</th>
                            <th class="text-center">Page/Screen</th>
                            <th class="text-center">Message/Description</th>
                            <th class="text-center">DateTime</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($errorLogs as $log)
                        <tr>
                            <td class="text-center">{{ $log->id }}</td>
                            <td class="text-center">{{ $log->error_type }}</td>
                            <td class="text-center">{{ $log->page_screen }}</td>
                            <td class="text-start">{{ $log->message }}</td>
                            <td class="text-center">{{ $log->created_at ? $log->created_at->format('d-m-Y H:i:s') : '' }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
  </div>
@endsection
