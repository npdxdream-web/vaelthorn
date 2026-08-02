<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\FriendRequest;
use App\Services\FriendService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class FriendController extends Controller
{
    public function __construct(private FriendService $friends)
    {
    }

    public function store(Request $request, Character $character)
    {
        $me = Auth::user()->character;

        abort_unless($me, 403);

        try {
            $this->friends->sendRequest($me, $character);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (UniqueConstraintViolationException $e) {
            return back()->withErrors(['friend' => 'มีคำขอเป็นเพื่อนที่กำลังดำเนินการอยู่แล้ว']);
        }

        return back()->with('success', 'ส่งคำขอเป็นเพื่อนแล้ว');
    }

    public function accept(FriendRequest $friendRequest)
    {
        $me = Auth::user()->character;

        abort_unless($me && $friendRequest->to_character_id === $me->id, 403);

        $this->friends->accept($friendRequest);

        return redirect()->route('notifications.index')->with('success', 'รับคำขอเป็นเพื่อนแล้ว');
    }

    public function reject(FriendRequest $friendRequest)
    {
        $me = Auth::user()->character;

        abort_unless($me && $friendRequest->to_character_id === $me->id, 403);

        $this->friends->reject($friendRequest);

        return redirect()->route('notifications.index')->with('success', 'ปฏิเสธคำขอเป็นเพื่อนแล้ว');
    }

    public function destroy(Character $character)
    {
        $me = Auth::user()->character;

        abort_unless($me, 403);

        $this->friends->unfriend($me, $character);

        return back()->with('success', 'ยกเลิกความเป็นเพื่อนแล้ว');
    }
}
