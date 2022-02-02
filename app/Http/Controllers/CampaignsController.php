<?php

namespace App\Http\Controllers;

use App\Campaign;
use App\Category;
use App\Country;
use App\Payment;
use App\Reward;
use App\HDW;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;

class CampaignsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = trans('app.start_a_campaign');
        $categories = Category::all();
        $countries = Country::all();

        return view('admin.start_campaign', compact('title', 'categories', 'countries'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $rules = [
            'category'      => 'required|regex:[A-Za-z1-9]',
            'title'         => 'required|regex:[A-Za-z1-9]',
            'description'   => 'required|regex:[A-Za-z1-9]',
            'short_description' => 'required|max:200|regex:[A-Za-z1-9]',
            'goal'          => 'required|regex:[1-9]',
            'end_method'    => 'required',
            'country_id'    => 'required',
                ];

        $this->validate($request, $rules);
         if (get_option('enable_recaptcha_login') == 1){
            $this->validate($request, array('g-recaptcha-response' => 'required'));

            $secret = get_option('recaptcha_secret_key');
            $gRecaptchaResponse = $request->input('g-recaptcha-response');
            $remoteIp = $request->ip();

            $recaptcha = new \ReCaptcha\ReCaptcha($secret);
            $resp = $recaptcha->verify($gRecaptchaResponse, $remoteIp);
            if ( ! $resp->isSuccess()) {
                return redirect()->back()->with('error', 'reCAPTCHA is not verified');
            }

        }
        $user_id = Auth::user()->id;

        $slug = unique_slug($request->title);

        //feature image has been moved to update
        $data = [
            'user_id'           => $user_id,
            'category_id'       => $request->category,
            'title'             => $request->title,
            'slug'              => $slug,
            'short_description' => $request->short_description,
            'description'       => $request->description,
            'campaign_owner_commission'              => get_option('campaign_owner_commission'),
            'goal'              => $request->goal,
            'min_amount'        => $request->min_amount,
            'max_amount'        => $request->max_amount,
            'recommended_amount' => $request->recommended_amount,
            'amount_prefilled'  => $request->amount_prefilled,
            'end_method'        => $request->end_method,
            'video'             => $request->video,
            'feature_image'     => $request->feature_image,
            'status'            => 0,
            'country_id'        => $request->country_id,
            'address'           => $request->address,
            'is_funded'         => 0,
            'is_feature'        => 0,
            'start_date'        => $request->start_date,
            'end_date'          => $request->end_date,
        ];

        $create = Campaign::create($data);

        if ($create){
            return redirect(route('edit_campaign', $create->id))->with('success', trans('app.campaign_created'));
        }
        return back()->with('error', trans('app.something_went_wrong'))->withInput($request->input());
    }

    public function myCampaigns(){
        $title = trans('app.my_campaigns');
        $user = request()->user();
        //$my_campaigns = $user->my_campaigns;
        $my_campaigns = Campaign::whereUserId($user->id)->orderBy('created_at', 'desc')->get();

        return view('admin.my_campaigns', compact('title', 'my_campaigns'));
    }

    public function myPendingCampaigns(){
        $title = trans('app.pending_campaigns');
        $user = request()->user();
        $my_campaigns = Campaign::pending()->whereUserId($user->id)->orderBy('created_at', 'desc')->get();

        return view('admin.my_campaigns', compact('title', 'my_campaigns'));
    }

    public function allCampaigns(){
        $title = trans('app.all_campaigns');
        $campaigns = Campaign::active()->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.all_campaigns', compact('title', 'campaigns'));
    }

    public function staffPicksCampaigns(){
        $title = trans('app.staff_picks');
        $campaigns = Campaign::staff_picks()->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.all_campaigns', compact('title', 'campaigns'));
    }
    public function fundedCampaigns(){
        $title = trans('app.funded');
        $campaigns = Campaign::funded()->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.all_campaigns', compact('title', 'campaigns'));
    }


    public function blockedCampaigns(){
        $title = trans('app.blocked_campaigns');
        $campaigns = Campaign::blocked()->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.all_campaigns', compact('title', 'campaigns'));
    }
    public function pendingCampaigns(){
        $title = trans('app.pending_campaigns');
        $campaigns = Campaign::pending()->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.all_campaigns', compact('title', 'campaigns'));
    }

    public function expiredCampaigns(){
        $title = trans('app.expired_campaigns');
        $campaigns = Campaign::active()->expired()->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.all_campaigns', compact('title', 'campaigns'));
    }

    public function searchAdminCampaigns(Request $request){
        $title = trans('app.campaigns_search_results');
        $campaigns = Campaign::where('title', 'like', "%{$request->q}%")->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.all_campaigns', compact('title', 'campaigns'));
    }

    public function deleteCampaigns($id = 0){
        if(config('app.is_demo')){
            return redirect()->back()->with('error', __('app.feature_disable_demo'));
        }

        if ($id){
            $campaign = Campaign::find($id);
            if ($campaign){
                $campaign->delete();
            }
        }
        return back()->with('success', trans('app.campaign_deleted'));
    }

    public function browseCampaign(){
        $campaigns = Campaign::active()->with('user')->orderBy('created_at', 'desc')->paginate(32);
        $title = __('app.browse_campaigns');

        return view('browse_campaigns', compact('title', 'campaigns'));
    }

    public function projectsWeLoved(){
        $title = trans('app.staff_picks');
        $campaigns = Campaign::staff_picks()->orderBy('created_at', 'desc')->paginate(32);
        return view('browse_campaigns', compact('title', 'campaigns'));
    }

    public function recentlyFundedCampaigns(){
        $title = trans('app.recently_funded_campaigns');
        $campaigns = Campaign::funded()->orderBy('created_at', 'desc')->paginate(32);
        return view('browse_campaigns', compact('title', 'campaigns'));
    }

    //recentlyFundedCampaigns
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($slug = null){
        if ( ! $slug){
            abort(404);
        }
        $campaign = Campaign::whereSlug($slug)->first();
        $title = $campaign->title;

        $enable_discuss = get_option('enable_disqus_comment');
        return view('campaign_single', compact('campaign', 'title', 'enable_discuss'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user_id = request()->user()->id;
        $campaign = Campaign::find($id);
        //todo: checked if admin then he can access...
        if ($campaign->user_id != $user_id){
            abort(404, __('app.unauthorised_access'));
        }

        $title = trans('app.edit_campaign');
        $categories = Category::all();
        $countries = Country::all();

        return view('admin.edit_campaign', compact('title', 'categories', 'countries', 'campaign'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id){

        $rules = [
            'category'      => 'required',
            'title'         => 'required',
            'short_description' => 'required|max:200',
            'description'   => 'required',
            'goal'          => 'required',
            'country_id'    => 'required',
        ];

        $this->validate($request, $rules);

        $data = [
            'category_id'       => $request->category,
            'title'             => $request->title,
            'short_description' => $request->short_description,
            'description'       => $request->description,
            'goal'              => $request->goal,
            'min_amount'        => $request->min_amount,
            'max_amount'        => $request->max_amount,
            'recommended_amount' => $request->recommended_amount,
            'amount_prefilled'  => $request->amount_prefilled,
            'end_method'        => $request->end_method,
            'video'             => $request->video,
            'feature_image'     => $request->feature_image,
            'country_id'        => $request->country_id,
            'address'           => $request->address,
            'start_date'        => $request->start_date,
            'end_date'          => $request->end_date,
        ];

        $update = Campaign::whereId($id)->update($data);

        if ($update){
            return redirect(route('edit_campaign', $id))->with('success', trans('app.campaign_created'));
        }
        return back()->with('error', trans('app.something_went_wrong'))->withInput($request->input());
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     *
     * Search Campaigns
     */
    public function search(Request $request){
        if ($request->q){
            $q = $request->q;
            $title = trans('app.search_campaigns');

            $campaigns = Campaign::active()->where('title', 'like', "%{$q}%")->orWhere('short_description', 'like', "%{$request->q}%")->orWhere('description', 'like', "%{$q}%")->paginate(30);
            $search_time = number_format(microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"], 2);

            return view('search', compact('title', 'campaigns', 'q', 'search_time'));
        }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function showBackers($slug){
        if ( ! $slug){
            abort(404);
        }
        $campaign = Campaign::whereSlug($slug)->first();
        $title = trans('app.backers').' | '.$campaign->title;
        return view('campaign_backers', compact('campaign', 'title'));

    }

    public function showUpdates($slug){
        if ( ! $slug){
            abort(404);
        }
        $campaign = Campaign::whereSlug($slug)->first();

        $title = $campaign->title;
        return view('campaign_update', compact('campaign', 'title'));
    }

    public function showFaqs($slug){
        if ( ! $slug){
            abort(404);
        }
        $campaign = Campaign::whereSlug($slug)->first();
        $title = $campaign->title;
        return view('campaign_faqs', compact('campaign', 'title'));
    }

    /**
     * @param $id
     * @return mixed
     *
     * todo: need to be moved it to reward controller
     */
    public function rewardsInCampaignEdit($id){
        $title = trans('app.campaign_rewards');
        $campaign = Campaign::find($id);
        $rewards = Reward::whereCampaignId($campaign->id)->get();
        return view('admin.campaign_rewards', compact('title', 'campaign', 'rewards'));
    }

    /**
     * @param Request $request
     * @param int $reward_id
     * @return mixed
     */
    public function addToCart(Request $request, $reward_id = 0){
        if ($reward_id){
            //If checkout request come from reward
            session( ['cart' =>  ['cart_type' => 'reward', 'reward_id' => $reward_id] ] );

            $reward = Reward::find($reward_id);
            if($reward->campaign->is_ended()){
                $request->session()->forget('cart');
                return redirect()->back()->with('error', trans('app.invalid_request'));
            }
        }else{
            //Or if comes from donate button
            session( ['cart' =>  ['cart_type' => 'donation', 'campaign_id' => $request->campaign_id, 'amount' => $request->amount ] ] );
        }


        return redirect(route('checkout'));
    }

    public function statusChange($id, $status = null){

        $campaign = Campaign::find($id);
        if ($campaign && $status){

            if ($status == 'approve'){
                $campaign->status = 1;
                $campaign->save();

            }elseif($status == 'block'){
                $campaign->status = 2;
                $campaign->save();
            }elseif($status == 'funded'){
                $campaign->is_funded = 1;
                $campaign->save();
            }elseif ($status == 'add_staff_picks'){
                $campaign->is_staff_picks = 1;
                $campaign->save();

            }elseif($status == 'remove_staff_picks'){
                $campaign->is_staff_picks = 0;
                $campaign->save();
            }elseif($status == 'feature'){
                Campaign::where('is_feature', '=', 1)->update(['is_feature' => 0]);
                $campaign->is_feature = 1;
                $campaign->save();
            }

        }
        return back()->with('success', trans('app.status_updated'));
    }

    /**
     * @return mixed
     *
     * Checkout page
     */
    public function checkout(){
        $title = trans('app.checkout');

        if ( ! session('cart')){
            return view('checkout_empty', compact('title'));
        }

        $reward = null;
        if(session('cart.cart_type') == 'reward'){
            $reward = Reward::find(session('cart.reward_id'));
            $campaign = Campaign::find($reward->campaign_id);
        }elseif (session('cart.cart_type') == 'donation'){
            $campaign = Campaign::find(session('cart.campaign_id'));
        }
        if (session('cart')){
            return view('checkout', compact('title', 'campaign', 'reward'));
        }
        return view('checkout_empty', compact('title'));
    }

    public function checkoutPost(Request $request){
        $title = trans('app.checkout');

        if ( ! session('cart')){
            return view('checkout_empty', compact('title'));
        }

        $cart = session('cart');
        $input = array_except($request->input(), '_token');
        session(['cart' => array_merge($cart, $input)]);

        if(session('cart.cart_type') == 'reward'){
            $reward = Reward::find(session('cart.reward_id'));
            $campaign = Campaign::find($reward->campaign_id);
        }elseif (session('cart.cart_type') == 'donation'){
            $campaign = Campaign::find(session('cart.campaign_id'));
        }

        //dd(session('cart'));
        return view('payment', compact('title', 'campaign'));
    }

    /**
     * @param Request $request
     * @return mixed
     *
     * Payment gateway PayPal
     */
    public function paypalRedirect(Request $request){
        if ( ! session('cart')){
            return view('checkout_empty', compact('title'));
        }
        //Find the campaign
        $cart = session('cart');

        $amount = 0;
        if(session('cart.cart_type') == 'reward'){
            $reward = Reward::find(session('cart.reward_id'));
            $amount = $reward->amount;
            $campaign = Campaign::find($reward->campaign_id);
        }elseif (session('cart.cart_type') == 'donation'){
            $campaign = Campaign::find(session('cart.campaign_id'));
            $amount = $cart['amount'];
        }
        $currency = get_option('currency_sign');
        $user_id = null;
        if (Auth::check()){
            $user_id = Auth::user()->id;
        }
        //Create payment in database


        $transaction_id = 'tran_'.time().str_random(6);
        // get unique recharge transaction id
        while( ( Payment::whereLocalTransactionId($transaction_id)->count() ) > 0) {
            $transaction_id = 'reid'.time().str_random(5);
        }
        $transaction_id = strtoupper($transaction_id);

        $payments_data = [
            'name' => session('cart.full_name'),
            'email' => session('cart.email'),

            'user_id'               => $user_id,
            'campaign_id'           => $campaign->id,
            'reward_id'             => session('cart.reward_id'),

            'amount'                => $amount,
            'payment_method'        => 'paypal',
            'status'                => 'initial',
            'currency'              => $currency,
            'local_transaction_id'  => $transaction_id,

            'contributor_name_display'  => session('cart.contributor_name_display'),
        ];
        //Create payment and clear it from session
        $created_payment = Payment::create($payments_data);
        $request->session()->forget('cart');

        // PayPal settings
        $paypal_action_url = "https://www.paypal.com/cgi-bin/webscr";
        if (get_option('enable_paypal_sandbox') == 1)
            $paypal_action_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";

        $paypal_email = get_option('paypal_receiver_email');
        $return_url = route('payment_success',$transaction_id);
        $cancel_url = route('checkout');
        $notify_url = route('paypal_notify', $transaction_id);

        $item_name = $campaign->title." [Contributing]";

        // Check if paypal request or response
        $querystring = '';

        // Firstly Append paypal account to querystring
        $querystring .= "?business=".urlencode($paypal_email)."&";

        // Append amount& currency (£) to quersytring so it cannot be edited in html
        //The item name and amount can be brought in dynamically by querying the $_POST['item_number'] variable.
        $querystring .= "item_name=".urlencode($item_name)."&";
        $querystring .= "amount=".urlencode($amount)."&";
        $querystring .= "currency_code=".urlencode($currency)."&";

        $querystring .= "first_name=".urlencode(session('cart.full_name'))."&";
        //$querystring .= "last_name=".urlencode($ad->user->last_name)."&";
        $querystring .= "payer_email=".urlencode(session('cart.email') )."&";
        $querystring .= "item_number=".urlencode($created_payment->local_transaction_id)."&";

        //loop for posted values and append to querystring
        foreach(array_except($request->input(), '_token') as $key => $value){
            $value = urlencode(stripslashes($value));
            $querystring .= "$key=$value&";
        }

        // Append paypal return addresses
        $querystring .= "return=".urlencode(stripslashes($return_url))."&";
        $querystring .= "cancel_return=".urlencode(stripslashes($cancel_url))."&";
        $querystring .= "notify_url=".urlencode($notify_url);

        // Append querystring with custom field
        //$querystring .= "&custom=".USERID;

        // Redirect to paypal IPN
        header('location:'.$paypal_action_url.$querystring);
        exit();
    }

    /**
     * @param Request $request
     * @param $transaction_id
     *
     * Check paypal notify
     */
    public function paypalNotify(Request $request, $transaction_id){
        //todo: need to  be check
        $payment = Payment::whereLocalTransactionId($transaction_id)->where('status','!=','success')->first();

        $verified = paypal_ipn_verify();
        if ($verified){
            //Payment success, we are ready to approve your payment
            $payment->status = 'success';
            $payment->charge_id_or_token = $request->txn_id;
            $payment->description = $request->item_name;
            $payment->payer_email = $request->payer_email;
            $payment->payment_created = strtotime($request->payment_date);
            $payment->save();
        }else{
            $payment->status = 'declined';
            $payment->description = trans('app.payment_declined_msg');
            $payment->save();
        }
        // Reply with an empty 200 response to indicate to paypal the IPN was received correctly
        header("HTTP/1.1 200 OK");
    }


    /**
     * @return array
     *
     * receive card payment from stripe
     */
    public function paymentStripeReceive(Request $request){

        $user_id = null;
        if (Auth::check()){
            $user_id = Auth::user()->id;
        }

        $stripeToken = $request->stripeToken;
        \Stripe\Stripe::setApiKey(get_stripe_key('secret'));
        // Create the charge on Stripe's servers - this will charge the user's card
        try {
            $cart = session('cart');

            //Find the campaign
            $amount = 0;
            if(session('cart.cart_type') == 'reward'){
                $reward = Reward::find(session('cart.reward_id'));
                $amount = $reward->amount;
                $campaign = Campaign::find($reward->campaign_id);
            }elseif (session('cart.cart_type') == 'donation'){
                $campaign = Campaign::find(session('cart.campaign_id'));
                $amount = $cart['amount'];
            }
            $currency = get_option('currency_sign');

            //Charge from card
            $charge = \Stripe\Charge::create(array(
                "amount"        => ($amount * 100), // amount in cents, again
                "currency"      => $currency,
                "source"        => $stripeToken,
                "description"   => $campaign->title." [Contributing]"
            ));

            if ($charge->status == 'succeeded'){
                //Save payment into database
                $data = [
                    'name' => session('cart.full_name'),
                    'email' => session('cart.email'),
                    'amount' => ($charge->amount / 100 ),

                    'user_id'               => $user_id,
                    'campaign_id'           => $campaign->id,
                    'reward_id'             => session('cart.reward_id'),
                    'payment_method'        => 'stripe',
                    'currency'              => $currency,
                    'charge_id_or_token'    => $charge->id,
                    'description'           => $charge->description,
                    'payment_created'       => $charge->created,

                    //Card Info
                    'card_last4'        => $charge->source->last4,
                    'card_id'           => $charge->source->id,
                    'card_brand'        => $charge->source->brand,
                    'card_country'      => $charge->source->US,
                    'card_exp_month'    => $charge->source->exp_month,
                    'card_exp_year'     => $charge->source->exp_year,

                    'contributor_name_display'  => session('cart.contributor_name_display'),
                    'status'                    => 'success',
                ];

                Payment::create($data);

                $request->session()->forget('cart');
                return ['success'=>1, 'msg'=> trans('app.payment_received_msg'), 'response' => $this->payment_success_html()];
            }
        } catch(\Stripe\Error\Card $e) {
            // The card has been declined
            return ['success'=>0, 'msg'=> trans('app.payment_declined_msg'), 'response' => $e];
        }
    }

    public function payment_success_html(){
        $html = ' <div class="payment-received">
                            <h1> <i class="fa fa-check-circle-o"></i> '.trans('app.payment_thank_you').'</h1>
                            <p>'.trans('app.payment_receive_successfully').'</p>
                            <a href="'.route('home').'" class="btn btn-filled">'.trans('app.home').'</a>
                        </div>';
        return $html;
    }

    public function paymentSuccess(Request $request, $transaction_id = null){
        if ($transaction_id){
            $payment = Payment::whereLocalTransactionId($transaction_id)->whereStatus('initial')->first();
            if ($payment){
                $payment->status = 'pending';
                $payment->save();
            }
        }

        $title = trans('app.payment_success');
        return view('payment_success', compact('title'));
    }

    /**
     * @date April 29, 2017
     * @since v.1.1
     */
    public function paymentBankTransferReceive(Request $request){
        $rules = [
            'bank_swift_code'   => 'required',
            'account_number'    => 'required',
            'branch_name'       => 'required',
            'branch_address'    => 'required',
            'account_name'      => 'required',
        ];
        $this->validate($request, $rules);

        //get Cart Item
        if ( ! session('cart')){
            return view('checkout_empty', compact('title'));
        }
        //Find the campaign
        $cart = session('cart');

        $amount = 0;
        if(session('cart.cart_type') == 'reward'){
            $reward = Reward::find(session('cart.reward_id'));
            $amount = $reward->amount;
            $campaign = Campaign::find($reward->campaign_id);
        }elseif (session('cart.cart_type') == 'donation'){
            $campaign = Campaign::find(session('cart.campaign_id'));
            $amount = $cart['amount'];
        }
        $currency = get_option('currency_sign');
        $user_id = null;
        if (Auth::check()){
            $user_id = Auth::user()->id;
        }
        //Create payment in database


        $transaction_id = 'tran_'.time().str_random(6);
        // get unique recharge transaction id
        while( ( Payment::whereLocalTransactionId($transaction_id)->count() ) > 0) {
            $transaction_id = 'reid'.time().str_random(5);
        }
        $transaction_id = strtoupper($transaction_id);

        $payments_data = [
            'name' => session('cart.full_name'),
            'email' => session('cart.email'),

            'user_id'               => $user_id,
            'campaign_id'           => $campaign->id,
            'reward_id'             => session('cart.reward_id'),

            'amount'                => $amount,
            'payment_method'        => 'bank_transfer',
            'status'                => 'pending',
            'currency'              => $currency,
            'local_transaction_id'  => $transaction_id,

            'contributor_name_display'  => session('cart.contributor_name_display'),

            'bank_swift_code'   => $request->bank_swift_code,
            'account_number'    => $request->account_number,
            'branch_name'       => $request->branch_name,
            'branch_address'    => $request->branch_address,
            'account_name'      => $request->account_name,
            'iban'              => $request->iban,
        ];
        //Create payment and clear it from session
        $created_payment = Payment::create($payments_data);
        $request->session()->forget('cart');

        return ['success'=>1, 'msg'=> trans('app.payment_received_msg'), 'response' => $this->payment_success_html()];

    }
    public function paymentSTRAKSTransferReceive(Request $request){
        $rules = [
            'straks_transaction_hash'    => 'bail:required',
               ];
        $this->validate($request, $rules);

        //get Cart Item
        if ( ! session('cart')){
            return view('checkout_empty', compact('title'));
        }
        //Find the campaign
        $cart = session('cart');

        $amount = 0;
        if(session('cart.cart_type') == 'reward'){
            $reward = Reward::find(session('cart.reward_id'));
            $amount = $reward->amount;
            $campaign = Campaign::find($reward->campaign_id);
        }elseif (session('cart.cart_type') == 'donation'){
            $campaign = Campaign::find(session('cart.campaign_id'));
            $amount = $cart['amount'];
        }
        $currency = get_option('currency_sign');
        $user_id = null;
        if (Auth::check()){
            $user_id = Auth::user()->id;
        }
        //Create payment in database


        $transaction_id = 'tran_'.time().str_random(6);
        // get unique recharge transaction id
        while( ( Payment::whereLocalTransactionId($transaction_id)->count() ) > 0) {
            $transaction_id = 'reid'.time().str_random(5);
        }
        $transaction_id = strtoupper($transaction_id);

        $payments_data = [
            'name' => session('cart.full_name'),
            'email' => session('cart.email'),

            'user_id'               => $user_id,
            'campaign_id'           => $campaign->id,
            'reward_id'             => session('cart.reward_id'),

            'amount'                => $amount,
            'payment_method'        => 'STRAKS',
            'status'                => 'pending',
            'currency'              => $currency,
            'straks_transaction_hash'  => $request->straks_transaction_hash,
            'straks_payment_address'  => $request->straks_payment_address,

            'contributor_name_display'  => session('cart.contributor_name_display'),

        ];
        //Create payment and clear it from session
        $created_payment = Payment::create($payments_data);
        $request->session()->forget('cart');

        return ['success'=>1, 'msg'=> trans('app.payment_received_msg'), 'response' => $this->payment_success_html()];

    }

    public function rewardDigitalDownloads($reward_id){
        $reward = Reward::find($reward_id);
        if ( ! $reward){
            abort(404);
        }
        $user = Auth::user();

        $verify_payment = Payment::whereUserId($user->id)->whereRewardId($reward_id)->whereStatus('success')->first();
        if ( ! $verify_payment){
            abort(404);
        }

        $media_download = get_media($reward->digital_downloads);

        $pathToFile = './uploads/'.$media_download->slug_ext;
        $name = $media_download->name;
        $headers = ['Content-Type: '.$media_download->mime_type];

        return response()->download($pathToFile, $name, $headers);
    }
}
