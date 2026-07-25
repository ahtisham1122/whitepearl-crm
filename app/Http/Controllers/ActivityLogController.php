<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Client;
use App\Models\InstallmentPlan;
use App\Models\Payment;
use App\Models\Plot;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $activities = Activity::with(['causer', 'subject'])
            ->when($request->filled('user_id'), function ($query) use ($request) {
                $query->where('causer_id', $request->integer('user_id'));
            })
            ->when($request->filled('description'), function ($query) use ($request) {
                $query->where('description', $request->string('description'));
            })
            ->when($request->filled('from'), function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->date('from'));
            })
            ->when($request->filled('to'), function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->date('to'));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $users = User::orderBy('name')->get();
        $descriptions = Activity::query()->distinct()->orderBy('description')->pluck('description');

        return view('activity-logs.index', compact('activities', 'users', 'descriptions'));
    }

    /**
     * A short, human-readable label for whatever record an activity was
     * about -- falling back to the values captured at the time of the
     * event when the record itself has since been deleted (the subject
     * relation returns null once the row is gone).
     */
    public static function describeSubject(Activity $activity): string
    {
        if ($activity->subject) {
            return match (true) {
                $activity->subject instanceof Client => $activity->subject->full_name,
                $activity->subject instanceof Plot => 'Plot '.$activity->subject->plot_number,
                $activity->subject instanceof Booking => $activity->subject->booking_number,
                $activity->subject instanceof Payment => $activity->subject->receipt_number,
                $activity->subject instanceof InstallmentPlan => 'Plan #'.$activity->subject->id,
                default => class_basename($activity->subject),
            };
        }

        $old = $activity->properties['old'] ?? $activity->properties['attributes'] ?? [];

        $label = $old['full_name'] ?? $old['plot_number'] ?? $old['booking_number'] ?? $old['receipt_number'] ?? null;

        if ($label) {
            return $label.' (deleted)';
        }

        return class_basename($activity->subject_type ?? 'record').' #'.$activity->subject_id.' (deleted)';
    }
}
