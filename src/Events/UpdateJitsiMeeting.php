<?php

namespace Zerp\Jitsi\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Zerp\Jitsi\Models\JitsiMeeting;

class UpdateJitsiMeeting
{
    use Dispatchable;

    public function __construct(
        public Request $request,
        public JitsiMeeting $meeting
    ) {}
}