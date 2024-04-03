<?php

namespace App\Http\Controllers;

use Exception;

class PlantillaController extends Controller
{
      public function descargarPlantilla($nombre){

        $url_save = public_path() . "/plantillas/" . $nombre;

         //descargar excel
         try {

            $content = file_get_contents($url_save);
        } catch (Exception $e) {
            exit($e->getMessage());
        }

        header("Content-Disposition: attachment; filename=" . $nombre);
        
        return $content;
    }
}
