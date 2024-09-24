<?php

namespace App\Models\Upload;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocUpload extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'document_url'
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}
