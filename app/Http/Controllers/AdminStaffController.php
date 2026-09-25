<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Countries;
use App\Models\Currency;
use App\Models\UserRoles;
use App\Models\Activities;
use App\Models\Tickets;
use App\Services\RoleModuleAccessService;
use Auth;
use DateTimeZone;
use Hash;
use Illuminate\Validation\Rule;
use App\Models\States;

class AdminStaffController extends Controller
{
    private function requireFullAdmin()
    {
        $user = Auth::user();
        if (!$user || !app(RoleModuleAccessService::class)->isFullAdmin($user)) {
            abort(403, 'Only the primary admin can manage admin staff users.');
        }

        return $user;
    }

    private function findAdminStaffUser(int $id): User
    {
        $staff = User::findOrFail($id);
        if (strtolower((string) $staff->user_type) !== 'admin' || (int) ($staff->is_support ?? 0) !== 1) {
            abort(404);
        }

        return $staff;
    }

    public function admin_staff(){
        $this->requireFullAdmin();
        $siteusers = User::where('user_type', '=', 'admin')
            ->where('is_support', 1)
            ->orderBy('created_at', 'desc')
            ->get();
        $user = Auth::user();
        $page = "admin_staff";
        return view('admin.admin_staff', compact('siteusers', 'user', 'page'));
    }
    public function admin_new_staff(){
        $this->requireFullAdmin();
        $countries = Countries::get();
        $tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        $user = Auth::user();
        $page = "admin_staff";
        $states = collect();

        return view('admin.admin_new_staff', compact('page', 'countries', 'tzlist', 'user', 'states'));
    }

    public function edit_admin_staff($id)
    {
        $this->requireFullAdmin();
        $staffUser = $this->findAdminStaffUser((int) $id);
        $countries = Countries::get();
        $states = collect();
        foreach ($countries as $country) {
            if ($country->country_name == $staffUser->country) {
                $states = States::where('country_id', '=', $country->id)->get();
                break;
            }
        }
        $tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        $user = Auth::user();
        $page = 'admin_staff';

        return view('admin.admin_new_staff', compact('page', 'countries', 'tzlist', 'user', 'staffUser', 'states'));
    }

    public function update_admin_staff(Request $request)
    {
        $actor = $this->requireFullAdmin();
        $staffUser = $this->findAdminStaffUser((int) $request->input('id'));

        $validated = $request->validate([
            'id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'phone' => ['required', 'phone_intl', Rule::unique('users', 'phone')->ignore($staffUser->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($staffUser->id)],
            'dob' => 'required|date',
            'designation' => 'required|string|max:255',
            'country' => 'required',
            'state' => 'required',
            'city' => 'required|string|max:255',
            'pincode' => 'required',
            'timezone' => 'required|string|max:255',
            'password' => 'nullable|string|min:8',
        ]);

        $country = Countries::find($validated['country']);
        if (!$country) {
            return back()->withInput()->withErrors(['country' => 'Please select a valid country.']);
        }

        $staffUser->name = $validated['name'];
        $staffUser->phone = $validated['phone'];
        $staffUser->email = $validated['email'];
        $staffUser->dob = $validated['dob'];
        $staffUser->designation = $validated['designation'];
        $staffUser->country = $country->country_name;
        $staffUser->state = $validated['state'];
        $staffUser->city = $validated['city'];
        $staffUser->pincode = $validated['pincode'];
        $staffUser->timezone = $validated['timezone'];
        $staffUser->user_type = 'admin';
        $staffUser->is_support = 1;

        if (!empty($validated['password'])) {
            $staffUser->password = Hash::make($validated['password']);
        }

        $crcode = $country->currency;
        $currency = Currency::where('currency_code', '=', $crcode)->first();
        $staffUser->currency = $currency
            ? $currency->currency_code . '(' . $currency->currency_symbol . ')'
            : 'USD($)';

        $staffUser->save();

        UserRoles::where('user_id', $staffUser->id)->update([
            'name' => $staffUser->name,
            'email' => $staffUser->email,
        ]);

        $activity = new Activities();
        $activity->subscriber_id = $actor->id;
        $activity->user_id = $actor->id;
        $activity->user_name = $actor->name;
        $activity->activity_name = 'Admin Staff Updated';
        $activity->activity_detail = 'Admin staff user ' . $staffUser->name . ' updated by ' . $actor->name . ' at ' . ($request->local_time ?? now()->format('d M, Y H:i:s'));
        $activity->activity_icon = 'user.png';
        $activity->local_time = $request->local_time;
        $activity->save();

        return redirect()->route('admin_staff')->with('user_updated', 'Staff member updated successfully.');
    }

