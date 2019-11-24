<?php

namespace App\Http\Controllers;

use App\Maker;
use Illuminate\Http\Request;

class MakerController extends Controller
{
    // maker register
    public function register($email, $password) {
        $maker = new \App\Maker();
        $state =  $maker->register($email, $password);
        return $state;
    }

    public function getID($session) {
        $maker = new \App\Maker();
        $id = $maker->getIdBySession($session);
        return $id;
    }
}