<?php

namespace bscheshirwork\fub;


use Yii;
use yii\base\Action;
use yii\base\Model;
use yii\helpers\Inflector;
use yii\web\HttpException;

/**
 * Class FileDeleteAction
 *
 * For delete file manually
 *
 * @package bscheshirwork\fub\models
 */
class FileDeleteAction extends Action
{
    /**
     * @var string The action of this controller action. [block|unblock]
     * Can be redefine use config
     * 'delete-file' => [
     *     'class' => FileDeleteAction::class,
     *     'action' => 'delete-file',
     * ],
     */
    public $action = 'delete-file';
    /**
     * @var string The name of action to redirect after delete file. Use controller/action notation
     */
    public $redirectAction = 'crud/update';

    /**
     * Run the action with id param
     * @param $id
     * @return mixed
     * @throws HttpException
     */
    public function run($id)
    {
        $method = 'action' . Inflector::camelize($this->action);

        if (method_exists($this, $method)) {
            return $this->$method($id);
        }

        throw new HttpException(400, Yii::t('main', 'Action does not exists'));
    }

    public function actionDeleteFile($id)
    {
        if (method_exists($this->controller, 'findModel')) {
            /** @var Model $model */
            $model = $this->controller->findModel($id);
            /** @var FileSaveBehavior $behavior */
            $behavior = $model->getBehavior('fileSave');
            $behavior->beforeDelete();
            $this->controller->redirect([$this->redirectAction, 'id' => $id]);
        }
    }
}