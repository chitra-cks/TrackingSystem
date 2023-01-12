<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Raw_material extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $casts = [
        'id' => 'string'
    ];

    protected $primaryKey = "id";
    protected $table = 'raw_material';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'quantity',
        'source',
        'price',
        'voucher_number',
        'voucher',
        'voucher_mimetype',
        'LR_number',
        'LR',
        'LR_mimetype',
        'status',
        'pickup_date',
        'ip_address'

    ];
    public $searchCoulmns = ['quantity','source','price','voucher_number','voucher','voucher_mimetype','LR_number','LR','LR_mimetype','status','pickup_date','ip_address','created_at','updated_at'];
    public function getPickupDateAttribute()
    {
        return Carbon::parse($this->attributes['pickup_date'])->format('d-M,Y');
    }

    public function getCreatedAtAttribute()
    {
        return Carbon::parse($this->attributes['created_at'])->format('d-M,Y');
    }

    public function getUpdatedAtAttribute()
    {
        return Carbon::parse($this->attributes['updated_at'])->format('d-M,Y');
    }

    public function format()
    {
        return [
          'id' => $this->id,
          'quantity'=>$this->quantity,
          'source' => $this->source,
          'price' => $this->price,
          'voucher_number'=>$this->voucher_number,
          'voucher' => $this->voucher,
          'voucher_mimetype' => $this->voucher_mimetype,
          'LR_number'=>$this->LR_number,
          'LR' => $this->LR,
          'LR_mimetype' => $this->LR_mimetype,
          'status'=>$this->status,
          'pickup_date' => $this->pickup_date,
          'ip_address' => $this->ip_address,
          'created_at'=>$this->created_at,
          'updated_at'=>$this->updated_at,
          'deleted_at'=> $this->deleted_at,
          'created_by'=> $this->created_by,
          'updated_by'=> $this->updated_by,
        ];
    }
}
