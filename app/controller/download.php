<?php

// TODO vérifier si l'utilisateur n'est pas un robot (imposer un délais d'attente exponentiel lorsque qu'il y a plus de 3 requêtes en moins de 5 secondes)

function fz_action_download_preview () {
    $file = fz_model_file_find_by_hash (params ('file_hash'));
    if ($file === null)
        halt (NOT_FOUND, 'There is no file for this hash code');

    return html ('download/preview.php');
}

function fz_action_download_start () {
    // TODO vérifier mot de passe s'il y a lieu
    // TODO vérifier si l'utilisateur est passé par download preview
    return render_file (option ('upload_dir').'/'.params ('file_hash'));
}

/**
 * Allows to download file with filez-1.x urls
 */
function fz_action_download_start_old1 () {
    // TODO
}
