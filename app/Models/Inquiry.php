<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    public const TYPES = [
        'contact' => 'Contact Message',
        'quote' => 'Project Quote',
        'order' => 'Order Request',
        'domain' => 'Domain Order',
        'hosting' => 'Hosting Order',
    ];

    public const STATUSES = [
        'new' => 'New',
        'contacted' => 'Contacted',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    protected $guarded = ['id'];
}
