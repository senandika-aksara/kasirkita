<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(DashboardService $dashboard): View
    {
        return view('manager.dashboard', [
            'summary' => $dashboard->summary(),
            'chart' => $dashboard->dailyChart(8),
            'bestSellers' => $dashboard->bestSellers(5),
            'activities' => $dashboard->recentActivity(6),
        ]);
    }
}
