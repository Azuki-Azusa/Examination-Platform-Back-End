<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Maker extends Model
{
    public function register($email, $password) {
        $pattern = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/';
        if (!preg_match($pattern,$email)) {
            $data = array('errcode' => 1, 'session' => null, 'errmsg' => 'illegal email address');
        }
        else if (18 < strlen($password)) {
            $data = array('errcode' => 1, 'session' => null, 'errmsg' => 'too long password');
        }
        else if (strlen($password) < 6) {
            $data = array('errcode' => 1, 'session' => null, 'errmsg' => 'too short password');
        }
        else {
            if (!$this->hasRegistered($email)) {
                $this->insert(
                    [
                    'email' => $email, 
                    'password' => $password,
                    ]
                );
                $session = $this->createSession($email, $password);
                $data = array('errcode' => 0, 'session' => $session, 'errmsg' => null);
            }
            else {
                $data = array('errcode' => 1, 'session' => null, 'errmsg' => 'been registered');
            }
        }

        return $data;
    }

    // return session
    public function verify($email, $password) {
        $id = $this->where([
            ['email', '=', $email],
            ['password', '=', $password]
        ])->value('id');
        if ($id) {
            $session =  $this->createSession($email, $password);
            $data = array('errcode' => 0, 'session' => $session, 'errmsg' => null);
        }
        else {
            $data = array('errcode' => 1, 'errmsg' => 'email or password incorrect');
        }
        return $data;
    }

    public function createSession($email, $password) {
        $session = md5(uniqid(microtime(true).$email.'asuka'.$password,true));
        session([$session => $this->getIdByEmail($email)]);
        return $session;
    }

    public function hasRegistered($email) {
        return $this->where('email', $email)->exists();
    }

    public function getIdByEmail($email) {
        return $this->where('email', $email)->value('id');
    }

    public function getIdBySession($session) {
        return session($session, 'default');
    }

}
