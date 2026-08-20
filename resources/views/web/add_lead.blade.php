@extends('web.layout.main')

@section('main-section')

<div class="col-lg-10 column-client">
    <div class="col-12 d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-primary text-center flex-grow-1 m-0">Add Lead / Enquiry</h3>
        <a href="{{ route('enquiries') }}" class="text-nowrap">Back to Enquiries</a>
    </div>

    <div class="col">
        <form class="register-box login-box" method="POST" action="{{ route('enquiries.store') }}">
            @csrf
            <input type="hidden" name="local_time" class="localtime" />

            <div class="row">
                <div class="col-md-4 p-1">
                    <label>Lead Source<span class="text-danger">*</span></label>
                </div>
                <div class="col-md-8 p-1">
                    <select name="lead_source" class="form-control form-select @error('lead_source') is-invalid @enderror" required>
                        <option value="">Select Source</option>
                        @foreach($leadSources as $sourceOption)
                            <option value="{{ $sourceOption }}" {{ old('lead_source') === $sourceOption ? 'selected' : '' }}>{{ $sourceOption }}</option>
                        @endforeach
                    </select>
                    @error('lead_source')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="col-md-4 p-1">
                    <label>Follow-up Status</label>
                </div>
                <div class="col-md-8 p-1">
                    <select name="lead_status" class="form-control form-select @error('lead_status') is-invalid @enderror">
                        @foreach($leadStatuses as $statusOption)
                            <option value="{{ $statusOption }}" {{ old('lead_status', 'Open') === $statusOption ? 'selected' : '' }}>{{ $statusOption }}</option>
                        @endforeach
                    </select>
                    @error('lead_status')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="col-md-4 p-1">
                    <label>Full Name<span class="text-danger">*</span></label>
                </div>
                <div class="col-md-8 p-1">
                    <input name="full_name" type="text" minlength="3" maxlength="255" class="form-control @error('full_name') is-invalid @enderror" value="{{ old('full_name') }}" required placeholder="Full Name">
                    @error('full_name')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="col-md-4 p-1">
                    <label>Phone<span class="text-danger">*</span></label>
                </div>
                <div class="col-md-8 p-1">
                    <input name="contact_no" type="tel" class="form-control @error('contact_no') is-invalid @enderror" value="{{ old('contact_no') }}" required placeholder="Phone Number">
                    @error('contact_no')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="col-md-4 p-1">
                    <label>Email<span class="text-danger">*</span></label>
                </div>
                <div class="col-md-8 p-1">
                    <input name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required placeholder="Email ID">
                    @error('email')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="col-md-4 p-1">
                    <label>Home Country<span class="text-danger">*</span></label>
                </div>
                <div class="col-md-8 p-1">
                    <select name="country" class="form-control form-select @error('country') is-invalid @enderror" required>
                        <option value="">Select Home Country</option>
                        @include('partials.country_select_options_by_name', [
                            'countries' => $allCountries,
                            'phoneForPrefill' => old('contact_no'),
                            'savedCountry' => old('country'),
                        ])
                    </select>
                    @error('country')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="col-md-4 p-1">
                    <label>COP (1st Preference)<span class="text-danger">*</span></label>
                </div>
                <div class="col-md-8 p-1">
                    <select name="country_pref[]" class="form-control form-select @error('country_pref.0') is-invalid @enderror" required>
                        <option value="">Select Country of Preference</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->country_name }}" {{ old('country_pref.0') === $country->country_name ? 'selected' : '' }}>{{ $country->country_name }}</option>
                        @endforeach
                    </select>
                    @error('country_pref.0')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="col-md-4 p-1">
                    <label>COP (2nd Preference)</label>
                </div>
                <div class="col-md-8 p-1">
                    <select name="country_pref[]" class="form-control form-select">
                        <option value="">Select 2nd Preference</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->country_name }}" {{ old('country_pref.1') === $country->country_name ? 'selected' : '' }}>{{ $country->country_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 p-1">
                    <label>COP (3rd Preference)</label>
                </div>
                <div class="col-md-8 p-1">
                    <select name="country_pref[]" class="form-control form-select">
                        <option value="">Select 3rd Preference</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->country_name }}" {{ old('country_pref.2') === $country->country_name ? 'selected' : '' }}>{{ $country->country_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 p-1">
                    <label>PVC (Visa Category)<span class="text-danger">*</span></label>
                </div>
                <div class="col-md-8 p-1">
                    <select name="visa_category" class="form-control form-select @error('visa_category') is-invalid @enderror" required>
                        <option value="">Select Visa Category</option>
                        @foreach($visaCategories as $category)
                            <option value="{{ $category }}" {{ old('visa_category') === $category ? 'selected' : '' }}>{{ $category }}</option>
                        @endforeach
                    </select>
                    @error('visa_category')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="col-md-4 p-1">
                    <label>Address</label>
                </div>
                <div class="col-md-8 p-1">
                    <textarea name="address" rows="2" class="form-control @error('address') is-invalid @enderror" placeholder="Address">{{ old('address') }}</textarea>
                    @error('address')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="col-md-4 p-1">
                    <label>Postcode</label>
                </div>
                <div class="col-md-8 p-1">
                    <input name="postcode" type="text" maxlength="10" style="text-transform:uppercase" class="form-control @error('postcode') is-invalid @enderror" value="{{ old('postcode') }}" placeholder="Postcode">
                    @error('postcode')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="col-md-4 p-1">
                    <label>Remarks / Notes</label>
                </div>
                <div class="col-md-8 p-1">
                    <textarea name="remarks" rows="2" class="form-control @error('remarks') is-invalid @enderror" placeholder="Optional notes about this lead">{{ old('remarks') }}</textarea>
                    @error('remarks')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="col-md-12 p-1 text-center">
                    <button type="submit" class="btn btn-primary px-4">Save Lead</button>
                </div>
            </div>
        </form>
    </div>
</div>

</div>

</div>

@endsection
