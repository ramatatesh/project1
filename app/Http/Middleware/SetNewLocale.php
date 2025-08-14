<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetNewLocale
{

    public function handle(Request $request, Closure $next)
    {
        $locale = $request->header('Accept-Language');

        if (in_array($locale, ['ar', 'en'])) {
            App::setLocale($locale);
        } else {
            App::setLocale(config('app.locale'));
        }

        return $next($request);

    }
//                if(! in_array($request->segment(2),config('app.available_locales'))) {
//            abort(400);
//        }
//
//        App::setLocale($request->segment(2));
//        return $next($request);
//    }


}