    public function add_new_staff(Request $request){
        $user = $this->requireFullAdmin();
        // $this->set_timezone();
        $data = new User();
        $this->validate(
            $request,
            [
                'name' => 'required|string|max:255',
                'phone' => 'required|phone_intl|unique:users',
                'email' => 'required|string|email|max:255|unique:users',
                'dob' => 'required',
                'designation' => 'required|string|max:255',
                'country' => 'required',
                'state' => 'required',
                'city' => 'required|string|max:255',
                'pincode' => 'required',
                'password' => 'required|string|min:8',
            ]
        );
        $country = Countries::find($request->country);
        $data->user_type = "admin";
        $data->added_by = $user->id;
        $data->name = $request['name'];
        $data->phone = $request['phone'];
        $data->email = $request['email'];
        $data->dob = $request['dob'];
        $data->status = "true";
        $data->category = null;
        $data->sub_category = null;
        $data->other_subcategory = null;
        $data->membership = null;
        $data->membership_type = null;
        $data->membership_start_date = null;
        $data->membership_expiry_date = null;
        $data->wallet = 0;
        $data->is_support = 1;
        $data->referral = null;
        $data->organization = $user->organization;
        $data->designation = $request['designation'];
        $data->employee_strength = $user->employee_strength;
        $data->country = $country->country_name;
        $data->state = $request['state'];
        $data->city = $request['city'];
        $data->pincode = $request['pincode'];
        $data->timezone = $request['timezone'];
        $crcode = $country->currency;
        $currency = Currency::where('currency_code', '=', $crcode)->first();
        if ($currency) {
            $data->currency = $currency->currency_code . "(" . $currency->currency_symbol . ")";
        } else {
            $data->currency = "USD($)";
        }
        $data->password = Hash::make($request['password']);
        // print_r($requet->$data);
        // die();
        $data->save();

        $role = UserRoles::where('user_id', '=', $data->id)->get();
        if ($role) {
            foreach ($role as $r) {
                $r->delete();
            }
        }

        // Admin Staff modules: Subscribers, Activity Logs, Demo Requests, Support
        foreach (['Subscribers', 'Activity Logs', 'Demo Requests', 'Support'] as $moduleName) {
            $moduleRole = new UserRoles();
            $moduleRole->user_id = $data->id;
            $moduleRole->subscriber_id = $data->added_by;
            $moduleRole->name = $data->name;
            $moduleRole->email = $data->email;
            $moduleRole->module = $moduleName;
            $moduleRole->read_only = 1;
            $moduleRole->write_only = 1;
            $moduleRole->update_only = 1;
            $moduleRole->delete_only = 0;
            $moduleRole->read_write_only = 1;
            $moduleRole->save();
        }

        $activity = new Activities();
        $activity->subscriber_id = $user->id;
        $activity->user_id = $user->id;
        $activity->user_name = $user->name;
        $activity->activity_name = "New Admin User Added";
        $activity->activity_detail = "New Admin User " . $request->name . " added by " . $user->name . " for " . $request->designation . " job role at " . $request->local_time;
        $activity->activity_icon = "user.png";
        $activity->local_time = $request->local_time;
        $activity->save();
        return redirect()->route('admin_staff')->with('admin_staff_added', "Staff added successfully.");

    }
    public function assign_supports(Request $request){
        $validated = $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $ticket = Tickets::find($validated['ticket_id']);
        $assignee = User::find($validated['user_id']);
        $ticket->served_by = $validated['user_id'];
        $ticket->save();

        if ($assignee) {
            app(\App\Services\TicketActivityService::class)->logAssignment(
                $ticket,
                $assignee,
                Auth::user()
            );
        }

        return redirect()->back()->with('success_assign', 'User assigned successfully.');
    }
}
