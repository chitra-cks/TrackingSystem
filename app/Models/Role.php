<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Role extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $casts = [
      'id' => 'string',
      'permission'=>'array'
   ];
    protected $keyType = 'string';
    protected $primaryKey = "id";

    /**
      * The attributes that are mass assignable.
      *
      * @var array
    */
    protected $fillable = [
     'name',
     'permission',
     'ip_address'

   ];
    public $searchCoulmns = ['title'];
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
          'name'=>$this->name,
          'permission' => $this->permission,
          'ip_address' => $this->mobile,
          'ip_address' => $this->ip_address,
          'created_at'=>$this->created_at,
          'updated_at'=>$this->updated_at,
          'deleted_at'=> $this->deleted_at,
          'created_by'=> $this->created_by,
          'updated_by'=> $this->updated_by,
        ];
    }
}
