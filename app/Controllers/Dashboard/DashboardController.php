<?php

namespace App\Controllers\Dashboard;

use App\Controllers\BaseController;
use App\Models\ProjectModel;
use App\Models\UserModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $userId = session()->get('id');

        $projectModel = new ProjectModel();
        $userModel = new UserModel();

        $user = $userModel->find($userId);

        $stats = [
            'active_projects' => $projectModel->where('user_id', $userId)->where('status !=', 'completed')->countAllResults(),
            'wallet_balance' => $user['wallet_balance'],
            'recent_projects' => $projectModel->where('user_id', $userId)->orderBy('created_at', 'DESC')->limit(5)->find()
        ];

        return view('dashboard/index', $stats);
    }
}
