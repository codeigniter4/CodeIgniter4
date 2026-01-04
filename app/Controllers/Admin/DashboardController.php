<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SimcardModel;
use App\Models\OrderModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $simcardModel = new SimcardModel();
        $orderModel = new OrderModel();

        // Stats
        $totalSimcards = $simcardModel->countAll();
        $freeSimcards = $simcardModel->where('status', 'free')->countAllResults();
        $soldSimcards = $simcardModel->where('status', 'sold')->countAllResults();

        $today = date('Y-m-d');
        $todayOrders = $orderModel->like('created_at', $today)->countAllResults();

        // This month using Jalali Helper
        // We need to filter orders where the date matches the current Jalali month
        // Since we store dates as Gregorian in DB, this is tricky with simple like.
        // We will fetch all orders for this month roughly or implement a better query.
        // For simplicity and correctness with the prompt, we will fetch current month orders by checking dates.

        // (Removed commented out complex logic for clarity)

        // Let's filter in PHP for the dashboard stat for correctness.
        $allOrdersDates = $orderModel->select('created_at')->findAll();
        $monthOrders = 0;
        $currentShamsiMonth = jdate('Y-m');
        foreach ($allOrdersDates as $o) {
            if ($o['created_at']) {
                $shamsiDate = jdate('Y-m', strtotime($o['created_at']));
                if ($shamsiDate == $currentShamsiMonth) {
                    $monthOrders++;
                }
            }
        }

        // Total sales (Sum of amount where payment_status = success)
        $totalSales = $orderModel->where('payment_status', 'success')->selectSum('amount')->first()['amount'] ?? 0;

        // Last 10 orders
        $lastOrders = $orderModel->orderBy('created_at', 'DESC')->findAll(10);

        return view('admin/dashboard', [
            'totalSimcards' => $totalSimcards,
            'freeSimcards'  => $freeSimcards,
            'soldSimcards'  => $soldSimcards,
            'todayOrders'   => $todayOrders,
            'monthOrders'   => $monthOrders,
            'totalSales'    => $totalSales,
            'lastOrders'    => $lastOrders
        ]);
    }
}
