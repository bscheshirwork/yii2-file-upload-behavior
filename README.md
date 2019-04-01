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

In form (also can be combine with model. In this case add `fileUpload` and `fileSave` to model)

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

In model

Blog.php
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
                'type' => 'blog',
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
                    'big' => [
                        'fileName' => [
                            function ($extension) {
                                /** @var FileSaveBehavior $behavior */
                                $behavior = $this->getBehavior('fileSave');
                                if ($fileId = $behavior->getFileId()) {
                                    $filePath = Yii::getAlias($behavior->directory) . DIRECTORY_SEPARATOR . $behavior->type . DIRECTORY_SEPARATOR . $fileId . '_big.' . $extension;

                                    return $filePath;
                                }

                                return false;
                            },
                            'extension' => 'svg',
                        ],
                        'fileUrl' => [
                            function ($extension, $version = 'default') {
                                /** @var FileSaveBehavior $behavior */
                                $behavior = $this->getBehavior('fileSave');
                                if (($fileId = $behavior->getFileId()) && $behavior->getFilePath($version, true)) {
                                    return Yii::getAlias($behavior->directoryUrl) . '/' . $behavior->type . '/' . $fileId . '_big.' . $extension;
                                }

                                return false;
                            },
                            'extension' => 'svg',
                            'fileNameVersion' => 'big',
                        ],
                        'postProcessing' => [
                            function ($oldFileName, $version = 'default') {
                                /** @var FileSaveBehavior $behavior */
                                $behavior = $this->getBehavior('fileSave');
                                $oldFileName = $behavior->getFilePath('default', false);
                                if ($filePath = $behavior->getFilePath($version, false)) {
                                    try {
                                        FileHelper::unlink($filePath);
                                    } catch (ErrorException $exception) {
                                    }
                                    rename($oldFileName, $filePath);
                                }
                            },
                            'fileNameVersion' => 'big',
                        ],
                    ],
                ],
            ],
        ];
    }
```


Display image

_form.php
```php
    <img src="<?= $model->model->getBehavior('fileSave')->getFileUrl('big') ?>">
```

