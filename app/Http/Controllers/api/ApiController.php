<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Contact;
use App\Rules\MatchOldPassword;

class ApiController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|max:50|min:2',
            'lastName' => 'required|max:50|min:2',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'regex:/^(?=(?:\D*\d\D*){8,14}$)[- \d()+]*/|min:10|max:30',
            'password' => 'required|min:8',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'validation_error' => $validator->messages()
            ]);
        } else {
            $token = Str::random(60);
            $user = User::create([
                'firstName' => $request->firstName,
                'lastName' => $request->lastName,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'api_token' => $token,
                'role' => (User::where('id', 1)) ? 1 : 0,
            ]);
        }
        return response()->json([
            'status' => 200,
            'username' => $user->name(),
            'role' => $user->roleName(),
            'token' => $token,
            'message' => 'User Regestered Successfully!'
        ]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'password' => 'required|min:8',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'validation_error' => $validator->messages()
            ]);
        } else {
            $token = Str::random(60);
            $user = User::where('email', $request->email)->first();
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => 401,
                    'message' => 'Invalid Credentials !'
                ]);
            } else {
                $token = Str::random(60);
                $user->update([
                    'api_token' => $token,
                ]);

                return response()->json([
                    'status' => 200,
                    'username' => $user->name(),
                    'role' => $user->roleName(),
                    'token' => $token,
                    'message' => 'Welcome ' . $user->name()
                ]);
            }
        }
    }

    public function logout(Request $request)
    {
        $user = User::where('api_token', $request->api_token)->first()->update([
            'api_token' => Str::random(60)
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'logged Out'
        ]);
    }

    public function profile(Request $request)
    {
        $user = User::find(auth()->user()->id);
        return response()->json([
            'status' => 200,
            'firstName' => $user->firstName,
            'lastName' => $user->lastName,
            'phone' => $user->phone,
            'email' => $user->email,
            'role' => $user->roleName(),

        ]);
    }

    public function profileUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|max:50|min:2',
            'lastName' => 'required|max:50|min:2',
            'phone' => 'regex:/^(?=(?:\D*\d\D*){8,14}$)[- \d()+]*/|min:10|max:30',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'validation_error' => $validator->messages()
            ]);
        } else {
            User::find(auth()->user()->id)->update([
                'firstName' => $request->firstName,
                'lastName' => $request->lastName,
                'phone' => $request->phone,
            ]);
            return response()->json([
                'status' => 200,
                'message' => 'User profile Updated Successfully!'
            ]);
        }
    }

    public function password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'currentPassword' => ['required', new MatchOldPassword],
            'password' => ['required', 'max:50', 'min:8'],
        ]);


        if ($validator->fails()) {
            return response()->json([
                'status' => 401,
                'validation_error' => $validator->messages()
            ]);
        } else {
            User::find(auth()->user()->id)->update(['password' => Hash::make($request->password)]);
            return response()->json([
                'status' => 200,
                'message' => 'password changed successfuly'
            ]);
        }
    }

    public function myContacts(Request $request)
    {
        $myContacts = Contact::where('user_id', auth()->user()->id)->get()->toArray();
        return response()->json([
            'status' => 200,
            'data' => $myContacts
        ]);
    }

    public function addContact(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:100|min:3',
            'phone' => 'required|regex:/^(?=(?:\D*\d\D*){8,14}$)[- \d()+]*/|min:10|max:30',
            'note' => 'max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 401,
                'validation_error' => $validator->messages()
            ]);
        } else {
            Contact::create([
                'user_id' => auth()->user()->id,
                'name' => $request->name,
                'phone' => $request->phone,
                'note' => $request->note,
            ]);
            return response()->json([
                'status' => 200,
                'message' => 'Contact Added Successfuly'
            ]);
        }
    }
    public function getUsers()
    {
        if (auth()->user()->role === 1) {
            return response()->json([
                'status' => 200,
                'data' => User::select('firstName', 'lastName', 'email', 'phone', 'role')->withCount(['contacts'])->get()
            ]);
        } else {
            return response()->json([
                'status' => 401,
                'message' => 'This Data Need Admin Role !'
            ]);
        }
    }

    public function getUserContacts()
    {
        if (auth()->user()->role === 1) {
            return response()->json([
                'status' => 200,
                'data' => User::select('id', 'firstName', 'lastName', 'email', 'phone', 'role')->with('contacts')->get()
            ]);
        } else {
            return response()->json([
                'status' => 401,
                'message' => 'This Data Need Admin Role !'
            ]);
        }
    }
}
