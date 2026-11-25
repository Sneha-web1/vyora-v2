<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_name',
        'company_code',
        'company_logo',
        'contact_no',
        'landline_no',
        'email',
        'gst_no',
        'pan_no',
        'tan_no',
        'cin_no',
        'iec',
        'duns_no',
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
