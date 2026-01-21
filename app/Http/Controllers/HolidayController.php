<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HolidayController extends Controller
{
    /**
     * Display a listing of the holidays.
     */
    public function index()
    {
        $holidays = Holiday::orderBy('date', 'desc')->get();
        return view('pages.holidays.index', compact('holidays'));
    }

    /**
     * Show the form for creating a new holiday.
     */
    public function create()
    {
        return view('pages.holidays.create');
    }

    /**
     * Store a newly created holiday in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        Holiday::create([
            'name' => $request->name,
            'date' => $request->date,
            'description' => $request->description,
        ]);

        return redirect()->route('holidays.index')
            ->with('success', 'Hari libur berhasil ditambahkan!');
    }

    /**
     * Show the form for editing the specified holiday.
     */
    public function edit(Holiday $holiday)
    {
        return view('pages.holidays.edit', compact('holiday'));
    }

    /**
     * Update the specified holiday in storage.
     */
    public function update(Request $request, Holiday $holiday)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $holiday->update([
            'name' => $request->name,
            'date' => $request->date,
            'description' => $request->description,
        ]);

        return redirect()->route('holidays.index')
            ->with('success', 'Hari libur berhasil diperbarui!');
    }

    /**
     * Remove the specified holiday from storage.
     */
    public function destroy(Holiday $holiday)
    {
        $holiday->delete();

        return redirect()->route('holidays.index')
            ->with('success', 'Hari libur berhasil dihapus!');
    }
}
