<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Library\Canvas;

class TemplateController extends Controller
{

    private $canvas = null;


    public function __construct() {
        $this->canvas = new Canvas(config("canvas.token"), config("canvas.url"), config("canvas.account"));
    }

    public function index(Request $req) {

        $loadedTemplates = [];
        
        $templates = \App\Models\Template::all();
        if($accountId = $req->get("account_id")) {
            $account = $this->canvas->getAccount($accountId);
            if($account) {
                $templates = $templates->filter(function($value) use ($account){
                    return stristr($account->name, $value->match_prefix);
                }); 
            }
            
        }

        if($templates->count() == 0) {
            $templates = \App\Models\Template::all();
        }
        
        foreach($templates as $template) {
            $loadedTemplates[] = $this->canvas->getCourse($template->course_id);
        }


        return response()->json(["templates"=>$loadedTemplates]);
    }
}
