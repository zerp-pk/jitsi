<?php

namespace Zerp\Jitsi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJitsiMeetingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|max:100',
            'description' => 'nullable',
            'start_time' => 'required',
            'duration' => 'required|integer|min:15|max:480',
            'status' => 'required',
            'participants' => 'nullable|array',
            'host_id' => 'nullable|exists:users,id'
        ];
    }
}