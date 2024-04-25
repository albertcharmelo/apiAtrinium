<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class RoleRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'current_role',
        'requested_role',
        'status',
    ];


    /**
     * Get the user associated with the role request.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Approve the role request.
     * Updates the status to 'approved' and assigns the requested role to the user.
     *
     * @return void
     */
    public function approve()
    {
        $this->status = 'approved';
        $this->save();
        $this->user->assignRole(Role::findByName($this->requested_role, 'api'));
    }

    /**
     * Reject the role request.
     * Updates the status to 'rejected'.
     *
     * @return void
     */
    public function reject()
    {
        $this->status = 'rejected';
        $this->save();
    }

    /**
     * Check if the role request is pending.
     *
     * @return bool
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the role request is approved.
     *
     * @return bool
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the role request is rejected.
     *
     * @return bool
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }
}
