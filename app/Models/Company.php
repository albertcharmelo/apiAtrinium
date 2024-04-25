<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'document_type',
        'document_number',
        'status',
        'user_id'
    ];

    /**
     * Get the user that owns the Company
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function activityTypes()
    {
        return $this->belongsToMany(ActivityType::class);
    }


    public function scopeWithoutActivityTypes($query)
    {
        return $query->whereDoesntHave('activityTypes');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%$search%")
            ->orWhere('email', 'like', "%$search%")
            ->orWhere('phone', 'like', "%$search%")
            ->orWhere('address', 'like', "%$search%")
            ->orWhere('document_type', 'like', "%$search%")
            ->orWhere('document_number', 'like', "%$search%");
    }

    public function scopeOwners($query)
    {
        return $query->whereHas('user', function ($query) {
            $query->whereHas('roles', function ($query) {
                $query->where('name', 'owner');
            });
        });
    }
}
