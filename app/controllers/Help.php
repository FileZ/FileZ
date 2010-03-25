<?php
/**
 * Copyright 2010  Université d'Avignon et des Pays de Vaucluse
 * email: gpl@univ-avignon.fr
 *
 * This file is part of Filez.
 *
 * Filez is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Filez is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Filez.  If not, see <http://www.gnu.org/licenses/>.
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