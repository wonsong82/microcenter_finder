<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OpenboxItem extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'product_price' => 'float',
        'product_original_price' => 'float',
        'openbox_price' => 'float'
    ];



}
