<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestPlat extends Model
{
    protected $table = 'request_plat';
    protected $guarded = ['id'];

    public function quotation() {
        return $this->belongsTo(Quotation::class);
    }

    public function requester() {
        return $this->belongsTo(User::class, 'request_user_id');
    }

    public function approver() {
        return $this->belongsTo(User::class, 'approve_user_id');
    }
}
