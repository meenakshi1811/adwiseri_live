<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/run-migrations', function () {
    Artisan::call('migrate', ['--force' => true]);

    return nl2br(Artisan::output());
});

Route::get('/clear-all', function () {
    $commands = [
        'optimize:clear',
        'cache:clear',
        'config:clear',
        'route:clear',
        'view:clear',
        'event:clear',
    ];

    $lines = [];
    foreach ($commands as $command) {
        try {
            Artisan::call($command);
            $result = trim(Artisan::output());
            $lines[] = $command . ': ' . ($result !== '' ? $result : 'done');
        } catch (\Throwable $e) {
            $lines[] = $command . ': failed — ' . $e->getMessage();
        }
    }

    return response('<pre>' . e(implode("\n", $lines)) . '</pre>');
})->name('clear_cache');

Route::get('/clear-cache', function () {
    return redirect()->route('clear_cache');
});

Auth::routes();

// Admin two-factor authentication (6-digit OTP)
Route::get('/admin/2fa', [App\Http\Controllers\Auth\LoginController::class, 'showAdminOtpForm'])->name('admin.2fa');
Route::post('/admin/2fa', [App\Http\Controllers\Auth\LoginController::class, 'verifyAdminOtp'])->name('admin.2fa.verify');
Route::post('/admin/2fa/resend', [App\Http\Controllers\Auth\LoginController::class, 'resendAdminOtp'])->name('admin.2fa.resend');

Route::get('/check_login', [App\Http\Controllers\WebController::class, 'check_login'])->name('check_login');
Route::get('/set_timezone', [App\Http\Controllers\WebController::class, 'set_timezone'])->name('set_timezone');
Route::get('/send_email', [App\Http\Controllers\WebController::class, 'send_email'])->name('send_email');
Route::get('/add_subscriber_roles', [App\Http\Controllers\WebController::class, 'add_subscriber_roles'])->name('add_subscriber_roles');

// Stripe Routes
Route::post('stripe', [App\Http\Controllers\StripeController::class, 'stripePost'])->name('stripe.post');
Route::post('reg_stripe', [App\Http\Controllers\StripeController::class, 'regstripePost'])->name('stripe.postreg');

Route::get('/export_users', [App\Http\Controllers\ExportController::class, 'export_users'])->name('export_users');
Route::get('/export_clients', [App\Http\Controllers\ExportController::class, 'export_clients'])->name('export_clients');
Route::get('/export_applications', [App\Http\Controllers\ExportController::class, 'export_applications'])->name('export_applications');
Route::get('/export_applications_report', [App\Http\Controllers\ExportController::class, 'export_applications_report'])->name('export_applications_report');
Route::get('/export_invoices_report', [App\Http\Controllers\ExportController::class, 'export_invoices_report'])->name('export_invoices_report');
Route::get('/store_report', [App\Http\Controllers\ExportController::class, 'store_report'])->name('store_report');

// Admin Exports
Route::get('/subscribers_export', [App\Http\Controllers\ExportController::class, 'subscribers_export'])->name('subscribers_export');
Route::get('/users_export', [App\Http\Controllers\ExportController::class, 'users_export'])->name('users_export');
Route::get('/clients_export', [App\Http\Controllers\ExportController::class, 'clients_export'])->name('clients_export');
Route::get('/payments_export', [App\Http\Controllers\ExportController::class, 'payments_export'])->name('payments_export');
Route::get('/invoices_export', [App\Http\Controllers\ExportController::class, 'invoices_export'])->name('invoices_export');
Route::get('/applications_export', [App\Http\Controllers\ExportController::class, 'applications_export'])->name('applications_export');
Route::get('/applications_report_export', [App\Http\Controllers\ExportController::class, 'applications_report_export'])->name('applications_report_export');
Route::get('/invoices_report_export', [App\Http\Controllers\ExportController::class, 'invoices_report_export'])->name('invoices_report_export');


Route::get('/emailtemplate', [App\Http\Controllers\EmailController::class, 'sendEmail'])->name('emailtemplate');
Route::post('/password_otp', [App\Http\Controllers\EmailController::class, 'password_otp'])->name('password_otp');
Route::post('/validate-phone', [App\Http\Controllers\WebController::class, 'validatePhone'])->name('validate.phone');

Route::get('/', [App\Http\Controllers\WebController::class, 'index'])->name('/');
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
// Route::get('/login', [App\Http\Controllers\WebController::class, 'login'])->name('login');
Route::get('/get_demo', [App\Http\Controllers\WebController::class, 'get_demo'])->name('get_demo');
Route::post('/demo_post', [App\Http\Controllers\WebController::class, 'demo_post'])->name('demo_post');
Route::get('/forget_password', [App\Http\Controllers\WebController::class, 'forget_password'])->name('forget_password');
Route::get('/user_register/{ref?}', [App\Http\Controllers\WebController::class, 'user_register'])->name('user_register');
Route::get('/userregister/{plan?}', [App\Http\Controllers\WebController::class, 'user_register_plan'])->name('user_register_plan');
Route::post('/check_registration', [App\Http\Controllers\WebController::class, 'check_registration'])->name('check_registration');
Route::post('/email_subscription', [App\Http\Controllers\WebController::class, 'email_subscription'])->name('email_subscription');
Route::get('/send_otp', [App\Http\Controllers\WebController::class, 'send_otp'])->name('send_otp');
Route::get('/user_registration', [App\Http\Controllers\WebController::class, 'user_registration'])->name('user_registration');
Route::get('/reg_pay', [App\Http\Controllers\WebController::class, 'reg_pay'])->name('reg_pay');
Route::post('/update_user', [App\Http\Controllers\WebController::class, 'update_user'])->name('update_user')->middleware(['auth','check.device']);
Route::post('/change_password', [App\Http\Controllers\WebController::class, 'change_password'])->name('change_password')->middleware(['auth','check.device']);
Route::post('/update_siteuser', [App\Http\Controllers\WebController::class, 'update_siteuser'])->name('update_siteuser')->middleware(['auth','check.device']);
Route::post('/update_client', [App\Http\Controllers\WebController::class, 'update_client'])->name('update_client')->middleware(['auth','check.device']);
Route::get('/logout', [App\Http\Controllers\HomeController::class, 'logout'])->name('logout');
Route::get('/thanks', [App\Http\Controllers\WebController::class, 'thanks'])->name('thanks');
Route::get('/moredetails', [App\Http\Controllers\WebController::class, 'moredetails'])->name('moredetails')->middleware(['auth','check.device']);
Route::get('/otp/{email?}', [App\Http\Controllers\WebController::class, 'otp'])->name('otp');
Route::post('/verify_otp', [App\Http\Controllers\WebController::class, 'verify_otp'])->name('verify_otp');
Route::post('/verify_password_otp', [App\Http\Controllers\WebController::class, 'verify_password_otp'])->name('verify_password_otp');
Route::get('/new_password/{email?}', [App\Http\Controllers\WebController::class, 'new_password'])->name('new_password');
Route::post('/save_password', [App\Http\Controllers\WebController::class, 'save_password'])->name('save_password');
Route::get('/features', [App\Http\Controllers\WebController::class, 'features'])->name('features');
Route::get('/price_plans', [App\Http\Controllers\WebController::class, 'membership'])->name('membership');
Route::get('/membership_renewal', [App\Http\Controllers\WebController::class, 'membershipRenewal'])->name('membership_renewal');

