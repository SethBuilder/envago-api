<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::whereHas('activities', fn($q) => $q->public())
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return response()->json(['data' => $categories]);
    }
}
