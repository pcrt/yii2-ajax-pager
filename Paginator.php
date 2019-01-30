<?php

namespace pcrt;

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\View;
use yii\widgets\ContentDecorator;

class Paginator extends ContentDecorator
{
    /**
     * @var string the view file that will be used to decorate the content enclosed by this widget.
     * This can be specified as either the view file path or [path alias](guide:concept-aliases).
     */
    public $viewFile = __DIR__ . '/_wrapper.php';
    /**
     * @var string the Type of paginator (InfiniteScroll, Pagination).
     */
    public $type = 'Pagination';
    /**
     * @var string the ID of paginator wrapper HTML element .
     */
    public $id = 'pcrt-paginator-wrapper';
    /**
     * @var array Options for the paginator component .
     */
    public $paginationOpt = [];
    /**
     * @var array Events for the paginator component .
     */
    public $paginationEvents = [];
    /**
     * @var array Options for the infiniteScroll component .
     */
    public $infiniteScrollOpt = [];
    /**
     * @var array Events for the infiniteScroll component .
     */
    public $infiniteScrollEvents = [];
    /**
     * @var array the parameters (name => value) to be extracted and made available in the decorative view.
     */
    public $params = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        parent::run();
        $this->registerClientScript();
    }

    /**
     * @inheritdoc
     */
    public function registerClientScript()
    {
        $view = $this->view;

        // Registering Assets
        $this->registerBundle($view);

        $options = []
        if($this->type === "InfiniteScroll"){
          // Encode Option to JSON
          $options = !empty($this->infiniteScrollOpt)
              ? Json::encode($this->infiniteScrollOpt)
              : '';
          $id = $this->id;
          // Init pagination Object and bind to Window
          $js[] = "window.paginator = $('#$id');";
          $js[] = "window.paginator.infiniteScroll(($options);";
          // Add pagination Event hook
          if (!empty($this->infiniteScrollEvents)) {
            foreach ($this->infiniteScrollEvents as $event => $handler) {
                $js[] = "window.paginator.on('$event', $handler);";
            }
          }
        }else{
          // Encode Option to JSON
          $options = !empty($this->paginationOpt)
              ? Json::encode($this->paginationOpt)
              : '';
          // Init pagination Object and bind to Window
          $js[] = "window.paginator = $('#$id');";
          $js[] = "window.paginator.pagination($options);";
          // Add pagination Event hook
          if (!empty($this->paginationEvents)) {
            foreach ($this->paginationEvents as $event => $handler) {
                $js[] = "window.paginator.addHook('$event', $handler);";
            }
          }
        }
        // Registering JS script on page
        $view->registerJs(implode("\n", $js));
    }

    /**
     * Registers asset bundle
     *
     * @param View $view
     */
    protected function registerBundle(View $view)
    {
        if($this->type === "InfiniteScroll"){
          InfiniteAsset::register($view);
        }else{
          PaginatorAsset::register($view);
        }

    }
}
