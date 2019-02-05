<?php

/**
 * @link http://www.protocollicreativi.it
 * @copyright Copyright (c) 2017 Protocolli Creativi s.n.c.
 * @license LICENSE.md
 */

namespace pcrt;

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\View;
use yii\widgets\ContentDecorator;
use yii\web\JsExpression;
use yii\base\InvalidParamException;

/**
 * Yii2 implementation of ContentDecorator pattern to insert a wrapper ajax data loader working in two mode :
 * as InfiniteScroll implementing InfiniteScroll.js library (https://infinite-scroll.com/)
 * as Paginator implementing simple-pagination.js library (http://flaviusmatis.github.io/simplePagination.js/)
 * @author Marco Petrini <marco@bhima.eu>
 */

class Paginator extends ContentDecorator
{

    const INFINITE = "InfiniteScroll";
    const PAGINATION = "Pagination";

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
     * @var string the ID of paginator element - Only for Paginator widget.
     */
    public $id = 'pcrt-pagination';
    /**
     * @var string the ID of paginator wrapper element.
     */
    public $id_wrapper = 'pcrt-paginator-wrapper';
    /**
     * @var string the Selector for append element .
     */
    public $append = '.pcrt-card';
    /**
     * @var string Ajax get data url .
     */
    public $url = [];
    /**
     * @var integer item per page params  .
     */
    public $pageSize = 30;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if($this->type !== self::INFINITE && $this->type !== self::PAGINATION){
          throw new InvalidParamException("The type parameter must contain a valid value .");
        }
        if($this->url === ""){
          throw new InvalidParamException("The url parameter is mandatory and must contain a valid value .");
        }
        if($this->id_wrapper === ""){
          throw new InvalidParamException("The id_wrapper parameter is mandatory and must contain a valid value .");
        }
        if($this->type === self::INFINITE){
          if($this->append === ""){
            throw new InvalidParamException("The append parameter is mandatory and must contain a valid value .");
          }
        }
        if($this->type === self::PAGINATION){
          if($this->id === ""){
            throw new InvalidParamException("The id parameter is mandatory and must contain a valid value .");
          }
        }
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
    * Return JsExpression implementation of InfiniteScroll js function
    * @return JsExpression
    */
    private function renderInfiniteScroll(){

      $script = new JsExpression("
        window.reload_table = function(){
          if(window.infScroll !== undefined){
              window.infScroll.destroy();
          }
          var elem = document.getElementById('".$this->id_wrapper."');
          elem.innerHTML = '';
          window.infScroll = new InfiniteScroll( elem, {
            path: function() {
                let page = this.pageIndex;
                return '".$this->url."&pageNumber='+page+'&pageSize=".$this->pageSize."';
            },
            append: '".$this->append."',
            history: false,
          });
          window.infScroll.loadNextPage();
        }

        $('document').ready(function(){
          window.reload_table();
        });");

      return $script;

    }

    /**
    * Return JsExpression implementation of Pagination js function
    * @return JsExpression
    */
    private function renderPagination(){

      $script = new JsExpression("
        function ajaxGetPage(_pageSize,_pageNum){
          var xhttp = new XMLHttpRequest();
          xhttp.open('GET', '".$this->url."&pageSize='+_pageSize+'&pageNumber='+_pageNum+'&_csrf='+yii.getCsrfToken(), true);
          xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
          xhttp.onreadystatechange = function() {
            if(xhttp.readyState == 4 && xhttp.status == 200) {
                  var result = JSON.parse(xhttp.responseText);
                  $('#".$id."').pagination('updateItems', result.total);
                  var elem = document.getElementById('".$this->id_wrapper."');
                  elem.innerHTML = result.html;
              }
          }
          xhttp.send();
        }

        window.reload_table = function(){
          $('#".$this->id."').pagination('destroy');

          $('#".$this->id."').pagination({
            'onInit': function(){
              ajaxGetPage(".$this->pageSize.",1)
            },
            'onPageClick': function(pageNumber, event){
              ajaxGetPage(".$this->pageSize.",pageNumber)
            },
            'itemsOnPage': $this->pageSize
          });
        }
        $('document').ready(function(){
          window.reload_table();
        });");

        return $script;
    }

    /**
     * @inheritdoc
     */
    public function registerClientScript()
    {
        $view = $this->view;
        $script = "";
        // Registering Assets
        $this->registerBundle($view);

        $options = [];
        if($this->type === self::INFINITE){
          $script = $this->renderInfiniteScroll();
        }
        if($this->type === self::PAGINATION){
          $script = $this->renderPagination();
        }
        // Registering JS script on page
        $view->registerJs($script);
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
