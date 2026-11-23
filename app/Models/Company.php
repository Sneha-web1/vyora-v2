<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'company_name',
        'company_code',
        'company_logo',
        'contact_no',
        'landline_no',
        'email',
        'gst_no',
        'pan_no',
        'cin_no',
        'iec',
        'website',
        'address',
        'country',
        'state',
        'city',
        'pincode',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function countryDetail()
    {
        return $this->belongsTo(Country::class, 'country', 'id');
    }

    public function stateDetail()
    {
        return $this->belongsTo(CountryState::class, 'state', 'id');
    }

    public function cityDetail()
    {
        return $this->belongsTo(StateCity::class, 'city', 'id');
    }
}
