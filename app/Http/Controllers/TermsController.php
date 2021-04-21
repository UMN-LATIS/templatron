<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Library\Canvas;
use Auth;

class TermsController extends Controller
{
    public $canvas = null;

    public function __construct() {
        $this->canvas = new Canvas(config("canvas.token"), config("canvas.url"), config("canvas.account"));
    }


    public function index() {
        Auth::user()->emplId = 2328381;

        if(!Auth::user()->emplId) {
            return abort(403);
        }
        return $this->canvas->getTerms();
    }
    
    
}
