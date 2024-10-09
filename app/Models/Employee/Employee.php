<?php

namespace App\Models\Employee;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'employer_id',
        'first_name',
        'last_name',
        'dob',
        'email',
        'is_internal',
        'status'
    ];

    protected $casts = [
        'is_internal' => 'boolean',
    ];
}