/*Newly added routes on 2026-03-06 by Meenakshi Nanta*/
Route::get('/create-new-lead/{id}', [App\Http\Controllers\WebController::class, 'createLead'])->name('createLead');
Route::post('/visa-enquiry-store', [App\Http\Controllers\VisaEnquiryController ::class, 'store'])->name('visa.enquiry.store');
Route::get('/enquiries', [App\Http\Controllers\WebController::class, 'enquiries'])->name('enquiries')->middleware(['auth','check.device']);
Route::get('/enquiries/create', [App\Http\Controllers\WebController::class, 'createManualLead'])->name('enquiries.create')->middleware(['auth','check.device']);
Route::post('/enquiries/store', [App\Http\Controllers\WebController::class, 'storeManualLead'])->name('enquiries.store')->middleware(['auth','check.device']);
Route::post('/convert-enquiry-client', [App\Http\Controllers\WebController::class, 'convertEnquiryClient'])->name('convert.enquiry.client');
Route::delete('/visa-enquiries/delete/{id}', [App\Http\Controllers\WebController::class, 'deleteEnquiry'])->name('visa_enquiries.delete')->middleware(['auth','check.device']);
Route::get('/visa-enquiries/view/{id}', [App\Http\Controllers\WebController::class, 'viewEnquiry'])->name('visa_enquiries.view')->middleware(['auth','check.device']);
Route::get('/visa-enquiries/edit/{id}', [App\Http\Controllers\WebController::class, 'editEnquiry'])->name('visa_enquiries.edit')->middleware(['auth','check.device']);
Route::post('/visa-enquiries/update/{id}', [App\Http\Controllers\WebController::class, 'updateEnquiry'])->name('visa_enquiries.update')->middleware(['auth','check.device']);
Route::post('/enquiries/{id}/lead-follow-up', [App\Http\Controllers\WebController::class, 'updateLeadFollowUp'])->name('enquiries.lead_follow_up')->middleware(['auth','check.device']);
Route::get('/enquiries/{id}/follow-up-history', [App\Http\Controllers\WebController::class, 'leadFollowUpHistoryData'])->name('enquiries.follow_up_history')->middleware(['auth','check.device']);
/* Backward-compatible enquiry edit/update routes */
Route::get('/edit-enquiry/{id}', [App\Http\Controllers\WebController::class, 'editEnquiry'])->middleware(['auth','check.device']);
Route::post('/update-enquiry/{id}', [App\Http\Controllers\WebController::class, 'updateEnquiry'])->middleware(['auth','check.device']);
Route::post('/save-appointment',[App\Http\Controllers\WebController::class,'storeAppointment'])->name('save_appointment')->middleware(['auth','check.device']);
Route::get('/appointment-records', [App\Http\Controllers\WebController::class, 'appointmentRecords'])->name('appointment_records')->middleware(['auth','check.device']);
Route::get('/get-appointment-records', [App\Http\Controllers\WebController::class, 'getAppointmentRecords'])->name('get_appointment_records')->middleware(['auth','check.device']);
Route::get('/appointment/{appointment}/{action}', [App\Http\Controllers\WebController::class, 'respondToAppointment'])->name('appointment.respond')->middleware('signed');
Route::post('/save-report-settings',[App\Http\Controllers\WebController::class,'saveReportSettings'])->name('save_report_settings');
Route::post('/save-payment-reminder-settings',[App\Http\Controllers\WebController::class,'savePaymentReminderSettings'])->name('save_payment_reminder_settings')->middleware('auth');
Route::post('/save-application-reminder',[App\Http\Controllers\WebController::class,'saveApplicationReminder'])->name('save_application_reminder')->middleware('auth');
Route::delete('/application-reminders/{id}',[App\Http\Controllers\WebController::class,'deleteApplicationReminder'])->name('delete_application_reminder')->middleware('auth');
Route::get('/email-templates', [App\Http\Controllers\WebController::class, 'getEmailTemplates'])->name('email_templates')->middleware('auth');
Route::post('/save-email-template', [App\Http\Controllers\WebController::class, 'saveEmailTemplate'])->name('save_email_template')->middleware('auth');
Route::get('/scheduled-report-download/{file}', [App\Http\Controllers\WebController::class, 'downloadScheduledReport'])->name('scheduled_report_download')->middleware('signed');

Route::get('/aboutadwiseri', [App\Http\Controllers\WebController::class, 'aboutadvisori'])->name('aboutadvisori');
Route::get('/userprofile', [App\Http\Controllers\WebController::class, 'userprofile'])->name('userprofile')->middleware(['auth','check.device','throttle:30,1']);
Route::post('/get_states', [App\Http\Controllers\WebController::class, 'get_states'])->name('get_states');
Route::post('/get_timezone', [App\Http\Controllers\WebController::class, 'get_timezone'])->name('get_timezone');
Route::post('/get_application', [App\Http\Controllers\WebController::class, 'get_application'])->name('get_application');
Route::post('/get_sub_category', [App\Http\Controllers\WebController::class, 'get_sub_category'])->name('get_sub_category');
Route::post('/check_user_limit', [App\Http\Controllers\WebController::class, 'check_user_limit'])->name('check_user_limit');
Route::post('/check_client_limit', [App\Http\Controllers\WebController::class, 'check_client_limit'])->name('check_client_limit');
Route::get('/dashboard', [App\Http\Controllers\WebController::class, 'dashboard'])->name('dashboard')->middleware(['auth','check.device']);
Route::get('/client', [App\Http\Controllers\WebController::class, 'client'])->name('client')->middleware(['auth','check.device']);
Route::get('/client_accounts', [App\Http\Controllers\ClientAccountController::class, 'index'])->name('client_accounts')->middleware(['auth','check.device']);
Route::get('/client_accounts_pdf', [App\Http\Controllers\ClientAccountController::class, 'downloadPdf'])->name('client_accounts_pdf')->middleware(['auth','check.device']);
Route::get('/add_client_account', [App\Http\Controllers\ClientAccountController::class, 'create'])->name('add_client_account')->middleware(['auth','check.device']);
Route::get('/client_account_balance', [App\Http\Controllers\ClientAccountController::class, 'balance'])->name('client_account_balance')->middleware(['auth','check.device']);
Route::post('/store_client_account', [App\Http\Controllers\ClientAccountController::class, 'store'])->name('store_client_account')->middleware(['auth','check.device']);
Route::get('/view_client_account/{id}', [App\Http\Controllers\ClientAccountController::class, 'show'])->name('view_client_account')->middleware(['auth','check.device']);
Route::get('/edit_client_account/{id}', [App\Http\Controllers\ClientAccountController::class, 'edit'])->name('edit_client_account')->middleware(['auth','check.device']);
Route::post('/update_client_account/{id}', [App\Http\Controllers\ClientAccountController::class, 'update'])->name('update_client_account')->middleware(['auth','check.device']);
Route::get('/delete_client_account/{id}/{localtime?}', [App\Http\Controllers\ClientAccountController::class, 'destroy'])->name('delete_client_account')->middleware(['auth','check.device']);
Route::get('/clientDatatable', [App\Http\Controllers\WebController::class, 'clientDatatable'])->name('clientDatatable')->middleware('auth');
Route::get('/users', [App\Http\Controllers\WebController::class, 'users'])->name('users')->middleware(['auth','check.device']);
Route::get('/add_client', [App\Http\Controllers\WebController::class, 'add_client'])->name('add_client')->middleware(['auth','check.device']);
Route::post('/add_new_client', [App\Http\Controllers\WebController::class, 'add_new_client'])->name('add_new_client');
Route::get('/affiliates_records', [App\Http\Controllers\AdminController::class, 'affiliates_records'])->name('affiliates_records')->middleware('admin_auth');
Route::get('/add_user', [App\Http\Controllers\WebController::class, 'add_user'])->name('add_user')->middleware(['auth','check.device']);
Route::post('/add_new_user', [App\Http\Controllers\WebController::class, 'add_new_user'])->name('add_new_user');

