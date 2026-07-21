<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Thread;
use App\Models\TravelPermit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CityController extends Controller
{
    public function show($id)
    {
        $city = City::with('kingdom')->findOrFail($id);
        $user = Auth::user();
        $character = $user->character()->with(['kingdom', 'currentKingdom', 'stats'])->first();

        if (! $user->isAdminGroup() && ! $character) {
            return redirect()->route('register')->with('warning', 'กรุณาสร้างตัวละครก่อนเข้าชมเมือง');
        }

        if (! $user->isAtLeastModerator() && $character) {
            $isHomeKingdom    = $character->kingdom_id === $city->kingdom_id;
            $isCurrentKingdom = $character->current_kingdom_id === $city->kingdom_id;
            $hasActivePermit  = TravelPermit::where('character_id', $character->id)
                ->where('kingdom_id', $city->kingdom_id)
                ->whereNotNull('activated_at')
                ->where('expires_at', '>', now())
                ->exists();

            if (! $isHomeKingdom && ! $isCurrentKingdom && ! $hasActivePermit) {
                abort(403, 'คุณไม่มีสิทธิ์เข้าถึงพื้นที่นี้');
            }
        }

        // Track last-visited location — whichever city the character just accessed
        if ($character && ($character->current_kingdom_id !== $city->kingdom_id || $character->current_city_id !== $city->id)) {
            $character->update([
                'current_kingdom_id' => $city->kingdom_id,
                'current_city_id'    => $city->id,
            ]);
        }

        $userId  = $user->id;
        $threads = Thread::where('city_id', $city->id)
            ->where(function ($q) use ($userId, $user) {
                if ($user->isAtLeastModerator()) {
                    return; // admin sees all
                }
                // Exclude archived threads from the main city feed
                $q->whereIn('status', ['approved', 'locked'])
                  ->orWhere(function ($q2) use ($userId) {
                      $q2->where('created_by', $userId)
                         ->whereIn('status', ['pending', 'draft', 'request_edit', 'rejected']);
                  });
            })
            ->withCount('posts')
            ->latest()
            ->get();

        $canWrite = $city->canWrite($user, $character);

        return view('city', compact('city', 'threads', 'character', 'canWrite'));
    }

    public function apiShow($id)
    {
        if (is_numeric($id)) {
            $city = City::with('kingdom')->findOrFail($id);
        } else {
            $slug = Str::slug((string) $id);
            $city = City::with('kingdom')
                ->get()
                ->first(fn (City $item) => Str::slug($item->name) === $slug);

            if (! $city) {
                abort(404, 'City not found');
            }
        }

        $authUser = auth()->user();
        $threads  = Thread::where('city_id', $city->id)
            ->where(function ($q) use ($authUser) {
                if ($authUser?->isAtLeastModerator()) {
                    return;
                }
                // Exclude archived from the main React city feed as well
                $q->whereIn('status', ['approved', 'locked']);
                if ($authUser) {
                    $q->orWhere(function ($q2) use ($authUser) {
                        $q2->where('created_by', $authUser->id)
                           ->whereIn('status', ['pending', 'draft', 'request_edit', 'rejected']);
                    });
                }
            })
            ->with('author.kingdom')
            ->withCount('posts')
            ->latest()
            ->get()
            ->map(function (Thread $thread) use ($city) {
                $author = $thread->author ?? $thread->creator;

                return [
                    'id'           => $thread->id,
                    'title'        => $thread->title,
                    'status'       => $thread->status,
                    'status_label' => $thread->status_label,
                    'status_color' => $thread->status_color,
                    'author'       => [
                        'id'         => $author?->id,
                        'name'       => $author?->name ?? 'Unknown',
                        'avatar_url' => $thread->author?->avatar_url,
                        'kingdom'    => $thread->author?->kingdom?->name,
                    ],
                    'tags'         => [$thread->status_label, $city->name],
                    'replies'      => $thread->posts_count,
                    'lastActivity' => $thread->updated_at->diffForHumans(),
                    'kingdomName'  => $city->kingdom?->name,
                ];
            });

        return response()->json([
            'id' => $city->id,
            'name' => $city->name,
            'description' => $city->description,
            'kingdom' => [
                'id' => $city->kingdom?->id,
                'name' => $city->kingdom?->name,
                'color' => $city->kingdom?->color,
                'icon' => $city->kingdom?->icon,
            ],
            'threads' => $threads,
        ]);
    }
}
