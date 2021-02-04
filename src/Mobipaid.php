<?php

namespace Stephenmudere\Mobipaid;

use \Stephenmudere\Mobipaid\Models\MobipaidWallet;

class Mobipaid
{
    //saves or updates exiting wallet details
    public function attach_wallet($wallet_details)
    {
        $exising_wallet = MobipaidWallet::where('user_id', $wallet_details['user_id'])->first();
        if (isset($exising_wallet->id)) {
        } else {
            $exising_wallet = new MobipaidWallet;
        }
        $exising_wallet->fill($wallet_details);

        return $exising_wallet->save();
    }

    public function payment_request($params)
    {
        $valid_fields = [
            'request_methods',
            'reference_number',
            'email',
            'merchant_phone_number',
            'mobile_number',
            'amount',
            'currency',
            'fixed_amount',
            'tax_id',
            'template_id',
            'payment_type',
            'payment_methods',
            'moto_enabled','shipping_enabled',
            'send_mms_invoice',
            'attach_invoice',
            'invoice_url',
            'send_mms_receipt',
            'attach_receipt',
            'receipt_file_type',
            'expiry_date',
            'customer_id',
            'customer_salutation',
            'customer_first_name',
            'customer_last_name',
            'send_confirmation',
            'response_url',
            'cancel_url',
            'redirect_url',
            'payment_frequency',
            'payment_start_date',
        ];
        $exising_wallet = MobipaidWallet::where('user_id', $params['user_id'])->first();

        if (! isset($exising_wallet->id)) {
            return ['code' => 403,'message' => 'no mobipaid walled found for the driver'];
        }
        $restclient = new RestClient($exising_wallet);
        unset($params['user_id']);
        //unset($params['user_id']);
        unset($params['trip_id']);
        $data = [];
        foreach ($params as $key => $value) {
            if (! in_array($key, $valid_fields)) {
                return ['code' => 403,'message' => 'invalid field supplied '.$key.' please refere to documantation https://docs.mobipaid.com/'];
            } else {
                $data[$key] = $value;
            }
        }

        return $restclient->payment_requests($data);
    }
}
