<?php

// TODO vérifier si l'utilisateur n'est pas un robot (imposer un délais d'attente exponentiel lorsque qu'il y a plus de 3 requêtes en moins de 5 secondes)
// TODO vérifier mot de passe s'il y a lieu
// TODO vérifier si l'utilisateur est passé par download preview
class App_Controller_Download extends Fz_Controller {

    /**
     * Display file info and open a download dialog
     */
    public function previewAction () {
        $file = $this->getFile();
        set ('file', $file);
        return html ('download/preview.php');
    }

    /**
     * Send a file
     */
    public function startAction () {
        $file = $this->getFile();
        $file->download_count = $file->download_count + 1;
        $file->save ();
        header ('Content-Disposition: attachment; filename="'.$file->getFileName ().'"');
        return render_file (fz_config_get ('app', 'upload_dir').'/'.$file->getId ());
    }

    /**
     * Allows to download file with filez-1.x urls
     */
    public function startFzOneAction () {
        // TODO
        $file_hash = $_GET ['ad'];
    }

    private function getFile () {
        $file = Fz_Db::getTable('File')->findByHash (params ('file_hash'));
        if ($file === null)
            halt (NOT_FOUND, 'There is no file for this hash code');
        return $file;
    }

}
