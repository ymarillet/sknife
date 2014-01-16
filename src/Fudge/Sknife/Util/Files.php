<?php
namespace Fudge\Sknife\Util;

/**
 * Files related functions
 * @author Yohann Marillet
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
     * @author Yohann Marillet
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
     * @param $filePath
     * @param  string $append
     * @return mixed
     * @author Yohann Marillet
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
     * @author Yohann Marillet
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
}
