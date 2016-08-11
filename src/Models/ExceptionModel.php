<?php 

namespace Yaro\LogEnvelope\Models;

use Illuminate\Database\Eloquent\Model;


class ExceptionModel extends Model
{
    protected $guarded = [];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'exceptions';
    
}
