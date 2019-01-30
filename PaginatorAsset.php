<?php

namespace pcrt;

use yii\web\AssetBundle;

class PaginationAsset extends AssetBundle
{
    // http://pagination.js.org/
    public $sourcePath = __DIR__ . '/assets';

    public $js = [
        'pagination/pagination.js'
    ];

    public $css = [
        'pagination/pagination.css'
    ];

    public $depends = [
        'yii\web\JqueryAsset'
    ];

}
