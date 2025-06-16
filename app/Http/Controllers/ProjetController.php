<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjetRequest;
use App\Models\Projet;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjetController extends Controller
{
    public function getProjetsByUser()
    {
        $projets = Auth::user()->projets()->orderBy('id')->get();
        return response()->json(["projets" => $projets]);
    }

    public function storeProjet(StoreProjetRequest $storeProjetRequest)
    {
        $user_id = Auth::user()->id;
        $validateData = $storeProjetRequest->validated();
        $validateData['user_id'] = $user_id;
        $projet = Projet::create($validateData);
        return response()->json($projet);
    }

    public function destroy(int $id)
    {
        try {
            $user_id = Auth::user()->id;
            $projet = Projet::find($id);
            if ($user_id != $projet->user_id) {
                return response()->json(["message" =>
                "You are not allowed to delete this task"]);
            }
            $projet->delete();
            return response()->json(["message" => "Projet deleted successfully"]);
        } catch (Exception $e) {
            return response()->json([
                "error" => "Projet Not Found",
                "details" => $e->getMessage()
            ]);
        }
    }
}
