<?php

namespace App\Http\Controllers;

use App\Models\Personnel;
use Illuminate\Http\Request;

class PersonnelController extends Controller
{
    public function index()
    {
        $personnel = Personnel::with('projects')->get();
        return view('personnel.index', compact('personnel'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'email' => 'required|email|unique:personnel,email',
            'phone' => 'nullable|string|max:50',
            'license_no' => 'nullable|string|max:100',
            'specialization' => 'nullable|string|max:255',
        ]);

        Personnel::create($validated);

        return redirect()->back()->with('success', 'Engineer/Architect registered to roster successfully!');
    }
}
