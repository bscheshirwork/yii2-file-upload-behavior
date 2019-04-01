<?php

namespace bscheshirwork\fub;


use Yii;
use yii\base\Behavior;
use yii\base\ErrorException;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\helpers\FileHelper;

/**
 * Class FileSaveBehavior
 *
 * For save file from temporary name to model-friendly name
 * rename file if pk is changes
 * delete file if model is deleted
 * return model-friendly name
 *
 * @package bscheshirwork\fub\models
 * @property $owner ActiveRecord
 */
class FileSaveBehavior extends Behavior
{
    /**
     * @var string directory for save uploading images
     */
    public $directory = '@storageWeb/images/single';
    /**
     * @var string url of directory for save uploading images
     */
    public $directoryUrl = '@storageUrl/images/single';

    /**
     * @var string extension for build file name.
     */
    public $extension = 'svg';

    /**
     * @var string The separator. Each modal can be store images to difference folder: directory/type/
     */
    public $type = '';

    /**
     * Glue for build string representation of composite primary keys
     * @var string
     */
    public $pkGlue = '_';

    /**
     * @var null|FileUploadBehavior the actual behavior from current model
     * In form:
     * $this->_model->getBehavior('fileSave')->uploader = $this->getBehavior('fileUpload');
     */
    public $uploader = null;

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    public function attach($owner)
    {
        parent::attach($owner);

        if (!$owner instanceof ActiveRecord) {
            throw new InvalidConfigException('FileSaveBehavior must be assign to ActiveRecord');
        }
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
        ];
    }

    /**
     * remove file
     */
    public function beforeDelete()
    {
        if ($filePath = $this->getFilePath()) {
            try {
                FileHelper::unlink($filePath);
            } catch (ErrorException $exception) {
            }
        }
    }

    /**
     * Move new file to actual ID
     * @throws \yii\base\Exception
     */
    public function afterUpdate()
    {
        if ($this->owner->oldPrimaryKey != $this->owner->primaryKey) {
            $oldFileId = implode($this->pkGlue, (array) $this->owner->oldPrimaryKey);
            $oldFileName = $this->directory . DIRECTORY_SEPARATOR . $this->type . DIRECTORY_SEPARATOR . $oldFileId . '.' . $this->extension;
            if ($filePath = $this->getFilePath(false)) {
                try {
                    FileHelper::unlink($filePath);
                } catch (ErrorException $exception) {
                }
                rename($oldFileName, $filePath);
                $this->deleteEmptyDirectory($oldFileName);
            }
        }
        if ($this->uploader->tempFileName ?? false) {
            $this->afterInsert();
        }
    }

    /**
     * Move file to actual ID
     * @throws \yii\base\Exception
     */
    public function afterInsert()
    {
        if ($this->uploader->tempFileName ?? false) {
            $directory = \Yii::getAlias($this->directory) . DIRECTORY_SEPARATOR . $this->type . DIRECTORY_SEPARATOR;
            if (!is_dir($directory)) {
                FileHelper::createDirectory(FileHelper::normalizePath($directory), 0777);
            }
            if ($filePath = $this->getFilePath(false)) {
                try {
                    FileHelper::unlink($filePath);
                } catch (ErrorException $exception) {
                }
                rename($this->uploader->tempFileName, $filePath);
                $this->deleteEmptyDirectory($this->uploader->tempFileName);
            }
        }
    }

    /**
     * @return bool|string propose filename w/o path.
     */
    public function getFileId()
    {
        $pk = $this->owner->getPrimaryKey();
        if ($pk === null) {
            return false;
        }

        return implode($this->pkGlue, (array) $pk);
    }

    /**
     * @param bool $checkFile
     * @return bool|string return path of stored file
     */
    public function getFilePath($checkFile = true)
    {
        if ($fileId = $this->getFileId()) {
            $filePath = Yii::getAlias($this->directory) . DIRECTORY_SEPARATOR . $this->type . DIRECTORY_SEPARATOR . $fileId . '.' . $this->extension;
            if ((!$checkFile) || is_file($filePath)) {
                return $filePath;
            }
        }

        return false;
    }

    /**
     * @return bool|string return Url of stored file
     */
    public function getFileUrl()
    {
        if (($fileId = $this->getFileId()) && $this->getFilePath()) {
            return Yii::getAlias($this->directoryUrl) . '/' . $this->type . '/' . $fileId . '.' . $this->extension;
        }

        return false;
    }

    /**
     * @param $lastFileName string Cleaning after move file
     */
    protected function deleteEmptyDirectory($lastFileName)
    {
        try {
            $directory = dirname(FileHelper::normalizePath($lastFileName));
            if ($this->checkDirectoryIsEmpty($directory)) {
                FileHelper::removeDirectory($directory);
            }
        } catch (ErrorException $exception) {
        }
    }

    /**
     * Check for empty dir
     * @param $dir
     * @return bool|null
     */
    protected function checkDirectoryIsEmpty($dir)
    {
        if (!is_readable($dir)) {
            return null;
        }
        $handle = opendir($dir);
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                return false;
            }
        }

        return true;
    }
}