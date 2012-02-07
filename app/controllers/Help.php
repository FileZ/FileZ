<?php

/**
 * @file
 * Short description.
 * 
 * Long description.
 * 
 * @package FileZ
 */

/**
 * Controller used to generate user documentation from markdown files
 */
class App_Controller_Help extends Fz_Controller {

    /**
     *
     */
    public function indexAction () {
        return $this->showPage ('index');
    }

    /**
     *
     */
    public function showPageAction () {
        return $this->showPage (params ('page'));
    }

    /**
     *
     */
    protected function showPage ($pageName) {
        $locale = option ('locale')->getLanguage ();
        $filename = str_replace ('_', '/', $pageName);
        $filename = option ('root_dir').'/doc/user/'.$locale.'/'.$filename.'.txt';

        if (file_exists ($filename)) {
            ob_start();
            include $filename;
            return html (Markdown (ob_get_clean()), 'layout/doc.html.php');
        } else {
            return halt (NOT_FOUND, __('This documentation does not exist'));
        }
    }
}