/* ---------------- Associates module (Referrals) ---------------- */
Route::get('/associates', [App\Http\Controllers\AssociateController::class, 'associates'])->name('associates')->middleware(['auth','check.device']);
Route::get('/add_associate', [App\Http\Controllers\AssociateController::class, 'add_associate'])->name('add_associate')->middleware(['auth','check.device']);
Route::post('/store_associate', [App\Http\Controllers\AssociateController::class, 'store_associate'])->name('store_associate')->middleware(['auth','check.device']);
Route::get('/edit_associate/{id}', [App\Http\Controllers\AssociateController::class, 'edit_associate'])->name('edit_associate')->middleware(['auth','check.device']);
Route::post('/update_associate', [App\Http\Controllers\AssociateController::class, 'update_associate'])->name('update_associate')->middleware(['auth','check.device']);
Route::get('/delete_associate/{id}', [App\Http\Controllers\AssociateController::class, 'delete_associate'])->name('delete_associate')->middleware(['auth','check.device']);
// Business (Referrals) tab
Route::get('/associate_business', [App\Http\Controllers\AssociateController::class, 'business'])->name('associate_business')->middleware(['auth','check.device']);
Route::get('/add_associate_business', [App\Http\Controllers\AssociateController::class, 'add_business'])->name('add_associate_business')->middleware(['auth','check.device']);
Route::post('/store_associate_business', [App\Http\Controllers\AssociateController::class, 'store_business'])->name('store_associate_business')->middleware(['auth','check.device']);
Route::get('/view_associate_business/{id}', [App\Http\Controllers\AssociateController::class, 'view_business'])->name('view_associate_business')->middleware(['auth','check.device']);
Route::get('/edit_associate_business/{id}', [App\Http\Controllers\AssociateController::class, 'edit_business'])->name('edit_associate_business')->middleware(['auth','check.device']);
Route::post('/update_associate_business', [App\Http\Controllers\AssociateController::class, 'update_business'])->name('update_associate_business')->middleware(['auth','check.device']);
Route::get('/delete_associate_business/{id}', [App\Http\Controllers\AssociateController::class, 'delete_business'])->name('delete_associate_business')->middleware(['auth','check.device']);
// Invoices tab
Route::get('/associate_invoices', [App\Http\Controllers\AssociateController::class, 'invoices'])->name('associate_invoices')->middleware(['auth','check.device']);
Route::get('/create_associate_invoice', [App\Http\Controllers\AssociateController::class, 'create_invoice'])->name('create_associate_invoice')->middleware(['auth','check.device']);
Route::post('/store_associate_invoice', [App\Http\Controllers\AssociateController::class, 'store_invoice'])->name('store_associate_invoice')->middleware(['auth','check.device']);
Route::get('/edit_associate_invoice/{id}', [App\Http\Controllers\AssociateController::class, 'edit_invoice'])->name('edit_associate_invoice')->middleware(['auth','check.device']);
Route::post('/update_associate_invoice', [App\Http\Controllers\AssociateController::class, 'update_invoice'])->name('update_associate_invoice')->middleware(['auth','check.device']);
Route::get('/view_associate_invoice/{id}', [App\Http\Controllers\AssociateController::class, 'view_invoice'])->name('view_associate_invoice')->middleware(['auth','check.device']);
Route::get('/print_associate_invoice/{id}', [App\Http\Controllers\AssociateController::class, 'print_invoice'])->name('print_associate_invoice')->middleware(['auth','check.device']);
Route::post('/resend_associate_invoice_email/{id}', [App\Http\Controllers\AssociateController::class, 'resend_invoice_email'])->name('resend_associate_invoice_email')->middleware(['auth','check.device']);
Route::get('/delete_associate_invoice/{id}', [App\Http\Controllers\AssociateController::class, 'delete_invoice'])->name('delete_associate_invoice')->middleware(['auth','check.device']);
// Payments tab
Route::get('/associate_payments', [App\Http\Controllers\AssociateController::class, 'payments'])->name('associate_payments')->middleware(['auth','check.device']);
Route::get('/add_associate_payment', [App\Http\Controllers\AssociateController::class, 'add_payment'])->name('add_associate_payment')->middleware(['auth','check.device']);
Route::post('/associate_invoice_details', [App\Http\Controllers\AssociateController::class, 'invoice_details'])->name('associate_invoice_details')->middleware(['auth','check.device']);
Route::post('/store_associate_payment', [App\Http\Controllers\AssociateController::class, 'store_payment'])->name('store_associate_payment')->middleware(['auth','check.device']);
Route::get('/view_associate_payment/{id}', [App\Http\Controllers\AssociateController::class, 'view_payment'])->name('view_associate_payment')->middleware(['auth','check.device']);
Route::get('/delete_associate_payment/{id}', [App\Http\Controllers\AssociateController::class, 'delete_payment'])->name('delete_associate_payment')->middleware(['auth','check.device']);
/* --------------- End Associates module --------------- */

Route::get('/contactus', [App\Http\Controllers\WebController::class, 'contactus'])->name('contactus');
Route::get('/cookie_notice', [App\Http\Controllers\WebController::class, 'refund_policy'])->name('refund_policy');
Route::post('/cookie-consent', [App\Http\Controllers\WebController::class, 'storeCookieConsent'])->name('cookie_consent.store');
Route::get('/gdpr', [App\Http\Controllers\WebController::class, 'terms_conditions'])->name('terms_conditions');
Route::get('/terms_of_use', [App\Http\Controllers\WebController::class, 'terms_use'])->name('terms_use');
Route::get('/privacy_policy', [App\Http\Controllers\WebController::class, 'privacy_policy'])->name('privacy_policy');
Route::get('/client_profile/{id?}', [App\Http\Controllers\WebController::class, 'client_profile'])->name('client_profile')->middleware(['auth','check.device']);
Route::get('/siteuser_profile/{id?}', [App\Http\Controllers\WebController::class, 'siteuser_profile'])->name('siteuser_profile')->middleware(['auth','check.device']);
Route::get('/delete_siteuser/{id?}/{localtime?}', [App\Http\Controllers\WebController::class, 'delete_siteuser'])->name('delete_siteuser')->middleware(['auth','check.device']);
Route::get('/delete_client/{id?}/{localtime?}', [App\Http\Controllers\WebController::class, 'delete_client'])->name('delete_client')->middleware(['auth','check.device']);
Route::post('/upload_client_doc', [App\Http\Controllers\WebController::class, 'upload_client_doc'])->name('upload_client_doc')->middleware(['auth','check.device']);
Route::post('/make_payment', [App\Http\Controllers\WebController::class, 'make_payment'])->name('make_payment');
Route::get('/pay_securely', [App\Http\Controllers\WebController::class, 'pay_securely'])->name('pay_securely');
Route::post('/upgrade_plan', [App\Http\Controllers\WebController::class, 'upgrade_plan'])->name('upgrade_plan');
Route::post('/downgrade_plan', [App\Http\Controllers\WebController::class, 'downgrade_plan'])->name('downgrade_plan');
Route::get('/upgrade_membership/{plan?}', [App\Http\Controllers\WebController::class, 'upgrade_membership'])->name('upgrade_membership')->middleware(['auth','check.device']);
Route::get('/user_membership', [App\Http\Controllers\WebController::class, 'user_membership'])->name('user_membership')->middleware(['auth','check.device']);
Route::get('/download_all_data', [App\Http\Controllers\WebController::class, 'download_all_data'])->name('download_all_data')->middleware(['auth','check.device']);

Route::get('/view_payment/{id?}', [App\Http\Controllers\WebController::class, 'view_payment'])->name('view_payment')->middleware(['auth','check.device']);
Route::get('/print_payment/{id?}', [App\Http\Controllers\WebController::class, 'print_payment'])->name('print_payment')->middleware(['auth','check.device']);
Route::get('/delete_payment/{id?}/{localtime?}', [App\Http\Controllers\WebController::class, 'delete_payment'])->name('delete_payment')->middleware(['auth','check.device']);

Route::get('/payment_made', [App\Http\Controllers\PaymentController::class, 'payment_made'])->name('payment_made')->middleware(['auth','check.device']);


Route::get('/my_payments', [App\Http\Controllers\PaymentController::class, 'my_payments'])->name('my_payments')->middleware(['auth','check.device']);
Route::get('/payments-tax-data', [App\Http\Controllers\PaymentController::class, 'taxSummaryData'])->name('payments_tax_data')->middleware(['auth','check.device']);
Route::get('/add_ar_payments', [App\Http\Controllers\PaymentController::class, 'add_ar_payments'])->name('add_ar_payments')->middleware(['auth','check.device']);
// routes/api.php
Route::get('/invoices/{id}', [App\Http\Controllers\PaymentController::class, 'getInvoiceDetails'])->name('getInvoiceDetails')->middleware(['auth','check.device']);
Route::get('/invoices_ap/{id}', [App\Http\Controllers\PaymentController::class, 'getAPInvoiceDetails'])->name('getAPInvoiceDetails')->middleware(['auth','check.device']);


Route::get('/subscriberPayments', [App\Http\Controllers\PaymentController::class, 'subscriberPayments'])->name('subscriberPayments')->middleware(['auth','check.device']);

Route::get('/add_ap_payments', [App\Http\Controllers\PaymentController::class, 'add_ap_payments'])->name('add_ap_payments')->middleware(['auth','check.device']);


Route::post('/payment_received', [App\Http\Controllers\PaymentController::class, 'payment_received'])->name('payment_received')->middleware(['auth','check.device']);

Route::post('/advance_payment', [App\Http\Controllers\PaymentController::class, 'advance_payment'])->name('advance_payment')->middleware(['auth','check.device']);


Route::get('/invoices', [App\Http\Controllers\WebController::class, 'invoices'])->name('invoices')->middleware(['auth','check.device']);
Route::get('/invoice_payment_made', [App\Http\Controllers\WebController::class, 'invoice_payment_made'])->name('invoice_payment_made')->middleware(['auth','check.device']);

