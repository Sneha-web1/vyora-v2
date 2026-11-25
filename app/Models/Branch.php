<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_name',
        'branch_code',
        'branch_owner_name',
        'contact_number',
        'email',
        'pincode',
        'gst_number',
        'country',
        'state',
        'city',
        'address_1',
        'address_2',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function countryData()
    {
        return $this->belongsTo(Country::class, 'country', 'id');
    }

    public function stateData()
    {
        return $this->belongsTo(CountryState::class, 'state', 'id');
    }

    public function cityData()
    {
        return $this->belongsTo(StateCity::class, 'city', 'id');
    }
}
