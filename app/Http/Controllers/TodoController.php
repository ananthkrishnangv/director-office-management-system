<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TodoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Todo::with(['relatedMeeting'])->where('user_id', $user->id);

        if ($request->status === 'completed') {
            $query->completed();
        } elseif ($request->status === 'pending') {
            $query->incomplete();
        }

        $todos = $query->orderByPriority()->orderBy('due_date')->get();

        $stats = [
            'total' => Todo::where('user_id', $user->id)->count(),
            'completed' => Todo::where('user_id', $user->id)->completed()->count(),
            'pending' => Todo::where('user_id', $user->id)->incomplete()->count(),
        ];

        if ($request->wantsJson()) {
            return response()->json(['todos' => $todos, 'stats' => $stats]);
        }

        return Inertia::render('Todos/Index', [
            'todos' => $todos,
            'stats' => $stats,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'due_date' => 'nullable|date',
        ]);

        $validated['user_id'] = $request->user()->id;
        $todo = Todo::create($validated);

        return response()->json(['message' => 'Todo created', 'todo' => $todo], 201);
    }

    public function update(Request $request, Todo $todo)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'is_completed' => 'boolean',
        ]);

        if (isset($validated['is_completed'])) {
            $validated['completed_at'] = $validated['is_completed'] ? now() : null;
        }

        $todo->update($validated);
        return response()->json(['message' => 'Updated', 'todo' => $todo->fresh()]);
    }

    public function toggle(Todo $todo)
    {
        $todo->toggle();
        return response()->json(['todo' => $todo]);
    }

    public function destroy(Todo $todo)
    {
        $todo->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
