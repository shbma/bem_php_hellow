<?
return function($bh){
    $bh->match('hello', function($ctx) {
        $ctx->tag('form');
        $ctx->js(true);         	    	    
    });
};
