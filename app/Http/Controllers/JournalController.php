<?php

namespace App\Http\Controllers;

use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Inertia\Inertia;

class JournalController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $entries = JournalEntry::forUser($user->id)
            ->orderBy('entry_date', 'desc')
            ->paginate(10);

        if ($request->wantsJson()) {
            return response()->json($entries);
        }

        return Inertia::render('Journal/Index', ['entries' => $entries]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'entry_date' => 'required|date',
            'title' => 'nullable|string|max:255',
            'content' => 'required|string',
            'tags' => 'nullable|array',
            'mood' => 'nullable|string|max:50',
        ]);

        $validated['user_id'] = $request->user()->id;
        $entry = JournalEntry::create($validated);

        return response()->json(['message' => 'Entry created', 'entry' => $entry], 201);
    }

    public function update(Request $request, JournalEntry $journal)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'required|string',
            'tags' => 'nullable|array',
        ]);

        $journal->update($validated);
        return response()->json(['entry' => $journal->fresh()]);
    }

    public function autoSave(Request $request, JournalEntry $journal)
    {
        $journal->autoSave($request->input('content', ''));
        return response()->json(['saved' => true]);
    }

    public function today(Request $request)
    {
        $entry = JournalEntry::getOrCreateForToday($request->user()->id);
        return response()->json(['entry' => $entry]);
    }
}
