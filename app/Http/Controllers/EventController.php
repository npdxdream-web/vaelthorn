<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\Kingdom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::with(['kingdom', 'participants'])
            ->withCount('participants')
            ->where('status', 'active')
            ->orderBy('start_at');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('city')) {
            $query->where('kingdom_id', $request->city);
        }

        $events   = $query->get();
        $kingdoms = Kingdom::orderBy('name')->get();
        $currentCharacter = auth()->user()->character;

        return view('events.index', compact('events', 'kingdoms', 'currentCharacter'));
    }

    public function show($id)
    {
        $event = Event::with([
            'kingdom',
            'requirements',
            'participants.character.kingdom',
            'rewards.item',
        ])->findOrFail($id);

        $currentCharacter = auth()->user()->character;
        $isJoined = $currentCharacter
            ? $event->participants()->where('character_id', $currentCharacter->id)->exists()
            : false;

        // Check if character meets requirements
        $meetsRequirements = true;
        $requirementResults = [];
        if ($currentCharacter) {
            $stats = $currentCharacter->stats;
            foreach ($event->requirements as $req) {
                $met = match($req->req_type) {
                    'level' => ($stats->level ?? 1) >= $req->min_value,
                    'stat'  => ($stats->{$req->req_key} ?? 0) >= $req->min_value,
                    'city'  => $currentCharacter->kingdom_id == $req->min_value,
                    default => true,
                };
                $requirementResults[] = [
                    'label' => $req->req_key ?? $req->req_type,
                    'min'   => $req->min_value,
                    'met'   => $met,
                ];
                if (! $met) $meetsRequirements = false;
            }
        }

        return view('events.show', compact(
            'event', 'currentCharacter', 'isJoined',
            'meetsRequirements', 'requirementResults'
        ));
    }

    public function join($id)
    {
        $event     = Event::findOrFail($id);
        $character = Auth::user()->character;

        if (! $character) {
            return back()->with('error', 'กรุณาสร้างตัวละครก่อน');
        }
        if ($event->status !== 'active') {
            return back()->with('error', 'Event นี้ไม่เปิดรับสมัครแล้ว');
        }
        if ($event->participants()->where('character_id', $character->id)->exists()) {
            return back()->with('error', 'เข้าร่วม Event นี้แล้ว');
        }

        EventParticipant::create([
            'event_id'     => $event->id,
            'character_id' => $character->id,
            'joined_at'    => now(),
        ]);

        return back()->with('success', 'เข้าร่วม ' . $event->title . ' แล้ว');
    }

    public function leave($id)
    {
        $event     = Event::findOrFail($id);
        $character = Auth::user()->character;

        if (! $character) {
            return back()->with('error', 'กรุณาสร้างตัวละครก่อน');
        }

        $event->participants()->where('character_id', $character->id)->delete();

        return back()->with('success', 'ออกจาก Event แล้ว');
    }
}
