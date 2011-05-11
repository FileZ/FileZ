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
 * Description of Fz_UploadMonitor
 *
 * @author Arnaud Didry <arnaud.didry@univ-avignon.fr>
 */
interface Fz_UploadMonitor {

    public function isInstalled ();
    public function getProgress ($uploadId);
    public function getUploadIdName ();

};
