picsou
======

configuration de Nginx + script php pour resizer, détourer, fournir la base64, etc d'une image

Nginx doit etre activé avec les modules proxy, memcache, image_filter et fastcgi.
Dans la plupart des cas, image_filter s'occupe du resize des images. 
Le PHP gère des cas particuliers comme le detourage, certains resizes, fournir le base64 des images
Dans le cas ou le serveur doit passé par le PHP, on met le resultat en memcache eviter au prochain appel le processus

le fichier nginx/default fournit la configuration du serveur de medias.

Image Original
-----------------
http://localhost:8001/1b35c3c2da01e3054539e0182b4cd15abb4435ee.jpg
  
Resize
-------
http://localhost:8001/r/1b35c3c2da01e3054539e0182b4cd15abb4435ee-150-150.jpg
 
Croppé
-------
http://localhost:8001/c/1b35c3c2da01e3054539e0182b4cd15abb4435ee-150-150.jpg
  
Transparente
------------
http://localhost:8001/1b35c3c2da01e3054539e0182b4cd15abb4435ee.png

Transparente resize
--------------------
http://localhost:8001/r/1b35c3c2da01e3054539e0182b4cd15abb4435ee-150-150.png
 
Base64 du JPG
-------------
http://localhost:8001/1b35c3c2da01e3054539e0182b4cd15abb4435ee.txt

Base64 du JPG resizé
--------------------
http://localhost:8001/r/1b35c3c2da01e3054539e0182b4cd15abb4435ee-150-150.txt

