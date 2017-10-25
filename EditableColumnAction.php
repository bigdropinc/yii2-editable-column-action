<?php
/**
 * Created by PhpStorm.
 * User: bigdrop
 * Date: 31.08.17
 * Time: 12:52
 */

namespace bigdropinc\widgets;

use yii;
use yii\base\Exception;
use yii\bootstrap\Html;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\UploadedFile;

/**
 * Class EditableColumnAction
 * @package backend\components
 * @inheritdoc
 *
 * @property integer $editableKey
 * @property integer $editableIndex
 * @property string $editableAttribute
 * @property array $postData
 */
class EditableColumnAction extends \kartik\grid\EditableColumnAction
{

    /**
     * @var ActiveRecord
     */
    protected $model;

    public $allowedAttributes = [];

    public function validateEditable()
    {
        if ($this->postOnly && !$this->request->isPost || $this->ajaxOnly && !$this->request->isAjax) {
            throw new BadRequestHttpException('This operation is not allowed!');
        }
        try {
            $this->initModel();
            $this->validateRequest();
            $result = $this->processModel();
        } catch (\Throwable $e) {
            $result = ['output' => '', 'message' => $e->getMessage()];
        }
        return $result;
    }

    /**
     * @throws Exception
     */
    protected function initModel()
    {
        $this->model = $this->findModel($this->editableKey);
        if (!$this->model) {
            throw new Exception($this->errorMessages['invalidModel']);
        }
        $this->model->scenario = $this->scenario;
        $this->formName = isset($this->formName) ? $this->formName : $this->model->formName();
    }

    protected function validateRequest()
    {
        $this->initErrorMessages();
        $post = $this->request->post();
        if (!isset($post['hasEditable'])) {
            throw new Exception($this->errorMessages['invalidEditable']);
        }
        if ($this->checkAccess && is_callable($this->checkAccess)) {
            call_user_func($this->checkAccess, $this->id, $this->model);
        }
        if (!$this->formName || is_null($this->editableIndex) || !isset($post[$this->formName][$this->editableIndex])) {
            throw new Exception($this->errorMessages['editableException']);
        }
    }

    protected function processModel()
    {
        if ($this->model->load($this->postData)) {
            $params = [$this->model, $this->editableAttribute, $this->editableKey, $this->editableIndex];
            $message = static::parseValue($this->outputMessage, $params);
            if (!$this->model->save()) {
                if (!$this->model->hasErrors()) {
                    throw new Exception($this->errorMessages['saveException']);
                }
                if (empty($message) && $this->showModelErrors) {
                    $message = Html::errorSummary($this->model, $this->errorOptions);
                }
            }
            $value = static::parseValue($this->outputValue, $params);
            return ['output' => $value, 'message' => $message];
        }
        return ['output' => '', 'message' => ''];
    }

    /**
     * @return integer|mixed
     */
    public function getEditableKey()
    {
        return $this->request->post('editableKey');
    }

    /**
     * @return integer|mixed
     */
    public function getEditableIndex()
    {
        return $this->request->post('editableIndex');
    }

    /**
     * @return mixed|string
     * @throws MethodNotAllowedHttpException
     */
    public function getEditableAttribute()
    {
        $attribute = $this->request->post('editableAttribute');
        if (!empty($this->allowedAttributes) && !ArrayHelper::isIn($attribute, $this->allowedAttributes)) {
            throw new MethodNotAllowedHttpException('This attribute update not allowed');
        }
        return $attribute;
    }

    /**
     * @return array
     */
    public function getPostData()
    {
        $post = $this->request->post();
        $postData = [$this->model->formName() => $post[$this->formName][$this->editableIndex]];
        if (!empty($_FILES)) {
            $postData[$this->model->formName()][$this->editableAttribute] = UploadedFile::getInstance($this->model, "[{$this->editableIndex}]{$this->editableAttribute}");
        }
        return $postData;
    }

    public function getRequest()
    {
        return Yii::$app->request;
    }

}