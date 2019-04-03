# Add upload file to form and store it to model-id-depends folder 



## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

add

```
"bscheshirwork/yii2-file-upload-behavior": "@dev"
```

to the require section of your `composer.json` file.


The namespace of module is a
`bscheshirwork\fub`

## Usage

We can add of this elements in some places for complex solution: 

In form (also can be combine with model. In this case add `fileUpload` and `fileSave` to model. See example below)

TagForm.php
```php
use bscheshirwork\fub\FileUploadBehavior;
...
    public function behaviors()
    {
        return [
            'fileUpload' => [ // name is important
                'class' => FileUploadBehavior::class,
                'attribute' => 'image',
                'tempDirectory' => '@storageWeb/images/temp',
                'tempDirectoryUrl' => '@storageUrl/images/temp',
            ],
        ];
    }
    
    public function form2Models(): ActiveRecordInterface
    {
        $this->getBehavior('fileUpload')->uploadFile();
        $this->_model->getBehavior('fileSave')->uploader = $this->getBehavior('fileUpload');

        return $this->_model;
    }
```

In model

Tag.php
```php
use bscheshirwork\fub\FileSaveBehavior;
...
    public function behaviors()
    {
        return [
            'fileSave' => [ // name is important
                'class' => FileSaveBehavior::class,
                'directory' => '@storageWeb/images/single',
                'directoryUrl' => '@storageUrl/images/single',
                'type' => 'tag',
                'fileVersions' => [
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
                            'fileNameVersion' => 'default',
                        ],
                    ],
                ],
            ],
        ];
    }
```

In view

_form.php
```php
use bscheshirwork\fub\FileInputWidget;
?>
...
    <?= $form->field($model, 'image')->widget(FileInputWidget::class, [
        'attributeLabel' => Yii::t('expand-tag', 'Image'),
        'buttonLabel' => Yii::t('expand-tag', 'Select tag SVG icon...'),
        'deleteButtonLabel' => Yii::t('expand-tag', 'Delete tag SVG icon'),
        'template' => "{label}\n{image}\n{tempImage}\n{deleteImageButton}\n<span class='btn btn-primary btn-file'>\n{buttonLabel}\n{input}</span>\n{hint}\n{error}",
        'uploader' => $model->getBehavior('fileUpload'),
        'saver' => $model->model->getBehavior('fileSave'),
        'deleteAction' => 'crud/delete-file',
    ]) ?>
```

In controller

CrudController.php
```php
use bscheshirwork\fub\FileDeleteAction;
...
   public function actions()
    {
        return [
            'delete-file' => [
                'class' => FileDeleteAction::class,
                'action' => 'delete-file',
            ],
        ];
    }
```

Display image

index.php
```php
            [
                'label' => Yii::t('expand-tag', 'Image'),
                'content' => function ($model) {
                    return $model->fileUrl ? Html::img($model->fileUrl) : '';
                },
            ],
```


## Advanced usage

In form

BlogForm.php
```php
use bscheshirwork\fub\FileUploadBehavior;
...
    public function behaviors()
    {
        return [
            'fileUpload' => [ // name is important
                'class' => FileUploadBehavior::class,
                'attribute' => 'image',
                'mimeTypeFilter' => 'image/jpeg',
                'tempDirectory' => '@storageWeb/images/temp',
                'tempDirectoryUrl' => '@storageUrl/images/temp',
            ],
        ];
    }
    
    public function form2Models(): ActiveRecordInterface
    {
        $this->getBehavior('fileUpload')->uploadFile();
        $this->_model->getBehavior('fileSave')->uploader = $this->getBehavior('fileUpload');

        return $this->_model;
    }
```

In model

