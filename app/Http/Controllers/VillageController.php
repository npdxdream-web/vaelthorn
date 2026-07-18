<?php

namespace App\Http\Controllers;

use App\Models\Village;
use App\Models\Thread;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class VillageController extends Controller
{
    public function show($id)
    {
        $village = Village::with('city')->findOrFail($id);
        $user = Auth::user();
        $character = $user->character()->with(['city', 'currentCity', 'stats'])->first();

        if (! $user->isAdminGroup() && ! $character) {
            return redirect()->route('register')->with('warning', 'กรุณาสร้างตัวละครก่อนเข้าชมหมู่บ้าน');
        }

        if (! $user->isAtLeastModerator() && $character && $character->city_id !== $village->city_id && $character->current_city_id !== $village->city_id) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงพื้นที่นี้');
        }

        $userId  = $user->id;
        $threads = Thread::where('village_id', $village->id)
            ->where(function ($q) use ($userId, $user) {
                if ($user->isAtLeastModerator()) {
                    return; // admin sees all
                }
                // Exclude archived threads from the main village feed
                $q->whereIn('status', ['approved', 'locked'])
                  ->orWhere(function ($q2) use ($userId) {
                      $q2->where('created_by', $userId)
                         ->whereIn('status', ['pending', 'draft', 'request_edit', 'rejected']);
                  });
            })
            ->withCount('posts')
            ->latest()
            ->get();

        $canWrite = $village->canWrite($user, $character);

        return view('village', compact('village', 'threads', 'character', 'canWrite'));
    }

    public function apiShow($id)
    {
        if (is_numeric($id)) {
            $village = Village::with('city')->findOrFail($id);
        } else {
            $slug = Str::slug((string) $id);
            $village = Village::with('city')
                ->get()
                ->first(fn (Village $item) => Str::slug($item->name) === $slug);

            if (! $village) {
                abort(404, 'Village not found');
            }
        }

        $authUser = auth()->user();
        $threads  = Thread::where('village_id', $village->id)
            ->where(function ($q) use ($authUser) {
                if ($authUser?->isAtLeastModerator()) {
                    return;
                }
                // Exclude archived from the main React village feed as well
                $q->whereIn('status', ['approved', 'locked']);
                if ($authUser) {
                    $q->orWhere(function ($q2) use ($authUser) {
                        $q2->where('created_by', $authUser->id)
                           ->whereIn('status', ['pending', 'draft', 'request_edit', 'rejected']);
                    });
                }
            })
            ->with('author.city')
            ->withCount('posts')
            ->latest()
            ->get()
            ->map(function (Thread $thread) use ($village) {
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
                        'city'       => $thread->author?->city?->name,
                    ],
                    'tags'         => [$thread->status_label, $village->name],
                    'replies'      => $thread->posts_count,
                    'lastActivity' => $thread->updated_at->diffForHumans(),
                    'cityName'     => $village->city?->name,
                ];
            });

        return response()->json([
            'id' => $village->id,
            'name' => $village->name,
            'description' => $village->description,
            'city' => [
                'id' => $village->city?->id,
                'name' => $village->city?->name,
                'color' => $village->city?->color,
                'icon' => $village->city?->icon,
            ],
            'threads' => $threads,
        ]);
    }
}
