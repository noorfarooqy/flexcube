<?php

namespace Noorfarooqy\NoorAuth\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthServices extends NoorServices
{

    public function login($request)
    {
        $this->request = $request;
        $this->setResponseType();
        $this->rules = [
            'email' => 'required|email|max:125',
            'password' => 'required|string|max:125',
        ];

        $this->CustomValidate();

        if ($this->has_failed) {
            $this->setError($this->getMessage(), 40001);
            return $this->getResponse();
        }

        $data = $this->ValidatedData();

        $user = $user = User::where('email', $data['email'])->first();
        $is_authentic = Hash::check($data['password'], $user?->password);
        if ($is_authentic && Auth::attempt($data)) {
            $user = User::where('email', $data['email'])->first();
            $token = $this->createUserToken($user);
            $resp = [
                'user' => $user,
                'api_token' => $token->plainTextToken, // TO DO Set the scope for the token using user permissions
            ];

            $this->setError('', 0);
            $this->setSuccess('success');
            return $this->getResponse($resp);
        }

        $this->setError($m = "User email and password do not match ", 50001);
        //TO DO record auth failures
        return $this->getResponse();
    }
    public function register($request)
    {
        $this->request = $request;
        $this->setResponseType();
        $register_domain = config('noor-auth.register_domain');
        $this->rules = [
            'name' => 'required|string|max:45',
            'email' => 'required|email|max:125|regex:/(.*)' . $register_domain . '/i|unique:users',
            'password' => 'required|string|max:125|confirmed',
        ];

        $this->CustomValidate();

        if ($this->has_failed) {
            $this->setError($this->getMessage(), 40001);
            return $this->getResponse();
        }

        $data = $this->ValidatedData();
        $data['password'] = Hash::make($data['password']);

        try {
            $user = User::create($data);

            $token = $this->createUserToken($user);
            $resp = [
                'user' => $user,
                'api_token' => $token->plainTextToken, // TO DO Set the scope for the token using user permissions
            ];

            return $this->getResponse($resp);
        } catch (\Throwable $th) {
            $this->setError(env('APP_DEBUG') ? $th->getMessage() : 'Oops! Something went wrong. Could not create user', 50001);
            return $this->getResponse();
        }
    }

    public function createUserToken($user)
    {
        //TO DO set user token scope
        return $user?->createToken('auth_token');
    }
}
