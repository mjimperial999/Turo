<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Models\{
    Inbox,
    InboxParticipant,
    Message,
    MessageUserState,
    Users
};

class InboxController extends Controller
{
    /* ===========================
     * FOLDERS
     * =========================== */

    /** “All Inboxes” */
    public function index()
    {
        $userID = session('user_id');
        $users = Users::with('image')->findOrFail($userID);

        $threads = Inbox::whereHas('participants', function ($q) use ($userID) {
            $q->where('inboxparticipant.participant_id', $userID);      // user is in thread
        })
            ->whereHas('messages', function ($q) use ($userID) {
                $q->where('sender_id', '!=', $userID);                      // …and at least ONE message is incoming
            })
            ->with(['participants', 'messages.userStates'])
            ->orderBy('timestamp', 'desc')
            ->get();

        $header = "Incoming Messages";

        $allUsers = Users::orderBy('role_id')
            ->orderBy('last_name')
            ->get(['user_id', 'first_name', 'last_name', 'role_id']);

        return view('inbox.index', compact('threads', 'allUsers', 'header', 'users'))->with('folder', 'inbox');
    }

    /** “All Sent” */
    public function sent()
    {
        $userID = session('user_id');
        $users = Users::with('image')->findOrFail($userID);

        $threads = Inbox::whereHas('messages', function ($q) use ($userID) {
            $q->where('sender_id', $userID);                            // message filter :contentReference[oaicite:1]{index=1}
        })
            ->with(['participants', 'messages.userStates'])
            ->orderBy('timestamp', 'desc')
            ->get();

        $header = "Sent Messages";

        $allUsers = Users::orderBy('role_id')
            ->orderBy('last_name')
            ->get(['user_id', 'first_name', 'last_name', 'role_id']);

        return view('inbox.index', compact('threads', 'allUsers', 'header', 'users'))->with('folder', 'sent');
    }

    /* ===========================
     * OPEN A THREAD
     * =========================== */

    public function show(Inbox $inbox, Request $request)
    {
        $userID = session('user_id');
        $users = Users::with('image')->findOrFail($userID);

        $folder = $request->query('folder', 'inbox');   // inbox | sent

        abort_unless($inbox->participants->contains('user_id', $userID), 403);

        /* mark everything read for me */
        MessageUserState::where('user_id', $userID)
            ->whereIn('message_id', $inbox->messages()->pluck('message_id'))
            ->update(['is_read' => 1]);

        $inbox->load(['messages.sender', 'messages.userStates']);

        /* ---------- rebuild sidebar according to current folder ---------- */
        $threads = Inbox::whereHas('participants', function ($q) use ($userID) {
            $q->where('inboxparticipant.participant_id', $userID);
        });

        if ($folder === 'sent') {
            // only threads with at least ONE message from me
            $threads->whereHas('messages', fn($q) => $q->where('sender_id', $userID));
        } else {                     // inbox
            // only threads with at least ONE message NOT from me
            $threads->whereHas('messages', fn($q) => $q->where('sender_id', '!=', $userID));
        }

        $threads = $threads->with(['participants', 'messages.userStates'])
            ->orderBy('timestamp', 'desc')
            ->get();

        $allUsers = Users::orderBy('role_id')->orderBy('last_name')
            ->get(['user_id', 'first_name', 'last_name', 'role_id']);

        return view('inbox.show', compact('inbox', 'threads', 'allUsers', 'folder', 'users'));
    }

    /* ===========================
     * COMPOSE NEW THREAD
     * =========================== */

    public function store(Request $request)
    {
        $request->validate([
            'participants' => 'required|string',          // comma-separated IDs
            'subject'      => 'nullable|string|max:255',
            'body'         => 'required|string',
        ]);

        $senderID = session('user_id');
        $now      = Carbon::now()->timestamp;

        DB::transaction(function () use ($request, $senderID, $now) {

            $inbox = Inbox::create([
                'inbox_id'  => (string) Str::uuid(),
                'timestamp' => $now,
            ]);

            // clean up list, always include sender
            $participants = collect(explode(',', $request->input('participants')))
                ->map(fn($id) => trim($id))
                ->filter()
                ->push($senderID)
                ->unique();

            foreach ($participants as $id) {
                InboxParticipant::create([
                    'inbox_id'       => $inbox->inbox_id,
                    'participant_id' => $id,
                ]);
            }

            $message = Message::create([
                'message_id' => (string) Str::uuid(),
                'inbox_id'   => $inbox->inbox_id,
                'sender_id'  => $senderID,
                'subject'    => $request->input('subject'),
                'body'       => $request->input('body'),
                'timestamp'  => $now,
            ]);

            foreach ($participants as $id) {
                MessageUserState::create([
                    'message_id' => $message->message_id,
                    'user_id'    => $id,
                    'is_read'    => $id === $senderID,   // sender = read
                ]);
            }
        });

        return redirect('/inbox');
    }

    /* ===========================
     * REPLY TO THREAD
     * =========================== */

    public function reply(Request $request, Inbox $inbox)
    {
        $request->validate([
            'body'    => 'required|string',
            'subject' => 'nullable|string|max:255',
        ]);

        $senderID = session('user_id');
        $now      = Carbon::now()->timestamp;

        abort_unless($inbox->participants->contains('user_id', $senderID), 403);

        DB::transaction(function () use ($request, $inbox, $senderID, $now) {

            $message = Message::create([
                'message_id' => (string) Str::uuid(),
                'inbox_id'   => $inbox->inbox_id,
                'sender_id'  => $senderID,
                'subject'    => $request->input('subject'),
                'body'       => $request->input('body'),
                'timestamp'  => $now,
            ]);

            foreach ($inbox->participants as $p) {
                MessageUserState::create([
                    'message_id' => $message->message_id,
                    'user_id'    => $p->user_id,
                    'is_read'    => $p->user_id === $senderID,
                ]);
            }

            $inbox->update(['timestamp' => $now]);   // bump thread
        });

        return back();
    }

    /* ───────────────  TOGGLE READ  ─────────────── */

    public function toggleRead(Message $message)
    {
        $userID = session('user_id');

        $state = MessageUserState::where([
            'message_id' => $message->message_id,
            'user_id'    => $userID,
        ])->firstOrFail();

        $state->update(['is_read' => ! $state->is_read]);

        return response()->noContent();
    }
}
