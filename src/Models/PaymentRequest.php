<?php 
namespace Stephenmudere\Mobipaid\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 
 */
class PaymentRequest extends Model
{

	protected $fillable = [
        'user_id', 'customer_id', 'amount','trip_id','webhook_status','reference_number'
    ];
	

}


 ?>