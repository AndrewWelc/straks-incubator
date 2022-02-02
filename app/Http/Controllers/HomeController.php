<?php

namespace App\Http\Controllers;

use App\Campaign;
use App\Category;
use App\Mail\ContactUs;
use App\Mail\ContactUsSendToSender;
use App\Payment;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
        global $is_db_connected;

        if(!$is_db_connected) {
            return view('welcome');
        }

        $title = trans('app.banner_main_header');

        $categories = Category::orderBy('category_name', 'asc')->get();
        $staff_picks = Campaign::active()->staff_picks()->with('user')->orderBy('id', 'desc')->take(8)->get();
        $new_campaigns = Campaign::active()->with('user')->orderBy('created_at', 'desc')->take(20)->get();
        $funded_campaigns = Campaign::active()->funded()->with('user')->orderBy('id', 'desc')->take(8)->get();
        $featured_campaign = Campaign::whereIsFeature(1)->first();

        $campaigns_count = Campaign::all()->count();
        $funded_campaigns_count = Campaign::active()->funded()->count();
        $users_count = User::all()->count();
        $fund_raised_count = Payment::whereStatus('success')->sum('amount');

        return view('home', compact('title','categories', 'staff_picks', 'new_campaigns', 'funded_campaigns', 'campaigns_count', 'users_count', 'fund_raised_count', 'featured_campaign', 'funded_campaigns_count'));

    }

    public function contactUs(){
        $title = trans('app.contact_us');
        return view('contact_us', compact('title'));
    }

    public function contactUsPost(Request $request){
        $rules = [
            'name'  => 'required',
            'email'  => 'required|email',
            'subject'  => 'required',
        ];
        if (get_option('enable_recaptcha_contact_form') == 1){
            $rules['g-recaptcha-response'] = 'required';
        }
        $this->validate($request, $rules);

        if (get_option('enable_recaptcha_contact_form') == 1) {
            $secret = get_option('recaptcha_secret_key');
            $gRecaptchaResponse = $request->input('g-recaptcha-response');
            $remoteIp = $request->ip();

            $recaptcha = new \ReCaptcha\ReCaptcha($secret);
            $resp = $recaptcha->verify($gRecaptchaResponse, $remoteIp);
            if (!$resp->isSuccess()) {
                return redirect()->back()->with('error', 'reCAPTCHA is not verified');
            }
        }

        try{
            Mail::send(new ContactUs($request));
            Mail::send(new ContactUsSendToSender($request));
        }catch (\Exception $exception){
            return redirect()->back()->with('error', '<h4>'.trans('app.smtp_error_message').'</h4>'. $exception->getMessage());
        }

        return redirect()->back()->with('success', trans('app.message_has_been_sent'));
    }


    public function acceptCookie(Request $request){
        return response(['accept_cookie' => true])->cookie('accept_cookie', true, 43800);
    }
    
}
