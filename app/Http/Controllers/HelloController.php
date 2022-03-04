<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HelloController extends Controller
{
    public function index(){
        date_default_timezone_set('Asia/Kolkata');
        $date = date('d/m/Y h:i:s a', time());

        global $tracer, $rootSpan;
        if ($rootSpan) {
            /** @var Span $rootSpan */
            $rootSpan->setAttribute('foo', 'bar');
            $rootSpan->setAttribute('Kishan', 'Sangani');
            $rootSpan->setAttribute('foo', 'bar1');
            $rootSpan->updateName('HelloController\\index dated ' . $date);
        }

        return "Hello from the other side! - Adele";
    }
}
