<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Api\User\ResetPasswordRequest;
use Session;
use App\Models\User;
use DB;
use Carbon\Carbon;
use Hash;

class ResetPasswordController extends Controller
{

    public function showResetForm(Request $request)
    {
        $token = $request->route()->parameter('token');
        return view('api.passwords.reset')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    public function submitResetPasswordForm(ResetPasswordRequest $request)
    {
        $checkEmailPassword = DB::table('password_resets')->where(['email' => $request->email,'token' => $request->token])->first();
        
        if(!$checkEmailPassword){
            return back()->withInput()->with('error', "Invalid token!");
        }

        User::where('email', $request->email)->update(['password' => Hash::make($request->password)]);

        $user = User::where('email', $request->email)->first();
        app()->setLocale($user->lang);

        DB::table('password_resets')->where(['email'=> $request->email])->delete();

        Session::flash('success', 'Your password has been reset successfully');
        return redirect()->route('api.password.reset.success');
    }
}
