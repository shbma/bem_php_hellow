<?php
return function($bh){
    $bh->match('page', function($ctx) {
        //$ctx->tag('div');           	    
	    $ctx->content([
	        'elem' => 'inner',
	        'content' => $ctx->content()
	    ], true);
	    $ctx->attr('name', 'Ivan');
    });
};
