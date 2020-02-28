<?php

namespace App;

use App\Exam;
use App\Candidate;
use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    

    public function getIdBySession($session)
    {
        return session($session, 'default');
    }
}
