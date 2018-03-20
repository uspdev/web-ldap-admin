<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Adldap\Laravel\Facades\Adldap;
use Carbon\Carbon;

class indexController extends Controller
{
    public function __construct() {
       $this->middleware('auth')->except(['index']);
    }

    public function index(){
        return view('index');
    }
}
