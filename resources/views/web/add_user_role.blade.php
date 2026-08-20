@extends('web.layout.main')

@section('main-section')

        <div class="col-lg-10 column-client">
            <div class="client-dashboard">
                <div id="new_assignment" class="col">
                  <h5 class="text-center text-primary">Edit UAR (User Access Rights)</h5>
                  <form id="user_role_form" class="register-box login-box" method="POST" action="{{ route('user_role_post') }}" enctype="multipart/form-data" style="width: 100%;">
                    @csrf
                    <input type="hidden" name="local_time" class="localtime" />
                    <div class="row" style="padding-top: 1.25rem;">
                        <div class="col-md-4 p-1">
                            <label>User/Advisor<span class="text-danger" style="font-size: 18px;">*</span></label>
                        </div>
                        <div class="col-md-8 p-1">
                            <select name="user_id" id="user_id" class="form-control form-select @error('user_id') is-invalid @enderror" required>
                                <option value="">Select User/Advisor</option>
                                @foreach($siteusers as $u)
                                <option value="{{ $u->id }}" {{ isset($staff) && (string) $staff->id === (string) $u->id ? 'selected' : ((string) old('user_id') === (string) $u->id ? 'selected' : '') }}>{{ $u->name }} ({{ $u->id }})</option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-12 p-1 text-center">
                            <label class="mb-0">Select Access Rights<span class="text-danger" style="font-size: 18px;">*</span></label>
                        </div>
                        <div class="col-md-12 p-1 d-flex justify-content-center">
                          <div class="access-rights-box">
                            @foreach($accessPresets as $presetKey => $preset)
                            <div class="access-rights-option">
                              <label class="d-flex align-items-start mb-0" style="cursor: pointer;">
                                <input type="radio" name="access_type" onclick="change_access(this);" value="{{ $presetKey }}" class="mt-1 me-2 access-type-radio" {{ ($detectedAccessType ?? 'limited_access') === $presetKey ? 'checked' : '' }} />
                                <span class="text-start">
                                  <strong>{{ $preset['label'] }}</strong>
                                  <br><small class="text-muted">{{ $preset['description'] }}</small>
                                </span>
                              </label>
                            </div>
                            @endforeach
                          </div>
                        </div>
                        <div class="col-md-12 p-1" id="access_table">
                          @include('partials.user_access_matrix', ['matrixRoles' => $matrixRoles])
                        </div>
                        <div class="col-md-12 p-1">
                            <button type="submit" class="btn btn-primary" style="width: 100%; display: block;">Apply Changes</button>
                        </div>
                    </div>
                </form>
                </div>
            </div>
        </div>
    </div>

  </div>

  <style>
    #access_table.access-matrix-locked {
      opacity: 0.85;
    }

    #access_table.access-matrix-locked .access-matrix-checkbox {
      cursor: not-allowed;
    }

    #user_role_form {
      width: 100%;
      max-width: 100%;
    }

    #user_role_form button[type="submit"] {
      width: 100% !important;
      display: block;
    }

    /* Access Rights options: a single centered box, radios stacked in a straight vertical line */
    .access-rights-box {
      width: 100%;
      max-width: 560px;
      margin: 0 auto;
      border: 1px solid #dee2e6;
      border-radius: 8px;
      background-color: #fff;
      padding: 0.25rem 1.25rem;
    }

    .access-rights-option {
      padding: 0.65rem 0;
    }

    .access-rights-option + .access-rights-option {
      border-top: 1px solid #f0f0f0;
    }
  </style>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js">
  </script>
  <script>
    const accessPresetPermissions = @json($presetPermissionsForJs);
    const permissionFields = ['read_only', 'write_only', 'update_only', 'delete_only', 'read_write_only'];

    function applyPresetPermissions(accessType) {
      const permissions = accessPresetPermissions[accessType];
      if (!permissions) {
        return;
      }

      Object.keys(permissions).forEach(function(module) {
        const prefix = module.toLowerCase() + '_';
        permissionFields.forEach(function(field) {
          const checked = Number(permissions[module][field]) === 1;
          $('input[name="' + prefix + field + '"]').prop('checked', checked);
        });
      });
    }

    function lockAccessMatrix(locked) {
      $('#access_table .access-matrix-checkbox').prop('disabled', locked);
      $('#access_table').toggleClass('access-matrix-locked', locked);
    }

    function change_access(elem) {
      const access = elem.value;

      if (access === 'limited_access') {
        lockAccessMatrix(false);
        return;
      }

      applyPresetPermissions(access);
      lockAccessMatrix(true);
    }

    $(document).ready(function() {
      const selectedAccess = $('input[name="access_type"]:checked').val();

      if (selectedAccess && selectedAccess !== 'limited_access') {
        applyPresetPermissions(selectedAccess);
        lockAccessMatrix(true);
      } else {
        lockAccessMatrix(false);
      }

      $('#user_id').on('change', function() {
        const userId = $(this).val();
        if (!userId) {
          return;
        }
        window.location.href = '{{ url('/add_user_role') }}/' + userId;
      });

      $('#user_role_form').on('submit', function() {
        $('#access_table .access-matrix-checkbox').prop('disabled', false);
      });
    });
  </script>

  @if(session()->has('role_added'))
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: '{{ session('role_added') }}'
      })
    </script>
  @endif

  @if(session()->has('deleted'))
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'Application Assignment deleted successfully.'
      })
    </script>

  @endif
  @if(session()->has('assignment_added'))
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'Application assigned successfully.'
      })
    </script>

  @endif
  @if(session()->has('assignment_updated'))
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'Application assignment updated.'
      })
    </script>

  @endif

@endsection()
