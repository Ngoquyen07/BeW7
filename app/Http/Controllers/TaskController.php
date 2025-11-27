<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $query = $user->tasks();

            if ($request->search) {
                $query->where("title", "like", "%{$request->search}%");
            }

            return $query->orderBy('id', 'asc')->paginate(10);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch tasks',
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $user = Auth::user();

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'status' => 'required|in:active,inactive',
            ]);

            $task = $user->tasks()->create($validated);

            return response()->json([
                'message' => 'Task created successfully',
                'success' => true,
                'data' => $task
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create task',
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
        try {
            $user = Auth::user();
            $task = $user->tasks()->where('id', $id)->first();
            if ($task) {
                return response()->json([
                    'message' => 'Task retrieved successfully',
                    'success' => true,
                    'data' => $task
                ],200);
            }
        }
        catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to catch task',
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        //
    }
}
