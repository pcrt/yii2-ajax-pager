<?php

namespace pcrt;

use yii\web\AssetBundle;

class PaginatorAsset extends AssetBundle
{
    // https://infinite-scroll.com/
    public $sourcePath = __DIR__ . '/assets';

    public $js = [
        'infinite/infinite-scroll.pkgd.min.js'
    ];

    public $css = [
    ];

    public $depends = [
        'yii\web\JqueryAsset'
    ];

}
