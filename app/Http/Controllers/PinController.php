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
        abort_unless($user->role_id == 1 && $user->requires_password_change, 403);

        $pin = UserPin::issueFor($user->user_id);

        /* very tiny mailer (uses Laravel's default SMTP settings) */
        Mail::raw(
            "Your Turo password-reset PIN is: $pin   (valid for 10 minutes)",
            fn($m) => $m->from('turoapplication40@gmail.com', 'Turo App')
                        ->to($user->email)
                        ->subject('Turo password-reset PIN')
        );

        return back()->with('success', 'A PIN has been sent to your e-mail');
    }

    /* -------- verify the pin --------------- */
    public function verify(Request $r)
    {
        $r->validate(['pin' => 'required|digits:6']);

        $userId = session('user_id');               // already logged in
        $row    = UserPin::find($userId);

        if (!$row || $row->pin_code !== $r->pin || now('Asia/Manila')->gt($row->expires_at)) {
            return back()->with('error','Invalid or expired PIN');
        }

        // mark session so we can show the password form
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
            'password' => 'required|confirmed|min:8'
        ]);

        $user = Users::findOrFail(session('user_id'));
        $user->update([
            'password_hash'          => Hash::make($r->password),
            'requires_password_change'=> 0
        ]);

        // cleanup
        UserPin::where('user_id',$user->user_id)->delete();
        session()->forget('pin_ok');

        return redirect('/terms');                 // now ask for consent
    }
}
