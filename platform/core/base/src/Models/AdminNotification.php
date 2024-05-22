<?php

namespace Botble\Base\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Query\Builder;

class AdminNotification extends BaseModel
{
    use MassPrunable;

    protected $table = 'admin_notifications';

    protected $fillable = [
        'title',
        'action_label',
        'action_url',
        'description',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function markAsRead(): void
    {
        $this->update([
            'read_at' => Carbon::now(),
        ]);
    }

    public function prunable(): Builder
    {
        return $this->whereDate('created_at', '>', Carbon::now()->subDays(30)->toDateString());
    }
}
