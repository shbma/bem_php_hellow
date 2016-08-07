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

// file: ../../libs/bem-core-php/common.blocks/ua/__svg/ua__svg.bh.php
    $bh->match('ua', function ($ctx, $json) {
        $ctx->applyBase();
        $ctx->content([
            $json->content,
            '(function(d,n){',
                'd.documentElement.className+=',
                '" ua_svg_"+(d[n]&&d[n]("http://www.w3.org/2000/svg","svg").createSVGRect?"yes":"no");',
            '})(document,"createElementNS");'
        ], true);
    });

// file: ../../libs/bem-components-php/common.blocks/link/link.bh.php
    $bh->match('link', function($ctx, $json) use ($bh) {
        $ctx
            ->tag('a')
            ->mix([ 'elem' => 'control' ]); // satisfy interface of `control`

        $url = !$ctx->isSimple($json->url)? // url could contain bemjson
            $bh->apply($json->url) :
            $json->url;
        $attrs = [ 'role' => 'link' ];

        $tabIndex = null;
        if (!$ctx->mod('disabled')) {
            if($url) {
                $attrs['href'] = $url;
                $tabIndex = $json->tabIndex;
            } else {
                $tabIndex = $json->tabIndex ?: 0;
            }
            $ctx->js(true);
        } else {
            $ctx->js($url? [ 'url' => $url ] : true);
            $attrs['aria-disabled'] = 'true';
        }


        ($tabIndex === null) || ($attrs['tabindex'] = $tabIndex);

        $json->title && ($attrs['title'] = $json->title);
        $json->target && ($attrs['target'] = $json->target);

        $ctx->attrs($attrs);
    });

// file: ../../libs/bem-components-php/common.blocks/menu/menu.bh.php
    $bh->match('menu', function($ctx, $json) {
        $mods = $ctx->mods();
        $attrs = [ 'role' => 'menu' ];

        $ctx
            ->js(true)
            ->tParam('menuMods', $mods)
            ->mix([ 'elem' => 'control' ]);

        $mods->disabled?
            $attrs['aria-disabled'] = 'true' :
            $attrs['tabindex'] = 0;

        $ctx->attrs($attrs);

        $refs = new stdClass();
        $refs->firstItem = null;
        $refs->checkedItems = [];

        if($json->content) {
            $isValDef = key_exists('val', $json);
            $isModeCheck = $ctx->mod('mode') === 'check';

            $containsVal = function ($val) use ($isValDef, $isModeCheck, $json) {
                return $isValDef &&
                    ($isModeCheck?
                        is_array($json->val) && in_array($val, $json->val) :
                        $json->val === $val);
            };

            $iterateItems = function (&$content) use (&$iterateItems, $containsVal, $refs) {
                foreach ($content as $_ => $itemOrGroup) {
                    if (!$itemOrGroup) {
                        break;
                    }
                    // menu__group
                    if ($itemOrGroup->block !== 'menu-item') {
                        if ($itemOrGroup->content) {
                            $iterateItems($itemOrGroup->content);
                        }
                        continue;
                    }

                    $refs->firstItem || ($refs->firstItem =& $content[$_]);
                    if ($containsVal($itemOrGroup->val)) {
                        $itemOrGroup->mods->checked = true;
                        $refs->checkedItems[] =& $content[$_];
                    }
                }
            };

            if(is_array($json->content)) throw new \Exception('menu: content must be an array of the menu items');

            $iterateItems($json->content);
        }

        $ctx
            ->tParam('refs', $refs)
            ->tParam('firstItem', $refs->firstItem)
            ->tParam('checkedItems', $refs->checkedItems);
    });

// file: ../../libs/bem-components-php/common.blocks/menu-item/menu-item.bh.php
    $bh->match('menu-item', function($ctx, $json) {
        $menuMods = (array)$ctx->tParam('menuMods');
        $menuMode = @$menuMods['mode'];
        $role = $menuMode?
            ($menuMode === 'check' ? 'menuitemcheckbox' : 'menuitemradio') :
            'menuitem';

        empty($menuMods) || $ctx->mods([
            'theme' => @$menuMods['theme'],
            'disabled' => @$menuMods['disabled']
        ]);

        $ctx
            ->js([ 'val' => $json->val ])
            ->attrs([
                'role' => $role,
                'id' => $json->id ?: $ctx->generateId(),
                'aria-disabled' => $ctx->mod('disabled') ? 'true' : null,
                'aria-checked' => $menuMode ? ($ctx->mod('checked')? 'true' : 'false') : null
            ]);
    });

