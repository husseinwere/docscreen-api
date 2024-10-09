<?php

namespace App\Models\Employee;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubmissionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'document_title',
        'status'
    ];
}
