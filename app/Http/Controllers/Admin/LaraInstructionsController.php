<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LaraInstruction;
use Illuminate\Http\Request;

class LaraInstructionsController extends Controller
{
    public function index()
    {
        $instructions = LaraInstruction::latest()->get();

        return view('admin.lara-instructions', compact('instructions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'context'     => 'required|in:provider,tourist',
            'instruction' => 'required|string|max:1000',
        ]);

        LaraInstruction::create([
            'context'     => $request->context,
            'instruction' => $request->instruction,
            'active'      => true,
        ]);

        return back()->with('success', 'Instrução salva.');
    }

    public function toggle(LaraInstruction $instruction)
    {
        $instruction->update(['active' => !$instruction->active]);

        return back();
    }

    public function destroy(LaraInstruction $instruction)
    {
        $instruction->delete();

        return back()->with('success', 'Instrução removida.');
    }
}
