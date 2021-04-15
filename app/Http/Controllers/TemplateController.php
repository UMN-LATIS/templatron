<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Library\Canvas;

class TemplateController extends Controller
{

    private $canvas = null;
    private $templates = [
        228353
    ];

    public function __construct() {
        $this->canvas = new Canvas(config("canvas.token"), config("canvas.url"), config("canvas.account"));
    }

    public function index() {

        $loadedTemplates = [];
        foreach($this->templates as $template) {
            $loadedTemplates[] = $this->canvas->getCourse($template);
        }


        return response()->json(["templates"=>$loadedTemplates]);
    }
}
