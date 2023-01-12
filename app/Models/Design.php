<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Design extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $casts = [
        'id' => 'string'
    ];

    protected $primaryKey = "id";
    protected $keyType = 'string';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'design_no',
        'status',
        'ip_address'
    ];
    public $searchCoulmns = ['title','design_no','status'];
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
          'design_no'=>$this->design_no,
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
