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
     * @var string the Type of paginator (InfiniteScroll, Paginator).
     */
    public $type = 'Paginator';
    /**
     * @var array Options for the paginator component .
     */
    public $paginationOpt = []; 
    /**
     * @var array Options for the infiniteScroll component .
     */
    public $infiniteScrollOpt = []; 
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
     * Select2 plugin placeholder check and initialization
     */
    protected function initPlaceholder()
    {
        $multipleSelection = ArrayHelper::getValue($this->options, 'multiple');

        if (!empty($this->options['prompt']) && empty($this->clientOptions['placeholder'])) {
            $this->clientOptions['placeholder'] = $multipleSelection
                ? ArrayHelper::remove($this->options, 'prompt')
                : $this->options['prompt'];

            return null;
        } elseif (!empty($this->options['placeholder'])) {
            $this->clientOptions['placeholder'] = ArrayHelper::remove($this->options, 'placeholder');
        }
        if (!empty($this->clientOptions['placeholder']) && !$multipleSelection) {
            $this->options['prompt'] = is_string($this->clientOptions['placeholder'])
                ? $this->clientOptions['placeholder']
                : ArrayHelper::getValue((array)$this->clientOptions['placeholder'], 'placeholder', '');
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($this->hasModel()) {
            echo Html::activeDropDownList($this->model, $this->attribute, $this->items, $this->options);
        } else {
            echo Html::dropDownList($this->name, $this->value, $this->items, $this->options);
        }
        $this->registerClientScript();
    }

    /**
     * @inheritdoc
     */
    public function registerClientScript()
    {
        $view = $this->view;

        $this->registerBundle($view);

        $options = !empty($this->clientOptions)
            ? Json::encode($this->clientOptions)
            : '';

        $id = $this->options['id'];

        $js[] = ";jQuery('#$id').select2($options);";
        if (!empty($this->clientEvents)) {
            foreach ($this->clientEvents as $event => $handler) {
                $js[] = "jQuery('#$id').on('$event', $handler);";
            }
        }

        $view->registerJs(implode("\n", $js));
    }

    /**
     * Registers asset bundle
     *
     * @param View $view
     */
    protected function registerBundle(View $view)
    {
        PaginatorAsset::register($view);
    }
}