Route::get('/new_invoice', [App\Http\Controllers\WebController::class, 'new_invoice'])->name('new_invoice')->middleware(['auth','check.device']);
Route::get('/new_invoice_ap', [App\Http\Controllers\WebController::class, 'new_invoice_ap'])->name('new_invoice_ap')->middleware(['auth','check.device']);
Route::post('/check_duplicate_invoice', [App\Http\Controllers\WebController::class, 'check_duplicate_invoice'])->name('check_duplicate_invoice')->middleware(['auth','check.device']);
Route::post('/check_duplicate_application', [App\Http\Controllers\WebController::class, 'check_duplicate_application'])->name('check_duplicate_application')->middleware(['auth','check.device']);
Route::post('/create_new_invoice', [App\Http\Controllers\WebController::class, 'create_new_invoice'])->name('create_new_invoice')->middleware(['auth','check.device']);
Route::post('/create_new_invoice_ap', [App\Http\Controllers\WebController::class, 'create_new_invoice_ap'])->name('create_new_invoice_ap')->middleware(['auth','check.device']);
Route::get('/edit_invoice/{id}', [App\Http\Controllers\WebController::class, 'edit_invoice'])->name('edit_invoice')->middleware(['auth','check.device']);
Route::post('/update_invoice/{id}', [App\Http\Controllers\WebController::class, 'update_invoice'])->name('update_invoice')->middleware(['auth','check.device']);
Route::get('/edit_invoice_ap/{id}', [App\Http\Controllers\WebController::class, 'edit_invoice_ap'])->name('edit_invoice_ap')->middleware(['auth','check.device']);
Route::post('/update_invoice_ap/{id}', [App\Http\Controllers\WebController::class, 'update_invoice_ap'])->name('update_invoice_ap')->middleware(['auth','check.device']);

Route::get('/view_invoice/{id?}', [App\Http\Controllers\WebController::class, 'view_invoice'])->name('view_invoice')->middleware(['auth','check.device']);
Route::post('/invoice/{id}/resend-email', [App\Http\Controllers\WebController::class, 'resendInvoiceEmail'])->name('resend_invoice_email')->middleware(['auth','check.device']);
Route::get('/invoice_preview/{id?}/{token?}', [App\Http\Controllers\WebController::class, 'invoice_preview'])->name('invoice_preview');
Route::get('/print_invoice/{id?}', [App\Http\Controllers\WebController::class, 'print_invoice'])->name('print_invoice')->middleware(['auth','check.device']);
Route::get('/delete_invoice/{id?}/{localtime?}', [App\Http\Controllers\WebController::class, 'delete_invoice'])->name('delete_invoice')->middleware(['auth','check.device']);
Route::post('/invoice_status', [App\Http\Controllers\WebController::class, 'invoice_status'])->name('invoice_status');
Route::get('/user_role', [App\Http\Controllers\WebController::class, 'user_role'])->name('user_role')->middleware(['auth','check.device']);
Route::get('/add_user_role/{id?}', [App\Http\Controllers\WebController::class, 'add_user_role'])->name('add_user_role')->middleware(['auth','check.device']);
Route::post('/user_role_post', [App\Http\Controllers\WebController::class, 'user_role_post'])->name('user_role_post')->middleware(['auth','check.device']);
Route::get('/delete_user_role/{id?}', [App\Http\Controllers\WebController::class, 'delete_user_role'])->name('delete_user_role')->middleware(['auth','check.device']);
Route::get('/job_role/{id?}', [App\Http\Controllers\WebController::class, 'job_role'])->name('job_role')->middleware(['auth','check.device']);
Route::get('/add_job_role', [App\Http\Controllers\WebController::class, 'add_job_role'])->name('add_job_role')->middleware(['auth','check.device']);
Route::post('/add_new_job_role', [App\Http\Controllers\WebController::class, 'add_new_job_role'])->name('add_new_job_role');
Route::get('/delete_job_role/{id?}/{localtime?}', [App\Http\Controllers\WebController::class, 'delete_job_role'])->name('delete_job_role')->middleware(['auth','check.device']);
Route::get('/job_id', [App\Http\Controllers\WebController::class, 'job_id'])->name('job_id');
Route::post('/send_message', [App\Http\Controllers\WebController::class, 'send_message'])->name('send_message');
Route::post('/generate-client-care-letter', [App\Http\Controllers\WebController::class, 'generate_client_care_letter'])->name('generate_client_care_letter')->middleware(['auth','check.device']);
Route::get('/wallet', [App\Http\Controllers\WebController::class, 'wallet'])->name('wallet')->middleware(['auth','check.device']);
Route::get('/referrals', [App\Http\Controllers\WebController::class, 'referrals'])->name('referrals')->middleware(['auth','check.device']);
Route::post('/add_amount', [App\Http\Controllers\WebController::class, 'add_amount'])->name('add_amount')->middleware(['auth','check.device']);
Route::get('/support', [App\Http\Controllers\WebController::class, 'support'])->name('support')->middleware(['auth','check.device']);
Route::get('/faqs', [App\Http\Controllers\WebController::class, 'faqs'])->name('faqs');
Route::get('/ask_support', [App\Http\Controllers\WebController::class, 'ask_support'])->name('ask_support')->middleware(['auth','check.device']);
Route::post('/ask_new_question', [App\Http\Controllers\WebController::class, 'ask_new_question'])->name('ask_new_question');
Route::get('/applications', [App\Http\Controllers\WebController::class, 'applications'])->name('applications')->middleware(['auth','check.device']);
Route::post('/applications/update-status', [App\Http\Controllers\WebController::class, 'updateApplicationStatus'])->name('applications.update_status')->middleware(['auth','check.device']);
Route::get('/user_application_tracking', [App\Http\Controllers\WebController::class, 'user_application_tracking'])->name('user_application_tracking')->middleware(['auth','check.device']);
Route::get('/clients/by-subscriber', [App\Http\Controllers\WebController::class, 'getClientsBySubscriber'])->name('clients.bySubscriber')->middleware(['auth','check.device']);
Route::get('/get-applications-by-client/{clientId}', [App\Http\Controllers\WebController::class, 'getApplicationsByClient'])->name('applications.byClient')->middleware(['auth','check.device']);
Route::get('/get-application-data/{id}', [App\Http\Controllers\WebController::class, 'getApplicationData'])->name('application.data')->middleware(['auth','check.device']);

Route::get('/add_application', [App\Http\Controllers\WebController::class, 'add_application'])->name('add_application')->middleware(['auth','check.device']);
Route::get('/update_application/{id?}', [App\Http\Controllers\WebController::class, 'update_application'])->name('update_application')->middleware(['auth','check.device']);
Route::get('/view_application/{id?}', [App\Http\Controllers\WebController::class, 'view_application'])->name('view_application')->middleware(['auth','check.device']);
Route::get('/application/{id}/document-list', [App\Http\Controllers\WebController::class, 'generate_application_document_list'])->name('generate_application_document_list')->middleware(['auth','check.device']);
Route::get('/application/{id}/document-list/download', [App\Http\Controllers\WebController::class, 'download_application_document_list'])->name('download_application_document_list')->middleware(['auth','check.device']);
Route::post('/application/{id}/document-list/send', [App\Http\Controllers\WebController::class, 'send_application_document_list'])->name('send_application_document_list')->middleware(['auth','check.device']);
Route::post('/add_new_application', [App\Http\Controllers\WebController::class, 'add_new_application'])->name('add_new_application');
Route::get('/sub_reports', [App\Http\Controllers\WebController::class, 'sub_reports'])->name('sub_reports')->middleware(['auth','check.device']);
Route::get('/sub_reports/support_tickets', [App\Http\Controllers\WebController::class, 'sub_reports_support_tickets'])->name('sub_reports_support_tickets')->middleware(['auth','check.device']);
Route::get('/sub_reports/activity_log', [App\Http\Controllers\WebController::class, 'sub_reports_activity_log'])->name('sub_reports_activity_log')->middleware(['auth','check.device']);
Route::get('/sub_analytics', [App\Http\Controllers\WebController::class, 'analytics'])->name('sub_analytics')->middleware(['auth','check.device']);

