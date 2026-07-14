<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OutboundClick;

class ClicksController extends Controller
{
    public function index()
    {
        $clicks = OutboundClick::with('activity')->latest()->paginate(50);

        return view('admin.clicks', compact('clicks'));
    }
}
