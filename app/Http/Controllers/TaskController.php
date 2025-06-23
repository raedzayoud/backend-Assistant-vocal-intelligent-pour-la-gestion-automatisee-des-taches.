<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Projet;
use App\Models\Task;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function getTasksByUser($idprojet)
    {
        $tasks = Task::where('projet_id', $idprojet)
            ->whereHas('projet', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->orderBy('id')
            ->get();

        return response()->json(["tasks" => $tasks]);
    }

    public function storeTask(StoreTaskRequest $request, $projet_id)
    {
        // Vérifier si le projet appartient à l'utilisateur connecté
        $projet = Projet::where('id', $projet_id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$projet) {
            return response()->json([
                'message' => 'Vous n\'êtes pas autorisé à ajouter une tâche à ce projet.'
            ]);
        }

        $validatedData = $request->validated();
        $validatedData['projet_id'] = $projet->id;


        $task = Task::create($validatedData);

        return response()->json($task);
    }


    public function destroy(int $id)
    {
        try {
            $task = Task::find($id);

            if (!$task) {
                return response()->json([
                    "error" => "Task not found."
                ], 404);
            }

            if ($task->projet->user_id !== Auth::id()) {
                return response()->json([
                    "error" => "You are not authorized to delete this task."
                ], 403);
            }

            $task->delete();

            return response()->json([
                "message" => "Task deleted successfully."
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                "error" => "An error occurred while deleting the task.",
                "details" => $e->getMessage()
            ], 500);
        }
    }
    public function update(UpdateTaskRequest $request, int $id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json([
                "error" => "Task not found."
            ], 404);
        }

        if ($task->projet->user_id !== Auth::id()) {
            return response()->json([
                "error" => "You are not authorized to update this task."
            ], 403);
        }

        $task->update($request->validated());
        return response()->json($task);
    }
}
