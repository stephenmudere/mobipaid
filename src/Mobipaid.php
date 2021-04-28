<?php

namespace Stephenmudere\Mobipaid;

use \Stephenmudere\Mobipaid\Models\MobipaidWallet;
use \Stephenmudere\Mobipaid\Models\PaymentRequest;
use \Stephenmudere\Mobipaid\RestClient;
use \Stephenmudere\Mobipaid\ZaPayu;
use Log;
use Notification;
use App\Notifications\SendSOS;
use App\Admin\Library\PhoneNumber;

class Mobipaid
{
    //saves or updates exiting wallet details
	public function attach_wallet($wallet_details){
        try {
            $exising_wallet = MobipaidWallet::where('user_id', $wallet_details['user_id'])->first();
            if (isset($exising_wallet->id)) {
        	
            } else {
                $exising_wallet = new MobipaidWallet;
            }
            $exising_wallet->fill($wallet_details);
            return $exising_wallet->save();
        } catch (\Illuminate\Database\QueryException $ex) {
            //dd($ex->getMessage());
            // Note any method of class PDOException can be called on $ex.
            Log::channel('payment')->info($ex->getMessage());

            return ['error' => 'failed to associate waller with a driver'];
        }
    }


	public function payment_request($params){
        try {
            $valid_fields = [
            //'request_methods',
            'reference_number',
            'email',
            //'merchant_phone_number',
            'mobile_number',
            'amount',
            // 'currency',
            // 'fixed_amount',
            // 'tax_id',
            // 'template_id',
            // 'payment_type',
            // 'payment_methods',
            // 'moto_enabled',
         //    'shipping_enabled',
            // 'send_mms_invoice',
            // 'attach_invoice',
            // 'invoice_url',
            // 'send_mms_receipt',
            // 'attach_receipt',
            // 'receipt_file_type',
            // 'expiry_date',
            //'customer_id',
            'customer_salutation',
            'customer_first_name',
            'customer_last_name',
            //'send_confirmation',
            //'response_url',
            //'cancel_url',
            //'redirect_url',
            //'payment_frequency',
            //'payment_start_date'
        ];
            $data = [];
            foreach ($valid_fields as $key => $value) {
                if (! array_key_exists($value, $params) || isset($params[$value]) && $params[$value] == "") {
                    return ['code' => 403,'message' => 'Required key '.$value.' not found please refere to documantation here https://docs.mobipaid.com/'];
                }
            
            }
            $exising_wallet = MobipaidWallet::where('user_id', $params['user_id'])->first();

            if (! isset($exising_wallet->id)) {
                return ['error' => 'no mobipaid wallet id found for the driver'];
            }
            $restclient = new RestClient($exising_wallet);
            // take request methods from .env or the db as per driver perferences
        $data = array(
            "request_methods" => config('mobipaid.request_methods'),
            "reference_number" => $params['reference_number'],
            "email" => isset($params['email']) && $params['email'] != ""?$params['email']:"example@example.com",
            "merchant_phone_number" => isset($params['merchant_phone_number']) && $params['merchant_phone_number'] != ""?$params['merchant_phone_number']: null,
            "mobile_number" => $params['mobile_number'],
            "customer_id" => isset($params['customer_id']) && $params['customer_id'] == ""?$params['customer_id']: "",
            "customer_salutation" => $params['customer_salutation'],
            "customer_first_name" => $params['customer_first_name'],
            "customer_last_name" => $params['customer_last_name'],
            "redirect_url" => config('mobipaid.redirect_url'),
            "response_url" => config('mobipaid.response_url'),
            "cancel_url" => config('mobipaid.cancel_url'),
            "fixed_amount" => config('mobipaid.fixed_amount'),
            "currency" => config('mobipaid.currency'),
            "amount" => (float)$params['amount'],
            "tax_id" => "",
            "template_id" => "",
            "moto_enabled" => config('mobipaid.moto_enabled'),
            "shipping_enabled" => config('mobipaid.shipping_enabled'),
            "send_mms_invoice" => config('mobipaid.send_mms_invoice'),
            "attach_invoice" => config('mobipaid.attach_invoice'),
            "invoice_url" => config('mobipaid.invoice_url'),
            "attach_receipt" => config('mobipaid.attach_receipt'),
            "receipt_file_type" => config('mobipaid.receipt_file_type'),
            "payment_type" => config('mobipaid.payment_type'),
            "payment_methods" => config('mobipaid.payment_methods'),
            "expiry_date" => ""
        );
            //dd($data);
            $return_info = $restclient->payment_requests($data);
            if (isset($return_info['result']) && 'success' == $return_info['result']) {
                $prequest['user_id'] = $params['user_id'];
                $prequest['customer_id'] = $params['customer_id'];
                $prequest['amount'] = $params['amount'];
                $prequest['trip_id'] = $params['trip_id'];
                $prequest['reference_number'] = $params['reference_number'];
                $prequest['transaction_id'] = $return_info['transaction_id'];
                //dd($prequest);
                $payment_request = PaymentRequest::create($prequest);

        }
            return $return_info;

        } catch (\Illuminate\Database\QueryException $ex) {
            //dd($ex->getMessage());
            // Note any method of class PDOException can be called on $ex.
            Log::channel('payment')->info($ex->getMessage());

            return ['error' => 'failed to associate payment request with users'];
        }
    }

	public function verify_card($data){
        try {
            $required_fields = [
            'merchantReference',
            'description',
            //'currencyCode',
            'amountInCents',
            'merchantUserId',
            'email',
            'firstName',
            'lastName',
            'mobile',
            'regionalId',
            'countryCode',
            'nameOnCard',
            'cardNumber',
            'cardExpiry',
			'cvv'
         ];
            $senddata = [];
            //dd($data);
            foreach ($required_fields as $key => $value) {
                if (! array_key_exists($value, $data)) {
                    return ['code' => 403,'message' => 'Required key '.$value.' not found'];
                } else {
                    $senddata[$value] = $data[$value];
                }
            }
            $zapayu = new ZaPayu();
            $senddata['cardExpiry'] = str_replace("/", "20", $senddata['cardExpiry']);
            $senddata['cardExpiry'] = str_replace("2020", "20", $senddata['cardExpiry']);

            return $zapayu->verify_card($senddata);
        } catch (\Illuminate\Database\QueryException $ex) {
            //dd($ex->getMessage());
            // Note any method of class PDOException can be called on $ex.
            Log::channel('payment')->info($ex->getMessage());

            return ['error' => 'failed to associate payment with a rider'];
        }
    }

    public function card_result($data){
        try {
            $required_fields = [
            'PayUReference'
         ];
            $senddata = [];
            //dd($data);
            foreach ($required_fields as $key => $value) {
                if (! array_key_exists($value, $data)) {
                    return ['code' => 403,'message' => 'Required key '.$value.' not found'];
                } else {
                    $senddata[$value] = $data[$value];
                }
            }
            $zapayu = new ZaPayu();
       
            return $zapayu->card_result($senddata);
        } catch (\Illuminate\Database\QueryException $ex) {
            //dd($ex->getMessage());
            // Note any method of class PDOException can be called on $ex.
            Log::channel('payment')->info($ex->getMessage());

            return ['error' => 'failed to associate payment with a rider'];
        }
    }
}
