<?php

class rex_twoeg_utils {
    public static function getHtmlFromMDFile($mdFile) {
        $file = rex_file::get(rex_path::addon('twoeg', $mdFile), '');
        if (!empty($file)) {
            return Parsedown::instance()->parse($file);
        }
        return 'File not found...';
    }
}