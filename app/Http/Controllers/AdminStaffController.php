<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Countries;
use App\Models\Currency;
use App\Models\UserRoles;
use App\Models\Activities;
use App\Models\Tickets;
use Auth;
use DateTimeZone;
use Hash;

class AdminStaffController extends Controller
{

    public function admin_staff(){
        $siteusers = User::where('user_type', '=', 'admin')->orderBy('created_at', 'desc')->get();
        $user = Auth::user();
        $page = "users";
        return view('admin.admin_staff', compact('siteusers', 'user', 'page'));
    }
    public function admin_new_staff(){
        $countries = Countries::get();
        $tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        $user = Auth::user();
        $page = "admin_staffs";
        return view('admin.admin_new_staff', compact('page','countries','tzlist','user'));
    }
    public function add_new_staff(Request $request){
        $user = Auth::user();
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
        $data->category = $user->category;
        $data->sub_category = $user->sub_category;
        $data->other_subcategory = $user->other_subcategory;
        $data->membership = $user->membership;
        $data->membership_type = $user->membership_type;
        $data->membership_start_date = $user->membership_start_date;
        $data->membership_expiry_date = $user->membership_expiry_date;
        $data->wallet = 0;
        $data->is_support = 1;
        $data->referral = $user->referral;
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
