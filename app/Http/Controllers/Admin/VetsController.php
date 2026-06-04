<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VetsController extends Controller
{
    public function index(){
        return view('content.admin.vets.index');
    }
}
