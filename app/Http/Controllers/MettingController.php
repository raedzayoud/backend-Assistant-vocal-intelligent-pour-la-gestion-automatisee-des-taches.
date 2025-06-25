<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MettingController extends Controller
{
    public function create(Request $request)
    {
        $prompt = $request->input('prompt');

        $title = 'Meeting'; // fallback title
        $now = Carbon::now();

        // Check for time keywords
        if (str_contains($prompt, 'tomorrow')) {
            $now->addDay();
        }
        //Rechercher une heure dans le format français comme 14h, 8h, 9h, etc. dans la chaîne $prompt.
        if (preg_match('/\b(\d{1,2})h(\d{2})?\b/', $prompt, $matches)) {
            // intavl Elle est utilisée pour extraire la partie entière d'une valeur, souvent une chaîne de caractères ou un nombre à virgule.
            $hour = intval($matches[1]);
            $minute = isset($matches[2]) ? intval($matches[2]) : 0;
            $now->setTime($hour, $minute);
        }

        // Optional: extract a word after “with”
        if (preg_match('/with (\w+)/', $prompt, $matches)) {
            //ucfirst(...) → met la première lettre en majuscule (ex: "client" → "Client")
            $title = ucfirst($matches[1]) . ' Meeting';
        }

        // Create unique Jitsi room
        $room = 'meet-' . Str::random(8);
        $link = "https://meet.jit.si/$room";

        return response()->json([
            'title' => $title,
            'start_time' => $now->toDateTimeString(),
            'room' => $room,
            'link' => $link,
        ]);
    }
}
