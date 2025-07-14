<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\{Users, UserPin};

class PinController extends Controller
{
    /* -------- show “enter pin” page -------- */
    public function show(Request $r)
    {
        return view('pin');
    }

    /* -------- send a fresh pin via Gmail ---- */
    public function send(Request $r)
    {
        $user = Users::where('email', $r->email)->firstOrFail();

        // only students & only if pw change still required
        abort_unless(($user->role_id == 1 || $user->role_id == 2) && $user->requires_password_change == 1, 403);

        $pin = UserPin::issueFor($user->user_id);

        Mail::html(
            /** ────── message body ────── */
            <<<HTML
    <div style="font-family:Arial,Helvetica,sans-serif;
                color:#1f1f1f;
                max-width:480px;
                margin:0 auto">
        <h2 style="margin-top:0;
                   color:#673ab7;
                   letter-spacing:.5px">
            Password&nbsp;Reset&nbsp;PIN
        </h2>

        <p style="font-size:15px;line-height:1.45em">
            Hello&nbsp;<strong>{$user->first_name}</strong>,
        </p>

        <p style="font-size:15px;line-height:1.45em">
            You—or someone using your e-mail—requested to reset the password
            on your Turo account.
            Please use the one-time PIN below within&nbsp;<strong>10 minutes</strong>.
        </p>

        <div style="text-align:center;
                    margin:28px 0 24px">
            <span style="display:inline-block;
                         background:#f5f5f5;
                         border:1px solid #d2d2d2;
                         border-radius:6px;
                         font-size:26px;
                         letter-spacing:6px;
                         font-weight:700;
                         color:#000;
                         padding:14px 18px">
                {$pin}
            </span>
        </div>

        <p style="font-size:14px;line-height:1.4em">
            Didn’t ask for this? Just ignore this message—your password
            remains unchanged.
        </p>

        <p style="margin-top:32px;font-size:14px;
                  color:#606060">
            — The Turo Team
        </p>

        <hr style="border:none;border-top:1px solid #e2e2e2;margin:32px 0">
    </div>
    HTML,
            /** ────── callback ────── */
            fn($m) => $m->from('turoapplication40@gmail.com', 'Turo App')
                ->to($user->email)
                ->subject('Your Turo one-time PIN')
        );

        return back()->with('success', 'A PIN has been sent to your e-mail: ' . $user->email);
    }

    /* -------- verify the pin --------------- */
    public function verify(Request $r)
    {
        $r->validate(['pin' => 'required|digits:6']);

        $userId = session('user_id');               // already logged in
        $row = UserPin::find($userId);

        if (!$row || $row->pin_code !== $r->pin || now('Asia/Manila')->gt($row->expires_at)) {
            return back()->with('error', 'Invalid or expired PIN');
        }

        // mark session so we can show the password form
        session(['pin_ok' => true]);

        return redirect('/replace-password');
    }

    public function sendRecovery(Request $r)
    {
        $r->validate(['email' => 'required|email']);
        $user = Users::where('email', $r->email)->firstOrFail();

        if (!in_array($user->role_id, [1, 2])) {
            abort(403);
        }

        session(['user_id' => $user->user_id]);
        $pin = UserPin::issueFor($user->user_id);

        Mail::html(
            /** ────── message body ────── */
            <<<HTML
    <div style="font-family:Arial,Helvetica,sans-serif;
                color:#1f1f1f;
                max-width:480px;
                margin:0 auto">
        <h2 style="margin-top:0;
                   color:#673ab7;
                   letter-spacing:.5px">
            Password&nbsp;Reset&nbsp;PIN
        </h2>

        <p style="font-size:15px;line-height:1.45em">
            Hello&nbsp;<strong>{$user->first_name}</strong>,
        </p>

        <p style="font-size:15px;line-height:1.45em">
            You—or someone using your e-mail—requested to reset the password
            on your Turo account.
            Please use the one-time PIN below within&nbsp;<strong>10 minutes</strong>.
        </p>

        <div style="text-align:center;
                    margin:28px 0 24px">
            <span style="display:inline-block;
                         background:#f5f5f5;
                         border:1px solid #d2d2d2;
                         border-radius:6px;
                         font-size:26px;
                         letter-spacing:6px;
                         font-weight:700;
                         color:#000;
                         padding:14px 18px">
                {$pin}
            </span>
        </div>

        <p style="font-size:14px;line-height:1.4em">
            Didn’t ask for this? Just ignore this message—your password
            remains unchanged.
        </p>

        <p style="margin-top:32px;font-size:14px;
                  color:#606060">
            — The Turo Team
        </p>

        <hr style="border:none;border-top:1px solid #e2e2e2;margin:32px 0">
    </div>
    HTML,
            /** ────── callback ────── */
            fn($m) => $m->from('turoapplication40@gmail.com', 'Turo App')
                ->to($user->email)
                ->subject('Your Turo one-time PIN')
        );

        return redirect('/pin')->with('success', 'A PIN has been sent to your e-mail: ' . $user->email);
    }

    public function verifyRecovery(Request $r)
    {
        $r->validate(['pin' => 'required|digits:6']);
        $userId = session('user_id');
        $row = UserPin::find($userId);

        if (!$row || $row->pin_code !== $r->pin || now('Asia/Manila')->gt($row->expires_at)) {
            return back()->with('error', 'Invalid or expired PIN');
        }

        session(['pin_ok' => true]);
        return redirect('/replace-password');
    }

    /* -------- view + save new password ----- */
    public function passwordForm()
    {
        abort_unless(session('pin_ok'), 403);
        return view('replace-password');            // /resources/views/replace-password.php
    }

    public function passwordSave(Request $r)
    {
        abort_unless(session('pin_ok'), 403);

        $r->validate([
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/[A-Z]/',      // upper-case
                'regex:/[a-z]/',      // lower-case
                'regex:/[0-9]/',      // digit
                'regex:/[@$!%*#?&]/', // symbol
            ],
        ], [
            'password.regex' => 'Password must contain upper-case, lower-case, number and symbol.'
        ]);

        $user = Users::findOrFail(session('user_id'));
        $user->update([
            'password_hash'          => Hash::make($r->password),
            'requires_password_change' => 0
        ]);

        // cleanup
        UserPin::where('user_id', $user->user_id)->delete();
        session()->forget('pin_ok');

        return redirect('/terms');                 // now ask for consent
    }
}
