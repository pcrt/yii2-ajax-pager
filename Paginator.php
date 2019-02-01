<?php

namespace pcrt;

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\View;
use yii\widgets\ContentDecorator;
use yii\web\JsExpression;

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
     * @var string the ID of paginator element - Only for Paginator widget.
     */
    public $id = 'pcrt-pagination';
    /**
     * @var string the ID of paginator wrapper element.
     */
    public $id_wrapper = 'pcrt-pagination-wrapper';
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
        $this->params['type'] = $this->type;
        parent::run();
        $this->registerClientScript();
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
        if($this->type === "InfiniteScroll"){

          $script = new JsExpression("
            window.reload_table = function(){
              if(window.infScroll !== undefined){
                  window.infScroll.destroy();
              }
              var elem = document.getElementById('".$this->id_wrapper."');
              elem.innerHTML = "";
              window.infScroll = new InfiniteScroll( elem, {
                path: function() {
                    let page = this.pageIndex;
                    return '".$this->url."&pageNumber='+page+'&pageSize=".$this->pageSize."' ;
                },
                append: '".$append."',
                history: false,
              });
              window.infScroll.loadNextPage();
            }

            $('document').ready(function(){
              window.reload_table();
            });");

        }else{

          $id = $this->id;

          $script = new JsExpression("
            function ajaxGetPage(_pageSize,_pageNum){
              var xhttp = new XMLHttpRequest();
              xhttp.open('GET', '".$this->url."&pageSize='+_pageSize+'&pageNumber='+_pageNum+'&_csrf='+yii.getCsrfToken(), true);
              xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
              xhttp.onreadystatechange = function() {
                if(xhttp.readyState == 4 && xhttp.status == 200) {
                      var result = JSON.parse(xhttp.responseText);
                      $('#".$id."').pagination('updateItems', result.total);
                      $('#pcrt-paginator-wrapper').html(result.html);
                  }
              }
              xhttp.send();
            }

            window.reload_table = function(){
              $('#".$id."').pagination('destroy');

              $('#".$id."').pagination({
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
