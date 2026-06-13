<?php

namespace ME\Erpaccount\Http\Controllers;

use Illuminate\Routing\Controller;

class AccountsTutorialController extends Controller
{
    public function index()
    {
        return view('erpaccount::phase4.accounts_tutorial.index');
    }
}