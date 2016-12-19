<?php 

namespace Yaro\LogEnvelope\Models;

use Illuminate\Database\Eloquent\Model;


class ExceptionModel extends Model
{
    
    protected $fillable = [
        'host', 
        'method', 
        'fullUrl', 
        'exception', 
        'error', 
        'line', 
        'file', 
        'class', 
        'storage', 
        'exegutor', 
        'file_lines',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'exceptions';
    
}
