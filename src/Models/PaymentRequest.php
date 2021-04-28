<?php
namespace Stephenmudere\Mobipaid\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
 */
class PaymentRequest extends Model
{

    protected $fillable = [
        'user_id', 'customer_id', 'amount','trip_id','webhook_status','reference_number','transaction_id','token','payment_id','currency','currency_symbol','result_code','result_description'
    ];
	

}


 ?>
