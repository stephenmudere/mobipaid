<?php

namespace Stephenmudere\Mobipaid\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
 */
class MobipaidWallet extends Model
{

    protected $fillable = [
        'mode', 'live_key', 'test_key','user_id'
    ];
	

}


 ?>
