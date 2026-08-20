@extends('web.layout.main')

@section('main-section')

    <div class="col-lg-10 column-client">
        <h3 class="text-primary text-center px-2">Edit Associate <small class="text-secondary">({{ $associate->associate_code }})</small></h3>
        <div class="col">
            <form id="associate_form" class="register-box login-box" method="POST" action="{{ route('update_associate') }}" autocomplete="off">
                @csrf
                <input type="hidden" name="id" value="{{ $associate->id }}" />
                <div class="row">
                    <div class="col-md-4 p-1"><label>Name<span class="text-danger" style="font-size:18px;">*</span></label></div>
                    <div class="col-md-8 p-1">
                        <input name="name" minlength="3" maxlength="100" required type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $associate->name) }}" placeholder="Name">
                        @error('name')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                    </div>

                    <div class="col-md-4 p-1"><label>Phone<span class="text-danger" style="font-size:18px;">*</span></label></div>
                    <div class="col-md-8 p-1">
                        <input name="phone" type="tel" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $associate->phone) }}" required placeholder="Phone Number">
                        @error('phone')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                    </div>

                    <div class="col-md-4 p-1"><label>Email<span class="text-danger" style="font-size:18px;">*</span></label></div>
                    <div class="col-md-8 p-1">
                        <input name="email" minlength="3" maxlength="100" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $associate->email) }}" required placeholder="Email ID">
                        @error('email')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                    </div>

                    <div class="col-md-4 p-1"><label>Organization</label></div>
                    <div class="col-md-8 p-1">
                        <input name="organization" maxlength="255" type="text" class="form-control @error('organization') is-invalid @enderror" value="{{ old('organization', $associate->organization) }}" placeholder="Organization (optional)">
                        @error('organization')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                    </div>

                    <div class="col-md-4 p-1"><label>Country<span class="text-danger" style="font-size:18px;">*</span></label></div>
                    <div class="col-md-8 p-1">
                        <select name="country" id="country" class="form-control form-select @error('country') is-invalid @enderror" required>
                            <option value="">Select Country</option>
                            @include('partials.country_select_options', ['countries' => $countries, 'savedCountry' => $associate->country, 'savedIsCountryName' => true])
                        </select>
                        @error('country')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                    </div>

                    <div class="col-md-4 p-1"><label>State/County<span class="text-danger" style="font-size:18px;">*</span></label></div>
                    <div class="col-md-8 p-1">
                        <select name="state" id="state" class="form-control form-select @error('state') is-invalid @enderror" required>
                            <option value="{{ $associate->state }}" selected>{{ $associate->state }}</option>
                        </select>
                        @error('state')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                    </div>

                    <div class="col-md-4 p-1"><label>City/Town<span class="text-danger" style="font-size:18px;">*</span></label></div>
                    <div class="col-md-8 p-1">
                        <input name="city" type="text" minlength="2" maxlength="100" class="form-control @error('city') is-invalid @enderror" value="{{ old('city', $associate->city) }}" required placeholder="City">
                        @error('city')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                    </div>

                    <div class="col-md-4 p-1"><label>Postcode<span class="text-danger" style="font-size:18px;">*</span></label></div>
                    <div class="col-md-8 p-1">
                        <input name="pincode" minlength="3" maxlength="10" style="text-transform:uppercase" type="text" class="form-control @error('pincode') is-invalid @enderror" value="{{ old('pincode', $associate->pincode) }}" required placeholder="Postcode">
                        @error('pincode')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                    </div>

                    <div class="col-md-4 p-1"><label>Client's Home Country</label></div>
                    <div class="col-md-8 p-1">
                        <select name="home_country" class="form-control form-select">
                            <option value="">Select Home Country</option>
                            @foreach($countries as $c)
                            <option value="{{ $c->country_name }}" {{ old('home_country', $associate->home_country) == $c->country_name ? 'selected' : '' }}>{{ $c->country_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 p-1"><label>Visa Country</label></div>
                    <div class="col-md-8 p-1">
                        <select name="visa_country" class="form-control form-select">
                            <option value="">Select Visa Country</option>
                            @foreach($countries as $c)
                            <option value="{{ $c->country_name }}" {{ old('visa_country', $associate->visa_country) == $c->country_name ? 'selected' : '' }}>{{ $c->country_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 p-1"><label>Application Type</label></div>
                    <div class="col-md-8 p-1">
                        <select name="application_type" class="form-control form-select">
                            <option value="">Select Application Type</option>
                            @foreach(['Admission','Visa Processing','AD+VP','Student Visa','Work Visa','Visitor Visa','PR / Immigration','Other'] as $type)
                            <option value="{{ $type }}" {{ old('application_type', $associate->application_type) == $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-12 p-1 text-center">
                        <button type="submit" class="btn btn-primary px-4">Update</button>
                        <a href="{{ route('associates') }}" class="btn btn-outline-primary px-4 ms-3">Back</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script>
    $(document).ready(() => {
        $("#country").change(function(){
            var country = $(this).val();
            $.ajax({
                url: "{{ route('get_states') }}",
                method: 'POST',
                data: { "_token": "{{ csrf_token() }}", country: country },
                cache: false,
                success: function(data){ $("#state").html(data); }
            });
        });
    });
</script>

@endsection()
