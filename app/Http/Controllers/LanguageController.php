<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;

class LanguageController extends Controller
{
    public function switchLanguage($locale)
    {
        $defaultLocale = config('app.locale', 'ar');

        if (! in_array($locale, ['ar', 'en'], true)) {
            $locale = $defaultLocale;
        }

        Session::put('locale', $locale);

        App::setLocale($locale);

        return Redirect::back();
    }
}
