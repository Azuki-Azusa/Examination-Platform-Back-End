<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    // 路由存在
    public function req($data) {
        return array('errcode' => 200, 'errmsg' => null, 'data' => $data);
    }
    
    // 获取MakerSession
    public function getCookie($request)
    {
        return $request->cookie('sessionMaker');
    }

    // 获取CandidateSession
    public function getCookie2($request)
    {
        return $request->cookie('sessionCandidate');
    }
}
