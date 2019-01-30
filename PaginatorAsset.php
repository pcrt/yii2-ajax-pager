<?php

namespace pcrt\widgets\select2;

use yii\web\AssetBundle;

class PaginatorAsset extends AssetBundle
{
    // http://pagination.js.org/
    public $sourcePath = __DIR__ . '/assets';

    public $js = [
        'js/select2.full.js'
    ];

    public $css = [
        'css/select2.css'
    ];
    
    public $depends = [
        'yii\web\JqueryAsset'
    ];

}