Blog.php
```php
use bscheshirwork\fub\FileSaveBehavior;
use bscheshirwork\fub\FileHelper;
...
    public function behaviors()
    {
        return [
            'fileSave' => [ // name is important
                'class' => FileSaveBehavior::class,
                'directory' => '@storageWeb/images/single',
                'directoryUrl' => '@storageUrl/images/single',
                'type' => 'blog',
                'fileVersions' => [
                    'default' => [
                        'fileName' => [
                            /** @see FileSaveBehavior::defaultFileName() */
                            [FileSaveBehavior::class, 'defaultFileName'],
                            'extension' => 'jpg',
                        ],
                        'fileUrl' => [
                            /** @see FileSaveBehavior::defaultFileUrl() */
                            [FileSaveBehavior::class, 'defaultFileUrl'],
                            'extension' => 'jpg',
                            'fileNameVersion' => 'default',
                        ],
                        'postProcessing' => [
                            /** @see BlogArticle::fileUploadDefaultPostProcessing() */
                            [$this, 'fileUploadDefaultPostProcessing'],
                            'imaginary' => 'http://imaginary:9000',
                            'imaginaryDir' => '/images/single',
                            'format' => 'jpeg',
                            'extension' => 'jpg',
                            'fileNameVersion' => 'default',
                        ],
                    ],
                    'big' => [
                        'fileName' => [
                            /** @see BlogArticle::fileNameForBehavior() */
                            [$this, 'fileNameForBehavior'],
                            'extension' => 'jpg',
                            'suffix' => 'big',
                        ],
                        'fileUrl' => [
                            /** @see BlogArticle::fileUrlForBehavior() */
                            [$this, 'fileUrlForBehavior'],
                            'extension' => 'jpg',
                            'suffix' => 'big',
                            'fileNameVersion' => 'big',
                        ],
                        'postProcessing' => [
                            /** @see BlogArticle::fileUploadPostProcessing() */
                            [$this, 'fileUploadPostProcessing'],
                            'imaginary' => 'http://imaginary:9000',
                            'imaginaryDir' => '/images/single',
                            'width' => 1110,
                            'height' => 340,
                            'extension' => 'jpg',
                            'fileNameVersion' => 'big',
                        ],
                    ],
                    'small' => [
                        'fileName' => [
                            /** @see BlogArticle::fileNameForBehavior() */
                            [$this, 'fileNameForBehavior'],
                            'extension' => 'jpg',
                            'suffix' => 'small',
                        ],
                        'fileUrl' => [
                            /** @see BlogArticle::fileUrlForBehavior() */
                            [$this, 'fileUrlForBehavior'],
                            'extension' => 'jpg',
                            'suffix' => 'small',
                            'fileNameVersion' => 'small',
                        ],
                        'postProcessing' => [
                            /** @see BlogArticle::fileUploadPostProcessing() */
                            [$this, 'fileUploadPostProcessing'],
                            'imaginary' => 'http://imaginary:9000',
                            'imaginaryDir' => '/images/single',
                            'width' => 350,
                            'height' => 208,
                            'extension' => 'jpg',
                            'fileNameVersion' => 'small',
                        ],
                    ],
                ],
            ],
        ];
    }
    ...
    
    public function fileNameForBehavior($extension, $suffix)
    {
        /** @var FileSaveBehavior $behavior */
        $behavior = $this->getBehavior('fileSave');
        if ($fileId = $behavior->getFileId()) {
            $filePath = Yii::getAlias($behavior->directory) . DIRECTORY_SEPARATOR . $behavior->type . DIRECTORY_SEPARATOR . $fileId . '_' . $suffix . '.' . $extension;

            return $filePath;
        }

        return false;
    }

    public function fileUrlForBehavior($extension, $suffix, $version = 'default')
    {
        /** @var FileSaveBehavior $behavior */
        $behavior = $this->getBehavior('fileSave');
        if (($fileId = $behavior->getFileId()) && $behavior->getFilePath($version, true)) {
            return Yii::getAlias($behavior->directoryUrl) . '/' . $behavior->type . '/' . $fileId . '_' . $suffix . '.' . $extension;
        }

        return false;
    }

    /**
     * @param $oldFileName
     * @param $imaginary
     * @param $imaginaryDir
     * @param string $format Specify the image format to output. Possible values are: jpeg, png, webp
     * @param string $extension
     * @param string $version
     */
    public function fileUploadDefaultPostProcessing($oldFileName, $imaginary, $imaginaryDir, $format = 'jpeg', $extension = 'jpg', $version = 'default')
    {
        /** @var FileSaveBehavior $behavior */
        $behavior = $this->getBehavior('fileSave');
        $fileId = $behavior->getFileId();
        if ($filePath = $behavior->getFilePath($version, false)) {
            try {
                FileHelper::unlink($filePath);
            } catch (ErrorException $exception) {
            }
            rename($oldFileName, $filePath);
            FileHelper::deleteEmptyDirectory($oldFileName);


            $originalImagePathForImagine = $imaginaryDir . DIRECTORY_SEPARATOR . $behavior->type . DIRECTORY_SEPARATOR . $fileId . '.' . $extension;
            $httpQuery = http_build_query([
                'file' => $originalImagePathForImagine,
                'type' => $format,
                'quality' => 100,
            ]);

            $url = $imaginary . '/convert?' . $httpQuery;

            file_put_contents($filePath, file_get_contents($url));
        }
    }

    public function fileUploadPostProcessing($oldFileName, $imaginary, $imaginaryDir, $width, $height, $extension = 'jpg', $version = 'default')
    {
        /** @var FileSaveBehavior $behavior */
        $behavior = $this->getBehavior('fileSave');
        $fileId = $behavior->getFileId();
        if ($filePath = $behavior->getFilePath($version, false)) {
            try {
                FileHelper::unlink($filePath);
            } catch (ErrorException $exception) {
            }

            $originalImagePathForImagine = $imaginaryDir . DIRECTORY_SEPARATOR . $behavior->type . DIRECTORY_SEPARATOR . $fileId . '.' . $extension;
            $httpQuery = http_build_query([
                'file' => $originalImagePathForImagine,
                'width' => $width,
                'height' => $height,
            ]);

            $url = $imaginary . '/crop?' . $httpQuery;

            file_put_contents($filePath, file_get_contents($url));
        }
    }

```


