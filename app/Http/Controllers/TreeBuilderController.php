<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TreeBuilderController extends Controller
{
    private int $sleepInUs = 2000;
    private function recursion($id, $currentDepth, $maxDepth) {
        if ($currentDepth > $maxDepth) {
            return;
        }
        global $tracer;
        $currentSpan = $tracer->spanBuilder('Child span('. $id . ')')->startSpan();
        $currentSpan->setAttribute('Generation', $currentDepth);
        $currentScope = $currentSpan->activate();
        usleep($this->sleepInUs);
        $this->recursion($id, $currentDepth + 1, $maxDepth);
        usleep($this->sleepInUs);
        $currentScope->detach();
        $currentSpan->end();
        return;
    }

    public function index($width = 3, $depth = 5){
        date_default_timezone_set('Asia/Kolkata');
        $date = date('d/m/Y h:i:s a', time());

        global $tracer, $rootSpan;
        if ($rootSpan) {
            /** @var Span $rootSpan */
            $rootSpan->setAttribute('Total Span Count', 1 + $width * $depth);
            $rootSpan->updateName('TreeBuilderController\\index dated ' . $date);

            if($tracer) {
                for ($i = 1; $i <= $width; $i++) {
                    $this->recursion($i, 1, $depth);
                }
            }
        }

        return 'And all that I can see, Is just a Complete Unary Tree - Software Fools Garden';
    }
}