Route::get('/email_broadcast', [App\Http\Controllers\WebController::class, 'email_broadcast'])->name('email_broadcast')->middleware(['auth','check.device']);
Route::post('/send_email_broadcast', [App\Http\Controllers\WebController::class, 'send_email_broadcast'])->name('send_email_broadcast')->middleware(['auth','check.device']);
Route::post('/upload_email_broadcast_image', [App\Http\Controllers\WebController::class, 'upload_email_broadcast_image'])->name('upload_email_broadcast_image')->middleware(['auth','check.device']);
Route::get('/communications', [App\Http\Controllers\WebController::class, 'communications'])->name('communications')->middleware(['auth','check.device']);
Route::get('/messaging', [App\Http\Controllers\WebController::class, 'messaging'])->name('messaging')->middleware(['auth','check.device']);
Route::post('/communicate', [App\Http\Controllers\WebController::class, 'communicate'])->name('communicate');
Route::get('/meeting_notes', [App\Http\Controllers\WebController::class, 'client_discussion'])->name('client_discussion')->middleware(['auth','check.device']);
Route::post('/post_client_discussion', [App\Http\Controllers\WebController::class, 'post_client_discussion'])->name('post_client_discussion');
Route::get('/user_applications', [App\Http\Controllers\WebController::class, 'user_applications'])->name('user_applications')->middleware(['auth','check.device']);
Route::post('/user_app_assignment', [App\Http\Controllers\WebController::class, 'user_app_assignment'])->name('user_app_assignment');
Route::get('/client_documents', [App\Http\Controllers\WebController::class, 'client_documents'])->name('client_documents')->middleware(['auth','check.device']);
Route::post('/upload_client_document', [App\Http\Controllers\WebController::class, 'upload_client_document'])->name('upload_client_document');
Route::get('/delete_client_document/{id?}', [App\Http\Controllers\WebController::class, 'delete_client_document'])->name('delete_client_document')->middleware(['auth','check.device']);
Route::get('/client_document_update/{id?}', [App\Http\Controllers\WebController::class, 'client_document_update'])->name('client_document_update')->middleware(['auth','check.device']);
Route::get('/update_application_assignment/{id?}', [App\Http\Controllers\WebController::class, 'update_application_assignment'])->name('update_application_assignment')->middleware(['auth','check.device']);
Route::get('/my_settings', [App\Http\Controllers\WebController::class, 'my_settings'])->name('my_settings')->middleware(['auth','check.device']);
Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications')->middleware(['auth','check.device']);
Route::post('/notifications/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications_mark_all_read');
Route::post('/messages/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllMessagesRead'])->name('messages_mark_all_read');
Route::post('/messages/{id}/mark-read', [App\Http\Controllers\NotificationController::class, 'markMessageRead'])->name('messages_mark_read');
Route::post('/messages/{id}/delete', [App\Http\Controllers\NotificationController::class, 'deleteMessage'])->name('messages_delete');
Route::post('/notifications/{id}/read', [App\Http\Controllers\NotificationController::class, 'markRead'])->name('notifications_mark_read');
Route::post('/notifications/{id}/delete', [App\Http\Controllers\NotificationController::class, 'deleteNotification'])->name('notifications_delete')->middleware(['auth','check.device']);
Route::post('/notifications/delete-selected', [App\Http\Controllers\NotificationController::class, 'deleteSelectedNotifications'])->name('notifications_delete_selected')->middleware(['auth','check.device']);
Route::post('/notifications/clear-all', [App\Http\Controllers\NotificationController::class, 'clearAllNotifications'])->name('notifications_clear_all')->middleware(['auth','check.device']);
Route::post('/save_notification_preferences', [App\Http\Controllers\NotificationController::class, 'savePreferences'])->name('save_notification_preferences')->middleware(['auth','check.device']);
Route::post('/admin_send_notification', [App\Http\Controllers\NotificationController::class, 'adminSendNotification'])->name('admin_send_notification')->middleware('admin_auth');
Route::get('/notification-counts', [App\Http\Controllers\NotificationController::class, 'counts'])->name('notification_counts');
Route::get('/my_query/{id?}', [App\Http\Controllers\WebController::class, 'my_query'])->name('my_query');
Route::get('/view_message/{id?}', [App\Http\Controllers\WebController::class, 'view_message'])->name('view_message')->middleware(['auth','check.device']);

Route::post('/add_service', [App\Http\Controllers\WebController::class, 'add_service'])->name('add_service')->middleware(['auth','check.device']);
Route::post('/save_cc_settings', [App\Http\Controllers\WebController::class, 'save_cc_settings'])->name('save_cc_settings')->middleware(['auth','check.device']);
Route::post('/save_cc_document_lists', [App\Http\Controllers\WebController::class, 'save_cc_document_lists'])->name('save_cc_document_lists')->middleware(['auth','check.device']);
Route::post('/save_dashboard_settings', [App\Http\Controllers\WebController::class, 'save_dashboard_settings'])->name('save_dashboard_settings')->middleware(['auth','check.device']);
Route::post('/save_enquiry_form_settings', [App\Http\Controllers\WebController::class, 'save_enquiry_form_settings'])->name('save_enquiry_form_settings')->middleware(['auth','check.device']);
Route::post('/get_service_fee', [App\Http\Controllers\WebController::class, 'get_service_fee'])->name('get_service_fee')->middleware(['auth','check.device']);

Route::get('/get_subscriber_service', [App\Http\Controllers\WebController::class, 'get_subscriber_service'])->name('get_subscriber_service')->middleware(['auth','check.device']);
Route::delete('/services_delete/{id}', [App\Http\Controllers\WebController::class,'services_delete'])->name('services_delete')->middleware(['auth','check.device']);

Route::post('/store-feedback', [App\Http\Controllers\WebController::class, 'storeFeedback'])->name('store-feedback')->middleware(['auth','check.device']);
Route::get('/get-feedback-popup', [App\Http\Controllers\WebController::class, 'showFeedbackPopup'])->name('get-feedback-popup')->middleware(['auth','check.device']);



Route::post('/post_contact', [App\Http\Controllers\WebController::class, 'post_contact'])->name('post_contact');
Route::post('/update_my_currency', [App\Http\Controllers\WebController::class, 'update_my_currency'])->name('update_my_currency');

