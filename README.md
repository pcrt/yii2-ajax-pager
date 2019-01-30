Yii2-Ajax-Pager
========

Yii2 Component extend yii\widgets\ContentDecorator [url](https://www.yiiframework.com/doc/api/2.0/yii-widgets-contentdecorator) used to add ajax-pagination functionality to GridView and ListView base component .

This extension create a Wrapper around content and permit to chose between "Paginator Pattern" or "InfiniteScroll Pattern" to manage pagination functionality . You can reload widget data without reload page !!!

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
    'paginationOpt' => [...],
    'paginationEvents' => [...],
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
    'infiniteScrollOpt' => [...],
    'infiniteScrollEvents' => [...],
    'view' => $this,
]) ?>

$this->renderPartial('ajax/list', [ 'dt' => $dataProvider ]);
// or do your magic here !

<?php Paginator::end() ?>
```

You can also pass an alternative "wrapper view" file :

```php
use pcrt/Paginator;
....

<?php Paginator::begin([
    'type' => 'InfiniteScroll',
    'viewFile' => '@app/views/wrapper.php',
    'infiniteScrollOpt' => [...],
    'infiniteScrollEvents' => [...],
    'view' => $this,
]) ?>

// Remenber to return $content variable inside of wrapper;
```
For the Option/Events params please refer to pagination.js [doc](http://pagination.js.org/), and Infinite-Scroll.js [doc](https://infinite-scroll.com/)
You can put param using Associative Array . If you can put JS code use yii\web\JsExpression .


In the controller:

```php
class MyController extends Controller {
	public function actionGetGridView(){

    $request = \Yii::$app->request;
    $offset = $request->get('offset'); // Param contain page number
    $searchModel = new MyControllerSearch();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
    $dataProvider->pagination = [
            'pageSize'=>'30',
            'page'=>$offset,
    ];
    return $this->renderAjax('@app/views/ajax/list', [ 'dt' => $dataProvider ]);

	}
}
```

## License

Yii2-Ajax-Pager is released under the BSD-3 License. See the bundled `LICENSE.md` for details.


# Useful URLs

* [Pagination JS](http://pagination.js.org/)
* [Infinite Scroll JS](https://infinite-scroll.com/)

Enjoy!
