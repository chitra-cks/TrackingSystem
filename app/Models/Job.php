<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Job extends Model
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
     'title',
     'status',
     'ip_address'

    ];
    public $searchCoulmns = ['title','status'];
    public function Vendor()
    {
        return $this->hasMany('App\Models\Vendor', 'job_id', 'id');
    }
    public function Job_history()
    {
        return $this->hasMany('App\Models\Job_history', 'job_id', 'id');
    }

    public function getCreatedAtAttribute()
    {
        return Carbon::parse($this->attributes['created_at'])->format('d-M,Y');
    }

    public function getUpdatedAtAttribute()
    {
        return Carbon::parse($this->attributes['updated_at'])->format('d-M,Y');
    }
    public function getTitleAttribute()
    {
        return ucwords($this->attributes['title']);
    }
    public function format()
    {
        return [
          'id' => $this->id,
          'title'=>$this->title,
          'status' => $this->status,
          'ip_address' => $this->ip_address,
          'created_at'=>$this->created_at,
          'updated_at'=>$this->updated_at,
          'deleted_at'=> $this->deleted_at,
          'created_by'=> $this->created_by,
          'updated_by'=> $this->updated_by,
        ];
    }
}
