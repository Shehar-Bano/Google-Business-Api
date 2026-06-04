<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ModulePlaceholderController extends Controller
{
    public function index(string $module): View
    {
        return view('content.admin.module-placeholder.index', compact('module'));
    }
}
