<?php

namespace App\Http\Controllers;

use App\Models\Projet;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class GeminiController extends Controller
{
    /*This line must be add to text to ensure the model return the best result
    - Extrais intention, titre, projet, date, heure au format JSON.*/
    public function askGemini(Request $request)
    {
        $prompt = $request->input('prompt');
        $apiKey = env('GEMINI_API_KEY');

        if (!$apiKey) {
            return response()->json([
                'error' => 'API key Gemini non définie dans .env (GEMINI_API_KEY)'
            ], 500);
        }

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=$apiKey", [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ]);


        // Récupération de la réponse brute
        $geminiResponse = $response->json();

        // Vérification si la réponse contient des candidats
        if (empty($geminiResponse['candidates'])) {
            return response()->json([
                'error' => 'Réponse vide de Gemini. Vérifie ta clé API, quota, ou prompt.',
                'full_response' => $geminiResponse
            ], 500);
        }

        // Récupérer le texte complet de la réponse Gemini
        $text = $geminiResponse['candidates'][0]['content']['parts'][0]['text'] ?? '';

        // Extraire la partie JSON via regex
        preg_match('/\{.*\}/s', $text, $matches);
        $json = $matches[0] ?? null;

        // Décoder le JSON extrait
        if ($json && $decoded = json_decode($json, true)) {
            return response()->json($decoded);
        }

        // Si pas de JSON décodable, retourner le texte brut avec erreur
        return response()->json([
            'raw_response' => $text,
            'error' => 'Impossible d\'extraire un JSON structuré depuis la réponse Gemini'
        ], 500);
    }


    public function StoreTheTaskAutomatique(Request $request)
    {
        $prompt = $request->input('prompt');
        $apiKey = env('GEMINI_API_KEY');

        if (!$apiKey) {
            return response()->json([
                'error' => 'API key Gemini non définie dans .env (GEMINI_API_KEY)'
            ], 500);
        }

        //Get user's existing project names
        $userId = Auth::id();
        $projects = Projet::where('user_id', $userId)->pluck('name')->toArray();
        //  echo $projects . '\n';
     //   print_r($projects);
        if (empty($projects)) {
            return response()->json([
                'error' => 'Aucun projet trouve pour cet utilisateur.'
            ], 404);
        }
        $projectList = implode(', ', $projects);
        //Construct a more detailed prompt
        $fullPrompt = "Voici la liste des projets existants de l'utilisateur : [$projectList].\n" .
            "À partir de la demande suivante, extraits les éléments suivants au format JSON : intention, titre, projet,description.\n\n" .
            "Demande utilisateur : \"$prompt\"";

        // Call Gemini API
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=$apiKey", [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $fullPrompt]
                    ]
                ]
            ]
        ]);

        $geminiResponse = $response->json();

        if (empty($geminiResponse['candidates'])) {
            return response()->json([
                'error' => 'Réponse vide de Gemini. Vérifie ta clé API, quota, ou prompt.',
                'full_response' => $geminiResponse
            ], 500);
        }

        $text = $geminiResponse['candidates'][0]['content']['parts'][0]['text'] ?? '';
        preg_match('/\{.*\}/s', $text, $matches);
        $json = $matches[0] ?? null;

        if ($json && $decoded = json_decode($json, true)) {
            $projectName = $decoded['projet'] ?? null;

            if (!$projectName) {
                return response()->json([
                    'error' => 'Nom du projet non trouve dans la réponse Gemini.',
                    'response' => $decoded
                ], 422);
            }

            // Check if the project exists for the user
            $projet = Projet::where('user_id', $userId)->where('name', $projectName)->first();

            if (!$projet) {
                return response()->json([
                    'error' => "Projet '$projectName' introuvable pour cet utilisateur."
                ], 404);
            }

            //Create and store the task
            $task = new Task();
            $task->titre = $decoded['titre'] ?? 'Tâche sans titre';
            $task->description=$decoded['description']??"Tâche sans description";
            $task->projet_id = $projet->id;
            $task->save();

            return response()->json([
                'message' => 'Tache creee avec succes',
                'task' => $task
            ]);
        }

        return response()->json([
            'raw_response' => $text,
            'error' => 'Impossible d\'extraire un JSON structuré depuis la réponse Gemini'
        ], 500);
    }
}
