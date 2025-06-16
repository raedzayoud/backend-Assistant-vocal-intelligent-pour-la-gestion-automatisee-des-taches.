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
    public function getTasksByUser()
    {
        // $tasks = Projet::user()->tasks()->orderBy('id')->get();
        // return response()->json(["tasks" => $tasks]);
    }

    public function storeTask(StoreTaskRequest $storeTaskRequest, $projet_id)
    {
        $validateData = $storeTaskRequest->validated();
        $validateData['projet_id'] = $projet_id;
        $task = Task::create($validateData);
        return response()->json($task);
    }

    public function destroy(int $id)
    {
        try {
            $task = Task::find($id);
            $task->delete();
            return response()->json(["message" => "task deleted successfully"]);
        } catch (Exception $e) {
            return response()->json([
                "error" => "task Not Found",
                "details" => $e->getMessage()
            ]);
        }
    }

    public function update(UpdateTaskRequest $request, int $id)
    {
        //
       // $user_id = Auth::user()->id;
        $tasks = Task::find($id);
        // if ($user_id != $tasks->user_id) {
        //     return response()->json(["message" =>
        //     "You are not allowed to update this task"], 403);
        // }
        $tasks->update($request->validated());
        return response()->json($tasks);
    }
}
