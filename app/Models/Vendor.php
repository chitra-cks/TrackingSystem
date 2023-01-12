<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Vendor extends Model
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
        'firstname',
        'lastname',
        'mobile',
        'price',
        'status',
        'ip_address',
        'job_id'
    ];


    public $searchCoulmns = ['fullname','job','mobile','price','status'];

    public function Job()
    {
        return $this->belongsTo('App\Models\Job');
    }

    public function Job_history()
    {
        return $this->hasMany('App\Models\Job_history', 'vendor_id', 'id');
    }

    public function getCreatedAtAttribute()
    {
        return Carbon::parse($this->attributes['created_at'])->format('d-M,Y');
    }

    public function getUpdatedAtAttribute()
    {
        return Carbon::parse($this->attributes['updated_at'])->format('d-M,Y');
    }

    public function getFirstnameAttribute()
    {
        return ucwords($this->attributes['firstname']);
    }

    public function getLastnameAttribute()
    {
        return ucwords($this->attributes['lastname']);
    }

    public function format()
    {
        return [
          'id' => $this->id,
          'job_id'=>$this->job_id,
          'fullname' => $this->firstname.' '.$this->lastname,
          'mobile' => $this->mobile,
          'status' => $this->status,
          'job' => isset($this->job->title) ? $this->job->title : null,
          'price' => $this->price,
          'ip_address' => $this->ip_address,
          'created_at'=>$this->created_at,
          'updated_at'=>$this->updated_at,
          'deleted_at'=> $this->deleted_at,
          'created_by'=> $this->created_by,
          'updated_by'=> $this->updated_by,
        ];
    }
}
