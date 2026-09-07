<?php

namespace Zerp\Jitsi\Http\Controllers;

use Zerp\Jitsi\Models\JitsiMeeting;
use Zerp\Jitsi\Http\Requests\StoreJitsiMeetingRequest;
use Zerp\Jitsi\Http\Requests\UpdateJitsiMeetingRequest;
use Zerp\Jitsi\Services\JitsiService;
use Zerp\Jitsi\Events\CreateJitsiMeeting;
use Zerp\Jitsi\Events\UpdateJitsiMeeting;
use Zerp\Jitsi\Events\DestroyJitsiMeeting;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\User;

class JitsiController extends Controller
{
    public function index()
    {
        if(Auth::user()->can('manage-jitsi-meetings')){
            $jitsimeetings = JitsiMeeting::query()
                ->with(['host'])
                ->where(function($q) {
                    if(Auth::user()->can('manage-any-jitsi-meetings')) {
                        $q->where('created_by', creatorId());
                    } elseif(Auth::user()->can('manage-own-jitsi-meetings')) {
                        $q->where(function($query) {
                            $query->where('creator_id', Auth::id())
                                  ->orWhere('host_id', Auth::id())
                                  ->orWhereJsonContains('participants', (string)Auth::id());
                        });
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })
                ->when(request('title'), function($q) {
                    $q->where(function($query) {
                    $query->where('title', 'like', '%' . request('title') . '%');
                    $query->orWhere('room_name', 'like', '%' . request('title') . '%');
                    });
                })
                ->when(request('status') !== null && request('status') !== '', fn($q) => $q->where('status', request('status')))
                ->when(request('date_range'), function($q) {
                    $dateRange = request('date_range');
                    if (strpos($dateRange, ' - ') !== false) {
                        [$startDate, $endDate] = explode(' - ', $dateRange);
                        $q->whereDate('start_time', '>=', trim($startDate))
                          ->whereDate('start_time', '<=', trim($endDate));
                    } else {
                        $q->whereDate('start_time', $dateRange);
                    }
                })
                ->when(request('sort'), fn($q) => $q->sortSafe(request('sort'), request('direction'), 'created_at', 'desc'), fn($q) => $q->latest())
                ->paginate(request('per_page', 10))
                ->withQueryString();



            return Inertia::render('Jitsi/JitsiMeetings/Index', [
                'jitsimeetings' => $jitsimeetings,
                'users' => User::where('created_by', creatorId())->select('id', 'name', 'avatar')->get(),
            ]);
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function store(StoreJitsiMeetingRequest $request)
    {
        if(Auth::user()->can('create-jitsi-meetings')){
            if (company_setting('jitsi_enabled') !== 'on') {
                return redirect()->back()->with('error', __('Jitsi Meet integration is disabled'));
            }

            $validated = $request->validated();

            try {
                // Generate the Jitsi room/meeting link
                $jitsiService = new JitsiService();
                $jitsiResponse = $jitsiService->createMeeting($validated);

                $jitsimeeting = new JitsiMeeting();
                $jitsimeeting->title = $validated['title'];
                $jitsimeeting->description = $validated['description'];
                $jitsimeeting->room_name = $jitsiResponse['id'];
                $jitsimeeting->start_url = $jitsiResponse['start_url'] ?? null;
                $jitsimeeting->join_url = $jitsiResponse['join_url'] ?? null;
                $jitsimeeting->start_time = $validated['start_time'];
                $jitsimeeting->duration = $validated['duration'];
                $jitsimeeting->status = $validated['status'];
                $jitsimeeting->participants = $validated['participants'];
                $jitsimeeting->host_id = $validated['host_id'];
                $jitsimeeting->creator_id = Auth::id();
                $jitsimeeting->created_by = creatorId();
                $jitsimeeting->save();

                // Dispatch event for packages to handle their fields
                CreateJitsiMeeting::dispatch($request, $jitsimeeting);

                return redirect()->route('jitsi.jitsi-meetings.index')->with('success', __('The Jitsi meeting has been created successfully.'));
            } catch (\Exception $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }
        }
        else{
            return redirect()->route('jitsi.jitsi-meetings.index')->with('error', __('Permission denied'));
        }
    }

    public function update(UpdateJitsiMeetingRequest $request, JitsiMeeting $jitsimeeting)
    {
        if(Auth::user()->can('edit-jitsi-meetings') && $jitsimeeting->status === 'Scheduled'){
            if (company_setting('jitsi_enabled') !== 'on') {
                return redirect()->back()->with('error', __('Jitsi Meet integration is disabled'));
            }

            $validated = $request->validated();

            try {
                // Refresh meeting link if room_name exists
                if ($jitsimeeting->room_name) {
                    $jitsiService = new JitsiService();
                    $jitsiResponse = $jitsiService->updateMeeting($jitsimeeting->room_name, $validated);

                    if (isset($jitsiResponse['start_url'])) {
                        $jitsimeeting->start_url = $jitsiResponse['start_url'];
                    }
                    if (isset($jitsiResponse['join_url'])) {
                        $jitsimeeting->join_url = $jitsiResponse['join_url'];
                    }
                }

                $jitsimeeting->title = $validated['title'];
                $jitsimeeting->description = $validated['description'];
                $jitsimeeting->start_time = $validated['start_time'];
                $jitsimeeting->duration = $validated['duration'];
                $jitsimeeting->participants = $validated['participants'];
                $jitsimeeting->host_id = $validated['host_id'];
                $jitsimeeting->save();

                // Dispatch event for packages to handle their fields
                UpdateJitsiMeeting::dispatch($request, $jitsimeeting);

                return redirect()->back()->with('success', __('The Jitsi meeting details are updated successfully.'));
            } catch (\Exception $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }
        }
        else{
            return redirect()->route('jitsi.jitsi-meetings.index')->with('error', __('Permission denied'));
        }
    }

    public function destroy(JitsiMeeting $jitsimeeting)
    {
        if(Auth::user()->can('delete-jitsi-meetings')){
            try {

                // No server-side resource to delete for a Jitsi room, but keep
                // the call for symmetry / future self-hosted management APIs.
                if ($jitsimeeting->room_name && company_setting('jitsi_enabled') === 'on') {
                    $jitsiService = new JitsiService();
                    $jitsiService->deleteMeeting($jitsimeeting->room_name);
                }


                // Dispatch event for packages to handle their fields
                DestroyJitsiMeeting::dispatch($jitsimeeting);

                $jitsimeeting->delete();
                return redirect()->back()->with('success', __('The Jitsi meeting has been deleted.'));
            } catch (\Exception $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }
        }
        else{
            return redirect()->route('jitsi.jitsi-meetings.index')->with('error', __('Permission denied'));
        }
    }



    public function updateStatus(JitsiMeeting $jitsimeeting)
    {
        if(Auth::user()->can('update-jitsi-status')){
            $status = request('status');

            if (!in_array($status, ['Scheduled', 'Started', 'Ended', 'Cancelled'])) {
                return redirect()->back()->with('error', __('Invalid status'));
            }

            $jitsimeeting->status = $status;
            $jitsimeeting->save();

            return redirect()->back()->with('success', __('Status updated successfully'));
        }
        else{
            return redirect()->back()->with('error', __('Permission denied'));
        }
    }


}
