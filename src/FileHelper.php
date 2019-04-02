<?php


namespace bscheshirwork\fub;


use yii\base\ErrorException;

/**
 * Class FileHelper
 * @package bscheshirwork\fub
 */
class FileHelper extends \yii\helpers\FileHelper
{

    /**
     * Cleaning after move file
     * @param $lastFileName string filename from old location
     */
    public static function deleteEmptyDirectory($lastFileName)
    {
        try {
            $directory = dirname(static::normalizePath($lastFileName));
            if (static::checkDirectoryIsEmpty($directory)) {
                static::removeDirectory($directory);
            }
        } catch (ErrorException $exception) {
        }
    }

    /**
     * Check for empty dir
     * @param $dir
     * @return bool|null
     */
    public static function checkDirectoryIsEmpty($dir)
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