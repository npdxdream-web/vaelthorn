<?php

namespace App\Http\Controllers;

use App\Models\Kingdom;
use App\Models\Thread;
use App\Models\Event;
use App\Models\Character;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $kingdoms = Kingdom::with('cities')->where('is_active', true)->get();

        $recentThreads = Thread::with('city')
            ->where('status', 'open')
            ->withCount('posts')
            ->latest('updated_at')
            ->take(3)
            ->get();

        $flashEvents = Event::where('type', 'flash')
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('end_at')->orWhere('end_at', '>', now());
            })
            ->orderBy('start_at')
            ->get();

        $echoText = collect(config('echoes.messages', []))->random();

        [$topPlayers, $myRank, $expToTop5] = $this->buildLeaderboard();

        return view('home', compact(
            'kingdoms', 'recentThreads', 'flashEvents', 'echoText',
            'topPlayers', 'myRank', 'expToTop5'
        ));
    }

    /**
     * Ranks eligible (non-staff) characters by a lifetime-exp-equivalent score —
     * exp is only a per-level counter in character_stats (resets on level-up),
     * so cross-level comparison sums each already-cleared level's threshold
     * from config('leveling.exp_to_next') plus the current level's exp. This
     * lets "exp to reach top 5" work even when the viewer and the 5th-place
     * character aren't at the same level.
     */
    private function buildLeaderboard(): array
    {
        $expToNextConfig = config('leveling.exp_to_next', []);

        $scoreOf = function (int $level, int $exp) use ($expToNextConfig): int {
            $score = $exp;
            for ($lvl = 1; $lvl < $level; $lvl++) {
                $score += $expToNextConfig[$lvl] ?? 0;
            }
            return $score;
        };

        $ranked = Character::query()
            ->whereHas('stats')
            ->whereHas('user', fn ($q) => $q->whereNotIn('role', ['admin', 'superadmin', 'moderator']))
            ->with(['stats', 'kingdom'])
            ->get()
            ->map(function (Character $character) use ($scoreOf) {
                $stats = $character->stats;
                return [
                    'character_id' => $character->id,
                    'name'         => $character->name,
                    'kingdom'      => $character->kingdom?->name ?? '-',
                    'level'        => $stats->level,
                    'exp'          => $stats->exp,
                    'exp_to_next'  => $stats->exp_to_next ?: 1,
                    'score'        => $scoreOf($stats->level, $stats->exp),
                ];
            })
            ->sortByDesc('score')
            ->values();

        $topPlayers = $ranked->take(5)->map(fn ($row) => [
            'name'        => $row['name'],
            'kingdom'     => $row['kingdom'],
            'level'       => $row['level'],
            'exp_percent' => (int) round(min(100, ($row['exp'] / max(1, $row['exp_to_next'])) * 100)),
        ])->all();

        $myRank     = null;
        $expToTop5  = null;
        $myCharacter = Auth::user()?->character;

        if ($myCharacter) {
            $position = $ranked->search(fn ($row) => $row['character_id'] === $myCharacter->id);

            if ($position !== false) {
                $myRank = $position + 1;

                if ($myRank > 5 && $ranked->count() >= 5) {
                    $fifthScore = $ranked[4]['score'];
                    $myScore    = $ranked[$position]['score'];
                    $expToTop5  = max(0, $fifthScore - $myScore);
                }
            }
        }

        return [$topPlayers, $myRank, $expToTop5];
    }
}
