<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShoppingItem extends Model
{
    protected $fillable = ['name', 'quantity', 'unit', 'category', 'notes'];
}
