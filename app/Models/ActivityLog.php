<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'username',
        'action',
        'subject',
        'details',
        'ip_address',
        'user_agent'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function log($action, $subject = null, $details = null)
    {
        $user = auth()->user();
        return self::create([
            'user_id' => $user?->id,
            'username' => $user?->username ?? 'Guest',
            'action' => $action,
            'subject' => $subject,
            'details' => is_array($details) ? json_encode($details) : $details,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
