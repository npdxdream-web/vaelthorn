<?php

namespace App\Services;

use App\Models\Character;
use App\Models\FriendRequest;
use App\Models\Friendship;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FriendService
{
    public function __construct(private NotificationService $notifications)
    {
    }

    public function sendRequest(Character $from, Character $to): FriendRequest
    {
        if ($from->id === $to->id) {
            throw ValidationException::withMessages([
                'friend' => 'คุณไม่สามารถส่งคำขอเป็นเพื่อนกับตัวเองได้',
            ]);
        }

        if ($from->isFriendWith($to)) {
            throw ValidationException::withMessages([
                'friend' => 'คุณเป็นเพื่อนกับตัวละครนี้อยู่แล้ว',
            ]);
        }

        if ($from->pendingRequestWith($to)) {
            throw ValidationException::withMessages([
                'friend' => 'มีคำขอเป็นเพื่อนที่ยังไม่ได้ตอบรับระหว่างตัวละครนี้อยู่แล้ว',
            ]);
        }

        $recentRejection = FriendRequest::where('status', 'rejected')
            ->where(function ($q) use ($from, $to) {
                $q->where(['from_character_id' => $from->id, 'to_character_id' => $to->id])
                    ->orWhere(['from_character_id' => $to->id, 'to_character_id' => $from->id]);
            })
            ->where('updated_at', '>', now()->subDay())
            ->latest('updated_at')
            ->first();

        if ($recentRejection) {
            $retryAt = $recentRejection->updated_at->addDay();
            $remaining = now()->diffForHumans($retryAt, true);

            throw ValidationException::withMessages([
                'friend' => "คำขอเป็นเพื่อนถูกปฏิเสธไปแล้ว กรุณาลองใหม่อีกครั้งในอีก {$remaining}",
            ]);
        }

        $request = FriendRequest::create([
            'from_character_id' => $from->id,
            'to_character_id'   => $to->id,
            'status'            => 'pending',
            'pair_key'          => $this->pairKey($from->id, $to->id),
        ]);

        $this->notifications->notifyFriendRequestReceived($request);

        return $request;
    }

    public function accept(FriendRequest $request): void
    {
        DB::transaction(function () use ($request) {
            $request->loadMissing(['fromCharacter', 'toCharacter']);

            Friendship::create([
                'character_id_1' => $request->from_character_id,
                'character_id_2' => $request->to_character_id,
            ]);
            Friendship::create([
                'character_id_1' => $request->to_character_id,
                'character_id_2' => $request->from_character_id,
            ]);

            $accepter = $request->toCharacter;
            $request->delete();

            $this->notifications->notifyFriendRequestAccepted($request, $accepter);
        });
    }

    public function reject(FriendRequest $request): void
    {
        $request->update(['status' => 'rejected', 'pair_key' => null]);
    }

    public function unfriend(Character $character, Character $other): void
    {
        Friendship::where('character_id_1', $character->id)->where('character_id_2', $other->id)->delete();
        Friendship::where('character_id_1', $other->id)->where('character_id_2', $character->id)->delete();
    }

    private function pairKey(int $a, int $b): string
    {
        return implode('-', [min($a, $b), max($a, $b)]);
    }
}
