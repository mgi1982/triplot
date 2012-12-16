<?php

namespace Triplot\TriplotBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Triplot\TriplotBundle\Entity\Fotos;

class DefaultController extends Controller {

    private function DMStoDEC($deg, $min, $sec) {
        return $deg + ((($min * 60) + ($sec)) / 3600);
    }

    private function realValue($value) {
        $parts = explode('/', $value);
        return $parts[0] / $parts[1];
    }

    public function indexAction() {
        
        return $this->render('TriplotTriplotBundle:Default:index.html.twig', 
                array('pictures' => $pictures)
        );
    }

    public function importAction() {
        $dir = __DIR__ . '/../Resources/public/pictures/';
        $files = array();
        if ($handle = opendir($dir)) {
            while ($file = readdir($handle)) {
                $file_path = $dir . $file; 
                $exif = @exif_read_data($file_path, 'IFD0');

                if ($exif == false) {
                    continue;
                }
                
                $item = array();

                if (isset($exif['GPSLongitude'][0]) && $exif['GPSLatitude'][0] !== '0/0') {
                    $deg = $this->realValue($exif['GPSLatitude'][0]);
                    $min = $this->realValue($exif['GPSLatitude'][1]);
                    $seg = $this->realValue($exif['GPSLatitude'][2]);
                    $ind = ($exif['GPSLatitudeRef'] == 'N') ? '+' : '-';
                    $item['latitude'] = $ind . $this->DMStoDEC($deg, $min, $seg);
                }

                if (isset($exif['GPSLongitude'][0]) && $exif['GPSLongitude'][0] !== '0/0') {
                    $deg = $this->realValue($exif['GPSLongitude'][0]);
                    $min = $this->realValue($exif['GPSLongitude'][1]);
                    $seg = $this->realValue($exif['GPSLongitude'][2]);
                    $ind = ($exif['GPSLongitudeRef'] == 'W') ? '-' : '+';
                    $item['longitude'] = $ind . $this->DMStoDEC($deg, $min, $seg);
                }
                
                if (isset($item['longitude']) && isset($item['latitude'])) {
                    $item['time'] = $exif['DateTime'];
                    $item['img'] = $file;
                    $files[] = $item;
                   
                    //Add to the database $item.
                    $f = new Fotos();
                    $f->setLatitude($item['latitude']);
                    $f->setLongitude($item['longitude']);
                    $f->setDate($item['time']);
                    $f->setTimestamp(strtotime($item['time']));
                    
                    $this->getDoctrine()
                        ->getManager()
                        ->persist($f)
                        ->flush();
                }
            }
            
            closedir($handle);
        }
        var_dump($files);
        return $this->render('TriplotTriplotBundle:Default:import.html.twig', array('files' => $files));

    }

}
