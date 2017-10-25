# yii2-editable-column-action
Editable column action for yii2 grid view widget

Installation
------------
The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist bigdropinc/yii2-editable-column-action "*"
```

or add

```
bigdropinc/yii2-editable-column-action
```

to the require section of your `composer.json` file.


Usage
-----

For Controller::actions() method return array with EditableColumnAction::className()

```php
class TestController extends yii\web\Controller
{
    public function actions()
    {
        return [
            'editColumnAction' => [
                'class' => bigdropinc\EditableColumnAction::className(),
                'modelClass' => MyModel::className(),
                'allowedAttributes' => ['status', 'title'],
            ]
        ];
    }
}
```
