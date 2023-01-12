<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Job_history extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $casts = [
        'id' => 'string'
    ];

    protected $keyType = 'string';
    protected $primaryKey = "id";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'assign_vendor_id',
        'source_vendor_id',
        'source_place',
        'job_id',
        'design_id',
        'quantity',
        'voucher_number',
        'voucher_bill',
        'voucher_bill_mimetype',
        'status',
        'pickup_date',
        'job_start_date',
        'job_end_date',
        'ip_address'

    ];
    public $searchCoulmns = ['source_vendor','assign_vendor','job','design','quantity','pickup_date','job_start_date'];

    public $reportserchCoulmns = ['source_vendor','assign_vendor','job','design','quantity','voucher_number','pickup_date','job_start_date'];

    public function getPickupDateAttribute()
    {
        return Carbon::parse($this->attributes['pickup_date'])->format('d-M,Y');
    }

    public function getJobStartDateAttribute()
    {
        return Carbon::parse($this->attributes['job_start_date'])->format('d-M,Y');
    }

    public function getJobEndDateAttribute()
    {
        return Carbon::parse($this->attributes['job_end_date'])->format('d-M,Y');
    }

    public function getCreatedAtAttribute()
    {
        return Carbon::parse($this->attributes['created_at'])->format('d-M,Y');
    }

    public function getUpdatedAtAttribute()
    {
        return Carbon::parse($this->attributes['updated_at'])->format('d-M,Y');
    }


    public function Assignvendor()
    {
        return $this->belongsTo('App\Models\Vendor', 'assign_vendor_id')->select('id', 'firstname', 'lastname', 'job_id')->with('job');
    }
    public function Sourcevendor()
    {
        return $this->belongsTo('App\Models\Vendor', 'source_vendor_id')->select('id', 'firstname', 'lastname');
    }
    public function Job()
    {
        return $this->belongsTo('App\Models\Job')->select('id', 'title');
    }
    public function Design()
    {
        return $this->belongsTo('App\Models\Design')->select('id', 'title');
    }



    public function format()
    {
        return [
          'id' => $this->id,
          'assign_vendor_id'=>$this->assign_vendor_id,
          'source_vendor_id'=>$this->source_vendor_id,
          'source_place'=>$this->source_place,
          'job_id' => $this->job_id,
          'design_id' => $this->design_id,
          'quantity'=>$this->quantity,
          'voucher_number' => $this->voucher_number,
          'voucher_bill' => $this->voucher_bill,
          'voucher_bill_mimetype'=>$this->voucher_bill_mimetype,
          'status' => $this->status,
          'assign_vendor'=>isset($this->assignvendor) && $this->assignvendor != null ? $this->assignvendor->firstname.' '.$this->assignvendor->lastname : null,
          'source_vendor'=>isset($this->sourcevendor) && $this->sourcevendor != null ? $this->sourcevendor->firstname.' '.$this->sourcevendor->lastname : null,
          'job'=> isset($this->job->title) ? $this->job->title : null,
          'design'=>isset($this->design->title) ? $this->design->title : null,
          'pickup_date' => $this->pickup_date,
          'job_start_date'=>$this->job_start_date,
          'job_end_date'=>$this->job_end_date,
          'ip_address' => $this->ip_address,
          'created_at'=>$this->created_at,
          'updated_at'=>$this->updated_at,
          'deleted_at'=> $this->deleted_at,
          'created_by'=> $this->created_by,
          'updated_by'=> $this->updated_by,
        ];
    }

    public function reportformate()
    {
        return [
                    'id'            => $this->id,
                    'assign_vendor'=>isset($this->assignvendor) && $this->assignvendor != null ? $this->assignvendor->firstname.' '.$this->assignvendor->lastname : null,
                    'source_vendor'=>isset($this->sourcevendor) && $this->sourcevendor != null ? $this->sourcevendor->firstname.' '.$this->sourcevendor->lastname : null,
                    'source_place'=>$this->source_place,
                    "quantity"=>$this->quantity,
                    'job' => isset($this->job->title) ? $this->job->title : null,
                    'design' => isset($this->design->title) ? $this->design->title : null,
                    'status'=>isset($this->status) ? $this->status : null,
                    'voucher_number'=>isset($this->voucher_number) ? $this->voucher_number : null,
                    'pickup_date'=> isset($this->pickup_date) ? $this->pickup_date : null,
                    'job_start_date'=> isset($this->job_start_date) ? $this->job_start_date : null,
                ];
    }
}
