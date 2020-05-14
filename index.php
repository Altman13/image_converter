<?php
require_once 'vendor/autoload.php';

use Jenssegers\ImageHash\ImageHash;
use Jenssegers\ImageHash\Implementations\DifferenceHash;

$hasher = new ImageHash(new DifferenceHash());

header('Content-Type: image/jpeg');
for ($i = 1; $i <= 5; $i++) {
    $fn_jpg = "$i.jpg";
    if (file_exists($fn_jpg)) {
        $svg_data = file_get_contents("$i.svg");
        $svg_xml = simplexml_load_string($svg_data);
        $new_height = intval($svg_xml['height']);
        $new_width = intval($svg_xml['width']);
        $image_jpg = imagecreatefromjpeg($fn_jpg);
        $width_jpg = ImageSX($image_jpg);
        $height_jpg = ImageSY($image_jpg);
        $new_image = imagecreatetruecolor($new_width, $new_height);
        imagecopyresampled($new_image, $image_jpg, 0, 0, 0, 0, $new_width, $new_height, $width_jpg, $height_jpg);
        ob_start();
        imagejpeg($new_image, null, 100);
        $f_data = ob_get_clean();
        $fn = "scalable_image_$i.jpg";
        fopen($fn, 'wb') or die("не удалось создать файл");
        if (file_exists($fn)) {
            file_put_contents($fn, $f_data);
        } else {
            echo 'файл не найден' . PHP_EOL;
        }
        if (file_exists("scalable_image_$i.jpg")) {
            $hash1 = $hasher->hash("scalable_image_$i.jpg");
            //TODO: можно поиграть с шагом, уменьшится размер, 
            $step = 100;
            do {
                imagejpeg($new_image, "convert_scalable_image_$i.jpg", $step);
                $hash_jpg = $hasher->hash("convert_scalable_image_$i.jpg");
                $distance = $hasher->distance($hash1, $hash_jpg);
                $step -= 5;
                echo $step . PHP_EOL;
                echo 'distance ' . $distance . PHP_EOL;
            } while ($distance < 2);
        } else {
            echo 'файл для конвертации не найден' . PHP_EOL;
        }
    } else {
        echo 'файл jpg не найден' . PHP_EOL;
    }
}
