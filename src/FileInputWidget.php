<?php

namespace bscheshirwork\fub;


use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\widgets\InputWidget;

/**
 * Class FileInputWidget
 *
 * For display image from model
 * display temporary image
 * display delete button
 * display custom file input button
 *
 *     <?= $form->field($model, 'image')->widget(\bscheshirwork\fub\models\FileInputWidget::class, [
 *         'buttonLabel' => Yii::t('tag', 'Select tag SVG icon...'),
 *         'template' => "{label}\n<span class='btn btn-primary btn-file form-control'>\n{buttonLabel}\n{input}</span>\n{hint}\n{error}",
 *     ]) ?>
 *
 * @package bscheshirwork\fub\models
 */
class FileInputWidget extends InputWidget
{
    /**
     * @var null|string attribute label
     */
    public $attributeLabel = null;
    /**
     * @var string The name of button who will replaced standard select file dialog
     */
    public $buttonLabel = 'Select...';
    /**
     * @var string The name of button for delete file
     */
    public $deleteButtonLabel = 'Delete';
    /**
     * @var string The name of button for delete file
     */
    public $deleteButtonOptions = ['class' => 'btn btn-danger'];
    /**
     * @var string The name of action for delete file. Use controller/action notation
     */
    public $deleteAction = 'crud/delete-file';
    /**
     * @var string the template that is used to arrange the label, the input field, the error message and the hint text.
     * This field will be automatically apply to [[\yii\widgets\ActiveField::$template]] in case widget instance is created via [[\yii\widgets\ActiveField::widget()]].
     * The following tokens will be replaced: `{image}`, `{tempImage}`, `{buttonLabel}`, `{label}`, `{input}`, `{error}` and `{hint}`.
     */
    public $template = "{label}\n{image}\n{tempImage}\n{deleteImageButton}\n{buttonLabel}\n{input}\n{hint}\n{error}";
    /**
     * @var null|FileSaveBehavior the actual behavior from current model
     */
    public $saver = null;
    /**
     * @var null|FileUploadBehavior the actual behavior from current model
     */
    public $uploader = null;
    /**
     * Query param for timestamp to avoid browser-side cache url of file
     * @var string
     */
    public $timestampQueryParam = '_';

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $content = parent::run();

        $image = '';
        $tempImage = '';
        $deleteImageButton = '';
        if ($this->saver ?? false) {
            $exist = $this->saver->getFilePath() ?? false;
            if ($exist && is_file($exist)) {
                $mimeType = FileHelper::getMimeType($exist);
                if (substr($mimeType, 0, 5) === 'image') {
                    $image = Html::img($this->saver->getFileUrl() . '?' . $this->timestampQueryParam . '=' . time());
                }
            }
        }
        if ($this->uploader ?? false) {
            $temp = $this->uploader->tempFileName ?? false;
            if ($temp && is_file($temp)) {
                $mimeType = FileHelper::getMimeType($temp);
                if (substr($mimeType, 0, 5) === 'image') {
                    $tempImage = Html::img($this->uploader->tempFileUrl . '?' . $this->timestampQueryParam . '=' . time());
                }
            }
        }
        if ($image) {
            $deleteImageButton = Html::a($this->deleteButtonLabel, [$this->deleteAction, 'id' => $this->saver->owner->primaryKey], $this->deleteButtonOptions);
        }

        if ($this->field) {
            if (!isset($this->field->form->options['enctype'])) {
                $this->field->form->options['enctype'] = 'multipart/form-data';
            }
            $this->field->template = strtr($this->template, [
                '{label}' => $this->attributeLabel,
                '{buttonLabel}' => $this->buttonLabel,
                '{image}' => $image,
                '{tempImage}' => $tempImage,
                '{deleteImageButton}' => $deleteImageButton,
                '{content}' => $content,
            ]);
        }

        return $this->renderInputHtml('file');
    }

}