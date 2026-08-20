@extends('web.layout.main')

@section('main-section')
@php

use App\Models\UserRoles;
$client_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Clients')->first();
$application_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Applications')->first();
$communication_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Communication')->first();
$invoice_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Invoices')->first();
$payment_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Payments')->first();
$report_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Reports')->first();
$subscription_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Subscription')->first();
$setting_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Settings')->first();
$support_roles = UserRoles::where('user_id','=',$user->id)->where('module','=','Support')->first();
@endphp
    <div class="col-lg-10 column-client">
        <div class="col position-relative py-2 mb-2">
            <h3 class="text-primary text-center m-0">Support</h3>
            @if($support_roles->write_only == 1 or $support_roles->read_write_only == 1)
            <div class="position-absolute top-50 end-0 translate-middle-y pe-2">
                <a href="{{ route('ask_support') }}"><b>Ask For Support (Raise Ticket)</b></a>
            </div>
            @endif
        </div>
        <div class="col">
            <p class="px-2 m-0 text-center"><strong>FAQs</strong></p>
            @foreach($faqs as $faq)
            <div class="col p-1">
                <button class="btn btn-outline-dark text-start" style="width: 100%;" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseExample{{ $faq->id }}" aria-expanded="false" aria-controls="collapseExample{{ $faq->id }}">
                    {{ $faq->question }}
                </button>
                <div class="collapse" id="collapseExample{{ $faq->id }}">
                    <div class="card card-body border-dark bg-light text-dark p-2 m-0">
                        {{ $faq->answer }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>

    </div>

    </div>

    </div>


    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script>
        $(document).ready(() => {

        });
    </script>
    @if (session()->has('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Thank you',
                text: 'Your message was sent to support. Our team will contact you shortly.',
            });
        </script>
    @endif
@endsection()
