<?php
namespace Fudge\Sknife\Util;

use Fudge\Sknife\Exception\BusinessException;

/**
 * Files related functions
 * @author Yohann Marillet <yohann.marillet@gmail.com>
 * @since 11/10/13
 */
class Files
{
    /**
     * Converts a string to a filesystem safe name
     *
     * @param  string $string      the string to clean
     * @param  string $replacement replacement character for unauthorized ones
     * @return string
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     * @see http://stackoverflow.com/questions/2668854/sanitizing-strings-to-make-them-url-and-filename-safe
     */
    public static function sanitize($string, $replacement = '-')
    {
        // Removes file browsing characters from the start and the end of the string
        $return = trim($string,'./');

        // Replace all weird characters with the replacement string
        $return = preg_replace('#[^\w\-~_\.]#', $replacement, $return);

        if (!empty($replacement)) {
            // remove multiple occurrences of the replacement string
            $return = preg_replace('#' . preg_quote($replacement, '#') . '+#', $replacement, $return);
        }

        return $return;
    }

    /**
     * Appends a string to the filename (before the extension) if the file exists
     *
     * @param string $path
     * @param string $name
     * @param string $append
     *
     * @return string
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public static function appendIfFileExists($path, $name, $append='-')
    {
        $return = $name;
        $filePath = rtrim($path,'/').'/'.$name;
        while (is_file($filePath)) {
            $parts = explode('.', $return);
            $last = array_pop($parts);

            if (empty($parts)) {
                $last .= $append;
            } else {
                $last = $append.$last;
            }

            array_push($parts, $last);
            $return = implode('.', $parts);
        }

        return $return;
    }

    /**
     * Recursive Rmdir
     * @param $dir
     * @see http://www.commentcamarche.net/faq/12255-warning-rmdir-directory-not-empty
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public static function rmdir_recursive($dir)
    {
        if (is_dir($dir)) {
            // content of the dir
            $dir_content = scandir($dir);
            // is it a directory ?
            if ($dir_content !== false) {
                // loop on entries
                foreach ($dir_content as $entry) {
                    // do not take current directory and parent directory into consideration
                    if ($entry != '.' && $entry!= '..') {
                        $entryPath = $dir . '/' . $entry;
                        if (!is_dir($entryPath)) {
                            // if the entry is a file: delete it
                            unlink($entryPath);
                        } else {
                            // the entry is a directory, loop on it !
                            static::rmdir_recursive($entryPath);
                        }
                    }
                }
            }
            // we deleted everything inside the directory, we can now delete it safely
            rmdir($dir);
        }
    }

    /**
     * Checks whether a file exists or not, throws an exception if not
     *
     * @param string $filepath
     * @throws BusinessException
     * @return bool
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public static function requireFileExists($filepath)
    {
        if (!file_exists($filepath)) {
            throw new BusinessException('File does not exist: "' . $filepath . '"');
        }

        return true;
    }

    /**
     * Checks whether a file is writable or not, throws an exception if not
     *
     * @param string $filepath
     * @throws BusinessException
     * @return bool
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public static function requireWritePermissions($filepath)
    {
        if (!self::hasWritePermissions($filepath)) {
            throw new BusinessException('Cannot write to "' . $filepath . '". Please check the filesystem permissions.');
        }

        return true;
    }

    /**
     * Checks whether a file is writable or not
     *
     * @param string $filepath
     * @return bool
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public static function hasWritePermissions($filepath) {
        $return = false;
        $dest_dir = dirname($filepath);
        if (!is_dir($dest_dir)
                || !is_writable($dest_dir)
                || (file_exists($filepath) && !is_writable($filepath))
        ) {
            $return = true;
        }

        return $return;
    }

    /**
     * Transforms an array of lines into a CSV file
     * Options available:
     * - delimiter: delimiter for the CSV file (default ',')
     * - enclosure: enclosure for the CSV file (default '"')
     * - prefix: prefix for the temp file name (default '_')
     * - folder: dest folder for the temp generated file (default system temp folder)
     *
     * @param array $lines 2-dimensional array
     * @param array $options
     * @return string the path to the generated file
     */
    public static function toCSV($lines,$options=array()) {
        if(!isset($options['delimiter'])) {
            $options['delimiter']=',';
        }

        if(!isset($options['enclosure'])) {
            $options['enclosure']='"';
        }

        if(!isset($options['prefix'])) {
            $options['prefix']='_';
        }

        if(!isset($options['folder'])) {
            $options['folder']=null;
        }

        $filename = tempnam($options['folder'], $options['prefix']);
        $fp = fopen($filename, 'w+');
        foreach($lines as $line) {
            fputcsv($fp, $line, $options['delimiter'], $options['enclosure']);
        }
        fclose($fp);

        return $filename;
    }
}
