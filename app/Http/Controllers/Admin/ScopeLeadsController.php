<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScopeLead;

class ScopeLeadsController extends Controller
{
    public function index()
    {
        $leads = ScopeLead::latest()->paginate(30);

        return view('admin.scope-leads', compact('leads'));
    }
}
