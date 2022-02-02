<?php

namespace App\Providers;

use App\Option;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(){

        try {
            DB::connection()->getPdo();

            //Auto-loading options to reduce the query
            $optionsQuery = Option::all('option_key', 'option_value');
            $options = [];
            if ($optionsQuery->count()){
                foreach ($optionsQuery as $option){
                    $options[$option->option_key] = $option->option_value;
                }
            }

            $GLOBALS['options'] = $options;

            //checking if database connected
            $GLOBALS['is_db_connected'] = true;

            /**
             * Set dynamic configuration for third party services
             */
            $facebookConfig = [
                'services.facebook' =>
                    [
                        'client_id' => get_option('fb_app_id'),
                        'client_secret' => get_option('fb_app_secret'),
                        'redirect' => url('login/facebook-callback'),
                    ]
            ];
            $googleConfig = [
                'services.google' =>
                    [
                        'client_id' => get_option('google_client_id'),
                        'client_secret' => get_option('google_client_secret'),
                        'redirect' => url('login/google-callback'),
                    ]
            ];
            $twitterConfig = [
                'services.twitter' =>
                    [
                        'client_id' => get_option('twitter_consumer_key'),
                        'client_secret' => get_option('twitter_consumer_secret'),
                        'redirect' => url('login/twitter-callback'),
                    ]
            ];
            config($facebookConfig);
            config($googleConfig);
            config($twitterConfig);

            /**
             * Email from name
             */

            $emailConfig = [
                'mail.from' =>
                    [
                        'address' => get_option('email_address'),
                        'name' => get_option('site_name'),
                    ]
            ];
            config($emailConfig);

            view()->composer('*', function ($view) {

                $view->with(['testing_variable' => true]);
            });

        } catch (\Exception $e) {
            $GLOBALS['is_db_connected'] = false;

            //die("Could not connect to the database.  Please check your configuration.");
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
