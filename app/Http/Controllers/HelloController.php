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

            if($tracer) {
                $childSpan = $tracer->spanBuilder('Child span')->startSpan();
                try {
                    throw new \Exception('Exception Example', 507);
                } catch (\Exception $exception) {
                    // echo "HelloController\\index: Exception: " . $exception->getCode() . "(" . $exception->getMessage() . ")" . PHP_EOL;
                    $childSpan->setStatus($exception->getCode(), $exception->getMessage());
                }
                $childSpan->activate();
                $grandchildSpan = $tracer->spanBuilder('Grandchild span')->startSpan();
                $grandchildSpan->setAttribute("Generation", 3);
                $grandchildSpan->end();
                $childSpan->end();
            }
        }

        return "Hello from the other side! - Adele";
    }
}
