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
        if(!Auth::user()->emplid) {
            return abort(403);
        }
        return $this->canvas->getTerms();
    }
    
    
}
