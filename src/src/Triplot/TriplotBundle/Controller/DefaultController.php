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

        $fotos = $this->getDoctrine()
            ->getRepository('TriplotTriplotBundle:Fotos')
            ->findAll();
        $days = array(); 
        $fotos_array = array();
        
        foreach($fotos as $foto) {
            if (!in_array($foto->getDate(), $days)) {
                $days[] = $foto->getDate();
            }
            $f = array();
            $f['latitude'] = $foto->getLatitude();
            $f['longitude'] = $foto->getLongitude();
            $f['file'] = $foto->getFile();
            $f['date'] = $foto->getDate();
            $fotos_array[] = $f;
        }
        
        $jsonFotos = json_encode($fotos_array);
        $jsonObject = "<script type='text/javascript'> var fotos = JSON.parse('$jsonFotos');</script>"; 
        
        return $this->render('TriplotTriplotBundle:Default:index.html.twig', 
                array('fotos' => $fotos_array, 'days' => $days, 'jsonObject' => $jsonObject)
        );
    }

    public function dayAction($day) {
        
        $fotos = $this->getDoctrine()
            ->getRepository('TriplotTriplotBundle:Fotos')
            ->findAll();
        $days = array(); 
        
        foreach($fotos as $foto) {
            if (!in_array($foto->getDate(), $days)) {
                $days[] = $foto->getDate();
            }
        }

        return $this->render('TriplotTriplotBundle:Default:index.html.twig', 
                array('fotos' => $fotos, 'days' => $days)
        );
    }
    
    public function importAction() {
        $em = $this->getDoctrine()->getManager();
        $em->createQuery('DELETE FROM TriplotTriplotBundle:Fotos')->getResult();
        
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
                    $files[] = $item;
                    //Add to the database $item.
                    $f = new Fotos();
                    $f->setLatitude($item['latitude']);
                    $f->setLongitude($item['longitude']);
                    $date = explode(' ', $item['time']);
                    $f->setDate(str_replace(':', '/', $date[0]));
                    $f->setTimestamp(strtotime($item['time']));
                    $f->setFile($file);

                    $em = $this->getDoctrine()->getManager(); 
                    $em->persist($f);
                    $em->flush();
                }
            }
            
            closedir($handle);
        }
        return $this->render('TriplotTriplotBundle:Default:import.html.twig', array('files' => $files));

    }

}
