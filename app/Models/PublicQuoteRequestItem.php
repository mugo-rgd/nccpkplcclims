<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicQuoteRequestItem extends Model
{
    protected $table = 'public_quote_request_items';

    protected $fillable = [
        'public_quote_request_id',
        'equipment_type_id',
        'service_type_id',
        'quantity',
    ];

    public function quoteRequest()
    {
        return $this->belongsTo(PublicQuoteRequest::class, 'public_quote_request_id');
    }

 // app/Models/PublicQuoteRequestItem.php
public function equipmentType()
{
    return $this->belongsTo(\App\Models\KplcEquipmentType::class, 'equipment_type_id');
}

   // app/Models/PublicQuoteRequestItem.php
public function serviceType()
{
    return $this->belongsTo(\App\Models\KplcServiceType::class, 'service_type_id');
}

public function calibrationItem()
{
    return $this->hasOne(CalibrationItem::class, 'quote_request_item_id');
}
}
