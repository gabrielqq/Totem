<?php // ♣

    # #
    # Copyright 2012 Daniel de Andrade Varela
    # Licensed under the Apache License, Version 2.0 (the “License”);
    # you may not use this file except in compliance with the License.
    # You may obtain a copy of the License at
    # 
    # http://www.apache.org/licenses/LICENSE-2.0
    # 
    # Unless required by applicable law or agreed to in writing, software
    # distributed under the License is distributed on an “AS IS” BASIS,
    # WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
    # See the License for the specific language governing permissions and
    # limitations under the License.
    
    require_once 'config.php';
    
    $x          = ( isset($_GET['x']   ) ) ? $_GET['x']              : $options['size']['width']  ;
    $y          = ( isset($_GET['y']   ) ) ? $_GET['y']              : $options['size']['height'] ;
    $ext        = ( isset($_GET['ext'] ) ) ? $_GET['ext']            : ""                         ;
    $url        = ( isset($_GET['url'] ) ) ? "{$_GET['url']}.{$ext}" : ""                         ;    
    $md5        = md5( preg_replace("/http(.){0,1}:\/\//", "", $url) );
    $bufferFile = "{$md5}_-_{$x}_x_{$y}.{$ext}";
    $bufferDir  = "buffer-external";
        
    // SE A IMAGEM JÁ ESTIVER NO BUFFER, APENAS 
    // REDIRECIONA PARA A IMAGEM CONTIDA NO BUFFER
    if( file_exists("{$bufferDir}/{$bufferFile}") ){
        $thisDir = basename( dirname($_SERVER['SCRIPT_FILENAME']) );
        $uri = $_SERVER['REQUEST_URI'];
        $goTo = preg_replace("/\/.*/", "", strtolower($_SERVER['SERVER_PROTOCOL'])) ."://". $_SERVER['HTTP_HOST'] . substr($uri, 0, strpos($uri, $thisDir)).$thisDir."/{$bufferDir}/{$bufferFile}";
        header("Location: $goTo");
        exit;
    }
    
    function url_exists($url) { 
        $hdrs = @get_headers($url); 
        return is_array($hdrs) ? preg_match('/^HTTP\\/\\d+\\.\\d+\\s+2\\d\\d\\s+.*$/',$hdrs[0]) : false; 
    } 
    
    $url = str_replace(":/", "://", $url);
    if( !empty($url) && url_exists($url) ){
        
        // IF DOWNLOAD-ORIGINAL-IMAGE
        require_once 'phpthumb/ThumbLib.inc.php';
        if( $options['external']['download-original-image'] ){
            // SE A IMAGEM ORIGINAL AINDA NÃO EXISTIR NO BUFFER
            // SALVA A IMAGEM ORIGINAL
            if( !file_exists($original_file = "{$bufferDir}/{$md5}_-_original.{$ext}") ){
                $thumb = PhpThumbFactory::create($url);
                $thumb->save($original_file);
            }
            
            // SE A IMAGEM ORIGINAL EXISTIR NO BUFFER
            // APENAS MONTA O OBJETO DESTA IMAGEM
            else{
                $thumb = PhpThumbFactory::create($original_file);
            }
        }
        
        // IF NOT DOWNLOAD-ORIGINAL-IMAGE
        else{
            $thumb = PhpThumbFactory::create($url);
        }
            
        // SALAVA A IMAGEM NO TAMANHO DESEJADO NO BUFFER 
        // E RETORNA A IMAGEM DO TAMANHO DESEJADO
        $thumb->adaptiveResize($x, $y);
        $thumb->save("{$bufferDir}/{$bufferFile}");
        $thumb->show();
    }
    
    // Caso a url não seja setada
    else{
        require_once 'phpthumb/ThumbLib.inc.php';
        $thumb = PhpThumbFactory::create($options['notfound']['path']);
        $thumb->adaptiveResize($x, $y);
        $thumb->show();
    }