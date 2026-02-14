<?php

namespace App\Http\Controllers\Admin;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->from;
        $to = $request->to;

        // Build base query with date filters
        $baseQuery = Project::query();

        if ($from) {
            $baseQuery->where(function ($q) use ($from) {
                $q->whereDate('booking_paid_at', '>=', $from)
                  ->orWhereDate('mid_paid_at', '>=', $from)
                  ->orWhereDate('final_paid_at', '>=', $from);
            });
        }

        if ($to) {
            $baseQuery->where(function ($q) use ($to) {
                $q->whereDate('booking_paid_at', '<=', $to)
                  ->orWhereDate('mid_paid_at', '<=', $to)
                  ->orWhereDate('final_paid_at', '<=', $to);
            });
        }

        // Total Revenue - Sum of all PAID amounts
        $totalRevenue = DB::table(function ($query) use ($baseQuery) {
            $query->fromSub($baseQuery, 'projects')
                ->selectRaw('
                    COALESCE(SUM(CASE WHEN booking_status = ? THEN booking_amount ELSE 0 END), 0) +
                    COALESCE(SUM(CASE WHEN mid_status = ? THEN mid_amount ELSE 0 END), 0) +
                    COALESCE(SUM(CASE WHEN final_status = ? THEN final_amount ELSE 0 END), 0) as total
                ', [Project::PAYMENT_PAID, Project::PAYMENT_PAID, Project::PAYMENT_PAID]);
        }, 't')->value('total') ?? 0;

        // Cash Collected - Total Revenue with CASH payment method
        $cashCollected = DB::table(function ($query) use ($baseQuery) {
            $query->fromSub($baseQuery, 'projects')
                ->where('payment_method', Project::PAYMENT_METHOD_CASH)
                ->selectRaw('
                    COALESCE(SUM(CASE WHEN booking_status = ? THEN booking_amount ELSE 0 END), 0) +
                    COALESCE(SUM(CASE WHEN mid_status = ? THEN mid_amount ELSE 0 END), 0) +
                    COALESCE(SUM(CASE WHEN final_status = ? THEN final_amount ELSE 0 END), 0) as total
                ', [Project::PAYMENT_PAID, Project::PAYMENT_PAID, Project::PAYMENT_PAID]);
        }, 't')->value('total') ?? 0;

        // Online Collected - Total Revenue with ONLINE payment method
        $onlineCollected = DB::table(function ($query) use ($baseQuery) {
            $query->fromSub($baseQuery, 'projects')
                ->where('payment_method', Project::PAYMENT_METHOD_ONLINE)
                ->selectRaw('
                    COALESCE(SUM(CASE WHEN booking_status = ? THEN booking_amount ELSE 0 END), 0) +
                    COALESCE(SUM(CASE WHEN mid_status = ? THEN mid_amount ELSE 0 END), 0) +
                    COALESCE(SUM(CASE WHEN final_status = ? THEN final_amount ELSE 0 END), 0) as total
                ', [Project::PAYMENT_PAID, Project::PAYMENT_PAID, Project::PAYMENT_PAID]);
        }, 't')->value('total') ?? 0;

        // Pending Balance - Sum of all PENDING amounts
        $pendingBalance = DB::table(function ($query) use ($baseQuery) {
            $query->fromSub($baseQuery, 'projects')
                ->selectRaw('
                    COALESCE(SUM(CASE WHEN booking_status = ? THEN booking_amount ELSE 0 END), 0) +
                    COALESCE(SUM(CASE WHEN mid_status = ? THEN mid_amount ELSE 0 END), 0) +
                    COALESCE(SUM(CASE WHEN final_status = ? THEN final_amount ELSE 0 END), 0) as total
                ', [Project::PAYMENT_PENDING, Project::PAYMENT_PENDING, Project::PAYMENT_PENDING]);
        }, 't')->value('total') ?? 0;

        // Supervisor-wise collection (only if supervisor_id column exists and is not null)
        $supervisorCollection = collect([]);

        if (\Schema::hasColumn('projects', 'supervisor_id')) {
            $supervisorCollection = Project::whereNotNull('supervisor_id')
                ->when($from, function ($query) use ($from) {
                    $query->where(function ($q) use ($from) {
                        $q->whereDate('booking_paid_at', '>=', $from)
                          ->orWhereDate('mid_paid_at', '>=', $from)
                          ->orWhereDate('final_paid_at', '>=', $from);
                    });
                })
                ->when($to, function ($query) use ($to) {
                    $query->where(function ($q) use ($to) {
                        $q->whereDate('booking_paid_at', '<=', $to)
                          ->orWhereDate('mid_paid_at', '<=', $to)
                          ->orWhereDate('final_paid_at', '<=', $to);
                    });
                })
                ->selectRaw('
                    users.name as supervisor,
                    COUNT(DISTINCT projects.id) as total_projects,
                    COALESCE(SUM(CASE WHEN projects.booking_status = ? THEN projects.booking_amount ELSE 0 END), 0) +
                    COALESCE(SUM(CASE WHEN projects.mid_status = ? THEN projects.mid_amount ELSE 0 END), 0) +
                    COALESCE(SUM(CASE WHEN projects.final_status = ? THEN projects.final_amount ELSE 0 END), 0) as collected_amount,
                    COALESCE(SUM(CASE WHEN projects.booking_status = ? THEN projects.booking_amount ELSE 0 END), 0) +
                    COALESCE(SUM(CASE WHEN projects.mid_status = ? THEN projects.mid_amount ELSE 0 END), 0) +
                    COALESCE(SUM(CASE WHEN projects.final_status = ? THEN projects.final_amount ELSE 0 END), 0) as pending_amount
                ', [
                    Project::PAYMENT_PAID, Project::PAYMENT_PAID, Project::PAYMENT_PAID,
                    Project::PAYMENT_PENDING, Project::PAYMENT_PENDING, Project::PAYMENT_PENDING
                ])
                ->leftJoin('users', 'users.id', '=', 'projects.supervisor_id')
                ->where('users.role', 'SUPERVISOR')
                ->groupBy('users.name')
                ->get();
        }

        return inertia('Admin/Dashboard', [
            'totalRevenue' => $totalRevenue,
            'cashCollected' => $cashCollected,
            'onlineCollected' => $onlineCollected,
            'pendingBalance' => $pendingBalance,
            'supervisorCollection' => $supervisorCollection,
        ]);
    }
}
