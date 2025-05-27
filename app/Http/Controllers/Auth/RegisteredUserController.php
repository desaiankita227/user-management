<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Jobs\SendMailJob;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole('agent');

        event(new Registered($user));

        Auth::login($user);

        // Dispatch a job to send a welcome email
        // SendMailJob::dispatch(
        //     $user->email,
        //     ['name' => $user->name],
        //     'emails.welcome',
        //     'Welcome to Our Platform'
        // );

        $data = array(
            'name'=>$user->name
        );
        $tomail = $user->email;
        $template = 'bookingrequest';
        $subject = 'You have Received a Request!';
        // Commonhelper::sendmail($tomail,$data,$template,$subject);
        dispatch(new SendMailJob($tomail,$data,$template,$subject));

        return redirect(RouteServiceProvider::HOME);
    }
}