Display image

_form.php
```php
    <img src="<?= $model->model->getBehavior('fileSave')->getFileUrl('big') ?>">
```

## Single model

in model:

Reason.php
```php
use bscheshirwork\fub\FileUploadBehavior;
use bscheshirwork\fub\FileSaveBehavior;
...
    public function behaviors()
    {
        return [
            'fileUpload' => [ // name is important
                'class' => FileUploadBehavior::class,
                'attribute' => 'image',
                'mimeTypeFilter' => ['image/jpeg', 'image/png',],
                'tempDirectory' => '@storageWeb/images/temp',
                'tempDirectoryUrl' => '@storageUrl/images/temp',
            ],
            'fileSave' => [ // name is important
                'class' => FileSaveBehavior::class,
                'directory' => '@storageWeb/images/single',
                'directoryUrl' => '@storageUrl/images/single',
                'type' => 'reason',
                'fileVersions' => [
                    'default' => [
                        'fileName' => [
                            /** @see FileSaveBehavior::defaultFileName() */
                            [FileSaveBehavior::class, 'defaultFileName'],
                            'extension' => 'jpg',
                        ],
                        'fileUrl' => [
                            /** @see FileSaveBehavior::defaultFileUrl() */
                            [FileSaveBehavior::class, 'defaultFileUrl'],
                            'extension' => 'jpg',
                            'fileNameVersion' => 'default',
                        ],
                        'postProcessing' => [
                            /** @see Reason::fileUploadDefaultPostProcessing() */
                            [$this, 'fileUploadDefaultPostProcessing'],
                            'imaginary' => 'http://imaginary:9000',
                            'imaginaryDir' => '/images/single',
                            'format' => 'jpeg',
                            'extension' => 'jpg',
                            'fileNameVersion' => 'default',
                        ],
                    ],
                    'small' => [
                        'fileName' => [
                            /** @see Reason::fileNameForBehavior() */
                            [$this, 'fileNameForBehavior'],
                            'extension' => 'jpg',
                            'suffix' => 'small',
                        ],
                        'fileUrl' => [
                            /** @see Reason::fileUrlForBehavior() */
                            [$this, 'fileUrlForBehavior'],
                            'extension' => 'jpg',
                            'suffix' => 'small',
                            'fileNameVersion' => 'small',
                        ],
                        'postProcessing' => [
                            /** @see Reason::fileUploadPostProcessing() */
                            [$this, 'fileUploadPostProcessing'],
                            'imaginary' => 'http://imaginary:9000',
                            'imaginaryDir' => '/images/single',
                            'width' => 350,
                            'height' => 250,
                            'extension' => 'jpg',
                            'fileNameVersion' => 'small',
                        ],
                    ],
                ],
            ],
        ];
    }
...

    /**
     * {@inheritDoc}
     */
    public function beforeValidate()
    {
        $this->getBehavior('fileUpload')->uploadFile();
        $this->getBehavior('fileSave')->uploader = $this->getBehavior('fileUpload');

        return parent::beforeValidate();
    }

    /**
     * Return file name for build specific version of file
     * @param $extension
     * @param $suffix
     * @return bool|string
     */
    public function fileNameForBehavior($extension, $suffix)
    {
        /** @var FileSaveBehavior $behavior */
        $behavior = $this->getBehavior('fileSave');
        if ($fileId = $behavior->getFileId()) {
            $filePath = Yii::getAlias($behavior->directory) . DIRECTORY_SEPARATOR . $behavior->type . DIRECTORY_SEPARATOR . $fileId . '_' . $suffix . '.' . $extension;

            return $filePath;
        }

        return false;
    }

    /**
     * Return URL for specific version of file
     * @param $extension
     * @param $suffix
     * @param string $version
     * @return bool|string
     */
    public function fileUrlForBehavior($extension, $suffix, $version = 'default')
    {
        /** @var FileSaveBehavior $behavior */
        $behavior = $this->getBehavior('fileSave');
        if (($fileId = $behavior->getFileId()) && $behavior->getFilePath($version, true)) {
            return Yii::getAlias($behavior->directoryUrl) . '/' . $behavior->type . '/' . $fileId . '_' . $suffix . '.' . $extension;
        }

        return false;
    }

    /**
     * First-time save file to model-depend directory and convert to single image form
     * @param $oldFileName
     * @param $imaginary
     * @param $imaginaryDir
     * @param string $format Specify the image format to output. Possible values are: jpeg, png, webp
     * @param string $extension
     * @param string $version
     */
    public function fileUploadDefaultPostProcessing($oldFileName, $imaginary, $imaginaryDir, $format = 'jpeg', $extension = 'jpg', $version = 'default')
    {
        /** @var FileSaveBehavior $behavior */
        $behavior = $this->getBehavior('fileSave');
        $fileId = $behavior->getFileId();
        if ($filePath = $behavior->getFilePath($version, false)) {
            try {
                FileHelper::unlink($filePath);
            } catch (ErrorException $exception) {
            }
            rename($oldFileName, $filePath);
            FileHelper::deleteEmptyDirectory($oldFileName);


            $originalImagePathForImagine = $imaginaryDir . DIRECTORY_SEPARATOR . $behavior->type . DIRECTORY_SEPARATOR . $fileId . '.' . $extension;
            $httpQuery = http_build_query([
                'file' => $originalImagePathForImagine,
                'type' => $format,
                'quality' => 100,
            ]);

            $url = $imaginary . '/convert?' . $httpQuery;

            file_put_contents($filePath, file_get_contents($url));
        }
    }

    /**
     * Get another version of file. Crop to size.
     * @param $oldFileName
     * @param $imaginary
     * @param $imaginaryDir
     * @param $width
     * @param $height
     * @param string $extension
     * @param string $version
     */
    public function fileUploadPostProcessing($oldFileName, $imaginary, $imaginaryDir, $width, $height, $extension = 'jpg', $version = 'default')
    {
        /** @var FileSaveBehavior $behavior */
        $behavior = $this->getBehavior('fileSave');
        $fileId = $behavior->getFileId();
        if ($filePath = $behavior->getFilePath($version, false)) {
            try {
                FileHelper::unlink($filePath);
            } catch (ErrorException $exception) {
            }

            $originalImagePathForImagine = $imaginaryDir . DIRECTORY_SEPARATOR . $behavior->type . DIRECTORY_SEPARATOR . $fileId . '.' . $extension;
            $httpQuery = http_build_query([
                'file' => $originalImagePathForImagine,
                'width' => $width,
                'height' => $height,
            ]);

            $url = $imaginary . '/crop?' . $httpQuery;

            file_put_contents($filePath, file_get_contents($url));
        }
    }

```

