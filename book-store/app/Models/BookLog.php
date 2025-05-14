<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookLog extends Model
{
    protected $fillable = ['book_id', 'action', 'details'];
}
