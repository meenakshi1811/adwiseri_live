@extends('web.layout.main')
@php

use App\Models\User;
@endphp
@section('main-section')

        <div class="col-lg-10 column-client">
            <div class="client-dashboard">
                {{-- <div class="client-btn d-flex mb-2"> --}}
                    {{-- <h3 class="text-primary">Referrals</h3> --}}
                    <div class="client-btn d-flex justify-content-between align-items-center mb-3 ">
                        <h3 class="text-primary text-center flex-grow-1 text-center m-0">Referrals</h3>
                    {{-- <form class="form-inline">
                        <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search">
                      </form>
                      <i class="fa-solid fa-magnifying-glass"></i> --}}
                </div>
                <div class="profile-detail">
                    <div class="col-12 profile-data module-summary-box">
                        {{-- <div class="row">
                            <div class="col-11"></div>
                            <div class="col-1 editss">
                                <img style="cursor: pointer;" onclick="document.getElementById('update_box').style.display='flex';" src="{{ asset('web_assets/images/edit.png') }}"width="20" height="20" alt="">
                            </div>
                        </div> --}}
                        {{-- Name : value, centred on the ":" like the Subscription module. --}}
                        <div class="detail-pairs">
                            <div class="detail-pair">
                                <span class="detail-pair-label">Referral Code</span>
                                <span class="detail-pair-sep">:</span>
                                <span class="detail-pair-value">{{ $user->referral }}</span>
                            </div>
                            <div class="detail-pair">
                                <span class="detail-pair-label">Referral Link</span>
                                <span class="detail-pair-sep">:</span>
                                <span class="detail-pair-value">{{ route('user_register', $user->referral) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-wrapper mt-3">
                    <table class="fl-table table table-hover p-0 m-0" id="userTable">
                        <thead>
                        <tr>
                            <th class="p-1 text-center">Referral ID</th>

                            <th class="p-1 text-center">Referred By (Sub_ID)</th>
                            <th class="p-1 text-center">Referred To (Sub_ID)</th>
                            <th class="p-1 text-center">Sub-Category</th>
                            <th class="p-1 text-center">Plan Name</th>
                            <th class="p-1 text-center">Purchase (USD)</th>

                            <th class="p-1 text-center"> DOS </th>
                            <th class="p-1 text-center">Commission (USD)</th>
                            <th class="p-1 text-center">Referred Date</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($referrals as $key => $ref)
                        <tr>
                            <td class="p-1 text-center">{{ $ref->id }}</td>
                            @php $referrer = User::where('referral', $ref->referral_code)->first(); @endphp
                            <td class="p-1 text-center">{{ $referrer ? $referrer->name . ' (' . $referrer->id . ')' : '-' }}</td>
                            <td class="p-1 text-center">{{ $ref->user_name }} ({{$ref->userid}})</td>
                            <td class="p-1 text-center">{{ $ref->user ? $ref->user->sub_category : '' }}</td>
                            <td class="p-1 text-center">{{ $ref->user ? $ref->user->membership : '' }}</td>
                            <td class="p-1 text-center">{{ $ref->total_amount }}</td>
                            <td class="p-1 text-center">   {{ $ref->user && $ref->user->membership_start_date && $ref->user->membership_expiry_date
                                ? (\Carbon\Carbon::parse($ref->user->membership_start_date)->diffInYears(\Carbon\Carbon::parse($ref->user->membership_expiry_date)) > 0
                                    ? \Carbon\Carbon::parse($ref->user->membership_start_date)->diffInYears(\Carbon\Carbon::parse($ref->user->membership_expiry_date)) . ' year' . (\Carbon\Carbon::parse($ref->user->membership_start_date)->diffInYears(\Carbon\Carbon::parse($ref->user->membership_expiry_date)) > 1 ? 's' : '')
                                    : '')
                                : '' }} </td>
                            <td class="p-1 text-center">{{ $ref->amount_added }}</td>
                            <td class="p-1 text-center">{{  \Carbon\Carbon::parse($ref->created_at)->format('d-m-Y H:i:s') }}</td>
                        </tr>
                        @endforeach
                        <tbody>
                    </table>
                    {{-- <table class="fl-table table table-hover p-0 m-0" id="userTable">
                        <thead>
                        <tr>
                            <th class="p-1 text-center">Sr.No.</th>
                            <th class="p-1 text-start">Referral Code</th>
                            <th class="p-1 text-start">Subscriber Name</th>
                            <th class="p-1 text-start">Amount Received</th>
                            <th class="p-1 text-start">Date</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($referrals as $key => $ref)
                        <tr>
                            <td class="p-1 text-center">{{ $key+1 }}</td>
                            <td class="p-1">{{ $ref->referral_code }}</td>
                            <td class="p-1">{{ $ref->user_name }} ({{$ref->userid}})</td>
                            <td class="p-1">{{ $ref->amount_added }}</td>
                            <td class="p-1">{{ $ref->formatted_created_at }}</td>
                        </tr>
                        @endforeach
                        <tbody>
                    </table> --}}
                </div>
                {{-- <div class="table-btn">
                    <button>Previous</button>
                    <button>1</button>
                    <button>2</button>
                    <button>3</button>
                    <button>Next</button>
                </div> --}}
            </div>
        </div>
    </div>

  </div>

@if(session()->has('deleted'))
  <script>
    Swal.fire({
      icon: 'success',
      title: 'Success',
      text: 'Client deleted successfully.'
    })
  </script>

@endif
@endsection()
