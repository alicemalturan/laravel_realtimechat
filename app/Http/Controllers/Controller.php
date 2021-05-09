<?php

namespace App\Http\Controllers;

use App\Events\Chat;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public function login(Request $req){
        if (Auth::attempt(['username'=>$req->username,'password'=>$req->password])){
            return response(['ok'],200);
        }
        return response(['no'],403);
    }
    public function register(Request $req){
        return response(User::insert([
            'username'=>$req->username,
            'password'=>bcrypt($req->password),
        ]));
    }

}
