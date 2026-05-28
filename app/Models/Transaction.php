<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $guarded = [];

    public function book() {
        return $this->belongsTo(PersonalBook::class, 'book_id');
    }

    public function borrower() {
        return $this->belongsTo(User::class, 'borrower_id');
    }

    public function owner() {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
