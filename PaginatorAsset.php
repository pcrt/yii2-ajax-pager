<?php

namespace pcrt;

use yii\web\AssetBundle;

class PaginatorAsset extends AssetBundle
{
    // http://pagination.js.org/
    public $sourcePath = __DIR__ . '/assets';

    public $js = [
        'pagination/simplePagination.js'
    ];

    public $css = [
        'pagination/simplePagination.css'
    ];

    public $depends = [
        'yii\web\JqueryAsset'
    ];

}
