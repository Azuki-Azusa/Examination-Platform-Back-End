<?php

namespace App\Http\Middleware;

use App\Maker;
use Closure;

class CheckMakerCookie
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $cookie = $request->cookie('sessionMaker');
        if (!$cookie) {
            $data = array('errcode' => 1, 'errmsg' => 'Cookie error');
            $req = array('errcode' => 200, 'errmsg' => null, 'data' => $data);
            return response($req);
        }
        $maker = new Maker();
        $maker_id = $maker->getIdBySession($cookie);
        if ($maker_id === 'default') {
            $data = array('errcode' => 2, 'errmsg' => 'Cookie error');
            $req = array('errcode' => 200, 'errmsg' => null, 'data' => $data);
            return response($req);
        }

        return $next($request);
    }
}
