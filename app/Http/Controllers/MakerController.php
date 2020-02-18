<?php

namespace App\Http\Controllers;

use App\Maker;
use Illuminate\Http\Request;

class MakerController extends Controller
{
    // maker register
    public function register(Request $request) {
        $email = $request->input('email');
        $password = $request->input('password');
        $maker = new Maker();
        $data = $maker->register($email, $password);
        return $this->req($data);
    }

    // maker login
    public function login($email, $password) {
        $maker = new Maker();
        $data = $maker->verify($email, $password);
        return $this->req($data);
    }

    // 路由存在
    public function req($data) {
        return array('errcode' => 200, 'errmsg' => null, 'data' => $data);
    }
}