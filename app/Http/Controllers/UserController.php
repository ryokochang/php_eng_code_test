<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->save();

        return response()->json(['success'=>'User register successfully.']);
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
        ]);


        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $this->authorize('allowLogin', User::class);
            return redirect()->intended('home');
        }
    }

    public function status(Request $request)
    {
        $this->authorize('allowAccess', User::class);

        $this->validate($request, [
            'id' => 'required|exists:users,id',
            'status' => ['required', Rule::in(['rejected', 'approved'])],
        ]);


        $user = User::find($request->id);
        $user->status = $request->status;
        $user->save();
        return response()->json(['success'=>'User status changed successfully.']);
    }
}
