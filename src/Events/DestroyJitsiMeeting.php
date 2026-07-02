<?php

namespace Zerp\Jitsi\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Zerp\Jitsi\Models\JitsiMeeting;

class DestroyJitsiMeeting
{
    use Dispatchable;

    public function __construct(
        public JitsiMeeting $meeting,
    ) {}
}