Route::get('/Affiliates_Reg', [App\Http\Controllers\WebController::class, 'Affiliates_Reg'])->name('Affiliates.create');
Route::post('/Affiliates_store', [App\Http\Controllers\WebController::class, 'Affiliates_Store'])->name('Affiliates.store');
Route::get('/AffiliatesLogin', [App\Http\Controllers\WebController::class, 'Affiliates_ceateLogin'])->name('affiliate.createLogin');
Route::post('/Affiliates_storeLogin', [App\Http\Controllers\WebController::class, 'Affiliates_storeLogin'])->name('affiliate.storeLogin');
Route::get('/Affiliates_forget_create', [App\Http\Controllers\WebController::class, 'Affiliates_forget_create'])->name('affiliate.forget_create');
Route::post('/change_password_affiliate', [App\Http\Controllers\WebController::class, 'change_password_affiliate'])->name('change_password_affiliate');
Route::post('/Affiliates_forget_store', [App\Http\Controllers\WebController::class, 'Affiliates_forget_store'])->name('affiliate.forget_store');
Route::get('/affiliate_notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('affiliate_notifications');
Route::get('/userprofile_affiliate', [App\Http\Controllers\WebController::class, 'userprofile_affiliate'])->name('userprofile_affiliate');
Route::post('/update_user_affiliate', [App\Http\Controllers\WebController::class, 'update_user_affiliate'])->name('update_user_affiliate');
Route::get('/logout_affiliate', [App\Http\Controllers\HomeController::class, 'logout_affiliate'])->name('logout_affiliate');
Route::get('/dashboard_affiliate', [App\Http\Controllers\WebController::class, 'dashboard_affiliate'])->name('affiliate.dashboard_affiliate');
Route::get('/subscribers_affiliate', [App\Http\Controllers\WebController::class, 'subscribers_affiliate'])->name('subscribers_affiliate');
Route::get('/commissions_affiliate', [App\Http\Controllers\WebController::class, 'commissions_affiliate'])->name('commissions_affiliate');
Route::get('/referrals_affiliate', [App\Http\Controllers\WebController::class, 'referrals'])->name('referrals_affiliate');
Route::get('/wallet_affiliate', [App\Http\Controllers\WebController::class, 'wallet'])->name('wallet_affiliate');
Route::group(['middleware' => ['ops.sys']], function () {
    Route::get('/support_affiliate', [App\Http\Controllers\WebController::class, 'support'])->name('support_affiliate');
    Route::get('/ask_support_affiliate', [App\Http\Controllers\WebController::class, 'ask_support'])->name('ask_support_affiliate');
    Route::get('/admin', [App\Http\Controllers\HomeController::class, 'index'])->name('admin');
    Route::get('/admin_profile', [App\Http\Controllers\AdminController::class, 'admin_profile'])->name('admin_profile')->middleware('admin_auth');
    Route::post('/update_admin_profile', [App\Http\Controllers\AdminController::class, 'update_admin_profile'])->name('update_admin_profile')->middleware('admin_auth');
    Route::get('/admin_dashboard', [App\Http\Controllers\AdminController::class, 'admin_dashboard'])->name('admin_dashboard')->middleware('admin_auth');
    Route::get('/admin_wallet', [App\Http\Controllers\AdminController::class, 'admin_wallet'])->name('admin_wallet')->middleware('admin_auth');
    Route::get('/admin_feedback', [App\Http\Controllers\AdminController::class, 'admin_feedback'])->name('admin_feedback')->middleware('admin_auth');

    Route::get('/admin_staff', [App\Http\Controllers\AdminStaffController::class, 'admin_staff'])->name('admin_staff')->middleware('admin_auth');

    Route::get('/admin_new_staff', [App\Http\Controllers\AdminStaffController::class, 'admin_new_staff'])->name('admin_new_staff')->middleware('admin_auth');
});






Route::post('/admin_new_staff', [App\Http\Controllers\AdminStaffController::class, 'add_new_staff'])->name('admin_new_staff')->middleware('admin_auth');


Route::post('/assign_supports', [App\Http\Controllers\AdminStaffController::class, 'assign_supports'])->name('assign_supports')->middleware('admin_auth');




Route::get('/manage_report_wallet', [App\Http\Controllers\AdminController::class, 'manage_report_wallet'])->name('manage_report_wallet')->middleware('admin_auth');
Route::get('/admin_referral', [App\Http\Controllers\AdminController::class, 'admin_referral'])->name('admin_referral')->middleware('admin_auth');

Route::get('/manage_report_referrals', [App\Http\Controllers\AdminController::class, 'manage_report_referrals'])->name('manage_report_referrals')->middleware('admin_auth');
Route::get('/demo_requests', [App\Http\Controllers\AdminController::class, 'demo_requests'])->name('demo_requests')->middleware('admin_auth');
Route::get('/demo_status/{id?}', [App\Http\Controllers\AdminController::class, 'demo_status'])->name('demo_status')->middleware('admin_auth');
Route::get('/subscribers', [App\Http\Controllers\AdminController::class, 'subscribers'])->name('subscribers')->middleware('admin_auth');
Route::get('/email_subscribers', [App\Http\Controllers\AdminController::class, 'email_subscribers'])->name('email_subscribers')->middleware('admin_auth');
Route::post('/store_email_subscriber', [App\Http\Controllers\AdminController::class, 'store_email_subscriber'])->name('store_email_subscriber')->middleware('admin_auth');
Route::get('/email_subscription_status/{id?}', [App\Http\Controllers\AdminController::class, 'email_subscription_status'])->name('email_subscription_status')->middleware('admin_auth');
Route::get('/admin/subscriber-journey-log-data', [App\Http\Controllers\JourneyLogController::class, 'subscriberJourneyLogData'])->name('admin_subscriber_journey_log_data')->middleware('admin_auth');
Route::get('/admin/user-activity-log-data', [App\Http\Controllers\JourneyLogController::class, 'userActivityLogData'])->name('admin_user_activity_log_data')->middleware('admin_auth');
Route::get('/admin/subscription-history-data', [App\Http\Controllers\JourneyLogController::class, 'subscriptionHistoryData'])->name('admin_subscription_history_data')->middleware('admin_auth');
Route::get('/subscription-history-data', [App\Http\Controllers\JourneyLogController::class, 'subscriberSubscriptionHistoryData'])->name('subscription_history_data')->middleware(['auth', 'check.device']);
Route::get('/admin/discount-offer-history-data', [App\Http\Controllers\JourneyLogController::class, 'discountOfferHistoryData'])->name('admin_discount_offer_history_data')->middleware('admin_auth');
Route::get('/discount-offer-history-data', [App\Http\Controllers\JourneyLogController::class, 'subscriberDiscountOfferHistoryData'])->name('discount_offer_history_data')->middleware(['auth', 'check.device']);
//HEERE
Route::get('/subscribersReport', [App\Http\Controllers\SubscriberFilterController::class, 'subscribersReport'])->name('subscribersReport')->middleware('admin_auth');



///  report filtyer
Route::get('/clientsReport', [App\Http\Controllers\ReportFilterController::class, 'clientsReport'])->name('clientsReport')->middleware('admin_auth');
Route::get('/applicationsReport', [App\Http\Controllers\ReportFilterController::class, 'applicationsReport'])->name('applicationsReport')->middleware('admin_auth');
Route::get('/usersReport', [App\Http\Controllers\ReportFilterController::class, 'usersReport'])->name('usersReport')->middleware('admin_auth');
Route::get('/activityReport', [App\Http\Controllers\ReportFilterController::class, 'activityReport'])->name('activityReport')->middleware('admin_auth');
Route::get('/invoicesReport', [App\Http\Controllers\ReportFilterController::class, 'invoicesReport'])->name('invoicesReport')->middleware('admin_auth');
Route::get('/invoicesReport_ap', [App\Http\Controllers\ReportFilterController::class, 'invoicesReport_ap'])->name('invoicesReport_ap')->middleware('admin_auth');
Route::get('/paymentReport', [App\Http\Controllers\ReportFilterController::class, 'paymentReport'])->name('paymentReport')->middleware('admin_auth');
Route::get('/communicationReport', [App\Http\Controllers\ReportFilterController::class, 'communicationReport'])->name('communicationReport')->middleware('admin_auth');
Route::get('/walletReport', [App\Http\Controllers\ReportFilterController::class, 'walletReport'])->name('walletReport')->middleware('admin_auth');
Route::get('/supportReport', [App\Http\Controllers\ReportFilterController::class, 'supportReport'])->name('supportReport')->middleware('admin_auth');
Route::get('/demoReport', [App\Http\Controllers\ReportFilterController::class, 'demoReport'])->name('demoReport')->middleware('admin_auth');
Route::get('/demoRequestReport', [App\Http\Controllers\ReportFilterController::class, 'demoRequestReport'])->name('demoRequestReport')->middleware('admin_auth');
Route::get('/documentReport', [App\Http\Controllers\ReportFilterController::class, 'documentReport'])->name('documentReport')->middleware('admin_auth');





Route::get('/referralsReport', [App\Http\Controllers\ReportFilterController::class, 'referralsReport'])->name('referralsReport');
Route::get('/associatesReport', [App\Http\Controllers\ReportFilterController::class, 'associatesReport'])->name('associatesReport')->middleware(['auth','check.device']);
Route::get('/manage_associates_report', [App\Http\Controllers\ReportFilterController::class, 'manage_associates_report'])->name('manage_associates_report')->middleware(['auth','check.device']);

Route::get('/changeAffiliateStatus', [App\Http\Controllers\AdminController::class, 'changeAffiliateStatus'])->name('changeAffiliateStatus');

Route::get('/affiliates_referrals', [App\Http\Controllers\AdminController::class, 'affiliates_referrals'])->name('affiliates_referrals');
Route::get('/affiliates_wallet', [App\Http\Controllers\AdminController::class, 'affiliates_wallet'])->name('affiliates_wallet');
Route::get('/affiliatesReport', [App\Http\Controllers\AdminController::class, 'affiliatesReport'])->name('affiliatesReport');

Route::get('/new_subscriber', [App\Http\Controllers\AdminController::class, 'new_subscriber'])->name('new_subscriber')->middleware('admin_auth');
Route::get('/update_subscriber/{id?}', [App\Http\Controllers\AdminController::class, 'update_subscriber'])->name('update_subscriber')->middleware('admin_auth');
Route::post('/register_new_subscriber', [App\Http\Controllers\AdminController::class, 'register_new_subscriber'])->name('register_new_subscriber');
Route::get('/new_user', [App\Http\Controllers\AdminController::class, 'new_user'])->name('new_user')->middleware('admin_auth');
Route::post('/register_new_user', [App\Http\Controllers\AdminController::class, 'register_new_user'])->name('register_new_user');
Route::get('/siteuser_update/{id?}', [App\Http\Controllers\AdminController::class, 'siteuser_update'])->name('siteuser_update')->middleware('admin_auth');
Route::get('/view_user/{id?}', [App\Http\Controllers\AdminController::class, 'view_user'])->name('view_user')->middleware('admin_auth');
Route::get('/new_client', [App\Http\Controllers\AdminController::class, 'new_client'])->name('new_client')->middleware('admin_auth');
Route::post('/register_new_client', [App\Http\Controllers\AdminController::class, 'register_new_client'])->name('register_new_client');
Route::get('/client_update/{id?}', [App\Http\Controllers\AdminController::class, 'client_update'])->name('client_update')->middleware('admin_auth');
Route::get('/view_client/{id?}', [App\Http\Controllers\AdminController::class, 'view_client'])->name('view_client')->middleware('admin_auth');
Route::get('/delete_user/{id?}', [App\Http\Controllers\AdminController::class, 'delete_user'])->name('delete_user')->middleware('admin_auth');
Route::get('/delete_clients/{id?}', [App\Http\Controllers\AdminController::class, 'delete_clients'])->name('delete_clients')->middleware('admin_auth');
Route::get('/delete_application/{id?}', [App\Http\Controllers\AdminController::class, 'delete_application'])->name('delete_application')->middleware('admin_auth');
Route::get('/delete_document/{id?}', [App\Http\Controllers\AdminController::class, 'delete_document'])->name('delete_document')->middleware('admin_auth');
Route::get('/communication', [App\Http\Controllers\AdminController::class, 'communication'])->name('communication')->middleware('admin_auth');
Route::get('/admin_notifications', [App\Http\Controllers\NotificationController::class, 'adminIndex'])->name('admin_notifications')->middleware('admin_auth');
Route::post('/offers_store', [App\Http\Controllers\AdminController::class, 'applyOffer'])->name('offers_store')->middleware('admin_auth');

Route::get('/manage_report_communications', [App\Http\Controllers\AdminController::class, 'manage_report_communications'])->name('manage_report_communications')->middleware('admin_auth');
Route::get('/admin_email_broadcast', [App\Http\Controllers\AdminController::class, 'admin_email_broadcast'])->name('admin_email_broadcast')->middleware('admin_auth');
Route::post('/admin_send_email_broadcast', [App\Http\Controllers\AdminController::class, 'admin_send_email_broadcast'])->name('admin_send_email_broadcast')->middleware('admin_auth');
Route::post('/admin_upload_email_broadcast_image', [App\Http\Controllers\AdminController::class, 'admin_upload_email_broadcast_image'])->name('admin_upload_email_broadcast_image')->middleware('admin_auth');
Route::get('/admin_messaging', [App\Http\Controllers\AdminController::class, 'admin_messaging'])->name('admin_messaging')->middleware('admin_auth');
Route::post('/admin_communicate', [App\Http\Controllers\AdminController::class, 'admin_communicate'])->name('admin_communicate')->middleware('admin_auth');
Route::get('/view_communication/{id?}', [App\Http\Controllers\AdminController::class, 'view_communication'])->name('view_communication')->middleware('admin_auth');
Route::get('/meetings', [App\Http\Controllers\AdminController::class, 'meetings'])->name('meetings')->middleware('admin_auth');
Route::get('/notes/{id?}', [App\Http\Controllers\AdminController::class, 'notes'])->name('notes')->middleware('admin_auth');

Route::get('/payments', [App\Http\Controllers\AdminController::class, 'payments'])->name('payments')->middleware('admin_auth');
Route::get('/admin_payment_made', [App\Http\Controllers\AdminController::class, 'admin_payment_made'])->name('admin_payment_made')->middleware('admin_auth');
Route::get('/manage_report_payments', [App\Http\Controllers\AdminController::class, 'manage_report_payments'])->name('manage_report_payments')->middleware('admin_auth');


Route::get('/manage_invoices', [App\Http\Controllers\AdminController::class, 'manage_invoices'])->name('manage_invoices')->middleware('admin_auth');
Route::get('/manage_reports_invoices', [App\Http\Controllers\AdminController::class, 'manage_reports_invoices'])->name('manage_reports_invoices')->middleware('admin_auth');
Route::get('/manage_reports_invoices_ap', [App\Http\Controllers\AdminController::class, 'manage_reports_invoices_ap'])->name('manage_reports_invoices_ap')->middleware('admin_auth');


Route::get('/activity_log', [App\Http\Controllers\AdminController::class, 'activity_log'])->name('activity_log')->middleware('admin_auth');
Route::get('/error_log', [App\Http\Controllers\AdminController::class, 'error_log'])->name('error_log')->middleware('admin_auth');
Route::get('/error_logs_export', [App\Http\Controllers\ExportController::class, 'error_logs_export'])->name('error_logs_export')->middleware('admin_auth');
Route::get('/chat/{id?}', [App\Http\Controllers\AdminController::class, 'chat'])->name('chat')->middleware('admin_auth');
Route::post('/send_response', [App\Http\Controllers\AdminController::class, 'send_response'])->name('send_response');
Route::get('/subscriber_status/{id?}/{localtime?}', [App\Http\Controllers\AdminController::class, 'subscriber_status'])->name('subscriber_status')->middleware('admin_auth');
Route::get('/manage_applications', [App\Http\Controllers\AdminController::class, 'manage_applications'])->name('manage_applications')->middleware('admin_auth');
Route::get('/manage_reports_applications', [App\Http\Controllers\AdminController::class, 'manage_reports_applications'])->name('manage_reports_applications')->middleware('admin_auth');
Route::get('/new_application', [App\Http\Controllers\AdminController::class, 'new_application'])->name('new_application')->middleware('admin_auth');
Route::post('/register_new_application', [App\Http\Controllers\AdminController::class, 'register_new_application'])->name('register_new_application');
Route::get('/application_update/{id?}', [App\Http\Controllers\AdminController::class, 'application_update'])->name('application_update')->middleware('admin_auth');
Route::get('/application_view/{id?}', [App\Http\Controllers\AdminController::class, 'application_view'])->name('application_view')->middleware('admin_auth');
Route::get('/admin/application/{id}/document-list', [App\Http\Controllers\AdminController::class, 'generate_application_document_list'])->name('admin.generate_application_document_list')->middleware('admin_auth');
Route::get('/admin/application/{id}/document-list/download', [App\Http\Controllers\AdminController::class, 'download_application_document_list'])->name('admin.download_application_document_list')->middleware('admin_auth');
Route::post('/get_job_role', [App\Http\Controllers\AdminController::class, 'get_job_role'])->name('get_job_role');
Route::post('/get_cc_countries', [App\Http\Controllers\AdminController::class, 'get_cc_countries'])->name('get_cc_countries');
Route::get('fetch_visa_country/{id?}', [App\Http\Controllers\AdminController::class, 'fetch_visa_country'])->name('fetch_visa_country');

Route::get('/documents', [App\Http\Controllers\AdminController::class, 'documents'])->name('documents')->middleware('admin_auth');
Route::get('/new_document', [App\Http\Controllers\AdminController::class, 'new_document'])->name('new_document')->middleware('admin_auth');
Route::post('/upload_document', [App\Http\Controllers\AdminController::class, 'upload_document'])->name('upload_document');
Route::get('/document_update/{id?}', [App\Http\Controllers\AdminController::class, 'document_update'])->name('document_update')->middleware('admin_auth');
Route::get('/application_management', [App\Http\Controllers\AdminController::class, 'application_management'])->name('application_management')->middleware('admin_auth');
Route::get('/application_tracking', [App\Http\Controllers\AdminController::class, 'application_tracking'])->name('application_tracking')->middleware('admin_auth');
Route::get('/clients/by-subscriber/{id}', [App\Http\Controllers\AdminController::class, 'getClientsBySubscriber'])->name('clients.bySubscriber')->middleware('admin_auth');
Route::get('/get-applications-by-client/{clientId}', [App\Http\Controllers\AdminController::class, 'getApplicationsByClient'])->name('applications.byClient')->middleware('admin_auth');
Route::get('/admin/get-application-data/{id}', [App\Http\Controllers\AdminController::class, 'getApplicationData'])->middleware('admin_auth');


Route::get('/new_app_assignment', [App\Http\Controllers\AdminController::class, 'new_app_assignment'])->name('new_app_assignment')->middleware('admin_auth');
Route::post('/post_app_assignment', [App\Http\Controllers\AdminController::class, 'post_app_assignment'])->name('post_app_assignment');
Route::get('/app_assignment_update/{id?}', [App\Http\Controllers\AdminController::class, 'app_assignment_update'])->name('app_assignment_update')->middleware('admin_auth');
Route::get('/delete_app_assignment/{id?}', [App\Http\Controllers\AdminController::class, 'delete_app_assignment'])->name('delete_app_assignment')->middleware('admin_auth');
Route::post('/get_client', [App\Http\Controllers\AdminController::class, 'get_client'])->name('get_client');
Route::post('/get_applications', [App\Http\Controllers\AdminController::class, 'get_applications'])->name('get_applications');
Route::post('/get_user', [App\Http\Controllers\AdminController::class, 'get_user'])->name('get_user');
Route::get('/admin_new_invoice', [App\Http\Controllers\AdminController::class, 'admin_new_invoice'])->name('admin_new_invoice')->middleware('admin_auth');
Route::post('/admin_new_invoice_post', [App\Http\Controllers\AdminController::class, 'admin_new_invoice_post'])->name('admin_new_invoice_post')->middleware('admin_auth');
Route::post('/admin_invoice/{id}/resend-email', [App\Http\Controllers\AdminController::class, 'resendInvoiceEmail'])->name('admin_resend_invoice_email')->middleware('admin_auth');
Route::get('/admin_edit_invoice/{id}', [App\Http\Controllers\AdminController::class, 'admin_edit_invoice'])->name('admin_edit_invoice')->middleware('admin_auth');
Route::post('/admin_update_invoice/{id}', [App\Http\Controllers\AdminController::class, 'admin_update_invoice'])->name('admin_update_invoice')->middleware('admin_auth');
Route::get('/invoice_detail/{id?}', [App\Http\Controllers\AdminController::class, 'invoice_detail'])->name('invoice_detail')->middleware('admin_auth');
Route::get('/print_invoice_detail/{id?}', [App\Http\Controllers\AdminController::class, 'print_invoice_detail'])->name('print_invoice_detail')->middleware('admin_auth');
Route::get('/reports', [App\Http\Controllers\AdminController::class, 'reports'])->name('reports')->middleware('admin_auth');
Route::get('/analytics', [App\Http\Controllers\AdminController::class, 'analytics'])->name('analytics')->middleware('admin_auth');


Route::get('/affiliates', [App\Http\Controllers\AdminController::class, 'Affiliates'])->name('affiliates')->middleware('admin_auth');

Route::get('/getCommision', [App\Http\Controllers\AdminController::class, 'getCommision'])->name('getCommision')->middleware('admin_auth');
Route::post('/affiliateCommissionPaid', [App\Http\Controllers\AdminController::class, 'affiliateCommissionPaid'])->name('affiliateCommissionPaid')->middleware('admin_auth');
Route::get('/affiliateReportAdmin', [App\Http\Controllers\AdminController::class, 'affiliateReportAdmin'])->name('affiliateReportAdmin')->middleware('admin_auth');
Route::get('/affiliateReferralReportAdmin', [App\Http\Controllers\AdminController::class, 'affiliateReferralReportAdmin'])->name('affiliateReferralReportAdmin')->middleware('admin_auth');
Route::get('/affiliateWalletReportAdmin', [App\Http\Controllers\AdminController::class, 'affiliateWalletReportAdmin'])->name('affiliateWalletReportAdmin')->middleware('admin_auth');


Route::get('/manage_support', [App\Http\Controllers\AdminController::class, 'manage_support'])->name('manage_support')->middleware('admin_auth');
Route::get('/manage_faq', [App\Http\Controllers\AdminController::class, 'manage_faq'])->name('manage_faq')->middleware('admin_auth');
Route::get('/add_faq', [App\Http\Controllers\AdminController::class, 'add_faq'])->name('add_faq')->middleware('admin_auth');
Route::get('/update_faq/{id?}', [App\Http\Controllers\AdminController::class, 'update_faq'])->name('update_faq')->middleware('admin_auth');
Route::post('/register_faq', [App\Http\Controllers\AdminController::class, 'register_faq'])->name('register_faq');
Route::get('/delete_faq/{id?}', [App\Http\Controllers\AdminController::class, 'delete_faq'])->name('delete_faq')->middleware('admin_auth');
Route::get('/settings', [App\Http\Controllers\AdminController::class, 'settings'])->name('settings')->middleware('admin_auth');
Route::post('/save_admin_dashboard_settings', [App\Http\Controllers\AdminController::class, 'save_admin_dashboard_settings'])->name('save_admin_dashboard_settings')->middleware('admin_auth');
Route::post('/invoice_settings', [App\Http\Controllers\AdminController::class, 'invoice_settings'])->name('invoice_settings');
Route::post('/update_currency', [App\Http\Controllers\AdminController::class, 'update_currency'])->name('update_currency');
Route::post('/update_timezone', [App\Http\Controllers\AdminController::class, 'update_timezone'])->name('update_timezone');
Route::get('/view_query/{id?}', [App\Http\Controllers\AdminController::class, 'view_query'])->name('view_query')->middleware('admin_auth');
Route::get('/admin/ticket-activity-log/{id}', [App\Http\Controllers\AdminController::class, 'ticketActivityLogData'])->name('admin_ticket_activity_log_data')->middleware('admin_auth');
Route::get('/query_response/{id?}', [App\Http\Controllers\AdminController::class, 'query_response'])->name('query_response')->middleware('admin_auth');
Route::get('/delete_query/{id?}', [App\Http\Controllers\AdminController::class, 'delete_query'])->name('delete_query')->middleware('admin_auth');
Route::get('/update_query_status/{id?}', [App\Http\Controllers\AdminController::class, 'update_query_status'])->name('update_query_status')->middleware('admin_auth');
Route::post('/send_query_response', [App\Http\Controllers\AdminController::class, 'send_query_response'])->name('send_query_response');


Route::get('/manage_users', [App\Http\Controllers\AdminController::class, 'manage_users'])->name('manage_users')->middleware('admin_auth');

Route::get('/manage_user_reports', [App\Http\Controllers\AdminController::class, 'manage_user_reports'])->name('manage_user_reports')->middleware('admin_auth');
Route::get('/manage_clients', [App\Http\Controllers\AdminController::class, 'manage_clients'])->name('manage_clients')->middleware('admin_auth');
Route::get('/manage_clients_report', [App\Http\Controllers\AdminController::class, 'manage_clients_report'])->name('manage_clients_report')->middleware('admin_auth');

Route::get('/client_documents_reports', [App\Http\Controllers\AdminController::class, 'client_documents_reports'])->name('client_documents_reports')->middleware('admin_auth');
Route::get('/manage_contactus', [App\Http\Controllers\AdminController::class, 'manage_contactus'])->name('manage_contactus')->middleware('admin_auth');
Route::get('/manage_membership', [App\Http\Controllers\AdminController::class, 'manage_membership'])->name('manage_membership')->middleware('admin_auth');
Route::get('/add_membership', [App\Http\Controllers\AdminController::class, 'add_membership'])->name('add_membership')->middleware('admin_auth');
Route::post('/post_membership', [App\Http\Controllers\AdminController::class, 'post_membership'])->name('post_membership');
Route::get('/membership_plan/{id?}', [App\Http\Controllers\AdminController::class, 'membership_plan'])->name('membership_plan')->middleware('admin_auth');
Route::get('/manage_features', [App\Http\Controllers\AdminController::class, 'manage_features'])->name('manage_features')->middleware('admin_auth');
Route::get('/add_feature', [App\Http\Controllers\AdminController::class, 'add_feature'])->name('add_feature')->middleware('admin_auth');
Route::post('/post_feature', [App\Http\Controllers\AdminController::class, 'post_feature'])->name('post_feature');
Route::get('/view_feature/{id?}', [App\Http\Controllers\AdminController::class, 'view_feature'])->name('view_feature')->middleware('admin_auth');
Route::get('/manage_about_adwiseri', [App\Http\Controllers\AdminController::class, 'manage_about_adwiseri'])->name('manage_about_adwiseri')->middleware('admin_auth');
Route::post('/update_about_adwiseri', [App\Http\Controllers\AdminController::class, 'update_about_adwiseri'])->name('update_about_adwiseri');
Route::get('/manage_landing_discounts_offers', [App\Http\Controllers\AdminController::class, 'manage_landing_discounts_offers'])->name('manage_landing_discounts_offers')->middleware('admin_auth');
Route::post('/update_landing_promo_settings', [App\Http\Controllers\AdminController::class, 'update_landing_promo_settings'])->name('update_landing_promo_settings')->middleware('admin_auth');
Route::post('/store_landing_promo_item', [App\Http\Controllers\AdminController::class, 'store_landing_promo_item'])->name('store_landing_promo_item')->middleware('admin_auth');
Route::post('/update_landing_promo_item/{id}', [App\Http\Controllers\AdminController::class, 'update_landing_promo_item'])->name('update_landing_promo_item')->middleware('admin_auth');
Route::get('/delete_landing_promo_item/{id}', [App\Http\Controllers\AdminController::class, 'delete_landing_promo_item'])->name('delete_landing_promo_item')->middleware('admin_auth');
Route::get('/manage_homepage_sections', [App\Http\Controllers\AdminController::class, 'manage_homepage_sections'])->name('manage_homepage_sections')->middleware('admin_auth');
Route::post('/update_homepage_sections', [App\Http\Controllers\AdminController::class, 'update_homepage_sections'])->name('update_homepage_sections')->middleware('admin_auth');


Route::post('/update_contactus', [App\Http\Controllers\AdminController::class, 'update_contactus'])->name('update_contactus');


// Testing routes
Route::get('/timezone_test', [App\Http\Controllers\WebController::class, 'timezone_test'])->name('timezone_test');


Route::post('/addClientApplication', [App\Http\Controllers\ApplicationController::class, 'addClientApplication'])->name('addClientApplication');

Route::get('/getClient', [App\Http\Controllers\ApplicationController::class, 'getClient'])->name('getClient');
Route::post('/addClientDependent', [App\Http\Controllers\ApplicationController::class, 'addClientDependent'])->name('addClientDependent');
Route::get('/manage_dependents', [App\Http\Controllers\ApplicationController::class, 'manage_dependants'])->name('manage_dependents');
Route::get('/get_dependants/{id?}', [App\Http\Controllers\ApplicationController::class, 'getDependants'])->name('get_dependants');
Route::post('/update_dependant/{id}', [App\Http\Controllers\ApplicationController::class, 'updateDependant'])->name('update_dependant');
Route::delete('/delete_dependant/{id}', [App\Http\Controllers\ApplicationController::class, 'deleteDependant'])->name('delete_dependant');
Route::get('/subscriber_dependents', [App\Http\Controllers\ApplicationController::class, 'subscriber_dependents'])->name('subscriber_dependents');

