<?php

namespace App\Http\Middleware;

use App\Candidate;
use Closure;

class CheckCandidateCookie
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
        $cookie = $request->cookie('sessionCandidate');
        if (!$cookie) {
            $data = array('errcode' => 1, 'errmsg' => 'Cookie error');
            $req = array('errcode' => 200, 'errmsg' => null, 'data' => $data);
            return response($req);
        }
        $candidate  = new Candidate();
        $candidate_id = $candidate->getCandidateIdBySession($cookie);
        if ($candidate_id === 'default') {
            $data = array('errcode' => 2, 'errmsg' => 'Cookie error');
            $req = array('errcode' => 200, 'errmsg' => null, 'data' => $data);
            return response($req);
        }

        return $next($request);
    }
}