// file: ../../libs/bem-components-php/common.blocks/menu/_focused/menu_focused.bh.php
    $bh->match('menu_focused', function($ctx) {
        $js = $ctx->extend($ctx->js() ?: [], [ 'live' => false ]);
        $ctx->js($js);
    });

// file: ../../libs/bem-components-php/common.blocks/menu/__group/menu__group.bh.php
    $bh->match('menu__group', function($ctx, $json) {
        $ctx->attr('role', 'group');

        if(key_exists('title', $json)) {
            $title = $json->title;
            $titleId = $ctx->generateId();
            $ctx
                ->attr('aria-labelledby', $titleId)
                ->content([
                    [
                        'elem' => 'group-title',
                        'attrs' => [
                            'role' => 'presentation',
                            'id' => $titleId
                        ],
                        'content' => $title
                    ],
                    $ctx->content()
                ], true);
        }
    });

// file: ../../libs/bem-components-php/common.blocks/icon/icon.bh.php
    $bh->match('icon', function($ctx, $json) {
        $attrs = [];
        $url = $json->url;
        if($url) $attrs['style'] = 'background-image:url(' . $url . ')';
        $ctx
            ->tag('span')
            ->attrs($attrs);
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

// file: ../../libs/bem-components-php/common.blocks/input/_has-clear/input_has-clear.bh.php
    $bh->match('input_has-clear__box', function($ctx) {
        $ctx->content([$ctx->content(), [ 'elem' => 'clear' ]], true);
    });

// file: ../../libs/bem-components-php/common.blocks/input/__clear/input__clear.bh.php
    $bh->match('input__clear', function($ctx) {
        $ctx->tag('span');
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

// file: ../../libs/bem-components-php/common.blocks/spin/spin.bh.php
    $bh->match('spin', function($ctx) {
        $ctx->tag('span');
    });

// file: ../../libs/bem-components-php/common.blocks/radio-group/radio-group.bh.php
    $bh->match('radio-group', function($ctx, $json) {
        $ctx
            ->tag('span')
            ->attrs([ 'role' => 'radiogroup' ])
            ->js(true)
            ->mix([ 'block' => 'control-group' ]);

        $mods = $ctx->mods();
        $isValDef = key_exists('val', $json);
        $content = [];

        foreach ($json->options as $i => $option) {
            if ($i && !$mods->type) {
                $content[] = [ 'tag' => 'br' ];
            }
            $content[] = [
                'block' => 'radio',
                'mods' => [
                    'type' => $mods->type,
                    'mode' => $mods->mode,
                    'theme' => $mods->theme,
                    'size' => $mods->size,
                    'checked' => $isValDef && $json->val === @$option['val'],
                    'disabled' => @$option['disabled'] ?: $mods->disabled
                ],
                'name' => $json->name,
                'val' => @$option['val'],
                'text' => @$option['text'],
                'title' => @$option['title'],
                'icon' => @$option['icon']
            ];
        }

        $ctx->content($content);
    });

// file: ../../libs/bem-components-php/common.blocks/radio/radio.bh.php
    $bh->match('radio', function($ctx, $json) {
        $ctx
            ->tag('label')
            ->js(true)
            ->content([
                [
                    'elem' => 'box',
                    'content' => [
                        'elem' => 'control',
                        'checked' => $ctx->mod('checked'),
                        'disabled' => $ctx->mod('disabled'),
                        'name' => $json->name,
                        'val' => $json->val
                    ]
                ],
                $json->text? [
                    'elem' => 'text',
                    'content' => $json->text
                ] : ''
            ]);
    });

// file: ../../libs/bem-components-php/common.blocks/radio/__box/radio__box.bh.php
    $bh->match('radio__box', function($ctx) {
        $ctx->tag('span');
    });

// file: ../../libs/bem-components-php/common.blocks/radio/__control/radio__control.bh.php
    $bh->match('radio__control', function($ctx, $json) {
        $ctx->tag('input');

        // NOTE: don't remove autocomplete attribute, otherwise js and DOM may be desynced
        $attrs = [
            'type' => 'radio',
            'autocomplete' => 'off',
            'name' => $json->name,
            'value' => $json->val
        ];

        $json->checked && ($attrs['checked'] = 'checked');
        $json->disabled && ($attrs['disabled'] = 'disabled');

        $ctx->attrs($attrs);
    });

// file: ../../libs/bem-components-php/common.blocks/radio/__text/radio__text.bh.php
    $bh->match('radio__text', function($ctx) {
        $ctx
            ->tag('span')
            ->attrs([ 'role' => 'presentation' ]);
    });

// file: ../../libs/bem-components-php/common.blocks/button/_togglable/button_togglable.bh.php
    $bh->match(['button_togglable_check', 'button_togglable_radio'], function($ctx) {
        $ctx->attr('aria-pressed', $ctx->mod('checked')? 'true' : 'false');
    });

// file: ../../libs/bem-components-php/common.blocks/checkbox/checkbox.bh.php
    $bh->match('checkbox', function($ctx, $json) {
        $ctx->tag('label')
            ->js(true)
            ->content([
                [
                    'elem' => 'box',
                    'content' => [
                        'elem' => 'control',
                        'checked' => $ctx->mod('checked'),
                        'disabled' => $ctx->mod('disabled'),
                        'name' => $json->name,
                        'val' => $json->val
                    ]
                ],
                $json->text ? [
                    'elem' => 'text',
                    'content' => $json->text
                ] : ''
            ]);
    });

// file: ../../libs/bem-components-php/common.blocks/checkbox/__box/checkbox__box.bh.php
    $bh->match('checkbox__box', function($ctx) {
        $ctx->tag('span');
    });

// file: ../../libs/bem-components-php/common.blocks/checkbox/__control/checkbox__control.bh.php
    $bh->match('checkbox__control', function($ctx, $json) {
        $ctx->tag('input');

        // NOTE: don't remove autocomplete attribute, otherwise js and DOM may be desynced
        $attrs = [ 'type' => 'checkbox', 'autocomplete' => 'off' ];

        $attrs['name'] = $json->name;
        $attrs['value'] = $json->val;
        $json->checked && ($attrs['checked'] = 'checked');
        $json->disabled && ($attrs['disabled'] = 'disabled');

        $ctx->attrs($attrs);
    });

// file: ../../libs/bem-components-php/common.blocks/checkbox/__text/checkbox__text.bh.php
    $bh->match('checkbox__text', function($ctx) {
        $ctx
            ->tag('span')
            ->attrs([ 'role' => 'presentation' ]);
    });

// file: ../../libs/bem-components-php/common.blocks/checkbox/_type/checkbox_type_button.bh.php
    $bh->match('checkbox_type_button', function($ctx, $json) {
        $mods = $ctx->mods();

        $ctx->content([[
            'block' => 'button',
            'mods' => [
                'togglable' => 'check',
                'checked' => $mods->checked,
                'disabled' => $mods->disabled,
                'theme' => $mods->theme,
                'size' => $mods->size
            ],
            'attrs' => [
                'role' => 'checkbox',
                'aria-pressed' => null,
                'aria-checked' => $mods->checked? 'true' : 'false'
            ],
            'title' => $json->title,
            'content' => [
                $json->icon,
                key_exists('text', $json)?
                    [ 'elem' => 'text', 'content' => $json->text ] :
                    ''
            ]
        ], [
            'block' => 'checkbox',
            'elem' => 'control',
            'checked' => $mods->checked,
            'disabled' => $mods->disabled,
            'name' => $json->name,
            'val' => $json->val
        ]]);
    });

// file: ../../libs/bem-components-php/common.blocks/dropdown/dropdown.bh.php
    $bh->match([
        'dropdown' => function($ctx) {
            $dropdown = $ctx->json();

            $mix = $ctx->phpize([$dropdown]);
            if (is_array($dropdown->switcher) && array_key_exists('mix', $dropdown->switcher)) {
                $mix->append($dropdown->switcher['mix']);
            }
            if (key_exists('mix', $dropdown)) $mix->append($dropdown->mix);

            $ctx
                ->js($ctx->extend([ 'id' => $ctx->generateId() ], $ctx->js()))
                ->tParam('dropdown', $dropdown)
                ->tParam('popupId', $ctx->generateId())
                ->tParam('theme', $ctx->mod('theme'))
                ->tParam('mix', $mix);

            return [[ 'elem' => 'switcher' ], [ 'elem' => 'popup' ]];
        },

        'dropdown__popup' => function($ctx) {
            $dropdown = $ctx->tParam('dropdown');
            $popup = $dropdown->popup;

            if ($ctx->isSimple($popup) || (@$popup['block'] ?: @$popup->block) !== 'popup') {
                $popup = [ 'block' => 'popup', 'content' => $popup ];
            }

            $dropdown->popup = $popup = $ctx->phpize($popup);
            if (empty($popup->attrs)) $popup->attrs = [];

            $popupMods = $popup->mods;
            $popupAttrs = &$popup->attrs;

            $popupMods->theme || ($popupMods->theme = $ctx->tParam('theme'));
            key_exists('autoclosable', $popupMods) || ($popupMods->autoclosable = true);

            $popupMods->target = 'anchor';
            $popupAttrs['id'] = $ctx->tParam('popupId');

            $popup->mix = $ctx->phpize([$dropdown, $popup->mix]);

            return $popup;
        },

        'dropdown__switcher' => function($ctx) {
            $dropdown = $ctx->tParam('dropdown');
            $dropdown->switcher = $switcher = $ctx->phpize($dropdown->switcher);

            if (key_exists('block', $switcher)) $swticher->mix = $ctx->tParam('mix');

            return $switcher;
        }
    ]);

// file: ../../libs/bem-components-php/common.blocks/popup/popup.bh.php
    $bh->match('popup', function($ctx, $json) {
        $ctx
            ->js([
                'mainOffset' => $json->mainOffset,
                'secondaryOffset' => $json->secondaryOffset,
                'viewportOffset' => $json->viewportOffset,
                'directions' => $json->directions,
                'zIndexGroupLevel' => $json->zIndexGroupLevel
            ])
            ->attrs([ 'aria-hidden' => 'true' ]);
    });

// file: ../../libs/bem-components-php/common.blocks/dropdown/_switcher/dropdown_switcher_button.bh.php
    $bh->match('dropdown_switcher_button__switcher', function($ctx, $json) {
        $dropdown = $ctx->tParam('dropdown');
        $switcher = $dropdown->switcher;

        // if (Array.isArray(content)) return content
        if ($ctx->isArray($switcher)) { // php!yolo. bug?
            return $switcher;
            // if (count($content) > 1) return $content;
            // $content = $content[0];
        }

        $res = $ctx->isSimple($switcher)?
            [ 'block' => 'button', 'text' => $switcher ] :
            $switcher;

        $res = $ctx->phpize($res);
        if (empty($res->attrs)) $res->attrs = [];

        if ($res->block === 'button') {
            $resMods = $res->mods;
            $resAttrs = &$res->attrs;
            $dropdownMods = $json->blockMods ?: $json->mods;
            $resMods->size || ($resMods->size = $dropdownMods->size);
            $resMods->theme || ($resMods->theme = $dropdownMods->theme);
            $resMods->disabled = $dropdownMods->disabled;

            $resAttrs['aria-haspopup'] = 'true';
            $resAttrs['aria-controls'] = $ctx->tParam('popupId');
            $resAttrs['aria-expanded'] = 'false';

            $res->mix = $ctx->tParam('mix');
        }

        return $res;
    });

// file: ../../libs/bem-components-php/common.blocks/dropdown/_switcher/dropdown_switcher_link.bh.php
    $bh->match('dropdown_switcher_link__switcher', function($ctx, $json) {
        $dropdown = $ctx->tParam('dropdown');
        $switcher = $dropdown->switcher;

        // $content = $ctx->content();
        // if (Array.isArray(content)) return content
        if ($ctx->isArray($switcher)) { // php!yolo. bug?
            return $switcher;
            // if (count($content) > 1) return $content;
            // $content = $content[0];
        }

        $res = $ctx->isSimple($switcher)?
            [ 'block' => 'link', 'mods' => [ 'pseudo' => true ], 'content' => $switcher ] :
            $switcher;

        $res = $ctx->phpize($res);
        if (empty($res->attrs)) $res->attrs = [];

        if ($res->block === 'link') {
            $resMods = $res->mods;
            $resAttrs = &$res->attrs;
            $dropdownMods = $json->blockMods ?: $json->mods;
            $resMods->theme || ($resMods->theme = $dropdownMods->theme);
            $resMods->disabled = $dropdownMods->disabled;

            $resAttrs['aria-haspopup'] = 'true';
            $resAttrs['aria-controls'] = $ctx->tParam('popupId');
            $resAttrs['aria-expanded'] = 'false';

            $res->mix = $ctx->tParam('mix');
        }

        return $res;
    });

// file: ../../libs/bem-components-php/common.blocks/link/_pseudo/link_pseudo.bh.php
    $bh->match('link_pseudo', function($ctx, $json) {
        $json->url || $ctx->tag('span')->attr('role', 'button');
    });

// file: ../../libs/bem-components-php/common.blocks/select/select.bh.php
    $bh->match('select', function($ctx, $json) {
        if (!$ctx->mod('mode')) throw new \Exception('Can\'t build select without mode modifier');

        $isValDef = key_exists('val', $json);
        $isModeCheck = $ctx->mod('mode') === 'check';

        // php!yolo
        $refs = new StdClass();
        $refs->firstOption = null;
        $refs->checkedOptions = [];
        $refs->optionIds = [];

        $containsVal = function ($val) use ($isValDef, $isModeCheck, $json) {
            return $isValDef &&
                ($isModeCheck?
                    in_array($val, $json->val) :
                    $json->val === $val);
        };

        $iterateOptions = function (&$content) use ($containsVal, &$iterateOptions, $refs, $ctx) {
            foreach ($content as $_ => $option) {
                if(isset($option['group'])) {
                    $iterateOptions($content[$_]['group']);
                } else {
                    $refs->firstOption || ($refs->firstOption =& $content[$_]);
                    $refs->optionIds[] = $content[$_]['id'] = $ctx->generateId();
                    if(isset($option['val']) and $containsVal($option['val'])) {
                        $content[$_]['checked'] = true;
                        $refs->checkedOptions[] =& $content[$_];
                    }
                }
            }
        };

        $iterateOptions($json->options);

        $ctx
            ->js([
                'name' => $json->name,
                'optionsMaxHeight' => $json->optionsMaxHeight
            ])
            ->tParam('select', $json)
            ->tParam('refs', $refs)
            ->tParam('firstOption', $refs->firstOption)
            ->tParam('checkedOptions', $refs->checkedOptions)
            ->content([
                [ 'elem' => 'button' ],
                [
                    'block' => 'popup',
                    'mods' => [ 'target' => 'anchor', 'theme' => $ctx->mod('theme'), 'autoclosable' => true ],
                    'directions' => ['bottom-left', 'bottom-right', 'top-left', 'top-right'],
                    'content' => [ 'block' => $json->block, 'mods' => $ctx->mods(), 'elem' => 'menu' ]
                ]
            ]);
    });

// file: ../../libs/bem-components-php/common.blocks/select/_focused/select_focused.bh.php
    $bh->match('select_focused', function($ctx) {
    	$ctx
    		->applyBase()
    		->js($ctx->extend($ctx->js(), [ 'live' => false ]));
    });

// file: ../../libs/bem-components-php/common.blocks/select/__control/select__control.bh.php
    $bh->match('select__control', function($ctx, $json) {
        $mods = $json->blockMods ?: $json->mods;
        $ctx
            ->tag('input')
            ->attrs([
                'type' => 'hidden',
                'name' => $ctx->tParam('select')->name,
                'value' => $json->val,
                'disabled' => $mods->disabled? 'disabled' : null,
                'autocomplete' => 'off'
            ]);
    });

// file: ../../libs/bem-components-php/common.blocks/select/__button/select__button.bh.php
    $bh->match('select__button', function($ctx, $json) {
        $mods = $json->blockMods ?: $json->mods;
        $select = $ctx->tParam('select');
        $refs = $ctx->tParam('refs');
        $checkedOptions = $refs->checkedOptions;
        $selectTextId = $ctx->generateId();

        $ctx->tParam('selectTextId', $selectTextId);

        return [
            'block' => 'button',
            'mix' => [ 'block' => $json->block, 'elem' => $json->elem ],
            'mods' => [
                'size' => $mods->size,
                'theme' => $mods->theme,
                'view' => $mods->view,
                'focused' => $mods->focused,
                'disabled' => $mods->disabled,
                'checked' => $mods->mode !== 'radio' && count($checkedOptions)
            ],
            'attrs' => [
                'role' => 'listbox',
                'aria-owns' => join(' ', $refs->optionIds),
                'aria-multiselectable' => $mods->mode === 'check'? 'true' : null,
                'aria-labelledby' => $selectTextId
            ],
            'id' => $select->id,
            'tabIndex' => $select->tabIndex,
            'content' => [
                $ctx->content(),
                [ 'block' => 'icon', 'mix' => [ 'block' => 'select', 'elem' => 'tick' ] ]
            ]
        ];
    });

    $bh->match('button__text', function($ctx) {
        if($ctx->tParam('select')) {
            $ctx->attr('id', $ctx->tParam('selectTextId'));
        }
    });

// file: ../../libs/bem-components-php/common.blocks/select/__menu/select__menu.bh.php
    $bh->match('select__menu', function($ctx, $json) {
        $mods = $ctx->mods();
        $select = $ctx->tParam('select');
        $optionToMenuItem = function ($option) use ($mods) {
            $res = [
                'block' => 'menu-item',
                'mods' => [ 'disabled' => $mods->disabled ?: @$option['disabled'] ],
                'attrs' => [ 'role' => 'option' ],
                'id' => @$option['id'],
                'val' => @$option['val'],
                'js' => [ 'checkedText' => @$option['checkedText'] ],
                'content' => @$option['text']
            ];

            if (!empty($option['icon'])) {
                $res['js']['text'] = @$option['text'];
                $res['content'] = [
                    $option['icon'],
                    $res['content']
                ];
            }

            return $res;
        };

        return $select->options ? [
            'block' => 'menu',
            'mix' => [ 'block' => $json->block, 'elem' => $json->elem ],
            'mods' => [
                'size' => $mods->size,
                'theme' => $mods->theme,
                'disabled' => $mods->disabled,
                'mode' => $mods->mode
            ],
            'val' => $select->val,
            'attrs' => [ 'role' => null, 'tabindex' => null ],
            'content' => array_map(function ($optionOrGroup) use ($select, $optionToMenuItem) {
                return isset($optionOrGroup['group'])?
                    [
                        'elem' => 'group',
                        'title' => @$optionOrGroup['title'],
                        'content' => array_map($optionToMenuItem, $optionOrGroup['group'])
                    ] :
                    $optionToMenuItem($optionOrGroup);
            }, $select->options)
        ] : null;
    });

// file: ../../libs/bem-components-php/common.blocks/select/_mode/select_mode_radio-check.bh.php
    $bh->match('select_mode_radio-check', function($ctx, $json) {
        $ctx->applyBase();
        $ctx->js($ctx->extend($ctx->js(), [ 'text' => $json->text ]));

        $checkedOptions = $ctx->tParam('checkedOptions');

        if (@$checkedOptions[0]) {
            $ctx->content([
                [
                    'elem' => 'control',
                    'val' => $checkedOptions[0]['val']
                ],
                $ctx->content()
            ], true);
        }
    });

    $bh->match('select_mode_radio-check__button', function($ctx) {
        $checkedOptions = $ctx->tParam('checkedOptions');

        $content = (array)(@$checkedOptions[0] ?: $ctx->tParam('select'));
        $ctx->content([
            'elem' => 'text',
            'content' => @$content['text']
        ]);
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

// file: ../../libs/bem-components-php/common.blocks/radio/_type/radio_type_button.bh.php
    $bh->match('radio_type_button', function($ctx, $json) {
        $mods = $ctx->mods();

        $ctx->content([[
            'block' => 'button',
            'mods' => [
                'togglable' => $mods->mode === 'radio-check'?
                    'check' :
                    'radio',
                'checked' => $mods->checked,
                'disabled' => $mods->disabled,
                'theme' => $mods->theme,
                'size' => $mods->size
            ],
            'title' => $json->title,
            'content' => [
                $json->icon,
                key_exists('text', $json)?
                    [ 'elem' => 'text', 'content' => $json->text ] :
                    ''
            ]
        ], [
            'block' => 'radio',
            'elem' => 'control',
            'checked' => $mods->checked,
            'disabled' => $mods->disabled,
            'name' => $json->name,
            'val' => $json->val
        ]]);
    });

return $bh;
