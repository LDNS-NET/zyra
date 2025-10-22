<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tenants\NetworkUser;

class TenantPayment extends Model
{
    protected $table = "tenant_payments";

    protected $fillable = [
        "user_id",
        "phone",
        "package_id",
        "receipt_number",
        "amount",
        "status",
        "intasend_reference",
        "intasend_checkout_id",
        "transaction_id",
        "response",
        "paid_at",
        "checked",
        "created_by",
        "disbursement_type",
    ];
  protected $casts = [
        'checked' => 'boolean',
        'paid_at' => 'datetime',
        'response' => 'array',
        'amount' => 'decimal:2',
    ];
    public function user()
    {
        return $this->belongsTo(NetworkUser::class, 'user_id');
    }


    protected static function booted()
    {
        static::addGlobalScope('created_by', function ($query) {
            if (auth()->check()) {
                $query->where('created_by', auth()->id());
            }
        });
        static::creating(function ($model) {
            if (auth()->check() && empty($model->created_by)) {
                $model->created_by = auth()->id();
            }
        });
    }
}
