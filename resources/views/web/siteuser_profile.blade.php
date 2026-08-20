@extends('web.layout.main')

@section('main-section')

        <div class="col-lg-10 userdash-client column-client profile-page-shell">
        <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 profile-page-header">
            <h3 class="text-primary text-center flex-grow-1 m-0 profile-page-title">Staff Profile</h3>
            <a href="{{ route('users') }}" class="text-nowrap">Back to Users</a>
        </div>
            <div class="profile-detail profile-detail-responsive">
                <div class="col-12 col-lg-7 profile-data profile-card-panel">
                    <div class="row">
                        <div class="col-11"></div>
                        <div class="col-1 editss">
                        <img style="cursor: pointer;" onclick="document.getElementById('siteuser_update_box').style.display='flex';" src="{{ asset('web_assets/images/edit.png') }}"width="20" height="20" alt="Edit staff profile" title="Edit Profile">
                        </div>
                    </div>
                    <div class="row det-row profile-detail-grid">
                        <div class="col-12 col-sm-6 col-md-5">
                            <p style="font-weight:550;">Name</p>
                        </div>
                        <div class="col-6">
                            <p>{{ $siteuser->name }}</p>
                        </div>
                        <div class="col-6">
                            <p style="font-weight:550;">User Type</p>
                        </div>
                        <div class="col-6">
                            <p>Staff Member</p>
                        </div>
                        <div class="col-6">
                            <p style="font-weight:550;">Phone Number</p>
                        </div>
                        <div class="col-6">
                            <p>@include('partials.phone_display', ['phone' => $siteuser->phone])</p>
                        </div>
                        <div class="col-6">
                            <p style="font-weight:550;">Email ID</p>
                        </div>
                        <div class="col-6">
                            <p>{{ $siteuser->email }}</p>
                        </div>
                        <div class="col-6">
                            <p style="font-weight:550;">Date of Birth</p>
                        </div>
                        <div class="col-6">
                            <p>{{  $siteuser->formatted_dob }}</p>
                        </div>
                        <div class="col-6">
                            <p style="font-weight:550;">Organization</p>
                        </div>
                        <div class="col-6">
                            <p>{{ $siteuser->organization ?: '—' }}</p>
                        </div>
                        <div class="col-6">
                            <p style="font-weight:550;">Designation/Role</p>
                        </div>
                        <div class="col-6">
                            <p>{{ $siteuser->designation }}</p>
                        </div>
                        <div class="col-6">
                            <p style="font-weight:550;">Country</p>
                        </div>
                        <div class="col-6">
                            <p>{{ $siteuser->country }}</p>
                        </div>
                        <div class="col-6">
                            <p style="font-weight:550;">State/County</p>
                        </div>
                        <div class="col-6">
                            <p>{{ $siteuser->state }}</p>
                        </div>
                        <div class="col-6">
                            <p style="font-weight:550;">City/Town</p>
                        </div>
                        <div class="col-6">
                            <p>{{ $siteuser->city }}</p>
                        </div>
                        <div class="col-6">
                            <p style="font-weight:550;">Postcode</p>
                        </div>
                        <div class="col-6">
                            <p>{{ $siteuser->pincode }}</p>
                        </div>
                        <div class="col-6">
                            <p style="font-weight:550;">Timezone</p>
                        </div>
                        <div class="col-6">
                            <p>{{ $siteuser->timezone }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-4 profile-pic profile-card-panel mt-3 mt-lg-0">
                    <div class="row align-items-center profile-picture-block">
                        <div class="col-10">
                            <p class="profile-logo-label mb-0">Profile Picture</p>
                        </div>
                        <div class="col-2 text-end">
                            <img style="cursor: pointer;" onclick="document.getElementById('siteuser_update_img_box').style.display='flex';" src="{{ asset('web_assets/images/edit.png') }}"width="20" height="20" alt="Edit profile picture" title="Edit Profile Picture">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 profilepic-row">
                            @if($siteuser->profile_img != "")
                            <img src="{{ asset('web_assets/users/user'.$siteuser->id.'/'.$siteuser->profile_img) }}" width="200" height="200" alt="Profile picture">
                            @else
                            <img src="{{ asset('web_assets/images/profile.jpg') }}" width="200" height="200" alt="Profile picture">
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

  </div>
  <div id="siteuser_update_box" class="profile-modal-overlay" style="display: none;">
    <div class="row justify-content-center g-0">
        <div class="col-12 col-md-10 col-lg-6 col-xl-5 loginouter-box profile-modal-panel">
            <div class="col text-end"><button type="button" class="btn btn-danger" style="width:fit-content;" onclick="document.getElementById('siteuser_update_box').style.display='none';">Close</button></div>
            <form class="details-box login-box" method="POST" action="{{ route('update_siteuser') }}">
            @csrf
            <input type="hidden" name="local_time" class="localtime" />
            <input type="hidden" name="id" value="{{ $siteuser->id }}">
            <input type="hidden" name="staff_profile" value="1">
                <h3 class="mb-5 pt-3 text-center">Update Staff Profile</h3>
                <div class="log-img mb-5">
                @if($siteuser->profile_img == "")
                <img src="{{ asset('web_assets/images/loginimg.png') }}" width="60" height="60" alt="">
                @else
                    <img src="{{ asset('web_assets/users/user'.$siteuser->id.'/'.$siteuser->profile_img) }}" width="60" height="60" alt="">
                @endif
                </div>
                <div class="mb-4">
                    <input name="name" minlength="3" maxlength="100" value="{{ old('name', $siteuser->name) }}" required type="text" class="form-control" placeholder="Name">
                </div>
                <div class="mb-4">
                    <input name="phone" value="{{ old('phone', \App\Support\PhoneNumber::displayE164($siteuser->phone)) }}" data-phone-e164="{{ \App\Support\PhoneNumber::displayE164($siteuser->phone) }}" required type="tel" class="form-control" placeholder="Phone">
                </div>
                <div class="mb-4">
                    <input name="dob" value="{{ old('dob', $siteuser->dob) }}" required type="date" max="{{ date('Y-m-d') }}" class="form-control date" placeholder="Date of Birth">
                </div>
                <div class="mb-4">
                    <select name="designation" class="form-control form-select @error('designation') is-invalid @enderror" required autocomplete="designation">
                        <option value="">Select Designation/Role</option>
                        <option {{ old('designation', $siteuser->designation) == "Branch Manager" ? 'selected' : '' }} value="Branch Manager">Branch Manager</option>
                        <option {{ old('designation', $siteuser->designation) == "Solicitor Partner" ? 'selected' : '' }} value="Solicitor Partner">Solicitor Partner</option>
                        <option {{ old('designation', $siteuser->designation) == "Associate Solicitor" ? 'selected' : '' }} value="Associate Solicitor">Associate Solicitor</option>
                        <option {{ old('designation', $siteuser->designation) == "Consultant/Advisor" ? 'selected' : '' }} value="Consultant/Advisor">Consultant/Advisor</option>
                        <option {{ old('designation', $siteuser->designation) == "Para-legal Team" ? 'selected' : '' }} value="Para-legal Team">Para-legal Team</option>
                        <option {{ old('designation', $siteuser->designation) == "Administrator" ? 'selected' : '' }} value="Administrator">Administrator</option>
                        <option {{ old('designation', $siteuser->designation) == "HR Executive" ? 'selected' : '' }} value="HR Executive">HR Executive</option>
                        <option {{ old('designation', $siteuser->designation) == "Sales Team Member" ? 'selected' : '' }} value="Sales Team Member">Sales Team Member</option>
                        <option {{ old('designation', $siteuser->designation) == "Accounts Team Member" ? 'selected' : '' }} value="Accounts Team Member">Accounts Team Member</option>
                        <option {{ old('designation', $siteuser->designation) == "Support Team Member" ? 'selected' : '' }} value="Support Team Member">Support Team Member</option>
                        <option {{ old('designation', $siteuser->designation) == "Other" ? 'selected' : '' }} value="Other">Other</option>
                    </select>
                </div>
                <div class="mb-4">
                    <select name="country" id="siteuser_country" required class="form-select" aria-label="Country">
                        <option value="">Country</option>
                        @include('partials.country_select_options', [
                            'countries' => $countries,
                            'phoneForPrefill' => $siteuser->phone ?? null,
                            'savedCountry' => $siteuser->country ?? null,
                            'savedIsCountryName' => true,
                        ])
                    </select>
                </div>
                <div class="mb-4">
                    <select name="state" id="siteuser_state" required class="form-select" aria-label="State">
                        @foreach ($states as $state)
                            <option {{ old('state', $siteuser->state) == $state->name ? 'selected' : '' }}
                                value="{{ $state->name }}">{{ $state->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <input type="text" minlength="3" maxlength="100" value="{{ old('city', $siteuser->city) }}" name="city" required class="form-control" placeholder="City">
                </div>
                <div class="mb-4">
                    <input name="pincode" minlength="3" maxlength="10" style="text-transform:uppercase" value="{{ old('pincode', $siteuser->pincode) }}" required type="text" class="form-control" placeholder="Postcode">
                </div>
                <div class="mb-4">
                    <select name="timezone" id="siteuser_timezone" required class="form-select" aria-label="Timezone">
                        <option value="">Select Timezone</option>
                        @foreach($tzlist as $zone)
                        <option {{ old('timezone', $siteuser->timezone) == $zone ? 'selected' : ''}} value="{{ $zone }}">{{ $zone }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-primary mb-4" style="width: fit-content;">Save</button>
                </div>
                
            </form>
        </div>
    </div>
</div>
<div id="siteuser_update_img_box" class="profile-modal-overlay" style="display: none;">
    <div class="row justify-content-center g-0 align-items-center min-vh-100">
        <div class="col-12 col-md-10 col-lg-6 col-xl-5 loginouter-box profile-modal-panel">
            <div class="col text-end"><button type="button" class="btn btn-danger" style="width:fit-content;" onclick="document.getElementById('siteuser_update_img_box').style.display='none';">Close</button></div>
            <form class="details-box login-box" method="POST" action="{{ route('update_siteuser') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="local_time" class="localtime" />
            <input type="hidden" name="id" value="{{ $siteuser->id }}">
            <input type="hidden" name="staff_profile_image" value="1">
                <h3 class="mb-5 pt-3 text-center">Update Staff Profile Image</h3>
                <div class="log-img mb-5">
                @if($siteuser->profile_img == "")
                <img src="{{ asset('web_assets/images/loginimg.png') }}" width="60" height="60" alt="">
                @else
                    <img style="border-radius: 50%;" src="{{ asset('web_assets/users/user'.$siteuser->id.'/'.$siteuser->profile_img) }}" width="60" height="60" alt="">
                @endif
                </div>

                <div class="col d-flex justify-content-center align-items-center mb-5" onclick="document.getElementById('siteuser_select_pic').click();">
                    <div style="width:100%;height:200px;box-shadow: 0px 0px 5px 0px lightgrey;border-radius: 10px;justify-content: center;align-items: center;display: flex;position:relative;" title="click to upload file">
                        <input id="siteuser_select_pic" type="file" name="profile_img" style="display: none;">
                        <p style="position:absolute;">Click to Upload File</p>
                        <img id="siteuser_profile_pic_preview" style="width: auto;height: auto;max-width: 100%;max-height: 100%;" src="">
                    </div>
                </div>

                <button type="submit" disabled="disabled" id="siteuser_save_photo" class="btn btn-primary mb-4" style="width: fit-content;">Save</button>
                
            </form>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script>
    $(document).ready(() => {
        @if(request()->query('edit'))
        document.getElementById('siteuser_update_box').style.display = 'flex';
        @endif
        @if($errors->any() && ! $errors->has('profile_img'))
        document.getElementById('siteuser_update_box').style.display = 'flex';
        @endif

        $("#siteuser_select_pic").change(function () {
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
                reader.onload = function (event) {
                    $("#siteuser_profile_pic_preview").attr("src", event.target.result);
                };
                reader.readAsDataURL(file);
            }
            $("#siteuser_save_photo").removeAttr('disabled');
        });
        $("#siteuser_country").change(function(){
            var country = $(this).val();
            //   console.log(country);
            $.ajax({
                url: "{{ route('get_states') }}",
                method: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    country: country,
                },
                cache:false,
                success: function(data){
                    // console.log(data);
                    $("#siteuser_state").html(data);
                }
            });
            $.ajax({
                url: "{{route('get_timezone')}}",
                method: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    country: country,
                },
                cache:false,
                success: function(data){
                    // console.log("zones = "+data);
                    $("#siteuser_timezone").html(data);
                }
            });
        });
    });
</script>
@error('profile_img')
    <script>
      Swal.fire({

        icon: 'warning', customClass: { icon: 'adwiseri-oops-icon' },
        title: 'Oops!',
        text: 'Please select a valid image.'
      })
    </script>
@enderror
@if(session()->has('success'))
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Congratulations',
        text: 'Staff profile has been updated!'
      })
    </script>

@endif

@endsection()
