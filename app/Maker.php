<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Maker extends Model
{
    public function register($email, $password) {
        if (!$this->hasRegistered($email)) {
            $pattern = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/';
            if (!preg_match($pattern,$email)) {
                return json_encode(array('result' => 0, 'message' => 'illegal'));
            }
            else if (18 < strlen($password)) {
                return json_encode(array('result' => 0, 'message' => 'too long'));
            }
            else if (strlen($password) < 6) {
                return json_encode(array('result' => 0, 'message' => 'too short'));
            }
            else {
                $this->insert(
                    [
                    'email' => $email, 
                    'password' => $password,
                    'name' => '',
                    'introduction' => ''
                    ]
                );
                $session = md5(uniqid(microtime(true).$email.'asuka'.$password,true));
                session([$session => $this->getId($email)->id]);
                return json_encode(array('result' => 1, 'session' => $session));
            }
            
        }
        else {
            return json_encode(array('result' => 0, 'session' => 'hasRegistered'));
        }
    }

    public function hasRegistered($email) {
        return $this->where('email', $email)->exists();
    }

    public function getIdByEmail($email) {
        return $this->where('email', $email)->first();
    }

    public function getIdBySession($session) {
        return session($session, 'default');
    }
}
