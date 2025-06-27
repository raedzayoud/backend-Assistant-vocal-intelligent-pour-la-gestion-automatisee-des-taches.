<?php

namespace App\Http\Controllers;

use App\Models\Meet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MettingController extends Controller
{
    public function create(Request $request)
    {
        $prompt = $request->input('prompt');

        $title = 'Meeting';
        $now = Carbon::now();

        // Handle "tomorrow" keyword
        if (str_contains(strtolower($prompt), 'tomorrow')) {
            $now->addDay();
        }

        if (preg_match('/\b(\d{1,2})h(\d{2})?\b/', $prompt, $matches)) {
            $hour = intval($matches[1]);
            $minute = isset($matches[2]) ? intval($matches[2]) : 0;

            // Set time with extracted hour and minute
            $now->setTime($hour, $minute);
        }

        // Extract name after "with" and capitalize it
        if (preg_match('/with (\w+)/i', $prompt, $matches)) {
            $title = ucfirst($matches[1]) . ' Meeting';
        }

        // Generate a unique room ID
        $room = (string) mt_rand(1000, 9999);

        $user_id = Auth::user()->id; // shorter form of Auth::user()->id

        // Store in database
        Meet::create([
            'user_id' => $user_id,
            'title' => $title,
            'start_time' => $now->toDateTimeString(),
            'room' => $room,
        ]);

        // Respond with meeting info
        return response()->json([
            'message' => 'Meet created successfully!',
            'title' => $title,
            'start_time' => $now->toDateTimeString(),
            'room' => $room,
            'user_id' => $user_id,
        ]);
    }


    public function getAllMeetByUser()
    {
        $user_id = Auth::user()->id;
        $meet = DB::select("select * from meets where user_id=?", [$user_id]);
        return response()->json([
            "meet" => $meet
        ]);
    }
}
