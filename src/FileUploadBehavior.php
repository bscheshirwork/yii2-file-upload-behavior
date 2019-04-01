<?php

namespace bscheshirwork\fub;


use Yii;
use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

/**
 * Class FileUploadBehavior
 *
 * For upload file and save as temporary
 * check upload status
 * display previous uploaded
 * mark previous uploaded temporary name
 *
 * @package bscheshirwork\fub\models
 * @property $owner Model
 */
class FileUploadBehavior extends Behavior
{
    /**
     * @var string attribute for activeForm
     */
    public $attribute = 'image';
    /**
     * @var string filter by mime type. Set property to false for disabled filter
     */
    public $mimeTypeFilter = 'image/svg';
    /**
     * @var string directory for save uploading images
     */
    public $tempDirectory = '@storageWeb/images/temp';
    /**
     * @var string url of directory for save uploading images
     */
    public $tempDirectoryUrl = '@storageUrl/images/temp';
    /**
     * @var null|string filename of temporary file. Will be fill after upload file
     */
    public $tempFileName = null;
    /**
     * @var null|string url of temporary file. Will be fill after upload file
     */
    public $tempFileUrl = null;

    /**
     * {@inheritdoc}
     * @param string $name
     * @param bool $checkVars
     * @return bool
     */
    public function canGetProperty($name, $checkVars = true)
    {
        if ($name == $this->attribute) {
            return true;
        }

        return parent::canGetProperty($name, $checkVars);
    }

    /**
     * {@inheritdoc}
     * @param string $name
     * @param bool $checkVars
     * @return bool
     */
    public function canSetProperty($name, $checkVars = true)
    {
        if ($name == $this->attribute) {
            return true;
        }

        return parent::canSetProperty($name, $checkVars);
    }

    /**
     * {@inheritdoc}
     * @param string $name
     * @return array|mixed|null
     * @throws \yii\base\UnknownPropertyException
     */
    public function __get($name)
    {
        if ($name == $this->attribute) {
            return $this->tempFileName;
        }

        return parent::__get($name);
    }

    /**
     * {@inheritdoc}
     * @param string $name
     * @param mixed $value
     * @throws \yii\base\UnknownPropertyException
     * @throws \yii\base\Exception
     */
    public function __set($name, $value)
    {
        if ($name == $this->attribute) {

            return;
        }
        parent::__set($name, $value);
    }

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    public function attach($owner)
    {
        parent::attach($owner);

        if (!$owner instanceof Model) {
            throw new InvalidConfigException('FileUploadBehavior must be assign to Model');
        }
    }

    /**
     * @throws \yii\base\Exception Get file from tmp upload dir
     */
    public function uploadFile()
    {
        $imageFile = UploadedFile::getInstance($this->owner, $this->attribute);
        if ($imageFile && $this->mimeTypeFilter && ($imageFile->type != $this->mimeTypeFilter) && (FileHelper::getMimeType($imageFile->tempName) != $this->mimeTypeFilter)) {
            return;
        }

        $directory = Yii::getAlias($this->tempDirectory) . DIRECTORY_SEPARATOR . Yii::$app->session->id . DIRECTORY_SEPARATOR;
        if (!is_dir($directory)) {
            FileHelper::createDirectory(FileHelper::normalizePath($directory), 0777);
        }

        if ($imageFile) {
            $uid = uniqid(time(), true);
            $fileName = $uid . '.' . $imageFile->extension;
            $filePath = $directory . $fileName;
            if ($imageFile->saveAs($filePath)) {
                $this->tempFileName = $filePath;
                $this->tempFileUrl = Yii::getAlias($this->tempDirectoryUrl) . '/' . Yii::$app->session->id . '/' . $fileName;
            }
        }
    }

}