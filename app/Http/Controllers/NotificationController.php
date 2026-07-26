<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notificationService)
    {
    }

    public function index(): View
    {
        return view('notifications.index', $this->notificationService->getGroupedAlerts());
    }
}
