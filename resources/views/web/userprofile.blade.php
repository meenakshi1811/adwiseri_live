@extends('web.layout.main')

@push('css')
<style>
    /* User Profile: details + pictures side by side, compact enough to fit beside the module menu. */
    .profile-layout { display: grid; grid-template-columns: minmax(0, 1.6fr) minmax(0, 1fr); gap: 16px; align-items: start; }
    .profile-card { background: #fff; border: 1px solid var(--ds-border, #E2E6F0); border-radius: 12px; overflow: hidden; }
    .profile-card-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 10px 16px; background: #F4F3FE; border-bottom: 1px solid var(--ds-border, #E2E6F0); }
    .profile-card-head h4 { margin: 0; font-size: 15px !important; font-weight: 700; color: var(--ds-primary-700, #4C3BB7); }
    .profile-edit-btn { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; padding: 0; border: 1px solid transparent; border-radius: 8px; background: transparent; cursor: pointer; }
    .profile-edit-btn:hover, .profile-edit-btn:focus-visible { background: #fff; border-color: var(--ds-border, #E2E6F0); }
    /* Each label sits right next to its value (no wide gap between the two). */
    .profile-fields { display: grid; grid-template-columns: max-content minmax(0, 1fr); column-gap: 28px; row-gap: 9px; margin: 0; padding: 16px 20px; font-size: 14px; line-height: 1.45; }
    .profile-fields dt { font-weight: 600; color: var(--ds-ink-2, #1E293B); }
    .profile-fields dd { margin: 0; color: #334155; overflow-wrap: anywhere; }
    .profile-fields dd p { margin: 0; }
    .profile-fields .profile-field-link { font-size: inherit; font-weight: 400; text-decoration: underline; }
    .profile-side { display: flex; flex-direction: column; gap: 16px; }
    .profile-media { display: flex; align-items: center; justify-content: center; padding: 16px; }
    .profile-media .profile-photo { width: 150px; height: 150px; object-fit: cover; border-radius: 12px; }
    .profile-media .profile-logo-img { display: block; width: auto; max-width: 100%; max-height: 96px; height: auto; object-fit: contain; }
    @media (max-width: 991.98px) { .profile-layout { grid-template-columns: 1fr; } }
    @media (max-width: 575.98px) { .profile-fields { grid-template-columns: 1fr; row-gap: 2px; } .profile-fields dd { margin-bottom: 8px; } }
</style>
@endpush

@section('main-section')

    <div class="col-lg-10 userdash-client column-client">
        {{-- <h3>Profile</h3> --}}
        <!-- <div class="client-btn d-flex justify-content-between mb-4"> -->
        <div class="col-12 d-flex justify-content-between align-items-center mb-3">
            <h3 style="font-size: 1.7rem;font-weight: 500;line-height: 1.2" class="text-primary text-center flex-grow-1 text-center m-0">User Profile</h3>
            <a href="#" onclick="document.getElementById('change_password').style.display='flex';">Change Password</a>
        </div>
        @php
            $profileUserType = match (strtolower((string) $user->user_type)) {
                'subscriber' => 'Subscriber',
                'affiliate' => 'Affiliate',
                'admin' => 'Admin',
                'user' => 'Staff Member',
                default => $user->user_type ?: '—',
            };
            $profileDetailsTitle = strtolower((string) $user->user_type) === 'subscriber' ? 'Subscriber Details' : 'User Details';
        @endphp
        {{-- Details on the left, Profile Picture and Organization Logo stacked on the right, so the whole
             profile fits on one screen beside the module menu (like the Dashboard). --}}
        <div class="profile-layout">
            <section class="profile-card profile-card-details" aria-labelledby="profileDetailsTitle">
                <div class="profile-card-head">
                    <h4 id="profileDetailsTitle">{{ $profileDetailsTitle }}</h4>
                    <button type="button" class="profile-edit-btn" title="Edit details" aria-label="Edit details"
                        onclick="document.getElementById('update_box').style.display='flex';">
                        <img src="{{ asset('web_assets/images/edit.png') }}" width="18" height="18" alt="">
                    </button>
                </div>
                <dl class="profile-fields">
                    <dt>Name</dt>
                    <dd>{{ $user->name }}</dd>
                    <dt>User Type</dt>
                    <dd>{{ $profileUserType }}</dd>
                    <dt>Phone Number</dt>
                    <dd>@include('partials.phone_display', ['phone' => $user->phone])</dd>
                    <dt>Email ID</dt>
                    <dd>{{ $user->email }}</dd>
                    <dt>Organization</dt>
                    <dd>{{ $user->organization ?: '—' }}</dd>
                    <dt>Designation</dt>
                    <dd>{{ $user->designation ?: '—' }}</dd>
                    <dt>Category</dt>
                    <dd>{{ $user->category ?: '—' }}</dd>
                    <dt>Sub-Category</dt>
                    <dd>{{ $user->sub_category ?: '—' }}@if($user->sub_category == "Other") ({{ $user->oter_subcategory }})@endif</dd>
                    <dt>Country</dt>
                    <dd>{{ $user->country ?: '—' }}</dd>
                    <dt>State/County</dt>
                    <dd>{{ $user->state ?: '—' }}</dd>
                    <dt>City/Town</dt>
                    <dd>{{ $user->city ?: '—' }}</dd>
                    <dt>Address</dt>
                    <dd>{{ $user->address_line ?: '—' }}</dd>
                    <dt>Website</dt>
                    <dd>@if(!empty($user->website))<a class="btn btn-link p-0 align-baseline profile-field-link" href="{{ \App\Support\BrandedMail::normalizeWebsiteUrl($user->website) }}" target="_blank" rel="noopener">{{ $user->website }}</a>@else — @endif</dd>
                    <dt>Postcode</dt>
                    <dd>{{ $user->pincode ?: '—' }}</dd>
                    <dt>Timezone</dt>
                    <dd>{{ $user->timezone ?: '—' }}</dd>
                </dl>
            </section>

            <div class="profile-side">
                <section class="profile-card" aria-labelledby="profilePictureTitle">
                    <div class="profile-card-head">
                        <h4 id="profilePictureTitle">Profile Picture</h4>
                        <button type="button" class="profile-edit-btn" title="Edit Profile Picture" aria-label="Edit profile picture"
                            onclick="document.getElementById('update_img_box').style.display='flex';">
                            <img src="{{ asset('web_assets/images/edit.png') }}" width="18" height="18" alt="">
                        </button>
                    </div>
                    <div class="profile-media">
                        @if ($user->profile_img != '')
                            <img class="profile-photo" src="{{ asset('web_assets/users/user' . $user->id . '/' . $user->profile_img) }}" alt="Profile picture">
                        @else
                            <img class="profile-photo" src="{{ asset('web_assets/images/profile.jpg') }}" alt="Profile picture">
                        @endif
                    </div>
                </section>

                @if ($user->user_type == 'Subscriber')
                    <section class="profile-card" aria-labelledby="profileLogoTitle">
                        <div class="profile-card-head">
                            <h4 id="profileLogoTitle">Organization Logo</h4>
                            <button type="button" class="profile-edit-btn" title="Edit Logo" aria-label="Edit organization logo"
                                onclick="document.getElementById('update_logo_box').style.display='flex';">
                                <img src="{{ asset('web_assets/images/edit.png') }}" width="18" height="18" alt="">
                            </button>
                        </div>
                        <div class="profile-media">
                            @if ($user->organization_logo != '')
                                <img class="profile-logo-img" src="{{ asset('web_assets/users/user' . $user->id . '/' . $user->organization_logo) }}" alt="Organization logo">
                            @else
                                <img class="profile-logo-img" src="{{ asset('web_assets/images/default_logo.png') }}" alt="Default logo">
                            @endif
                        </div>
                    </section>
                @endif
            </div>
        </div>
    </div>
    </div>

    </div>
    <div id="update_box"
        style="width:100%;display: none;flex-direction: column;position: fixed;top: 0;left: 0;height: 100vh;overflow: scroll; background: rgba(0, 0, 0, 0.3);">
        <div class="row">
            <div class="col-lg-4"></div>
            <div class="col-lg-4 loginouter-box">
                <div class="col text-end"><button class="btn btn-danger" style="width:fit-content;"
                        onclick="document.getElementById('update_box').style.display='none';">Close</button></div>
                <form class="details-box login-box" method="POST" action="{{ route('update_user') }}">
                    @csrf
                    <input type="hidden" name="profile" value="profile">
                    <input type="hidden" name="local_time" class="localtime" />
                    <h3 class="mb-5 pt-3 text-center">Update Profile</h3>
                    <div class="log-img mb-5">
                        @if ($user->profile_img == '')
                            <img src="{{ asset('web_assets/images/loginimg.png') }}" width="60" height="60"
                                alt="">
                        @else
                            <img src="{{ asset('web_assets/users/user' . $user->id . '/' . $user->profile_img) }}" width="60"
                                height="60" alt="">
                        @endif
                    </div>
                    <div class="mb-4">
                        <input name="name" minlength="3" maxlength="100" value="{{ $user->name }}" required
                            type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp"
                            placeholder="Name">
                    </div>
                    <div class="mb-4">
                        <input name="phone" value="{{ \App\Support\PhoneNumber::displayE164($user->phone) }}" data-phone-e164="{{ \App\Support\PhoneNumber::displayE164($user->phone) }}" required type="tel"
                            class="form-control" id="profile_phone_input" aria-describedby="emailHelp"
                            placeholder="Phone">
                    </div>
                    <div class="mb-4">
                        <input name="organization" minlength="3" maxlength="100" value="{{ $user->organization }}"
                            required type="text" class="form-control" id="exampleInputEmail1"
                            aria-describedby="emailHelp" placeholder="Organization Name">
                    </div>
                    <div class="mb-4">
                        <input name="designation" minlength="3" maxlength="100" value="{{ $user->designation }}"
                            required type="text" class="form-control" id="exampleInputEmail1"
                            aria-describedby="emailHelp" placeholder="Your designation">
                    </div>
                    <div class="mb-4">
                        <div class="mb-4">
                            <select name="employee_strength" required class="form-select" id="exampleInputEmail1"
                                aria-describedby="emailHelp">
                                <option value="">Employee Strength</option>
                                <option {{ $user->employee_strength == '1-10' ? 'selected' : '' }} value="1-10">1-10
                                </option>
                                <option {{ $user->employee_strength == '10-20' ? 'selected' : '' }} value="10-20">10-20
                                </option>
                                <option {{ $user->employee_strength == '20-50' ? 'selected' : '' }} value="20-50">20-50
                                </option>
                                <option {{ $user->employee_strength == '50-100' ? 'selected' : '' }} value="50-100">
                                    50-100</option>
                                <option {{ $user->employee_strength == 'Above 100' ? 'selected' : '' }}
                                    value="Above 100">Above 100</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-4">
                        <input name="address_line" minlength="3" maxlength="150" value="{{ $user->address_line }}"
                            required type="text" class="form-control" id="exampleInputEmail1"
                            aria-describedby="emailHelp" placeholder="Address line">
                    </div>
                    <div class="mb-4">
                        <input name="website" maxlength="255" value="{{ $user->website ?? '' }}"
                            type="url" class="form-control" id="profile_website_input"
                            aria-describedby="emailHelp" placeholder="Website (https://example.com)">
                    </div>
                    <div class="mb-4">
                        <select name="country" id="country" required class="form-select"
                            aria-label="Default select example">
                            <option selected value="">Country</option>
                            @include('partials.country_select_options', [
                                'countries' => $countries,
                                'phoneForPrefill' => $user->phone ?? null,
                                'savedCountry' => $user->country ?? null,
                                'savedIsCountryName' => true,
                            ])
                        </select>
                        <small id="subscriber-country-lock-hint" class="text-muted d-block mt-1" style="display:none;"></small>
                    </div>
                    <div class="mb-4">
                        <select name="state" id="state" required class="form-select"
                            aria-label="Default select example">
                            @foreach ($states as $state)
                                <option {{ $user->state == $state->name ? 'selected' : '' }} value="{{ $state->name }}">
                                    {{ $state->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <input type="text" minlength="3" maxlength="100" value="{{ $user->city }}"
                            name="city" required class="form-control" aria-label="Default select example"
                            placeholder="City">
                    </div>
                    <div class="mb-4">
                        <input name="pincode" minlength="3" maxlength="10" style="text-transform:uppercase"
                            value="{{ $user->pincode }}" required type="text" class="form-control"
                            id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Postcode">
                    </div>
                    <div class="mb-4">
                        <select name="timezone" id="timezone" required class="form-select"
                            aria-label="Default select example">
                            <option selected value="">Select Timezone</option>
                            @foreach ($tzlist as $zone)
                                <option {{ $user->timezone == $zone ? 'selected' : '' }} value="{{ $zone }}">
                                    {{ $zone }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary mb-4" style="width: fit-content;">Save</button>
                    </div>
                    <!-- <a href="dashboard.html" class="btn btn-primary mb-4">Next</a> -->
                    <!-- <p class="text-center reg-logbtn">Already have an account! <a href="{{ route('login') }}" class="text-dark"> <strong>Login</strong></a></p> -->
                </form>
            </div>
            <div class="col-lg-4"></div>
        </div>
    </div>
    <div id="update_img_box"
        style="width:100%;display: none;flex-direction: column;position: fixed;top: 0;left: 0;height: 100vh;overflow: scroll; background: rgba(0, 0, 0, 0.3);justify-content: center;">
        <div class="row">
            <div class="col-lg-4"></div>
            <div class="col-lg-4 loginouter-box">
                <div class="col text-end"><button class="btn btn-danger" style="width:fit-content;"
                        onclick="document.getElementById('update_img_box').style.display='none';">Close</button></div>
                <form class="details-box login-box" method="POST" action="{{ route('update_user') }}"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="profile_image" value="profile_image">
                    <input type="hidden" name="local_time" class="localtime" />
                    <h3 class="mb-5 pt-3 text-center">Update Profile Image</h3>
                    <div class="log-img mb-5">
                        @if ($user->profile_img == '')
                            <img src="{{ asset('web_assets/images/loginimg.png') }}" width="60" height="60"
                                alt="">
                        @else
                            <img style="border-radius: 50%;"
                                src="{{ asset('web_assets/users/user' . $user->id . '/' . $user->profile_img) }}"
                                width="60" height="60" alt="">
                        @endif
                    </div>

                    <div class="col d-flex justify-content-center align-items-center mb-5"
                        onclick="document.getElementById('select_pic').click();">
                        <div style="width:100%;height:200px;box-shadow: 0px 0px 5px 0px lightgrey;border-radius: 10px;justify-content: center;align-items: center;display: flex;position:relative;"
                            title="click to upload file">
                            <input id="select_pic" type="file" name="profile_img" style="display: none;">
                            <p style="position:absolute;">Click to Upload File</p>
                            <img id="profile_pic_preview"
                                style="width: auto;height: auto;max-width: 100%;max-height: 100%;" src="">
                        </div>
                    </div>

                    <button type="submit" disabled="disabled" id="save_photo"
                        class="btn btn-primary mb-4" style="width: fit-content;">Save</button>
                    <!-- <a href="dashboard.html" class="btn btn-primary mb-4">Next</a> -->
                    <!-- <p class="text-center reg-logbtn">Already have an account! <a href="{{ route('login') }}" class="text-dark"> <strong>Login</strong></a></p> -->
                </form>
            </div>
            <div class="col-lg-4"></div>
        </div>
    </div>
    <div id="update_logo_box"
        style="width:100%;display: none;flex-direction: column;position: fixed;top: 0;left: 0;height: 100vh;overflow: scroll; background: rgba(0, 0, 0, 0.3);justify-content: center;">
        <div class="row">
            <div class="col-lg-4"></div>
            <div class="col-lg-4 loginouter-box">
                <div class="col text-end"><button class="btn btn-danger" style="width:fit-content;"
                        onclick="document.getElementById('update_logo_box').style.display='none';">Close</button></div>
                <form class="details-box login-box" method="POST" action="{{ route('update_user') }}"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="logo_image" value="logo_image">
                    <input type="hidden" name="local_time" class="localtime" />
                    <h3 class="mb-5 pt-3 text-center">Update Organization Logo</h3>
                    <div class="log-img mb-5">
                        @if ($user->profile_img == '')
                            <img src="{{ asset('web_assets/images/loginimg.png') }}" width="60" height="60"
                                alt="">
                        @else
                            <img style="border-radius: 50%;"
                                src="{{ asset('web_assets/users/user' . $user->id . '/' . $user->profile_img) }}"
                                width="60" height="60" alt="">
                        @endif
                    </div>

                    <div class="col d-flex justify-content-center align-items-center mb-5"
                        onclick="document.getElementById('select_logo').click();">
                        <div style="width:100%;height:200px;box-shadow: 0px 0px 5px 0px lightgrey;border-radius: 10px;justify-content: center;align-items: center;display: flex;position:relative;"
                            title="click to upload file">
                            <input id="select_logo" type="file" name="organization_logo" style="display: none;">
                            <p style="position:absolute;">Click to Upload File</p>
                            <img id="logo_preview" style="width: auto;height: auto;max-width: 100%;max-height: 100%;"
                                src="">
                        </div>
                    </div>

                    <button type="submit" disabled id="save_logo"
                        class="btn btn-primary mb-4" style="width: fit-content;">Save</button>
                    <!-- <a href="dashboard.html" class="btn btn-primary mb-4">Next</a> -->
                    <!-- <p class="text-center reg-logbtn">Already have an account! <a href="{{ route('login') }}" class="text-dark"> <strong>Login</strong></a></p> -->
                </form>
            </div>
            <div class="col-lg-4"></div>
        </div>
    </div>
    <div id="change_password"
        style="width:100%;display: none;flex-direction: column;position: fixed;top: 0;left: 0;height: 100vh;overflow: scroll; background: rgba(0, 0, 0, 0.3);">
        <div class="row">
            <div class="col-lg-4"></div>
            <div class="col-lg-4 loginouter-box">
                <div class="col text-end"><button class="btn btn-danger" style="width:fit-content;"
                        onclick="document.getElementById('change_password').style.display='none';">Close</button></div>
                <form class="details-box login-box" method="POST" action="{{ route('change_password') }}">
                    @csrf
                    <input type="hidden" name="local_time" class="localtime" />
                    <h3 class="mb-5 pt-3 text-center">Change Password</h3>
                    <div class="log-img mb-5">
                        @if ($user->profile_img == '')
                            <img src="{{ asset('web_assets/images/loginimg.png') }}" width="60" height="60"
                                alt="">
                        @else
                            <img src="{{ asset('web_assets/users/user' . $user->id . '/' . $user->profile_img) }}" width="60"
                                height="60" alt="">
                        @endif
                    </div>
                    <div class="mb-4">
                        <input name="old_password" required type="password" class="form-control" id="old_password" aria-describedby="emailHelp" placeholder="Old Password">
                    </div>
                    <div class="mb-4">
                        <input name="new_password" pattern="^[a-zA-Z0-9$#@!&%_=+-]+$" minlength="8" maxlength="100" required type="password" class="form-control" id="new_password" aria-describedby="emailHelp" placeholder="New Password">
                    </div>

                    <div class="mb-4">
                        <input name="password" pattern="^[a-zA-Z0-9$#@!&%_=+-]+$" minlength="8" maxlength="100" required type="password" class="form-control" id="password" aria-describedby="emailHelp" placeholder="Confirm Password">
                    </div>
                    <button type="submit" id="save_password" disabled class="btn btn-primary mb-4" style="width: fit-content;">Save</button>
                    <!-- <a href="dashboard.html" class="btn btn-primary mb-4">Next</a> -->
                    <!-- <p class="text-center reg-logbtn">Already have an account! <a href="{{ route('login') }}" class="text-dark"> <strong>Login</strong></a></p> -->
                </form>
            </div>
            <div class="col-lg-4"></div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script>
        $(document).ready(() => {
            $("#select_pic").change(function() {
                const file = this.files[0];
                var filepath = $(this).val();
                var allowedExtensions = /(\.jpg|\.jpeg|\.png|\.JPG|\.JPEG|\.PNG)$/i;
                if (!allowedExtensions.exec(filepath)) {
                    Swal.fire({
                        title: "Oops!",
                        icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                        html: "Please select a valid file format.<br>(jpg, jpeg, png)"
                    });
                    $(this).val("");
                    return false;
                }
                const size = (this.files[0].size / 1024 / 1024).toFixed(2);
                if (size > 4) {
                    Swal.fire({
                        title: "Oops!",
                        icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                        html: "Please select a file up to 4 MB."
                    });
                    $(this).val("");
                    return false;
                }
                if (file) {
                    let reader = new FileReader();
                    reader.onload = function(event) {
                        $("#profile_pic_preview").attr("src", event.target.result);
                    };
                    reader.readAsDataURL(file);
                }
                $("#save_photo").removeAttr('disabled');
            });
            $("#select_logo").change(function() {
                const file = this.files[0];
                var filepath = $(this).val();
                var allowedExtensions = /(\.jpg|\.jpeg|\.png|\.JPG|\.JPEG|\.PNG)$/i;
                if (!allowedExtensions.exec(filepath)) {
                    Swal.fire({
                        title: "Oops!",
                        icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                        html: "Please select a valid file format.<br>(jpg, jpeg, png)"
                    });
                    $(this).val("");
                    return false;
                }
                const size = (this.files[0].size / 1024 / 1024).toFixed(2);
                if (size > 4) {
                    Swal.fire({
                        title: "Oops!",
                        icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                        html: "Please select a file up to 4 MB."
                    });
                    $(this).val("");
                    return false;
                }
                if (file) {
                    let reader = new FileReader();
                    reader.onload = function(event) {
                        $("#logo_preview").attr("src", event.target.result);
                    };
                    reader.readAsDataURL(file);
                }
                $("#save_logo").removeAttr('disabled');
            });
            $("#country").change(function() {
                var country = $(this).val();
                // console.log(counrty);
                $.ajax({
                    url: 'get_states',
                    method: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        country: country,
                    },
                    cache: false,
                    success: function(data) {
                        console.log(data);
                        $("#state").html(data);
                    }
                });
                $.ajax({
                    url: @json(route('get_timezone')),
                    method: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        country: country,
                    },
                    cache: false,
                    success: function(data) {
                        // console.log("zones = "+data);
                        $("#timezone").html(data);
                    }
                });
            });
            $("#password").keyup(function(){
                var new_pass = $("#new_password").val();
                var pass = $(this).val();
                if(pass == new_pass){
                    $("#save_password").removeAttr('disabled');
                }
                else{
                    $("#save_password").attr('disabled','disabled');
                }
            });
            $("#new_password").keyup(function(){
                var new_pass = $("#old_password").val();
                var pass = $(this).val();
                if(pass == new_pass){
                    $("#save_password").removeAttr('disabled');
                }
                else{
                    $("#save_password").attr('disabled','disabled');
                }
            });
        });
    </script>
    @if ($user->user_type == 'Subscriber')
        @include('partials.subscriber_country_lock_script', ['initialSubCategory' => $user->sub_category ?? null])
    @endif
    @error('profile_img')
        <script>
            Swal.fire({

                icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                title: 'Oops!',
                text: 'Please select a valid image.'
            })
        </script>
    @enderror
    @if (session()->has('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Congratulations',
                text: 'Profile has been Updated!'
            })
        </script>
    @endif
    @if (session()->has('logo_updated'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Congratulations',
                text: 'Logo has been Updated!'
            })
        </script>
    @endif

    @if(session()->has('price_plan_expiry'))
  <script>
    Swal.fire({
      icon: 'warning',
      title: 'Your subscription plan has expired',
      html: @json('Please <a href="' . ($user->user_type == 'Subscriber' ? route('membership') : '#') . '">renew or upgrade</a> your plan to continue.')
    })
  </script>

@endif
    @if (session()->has('wrong_password'))
        <script>
            Swal.fire({
                icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
                title: 'Wrong Password!',
                text: 'The old password you entered is incorrect.'
            })
        </script>
    @endif

@endsection()
