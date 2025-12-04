<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $query = $user->tasks()->orderBy('updated_at', 'desc');

            if ($request->search) {
                $query->where("title", "like", "%{$request->search}%");
            }

            return $query->paginate(5);

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

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Failed to create task',
                'success' => false,
                'error' => $e->errors()
            ], 422);
        }
        catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create task',
                'success' => false,
                'error' => $e->getMessage()
            ],500);
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
            return response()->json([
                'message' => 'Task not found',
            ],404);
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
    public function update(Request $request, $id)
    {
        try {
            // 2. Validate dữ liệu
            $task = Auth::user()->tasks()->where('id', $id)->first();
            Gate::authorize('update', $task);
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'status' => 'required',
            ]);

            // 3. Cập nhật task
            $task->update($validated);

            return response()->json([
                'message' => 'Task updated successfully',
                'success' => true,
                'data' => $task
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {

            // Lỗi validate
            return response()->json([
                'message' => 'Validation failed',
                'success' => false,
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {

            // Lỗi không có quyền (Policy)
            return response()->json([
                'message' => 'You are not allowed to update this task',
                'success' => false,
            ], 403);

        } catch (\Exception $e) {

            // Lỗi bất ngờ khác
            return response()->json([
                'message' => 'Server error',
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $task = Auth::user()->tasks()->where('id', $id)->first();
        Gate::authorize('delete', $task);
        // 2. Xóa task
        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully',
            'success' => true,
        ], 200);
    }

}
