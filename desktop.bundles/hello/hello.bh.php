<?php
require_once __DIR__ . "/../../vendor/bem/bh/index.php";
$bh = new \BEM\BH();
$bh->setOptions(json_decode("{\"jsAttrName\":\"data-bem\",\"jsAttrScheme\":\"json\"}", 1));
// file: ../../libs/bem-core-php/common.blocks/ua/ua.bh.php
    $bh->match('ua', function ($ctx) {
        $ctx
            ->bem(false)
            ->tag('script')
            ->content([
                '(function(e,c){',
                    'e[c]=e[c].replace(/(ua_js_)no/g,"$1yes");',
                '})(document.documentElement,"className");'
            ], true);
    });

// file: ../../libs/bem-core-php/common.blocks/page/__css/page__css.bh.php
    $bh->match('page__css', function ($ctx, $json) {
        $ctx->bem(false);

        if($json->url) {
            $ctx
                ->tag('link')
                ->attr('rel', 'stylesheet')
                ->attr('href', $json->url);
        } else {
            $ctx->tag('style');
        }
    });

// file: ../../libs/bem-core-php/desktop.blocks/page/__css/page__css.bh.php
    $bh->match('page__css', function ($ctx, $json) {
        if (!key_exists('ie', $json)) {
            return;
        }
        $ie = $json->ie;
        if ($ie === true) {
            $url = $json->url;
            return array_map(function ($v) use ($url) {
                return [ 'elem' => 'css', 'url' => $url . '.ie' . $v . '.css', 'ie' => 'IE ' . $v ];
            }, [6, 7, 8, 9]);
        } else {
            $hideRule = !$ie?
                ['gt IE 9', '<!-->', '<!--'] :
                ($ie === '!IE'?
                    [$ie, '<!-->', '<!--'] :
                    [$ie, '', '']);
            return [
                '<!--[if ' . $hideRule[0] . ']>' . $hideRule[1],
                $json,
                $hideRule[2] . '<![endif]-->'
            ];
        }
    });

// file: ../../libs/bem-core-php/common.blocks/page/__js/page__js.bh.php
    $bh->match('page__js', function ($ctx, $json) {
        $nonce = $ctx->tParam('nonceCsp');
        $ctx
            ->bem(false)
            ->tag('script');

        if ($json->url) {
            $ctx->attr('src', $json->url);
        } else {
            $ctx->attr('nonce', $nonce);
        }
    });

// file: ../../desktop.blocks/hello/hello.bh.php
    $bh->match('hello', function($ctx) {
        $ctx->tag('form');
        $ctx->js(true);         	    	    
    });

// file: ../../libs/bem-components-php/common.blocks/input/input.bh.php
    $bh->match('input', function($ctx, $json) {
        $ctx
            ->tag('span')
            ->js(true)
            ->tParam('_input', $json)
            ->content([ 'elem' => 'box', 'content' => [ 'elem' => 'control' ] ], true);
    });

// file: ../../libs/bem-components-php/common.blocks/input/__box/input__box.bh.php
    $bh->match('input__box', function($ctx) {
        $ctx->tag('span');
    });

// file: ../../libs/bem-components-php/common.blocks/input/__control/input__control.bh.php
    $bh->match('input__control', function($ctx) {
        $ctx->tag('input');

        $input = $ctx->tParam('_input');
        $attrs = [
            'id' => $input->id,
            'name' => $input->name,
            'value' => $input->val,
            'maxlength' => $input->maxLength,
            'tabindex' => $input->tabIndex,
            'placeholder' => $input->placeholder
        ];

        $input->autocomplete === false && ($attrs['autocomplete'] = 'off');

        if(isset($input->mods) && $input->mods->disabled) {
            $attrs['disabled'] = 'disabled';
        }

        $ctx->attrs($attrs);
    });

// file: ../../libs/bem-components-php/common.blocks/button/button.bh.php
    $bh->match('button', function($ctx, $json) {
        $ctx->tag($json->tag ?: 'button'); // NOTE: need to predefine tag

        $json->icon = $ctx->phpize($json->icon);
        $modType = $ctx->mod('type');
        $isRealButton = ($ctx->tag() === 'button')
            && (!$modType || $modType === 'submit');

        $ctx
            ->tParam('_button', $json)
            ->js(true)
            ->attrs([
                'role' => 'button',
                'tabindex' => $json->tabIndex,
                'id' => $json->id,
                'type' => $isRealButton? ($modType ?: 'button') : null,
                'name' => $json->name,
                'value' => $json->val,
                'title' => $json->title
            ])
            ->mix([ 'elem' => 'control' ]); // NOTE: satisfy interface of `control`

        if ($ctx->mod('disabled')) {
            $isRealButton ?
                $ctx->attr('disabled', 'disabled')
                : $ctx->attr('aria-disabled', 'true');
        }

        $content = $ctx->content();
        if ($content === null) {
            $content = [$json->icon];
            key_exists('text', $json) && ($content[] = ['elem' => 'text', 'content' => $json->text]);
            $ctx->content($content);
        }
    });

// file: ../../libs/bem-components-php/common.blocks/button/_focused/button_focused.bh.php
    $bh->match('button_focused', function($ctx, $json) {
        $ctx->js($ctx->extend($json->js, [ 'live' => false ]), true);
    });

// file: ../../libs/bem-components-php/common.blocks/button/__text/button__text.bh.php
    $bh->match('button__text', function($ctx) {
        $ctx->tag('span');
    });

// file: ../../libs/bem-core-php/common.blocks/page/page.bh.php
    $bh->match('page', function ($ctx, $json) {
        $ctx
            ->tag('body')
            ->tParam('nonceCsp', $json->nonce)
            ->content([
                $ctx->content(),
                $json->scripts
            ], true);

        return [
            $json->doctype ?: '<!DOCTYPE html>',
            [
                'tag' => 'html',
                'cls' => 'ua_js_no',
                'content' => [
                    [
                        'elem' => 'head',
                        'content' => [
                            ['tag' => 'meta', 'attrs' => ['charset' => 'utf-8']],
                            $json->uaCompatible === false? '' : [
                                'tag' => 'meta',
                                'attrs' => [
                                    'http-equiv' => 'X-UA-Compatible',
                                    'content' => $json->uaCompatible ?: 'IE=edge'
                                ]
                            ],
                            ['tag' => 'title', 'content' => $json->title],
                            ['block' => 'ua', 'attrs' => ['nonce' => $json->nonce]],
                            $json->head,
                            $json->styles,
                            $json->favicon ? ['elem' => 'favicon', 'url' => $json->favicon] : '',
                        ]
                    ],
                    $json
                ]
            ]
        ];
    });

    $bh->match('page__head', function ($ctx) {
        $ctx->bem(false)->tag('head');
    });

    $bh->match('page__meta', function ($ctx) {
        $ctx->bem(false)->tag('meta');
    });

    $bh->match('page__link', function ($ctx) {
        $ctx->bem(false)->tag('link');
    });

    $bh->match('page__favicon', function ($ctx, $json) {
        $ctx
            ->bem(false)
            ->tag('link')
            ->attr('rel', 'shortcut icon')
            ->attr('href', $json->url);
    });

// file: ../../desktop.blocks/page/page.bh.php
    $bh->match('page', function($ctx) {
        //$ctx->tag('div');           	    
	    $ctx->content([
	        'elem' => 'inner',
	        'content' => $ctx->content()
	    ], true);
	    $ctx->attr('name', 'Ivan');
    });

return $bh;