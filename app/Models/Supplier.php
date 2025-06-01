<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'login_url',
        'invoices_url',
        'username',
        'password',
        'script_identifier',
        'active',
    ];

    /**
     * Get the invoices for the supplier.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
