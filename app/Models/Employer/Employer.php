<?php

namespace App\Models\Employer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'function',
        'billing_email',
        'kvk_number',
        'organization',
        'address',
        'location',
        'postcode',
        'package',
        'status'
    ];
}
