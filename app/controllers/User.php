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
 * Controller used for user administration
 */
class App_Controller_User extends Fz_Controller {

    public function init () {
        layout ('layout'.DIRECTORY_SEPARATOR.'admin.html.php');
    }

    /**
     * Action list users
     * List users.
     */
    public function indexAction () {
        $this->secure ('admin');
        set ('users', Fz_Db::getTable ('User')->findAll ()); // TODO paginate
        return html ('user/index.php');
    }

    /**
     * Action called to display user details
     */
    public function showAction () {
        $this->secure ('admin');
        set ('user', Fz_Db::getTable ('User')->findById (params ('id')));
        return html ('user/show.php');
    }

    /**
     * Action called to create a new user
     */
    public function createAction () {
        $this->secure ('admin');
        return html ('user/create.php');
        //TODO
    }

    /**
     * Action called to edit a user
     */
    public function editAction () {
        $this->secure ('admin');
        return html ('user/edit.php');
        //TODO
    }

    /**
     * Action called to delete a user
     */
    public function deleteAction () {
        $this->secure ('admin');
        return html ('user/delete.php');
        //TODO
    }
}

