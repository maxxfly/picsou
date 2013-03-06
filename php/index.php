<?
  $uri = $_SERVER["REQUEST_URI"];
  
  
  $memcache_obj = memcache_connect("localhost", 11211);
  memcache_set_compress_threshold($memcache_obj, 2000000, 0);
    
  if(preg_match("/^\/(t?rwb)\/([a-z0-9A-Z]+)-(\d+)-(\d+)\.(txt|jpg)$/", $uri, $arr))
  {
    $format = $arr[5];
    $width = $arr[3];
    $height = $arr[4];
    $file = $arr[2] .".jpg";
    $with_bg = 1;       
    
    if($arr[1] == "trwb")
    {
      $trim = 1;   
    }
  }
  
  elseif(preg_match("/^\/([rc])\/([a-z0-9A-Z]+)-(\d+)-(\d+)\.(txt|png)$/", $uri, $arr))
  {
    $format = $arr[5];
    $width = $arr[3];
    $height = $arr[4];
    $file = $arr[2] .".jpg";
  }
  elseif(preg_match("/^\/([a-z0-9A-Z]+)\.(txt|png)$/", $uri, $arr))
  {
    $format = $arr[2];
    $file = $arr[1] .".jpg";
  }
  else
  {
    echo "BAD";
  }

  if($file && $format)
  {
    $p = $_SERVER["HTTP_HOST"] . str_replace(array('.png', '.txt'), '.jpg', $_SERVER["REQUEST_URI"]);
    $p = str_replace('//', '/', $p);
      
    if(isset($with_bg) && $with_bg == 1)
    {
    
      $p = $_SERVER["HTTP_HOST"] ."/". $file ;
        
      $image_clone = new Imagick('http://'. $p);
      
      if(isset($trim) && $trim == 1)
      {
        $image_clone->trimImage(0);
      }
      
      $image_clone->resizeImage($width, $height, imagick::FILTER_LANCZOS, 0.9, true);

      $d = $image_clone->getImageGeometry();
          
      $image = new Imagick();      
      $image->newImage($width,  $height, new ImagickPixel( 'white' ));

      $image->compositeImage($image_clone, imagick::COMPOSITE_DEFAULT, (($width - $d['width'])/2) , ($height - $d['height'])/2);
      
      $image->setImageCompression(imagick::COMPRESSION_JPEG);
      $image->setCompressionQuality(90);      
      $image->setImageFormat('jpg');     
    }
    else
    { 
      $image = new Imagick('http://'. $p);
    }
  
    if($format == 'png')
    {
      /* on va commencer par la methode de choper les 4 coins */
      $d_img = $image->getImageGeometry();  // $d_img['width'] // $d_img['height']
      $lt = $image->getImagePixelColor(2,2);
      $rt = $image->getImagePixelColor($d_img['width']-2, 0);
      $lb = $image->getImagePixelColor(0, $d_img['height'] - 2);
      $rb = $image->getImagePixelColor($d_img['width']-2, $d_img['height'] - 2);    

      $range = 0.02;
    
      header("Content-Type: image/png");      
      $image->setImageFormat( "png32" );
      $image->setBackgroundColor(new ImagickPixel("transparent"));
      $image->setImageOpacity( 0.999 );      
      
      // le fond a pas l'air uni, on touche pas a l'image
      if( color_similar($lt, $rb, 0.2) )
      {
        // dans le cas ou on a une difference de teinte
        if(!color_similar($lt, $rb, 0.2) )
        {
          $range = $range * 2;
        }
      
        $pixels_it = $image->getPixelIterator();
            
        foreach($pixels_it as $row => $pixels)
        {
          foreach($pixels as $column => $pixel)
          {

            if(color_similar($pixel, $lt, $range))
            {
              $pixel->setcolor('Transparent');
            }
            
          }
          $pixels_it->syncIterator();
        }
      }
            
      memcache_set($memcache_obj, $uri, $image->getImageBlob(), false, 86400);
      echo $image;
    }
    elseif($format == 'txt')
    {
    
      $o = "data:image/jpeg;base64,";
      $o .= base64_encode($image);
            
      memcache_set($memcache_obj, $uri, $o, false, 86400);      
      echo $o;
    }
    elseif($format == 'jpg')  
    {    
        header("Content-Type: image/jpg"); 
        memcache_set($memcache_obj, $uri, $image->getImageBlob(), false, 86400);     
        echo $image;
    }
  }

  function color_similar($px1, $px2, $range)
  {
    return(
        abs($px1->getColorValue(imagick::COLOR_RED) - $px2->getColorValue(imagick::COLOR_RED)) <= $range &&
        abs($px1->getColorValue(imagick::COLOR_GREEN) - $px2->getColorValue(imagick::COLOR_GREEN)) <= $range &&
        abs($px1->getColorValue(imagick::COLOR_BLUE) - $px2->getColorValue(imagick::COLOR_BLUE)) <= $range
    );  
  }

?>
