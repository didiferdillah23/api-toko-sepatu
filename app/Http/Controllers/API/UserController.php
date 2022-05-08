<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Rules\Password;

class UserController extends Controller
{
    public function register(Request $req)
    {
        try{
            $req->validate([
                'name' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:255', 'unique:users'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'phone' => ['nullable', 'string', 'max:255'],
                'password' => ['required', 'string', new Password],
            ]);

            User::create([
                'name' => $req->name,
                'username' => $req->username,
                'email' => $req->email,
                'phone' => $req->phone,
                'password' => Hash::make($req->password)
            ]);

            $user = User::where('email', $req->email)->first();

            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success(
                [
                    'access_token' => $tokenResult,
                    'token_type' => 'Bearer',
                    'user' => $user
                ],
                'Berhasil registrasi'
            );
        }catch(Exception $e){
            return ResponseFormatter::error(
                [
                    'message' => 'Terjadi kesalahan',
                    'error' => $e
                ],
                'Autentikasi gagal', 500
            );
        }

    }

    public function login(Request $req)
    {
        try {
            $req->validate([
                'email' => 'email|required',
                'password' => 'required'
            ]);

            $credentials = request(['email', 'password']);
            if(!Auth::attempt($credentials)){
                return ResponseFormatter::error([
                    'message' => 'Unautorized',
                ], 'Autentikasi gagal', 500);
            }

            $user = User::where('email', $req->email)->first();

            if( !Hash::check($req->password, $user->password, []) ){
                throw new \Exception('invalid Credentials');
            }

            $tokenResult = $user->createToken('authToken')->plainTextToken;
            return ResponseFormatter::success(
                [
                    'access_token' => $tokenResult,
                    'token_type' => 'Bearer',
                    'user' => $user
                ],
                'Berhasil login'
            );
        } catch (Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Terjadi kesalahan',
                'error' => $e
            ], 'Autentikasi gagal', 500);
        }
    }

    public function fetch(Request $req)
    {
        return ResponseFormatter::success(
            $req->user(), 'Data profil user berhasil diambil'
        );
    }

    public function updateProfile(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'email' => 'email',
        ]);

        $user = Auth::user();

        if( !$validator->fails() ){
            $user->update($req->all());
            return ResponseFormatter::success($user, 'Berhasil mengupdate profile');
        }else{
            return ResponseFormatter::error(['message' => 'Gagal mengupdate profile'],'Validasi gagal', 500);
        }
        
    }
    
    public function logout(Request $req)
    {
        $token = $req->user()->currentAccessToken()->delete();
        return ResponseFormatter::success($token, 'Token revoked');
    }
}
