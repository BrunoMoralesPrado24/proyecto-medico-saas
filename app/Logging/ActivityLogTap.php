<?php

namespace App\Logging;

use Spatie\Activitylog\Contracts\Activity;
use Illuminate\Support\Facades\Request;

class ActivityLogTap
{
    /**
     * Intercepta la creación del log de auditoría.
     */
    public function __invoke(Activity $activity): void
    {
        $activity->properties = $activity->properties->merge([
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
