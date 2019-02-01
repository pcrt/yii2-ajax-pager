Yii2-Ajax-Pager
========

Yii2 Component extend yii\widgets\ContentDecorator [url](https://www.yiiframework.com/doc/api/2.0/yii-widgets-contentdecorator) used to add ajax-pagination functionality to GridView and ListView base component .

This extension create a Wrapper around content and permit to chose between "Paginator Pattern" or "InfiniteScroll Pattern" to manage pagination functionality without reload page .

##Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
$ php composer.phar require pcrt/yii2-ajax-pager "@dev"
```

or add

```
"pcrt/yii2-ajax-pager": "@dev"
```

to the require section of your `composer.json` file.

## Usage

Once the extension is installed, you can add component in your View:
The component can work as Pagination :

```php
use pcrt/Paginator;
....

<?php Paginator::begin([
    'type' => 'Pagination',
    'id' => 'pcrt-pagination', // Id of pagination container
    'id_wrapper' => 'pcrt-pagination-wrapper', // Id of wrapper container
    'view' => $this,
]) ?>

$this->renderPartial('ajax/list', [ 'dt' => $dataProvider ]);
// or do your magic here !

<?php Paginator::end() ?>
```

or in InfiniteScroll mode:

```php
use pcrt/Paginator;
....

<?php Paginator::begin([
    'type' => 'InfiniteScroll',
    'append' => '.pcrt-card', // Selector for pagination element extractor
    'id_wrapper' => 'pcrt-pagination-wrapper', // Id of wrapper container
    'view' => $this,
]) ?>

$this->renderPartial('ajax/_card', [ 'dt' => $dataProvider ]);
// or do your magic here !

<?php Paginator::end() ?>
```

You can also pass an alternative "wapper view" file :

```php
use pcrt/Paginator;
....

<?php Paginator::begin([
    'type' => 'InfiniteScroll',
    'viewFile' => '@app/views/wrapper.php', // Alternative view Wrapper
    'append' => '.pcrt-card', // Selector for pagination element extractor
    'id_wrapper' => 'pcrt-pagination-wrapper', // Id of wrapper container
    'view' => $this,
]) ?>

// Remenber to return $content variable inside of wrapper;
```

In the controller for InfiniteScroll return a renderAjax file:

```php
class MyController extends Controller {

  public function actionGetGridView(){

    public function actionGetGridView($pageNumber=0,$pageSize=50){
      if($pageSize == ""){
        $pageSize = 50;
      }
      $request = \Yii::$app->request;

      $searchModel = new MyControllerSearch();
      $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
      $dataProvider->pagination = [
              'pageSize'=>$pageSize,
              'page'=>$pageNumber-1,
      ];
      $result = $dataProvider->getTotalCount();
      $data = $this->renderAjax('_list', [
          'searchModel' => $searchModel,
          'dataProvider' => $dataProvider,
      ]);
      return $data;

  	}
	}

}
```

for Pagination return a jSON Object with data (html render) and total (total number of element):

```php
class MyController extends Controller {

  public function actionGetGridView($pageNumber=0,$pageSize=50){
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    if($pageSize == ""){
      $pageSize = 50;
    }
    $request = \Yii::$app->request;

    $searchModel = new MyControllerSearch();
    $dataProvider = $searchModel->search();
    $dataProvider->pagination = [
            'pageSize'=>$pageSize,
            'page'=>$pageNumber-1,
    ];
    $result = $dataProvider->getTotalCount();
    $data = $this->renderAjax('_list', [
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
    ]);
    return ['html'=>$data,'total'=>$result];
	}
}
```


## License

Yii2-Ajax-Pager is released under the BSD-3 License. See the bundled `LICENSE.md` for details.


# Useful URLs

* [Simple Pagination JS](http://flaviusmatis.github.io/simplePagination.js/)
* [Infinite Scroll JS](https://infinite-scroll.com/)

Enjoy!