in controller:

CrudController.php
```php
use bscheshirwork\fub\FileDeleteAction;
...
    public function actions()
    {
        return [
            'delete-file' => [
                'class' => FileDeleteAction::class,
                'action' => 'delete-file',
            ],
        ];
    }
```

in view:

_form.php
```php
    <?= $form->field($model, 'image')->widget(FileInputWidget::class, [
        'attributeLabel' => Yii::t('reason', 'Image'),
        'buttonLabel' => Yii::t('reason', 'Select image...'),
        'deleteButtonLabel' => Yii::t('reason', 'Delete images'),
        'template' => "{label}\n{image}\n{tempImage}\n{deleteImageButton}\n<span class='btn btn-primary btn-file'>\n{buttonLabel}\n{input}</span>\n{hint}\n{error}",
        'uploader' => $model->getBehavior('fileUpload'),
        'saver' => $model->getBehavior('fileSave'),
        'deleteAction' => 'crud/delete-file',
    ]) ?>
```

index.php
```php
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'id',
            'name',
            [ // main image
                'headerOptions' => ['class' => 'image-column'],
                'content' => function ($model) {
                    /** @var Reason $model */
                    return Html::img($model->getBehavior('fileSave')->getFileUrl('small'), ['class' => 'img-fit-to-column']);
                },
            ],

            [
                'class' => yii\grid\ActionColumn::class,
                'template' => '{update} {delete}',
            ],
        ],
    ]); ?>
```


view.php
```php
    <img src="<?= $model->getBehavior('fileSave')->getFileUrl('small') ?>">
```
