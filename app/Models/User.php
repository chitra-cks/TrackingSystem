<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Config;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use HasApiTokens;
    use SoftDeletes;

    protected $casts = [
        'id' => 'string',
        'email_verified_at' => 'datetime'
    ];

    protected $primaryKey = "id";
    protected $append = ['fullname'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'password',
        'mobile',
        'gender',
        'address',
        'status',
        'role_id',
        'ip_address',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public $searchCoulmns = ['fullname','email','mobile','gender','role','status'];

    public function Role()
    {
        return $this->belongsTo('App\Models\Role');
    }
    public function getCreatedAtAttribute()
    {
        return Carbon::parse($this->attributes['created_at'])->format('d-M,Y');
    }

    public function getUpdatedAtAttribute()
    {
        return Carbon::parse($this->attributes['updated_at'])->format('d-M,Y');
    }

    public function getFullnameAttribute()
    {
        return "{$this->firstname} {$this->lastname}";
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
        $data['id']         =   $this->id;
        $data['role_id']    =   $this->role_id;
        $data['fullname']  =   $this->fullname;
        $data['address']    =   $this->address;
        $data['mobile']    =   $this->mobile;
        if (isset($this->gender) && $this->gender != '' && trim($this->gender) == "M") {
            $data['gender']     =   'Male';
        } elseif (isset($this->gender) && $this->gender != '' && trim($this->gender) == "F") {
            $data['gender']     =   'Female';
        } else {
            $data['gender']     =   $this->gender;
        }
        $data['email']      =   $this->email;
        $data['status']     = $this->status;
        $data['created_at']   = $this->created_at;
        $data['updated_at']     = $this->updated_at;
        $data['ip_address'] = $this->ip_address;
        $data['created_by'] =  $this->created_by;
        $data['updated_by'] =  $this->updated_by;
        $data['role'] =  isset($this->role->name) ? $this->role->name : null;

        return $data;
    }
}
