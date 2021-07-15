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
use yii\helpers\Url;

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
     * @var array the parameters (name => value) to be extracted and made available in the decorative view.
     */
    public $params = [];
    
    public $nextText = 'Next';
    
    public $prevText = 'Prev';

    public $placeholders = false;

    public $placeholdersTemplate = '';

    public $placeholdersLabel = 'No results';
        
    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->type !== self::INFINITE && $this->type !== self::PAGINATION) {
            throw new InvalidParamException("The type parameter must contain a valid value .");
        }
        if ($this->url === "") {
            throw new InvalidParamException("The url parameter is mandatory and must contain a valid value .");
        }
        if ($this->id_wrapper === "") {
            throw new InvalidParamException("The id_wrapper parameter is mandatory and must contain a valid value .");
        }
        if ($this->type === self::INFINITE) {
            if ($this->append === "") {
                throw new InvalidParamException("The append parameter is mandatory and must contain a valid value .");
            }
        }
        if ($this->type === self::PAGINATION) {
            if ($this->id === "") {
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
        $this->params['type'] = $this->type;
        $this->params['id'] = $this->id;
        $this->params['id_wrapper'] = $this->id_wrapper;
        parent::run();
        $this->registerClientScript();
    }

    /**
    * Return JsExpression implementation of InfiniteScroll js function
    * @return JsExpression
    */
    private function renderInfiniteScroll()
    {
        if ($this->params) {
            $params = $this->params;
        }

        $urlArray = array_merge([$this->url, 'pageSize' => $this->pageSize], $params);
      
        $url = Url::to($urlArray);
        
        $infName = 'infScroll' . rand(0, 999999);

        $script = new JsExpression("
        window.reload_table = function(){
          if(window.".$infName." !== undefined){
              window.".$infName.".destroy();
          }
          var elem = document.getElementById('".$this->id_wrapper."');
          elem.innerHTML = '';
          window.".$infName." = new InfiniteScroll( elem, {
            path: function() {
                let page = this.pageIndex;
                return '".$url."&pageNumber='+page;
            },
            append: '".$this->append."',
            history: false,
          });

          // Add event emit on Nextpage loaded .
          window.".$infName.".on( 'append', function( response, path, items ) {
            var event = new Event('table_loaded');
            window.dispatchEvent(event);
          });

          window.".$infName.".loadNextPage();
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
    private function renderPagination()
    {
        if ($this->params) {
            $params = $this->params;
        }

        $urlArray = array_merge([$this->url, 'pageSize' => $this->pageSize], $params);
      
        $url = Url::to($urlArray);

        $getName = 'ajaxGetPage' . rand(0, 999999);
        $refreshName = 'refresh' . rand(0, 999999);

        if ($this->placeholders) {
          if ($this->placeholdersTemplate)
            $template = str_replace("\n","",addslashes($this->placeholdersTemplate));
          else
            $template = addslashes('<div style="font-style: italic;padding: 50px 0 50px; text-align: center; font-size: 2em;">' . $this->placeholdersLabel . '</div>');

          $ajaxFunc = "
          
          function ajaxPagerManageError(elem, xhttp) {
            console.log('Paginator error')
            console.log(xhttp);
            elem.innerHTML = '<div style=\"font-style: italic;padding: 50px 0 50px; text-align: center; font-size: 2em; color: red;text-decoration: underline;\">Error ' + xhttp.status + '</div>';
          }
          
          function ".$getName."(_pageSize,_pageNum){
            var elem = document.getElementById('".$this->id_wrapper."');

            $(elem).parent().find('.pcrt-row-h').hide()
            $('#".$this->id."').attr('style','display:none !important');

            elem.innerHTML = '<div style=\"text-align:center;padding: 50px 0 50px;\"><img height=\"30\" width=\"30\" src=\"data:image/gif;base64," . base64_encode(file_get_contents(__DIR__ . "/assets/index.gif")) . "\" /></div>';

            var xhttp = new XMLHttpRequest();
            xhttp.open('GET', '".$url."&pageNumber='+_pageNum, true);
            xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhttp.onreadystatechange = function() {
              if(xhttp.readyState == 4 && xhttp.status == 200) {
                var result = JSON.parse(xhttp.responseText);

                if (result.total == 0) {

                  elem.innerHTML = \"".$template."\";
                } else {
                  $('#".$this->id."').pagination('updateItems', result.total);

                  $(elem).parent().find('.pcrt-row-h').show()
                  $('#".$this->id."').attr('style','display:block');
                  
                  elem.innerHTML = result.html;
                }

                // Add event emit on Nextpage loaded .
                var event = new Event('table_loaded');
                window.dispatchEvent(event);
              } else if(xhttp.readyState == 4 && xhttp.status != 200 && xhttp.status != 0) {
                ajaxPagerManageError(elem, xhttp);
              }
            }
            xhttp.send();
          }";
        } else {
          $ajaxFunc = "function ".$getName."(_pageSize,_pageNum){
            var xhttp = new XMLHttpRequest();
            xhttp.open('GET', '".$url."&pageNumber='+_pageNum, true);
            xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhttp.onreadystatechange = function() {
              if(xhttp.readyState == 4 && xhttp.status == 200) {
                var result = JSON.parse(xhttp.responseText);
                $('#".$this->id."').pagination('updateItems', result.total);
                var elem = document.getElementById('".$this->id_wrapper."');

                elem.innerHTML = result.html;

                // Add event emit on Nextpage loaded .
                var event = new Event('table_loaded');
                window.dispatchEvent(event);
              } else if(xhttp.readyState == 4 && xhttp.status != 200 && xhttp.status != 0) {
                ajaxPagerManageError(elem, xhttp);
              }
            }
            xhttp.send();
          }";
        }

        $script = new JsExpression($ajaxFunc . "
        var globalPageNumber = 1;

        function ".$refreshName."(save = false) {
          if (!save) {
            globalPageNumber = 1
          }

          $('#".$this->id."').pagination('destroy');

          $('#".$this->id."').pagination({
            'onInit': function(){
              ".$getName."(".$this->pageSize.", globalPageNumber)
            },
            'onPageClick': function(pageNumber, event){
              globalPageNumber = pageNumber
              ".$getName."(".$this->pageSize.", pageNumber)
            },
            'itemsOnPage': $this->pageSize,
            'prevText': '$this->prevText',
            'nextText': '$this->nextText',
            'currentPage': globalPageNumber
          });
        }

        window.reload_table = function(save = false){
          ".$refreshName."(save)
        }
        $('document').ready(function(){
          ".$refreshName."()
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
        if ($this->type === self::INFINITE) {
            $script = $this->renderInfiniteScroll();
        }
        if ($this->type === self::PAGINATION) {
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
        if ($this->type === "InfiniteScroll") {
            InfiniteAsset::register($view);
        } else {
            PaginatorAsset::register($view);
        }
    }
}
