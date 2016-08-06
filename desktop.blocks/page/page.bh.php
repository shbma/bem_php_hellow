return function($bh){
    $bh->match('page', function($ctx) {
        //$ctx->tag('div');           	    
	    $ctx->content([
	        'elem' => 'inner',
	        'content' => $ctx->json()
	    ], true);
	    $ctx->attr('name', 'Ivan');
    });
};
