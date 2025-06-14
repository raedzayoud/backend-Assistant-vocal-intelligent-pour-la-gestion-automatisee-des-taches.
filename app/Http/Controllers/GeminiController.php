<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
}
