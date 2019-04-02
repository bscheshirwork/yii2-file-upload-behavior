<?php

namespace bscheshirwork\fub;


use Yii;
use yii\base\Behavior;
use yii\base\ErrorException;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;

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
     * @var string The separator. Each modal can be store images to difference folder: directory/type/
     */
    public $type = '';

    /**
     * Glue for build string representation of composite primary keys
     * @var string
     */
    public $pkGlue = '_';

    /**
     * Config for version of files. By default this is only one version named 'default'
     *
     * 'fileName' => Config for create file name of image.
     * This can be a simple equial to model id with extension (default)
     * or some another transformations.
     * First element of array is a function (can be a closure)
     * any another elements will be pass to this function as params.
     * Note: "$this" can be use in closure.
     * @see FileSaveBehavior::getFilePath()
     *
     * 'fileUrl' => Config for create urlof image.
     * First element of array is a function (can be a closure)
     * any another elements will be pass to this function as params.
     * @see FileSaveBehavior::getFileUrl()
     *
     * 'postProcessing' => Config for process image after model is changes.
     * This can be a simple save (default) or a some other actions like crop image
     * First element of array is a function (can be a closure)
     * Second element is a oldFileName - source for transforms. This is a first param to pass into function.
     * any another elements will be pass to this function as rest params.
     * Note: "$this" can be use in closure: $this->getBehavior('fileSave')->directory
     * @see FileSaveBehavior::attachFileToModel()
     *
     * @var array
     */
    public $fileVersions = [
        'default' => [
            'fileName' => [
                /** @see FileSaveBehavior::defaultFileName() */
                [FileSaveBehavior::class, 'defaultFileName'],
                'extension' => 'svg',
            ],
            'fileUrl' => [
                /** @see FileSaveBehavior::defaultFileUrl() */
                [FileSaveBehavior::class, 'defaultFileUrl'],
                'extension' => 'svg',
                'fileNameVersion' => 'default',
            ],
            'postProcessing' => [
                /** @see FileSaveBehavior::defaultAttachFileToModel() */
                [FileSaveBehavior::class, 'defaultAttachFileToModel'],
            ],
        ],
    ];

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
        foreach ($this->fileVersions as $version => $nothing) {
            if ($filePath = $this->getFilePath($version)) {
                try {
                    FileHelper::unlink($filePath);
                } catch (ErrorException $exception) {
                }
            }
        }
    }

    /**
     * Move new file to actual ID
     * @throws \yii\base\Exception
     */
    public function afterUpdate()
    {
        /** move existing image */
        if ($this->owner->oldPrimaryKey != $this->owner->primaryKey) {
            $oldFileId = implode($this->pkGlue, (array) $this->owner->oldPrimaryKey);
            $oldFileName = $this->directory . DIRECTORY_SEPARATOR . $this->type . DIRECTORY_SEPARATOR . $oldFileId . '.' . $this->extension;
            $this->attachFileToModel($oldFileName);
        }
        /** upload new image */
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
            $this->attachFileToModel($this->uploader->tempFileName);
        }
    }

    /**
     * @return bool|string propose base filename w/o path.
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
     * @return bool|string return path of stored file
     */
    public function defaultFileName($extension)
    {
        if ($fileId = $this->getFileId()) {
            $filePath = Yii::getAlias($this->directory) . DIRECTORY_SEPARATOR . $this->type . DIRECTORY_SEPARATOR . $fileId . '.' . $extension;

            return $filePath;
        }

        return false;
    }

    /**
     * @param string $version the version from $fileVersions
     * @return bool|string return Url of stored file
     */
    public function defaultFileUrl($extension, $version = 'default')
    {
        if (($fileId = $this->getFileId()) && $this->getFilePath($version, true)) {
            return Yii::getAlias($this->directoryUrl) . '/' . $this->type . '/' . $fileId . '.' . $extension;
        }

        return false;
    }

    /**
     * Attach uploaded file to model.
     * Move existing file to new name.
     * @param string $oldFileName
     * @param string $version the version from $fileVersions
     */
    public function defaultAttachFileToModel($oldFileName, $version = 'default')
    {
        if ($filePath = $this->getFilePath($version, false)) {
            try {
                FileHelper::unlink($filePath);
            } catch (ErrorException $exception) {
            }
            rename($oldFileName, $filePath);
            FileHelper::deleteEmptyDirectory($oldFileName);
        }
    }

    /**
     * @param string $version the version from $fileVersions
     * @param bool $checkFile
     * @return bool|string return path of stored file
     */
    public function getFilePath($version = 'default', $checkFile = true)
    {
        if ($fileId = $this->getFileId()) {
            if ($params = $this->fileVersions[$version]['fileName']) {
                $function = array_shift($params);
                $filePath = call_user_func_array($function, $params);
                if ((!$checkFile) || is_file($filePath)) {
                    return $filePath;
                }
            }
        }

        return false;
    }

    /**
     * get Url of stored file
     * @param string $version the version from $fileVersions
     * @return bool|string return Url of stored file
     */
    public function getFileUrl($version = 'default')
    {
        if (($fileId = $this->getFileId())) {
            if ($params = $this->fileVersions[$version]['fileUrl']) {
                $function = array_shift($params);
                $fileUrl = call_user_func_array($function, $params);

                return $fileUrl;
            }
        }

        return false;
    }

    /**
     * Call function for:
     * Attach uploaded file to model.
     * Move existing file to new name.
     * @param $oldFileName
     */
    public function attachFileToModel($oldFileName)
    {
        foreach ($this->fileVersions as $version => $nothing) {
            if ($params = $this->fileVersions[$version]['postProcessing']) {
                $function = array_shift($params);
                array_unshift($params, $oldFileName);
                call_user_func_array($function, $params);
            }
        }
    }
}