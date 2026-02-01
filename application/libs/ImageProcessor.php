<?php
/**
 * 이미지 처리 라이브러리
 * - 이미지 리사이즈
 * - 썸네일 생성 (대/중/소)
 * - 워터마크 적용
 */

class ImageProcessor {
    
    private $config = [];
    
    public function __construct() {
        // DB에서 설정 로드
        $this->loadConfig();
    }
    
    /**
     * 설정 로드
     */
    private function loadConfig() {
        $configs = getDbArray("SELECT config_key, config_value FROM site_config WHERE config_group IN ('image', 'thumbnail', 'watermark')");
        
        foreach ($configs as $config) {
            $this->config[$config['config_key']] = $config['config_value'];
        }
        
        // 기본값 설정
        $defaults = [
            'image_max_width' => 900,
            'image_quality' => 100,
            'thumb_big_width' => 900,
            'thumb_big_height' => 600,
            'thumb_middle_width' => 640,
            'thumb_middle_height' => 480,
            'thumb_small_width' => 480,
            'thumb_small_height' => 360,
            'thumb_quality' => 100,
            'thumbnail_delete_original' => 'N',
            'thumbnail_transparent_bg' => 'white',
            'watermark_enabled' => 'N',
            'watermark_target_board' => 'Y',
            'watermark_target_page' => 'Y',
            'watermark_position' => 5,
            'watermark_image' => '',
            'watermark_opacity' => 80,
            'image_sharpen' => 'N',
            'image_sharpen_value' => '80/0.5/3'
        ];
        
        foreach ($defaults as $key => $value) {
            if (!isset($this->config[$key])) {
                $this->config[$key] = $value;
            }
        }
    }
    
    /**
     * 이미지 처리 메인 함수
     * @param string $sourcePath 원본 이미지 경로
     * @param string $originalName 원본 파일명
     * @return array 처리된 이미지 정보
     */
    public function processImage($sourcePath, $originalName) {
        $result = [
            'success' => false,
            'original' => null,
            'big' => null,
            'middle' => null,
            'small' => null,
            'message' => ''
        ];
        
        if (!file_exists($sourcePath)) {
            $result['message'] = '원본 파일이 존재하지 않습니다.';
            return $result;
        }
        
        $size = @getimagesize($sourcePath);
        if ($size === false || $size[2] < 1 || $size[2] > 3) {
            $result['message'] = '지원하지 않는 이미지 형식입니다. (GIF, JPG, PNG만 지원)';
            return $result;
        }
        
        // Animated GIF 체크
        if ($size[2] == 1 && $this->isAnimatedGif($sourcePath)) {
            $result['message'] = 'Animated GIF는 처리하지 않습니다.';
            $result['success'] = true;
            $result['original'] = basename($sourcePath);
            return $result;
        }
        
        $pathInfo = pathinfo($sourcePath);
        $targetDir = $pathInfo['dirname'];
        $baseFilename = $pathInfo['filename'];
        $ext = $pathInfo['extension'];
        
        try {
            // 1. 원본 리사이즈 (최대 가로폭 제한)
            $maxWidth = (int)$this->config['image_max_width'];
            if ($size[0] > $maxWidth) {
                $resizedFile = $this->thumbnail(
                    1, // 원본 리사이즈
                    $originalName,
                    $pathInfo['dirname'],
                    $targetDir,
                    $maxWidth,
                    0, // 높이는 비율에 맞게
                    true, // 강제 생성
                    false, // 크롭 안 함
                    'top',
                    $this->config['image_sharpen'] === 'Y',
                    $this->config['image_sharpen_value']
                );
                $result['original'] = $resizedFile;
            } else {
                $result['original'] = basename($sourcePath);
            }
            
            // 2. 큰 썸네일 생성
            $result['big'] = $this->thumbnail(
                2, // 썸네일
                $originalName,
                $targetDir,
                $targetDir,
                (int)$this->config['thumb_big_width'],
                (int)$this->config['thumb_big_height'],
                true,
                false,
                'top',
                false,
                ''
            );
            
            // 파일명 변경 (original_big.ext)
            if ($result['big']) {
                $oldPath = $targetDir . '/' . $result['big'];
                $newName = $baseFilename . '_big.' . $ext;
                $newPath = $targetDir . '/' . $newName;
                if (rename($oldPath, $newPath)) {
                    $result['big'] = $newName;
                }
            }
            
            // 3. 중간 썸네일 생성
            $result['middle'] = $this->thumbnail(
                2,
                $originalName,
                $targetDir,
                $targetDir,
                (int)$this->config['thumb_middle_width'],
                (int)$this->config['thumb_middle_height'],
                true,
                false,
                'top',
                false,
                ''
            );
            
            if ($result['middle']) {
                $oldPath = $targetDir . '/' . $result['middle'];
                $newName = $baseFilename . '_middle.' . $ext;
                $newPath = $targetDir . '/' . $newName;
                if (rename($oldPath, $newPath)) {
                    $result['middle'] = $newName;
                }
            }
            
            // 4. 작은 썸네일 생성
            $result['small'] = $this->thumbnail(
                2,
                $originalName,
                $targetDir,
                $targetDir,
                (int)$this->config['thumb_small_width'],
                (int)$this->config['thumb_small_height'],
                true,
                false,
                'top',
                false,
                ''
            );
            
            if ($result['small']) {
                $oldPath = $targetDir . '/' . $result['small'];
                $newName = $baseFilename . '_small.' . $ext;
                $newPath = $targetDir . '/' . $newName;
                if (rename($oldPath, $newPath)) {
                    $result['small'] = $newName;
                }
            }
            
            // 5. 워터마크 적용 (JPG만)
            if ($this->config['watermark_enabled'] === 'Y' && $size[2] == 2 && !empty($this->config['watermark_image'])) {
                $watermarkPath = PUBLIC_PATH . $this->config['watermark_image'];
                if (file_exists($watermarkPath)) {
                    // 원본에 워터마크
                    if ($result['original']) {
                        $this->watermarkImage(
                            $targetDir . '/' . $result['original'],
                            $watermarkPath,
                            $targetDir . '/' . $result['original'],
                            (int)$this->config['watermark_position']
                        );
                    }
                    
                    // 큰 썸네일에 워터마크
                    if ($result['big']) {
                        $this->watermarkImage(
                            $targetDir . '/' . $result['big'],
                            $watermarkPath,
                            $targetDir . '/' . $result['big'],
                            (int)$this->config['watermark_position']
                        );
                    }
                }
            }
            
            // 6. 원본 이미지 삭제 옵션 처리
            if ($this->config['thumbnail_delete_original'] === 'Y' && $result['original'] && $result['big']) {
                $originalPath = $targetDir . '/' . $result['original'];
                if (file_exists($originalPath) && $result['original'] !== basename($sourcePath)) {
                    @unlink($originalPath);
                    $result['original'] = ''; // 원본 삭제됨
                    $result['message'] .= ' (원본 삭제됨)';
                }
            }
            
            $result['success'] = true;
            $result['message'] = '이미지 처리 완료';
            
        } catch (Exception $e) {
            $result['message'] = '이미지 처리 중 오류: ' . $e->getMessage();
        }
        
        return $result;
    }
    
    /**
     * 썸네일 생성 (제공해주신 코드 기반)
     */
    private function thumbnail($original, $filename, $source_path, $target_path, $thumb_width, $thumb_height, $is_create, $is_crop=false, $crop_mode='top', $is_sharpen=false, $um_value='80/0.5/3') {
        if(!$thumb_width && !$thumb_height)
            return '';

        $source_file = "$source_path/$filename";

        if(!is_file($source_file))
            return '';

        $size = @getimagesize($source_file);
        if($size[2] < 1 || $size[2] > 3)
            return '';

        if (!is_dir($target_path)) {
            @mkdir($target_path, 0755, true);
            @chmod($target_path, 0755);
        }

        if(!(is_dir($target_path) && is_writable($target_path)))
            return '';

        if($size[2] == 1) {
            if($this->isAnimatedGif($source_file))
                return basename($source_file);
        }

        $ext = array(1 => 'gif', 2 => 'jpg', 3 => 'png');

        $thumb_filename = preg_replace("/\.[^\.]+$/i", "", $filename);

        if($original==1) {
            $thumb_file = "$target_path/{$thumb_filename}.".$ext[$size[2]];
        }elseif($original==2){
            $thumb_file = "$target_path/{$thumb_filename}_thumb.".$ext[$size[2]];
        }

        $thumb_time = @filemtime($thumb_file);
        $source_time = @filemtime($source_file);

        if (file_exists($thumb_file)) {
            if ($is_create == false && $source_time < $thumb_time) {
                return basename($thumb_file);
            }
        }

        $src = null;
        $degree = 0;

        if ($size[2] == 1) {
            $src = @imagecreatefromgif($source_file);
            $src_transparency = @imagecolortransparent($src);
        } else if ($size[2] == 2) {
            $src = @imagecreatefromjpeg($source_file);

            if(function_exists('exif_read_data')) {
                $exif = @exif_read_data($source_file);
                if(!empty($exif['Orientation'])) {
                    switch($exif['Orientation']) {
                        case 8:
                            $degree = 90;
                            break;
                        case 3:
                            $degree = 180;
                            break;
                        case 6:
                            $degree = -90;
                            break;
                    }

                    if($degree) {
                        $src = imagerotate($src, $degree, 0);

                        if($degree == 90 || $degree == -90) {
                            $tmp = $size;
                            $size[0] = $tmp[1];
                            $size[1] = $tmp[0];
                        }
                    }
                }
            }
        } else if ($size[2] == 3) {
            $src = @imagecreatefrompng($source_file);
            @imagealphablending($src, true);
        } else {
            return '';
        }

        if(!$src)
            return '';

        $is_large = true;

        if($thumb_width) {
            if(!$thumb_height) {
                $thumb_height = round(($thumb_width * $size[1]) / $size[0]);
            } else {
                if($size[0] < $thumb_width || $size[1] < $thumb_height)
                    $is_large = false;
            }
        } else {
            if($thumb_height) {
                $thumb_width = round(($thumb_height * $size[0]) / $size[1]);
            }
        }

        $dst_x = 0;
        $dst_y = 0;
        $src_x = 0;
        $src_y = 0;
        $dst_w = $thumb_width;
        $dst_h = $thumb_height;
        $src_w = $size[0];
        $src_h = $size[1];

        $ratio = $dst_h / $dst_w;

        if($is_large) {
            if($is_crop) {
                switch($crop_mode)
                {
                    case 'center':
                        if($size[1] / $size[0] >= $ratio) {
                            $src_h = round($src_w * $ratio);
                            $src_y = round(($size[1] - $src_h) / 2);
                        } else {
                            $src_w = round($size[1] / $ratio);
                            $src_x = round(($size[0] - $src_w) / 2);
                        }
                        break;
                    default:
                        if($size[1] / $size[0] >= $ratio) {
                            $src_h = round($src_w * $ratio);
                        } else {
                            $src_w = round($size[1] / $ratio);
                        }
                        break;
                }

                $dst = imagecreatetruecolor($dst_w, $dst_h);

                if($size[2] == 3) {
                    imagealphablending($dst, false);
                    imagesavealpha($dst, true);
                } else if($size[2] == 1) {
                    $palletsize = imagecolorstotal($src);
                    if($src_transparency >= 0 && $src_transparency < $palletsize) {
                        $transparent_color   = imagecolorsforindex($src, $src_transparency);
                        $current_transparent = imagecolorallocate($dst, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
                        imagefill($dst, 0, 0, $current_transparent);
                        imagecolortransparent($dst, $current_transparent);
                    }
                }
            } else {
                $dst = imagecreatetruecolor($dst_w, $dst_h);
                // 투명 배경 처리 옵션 적용
                if ($this->config['thumbnail_transparent_bg'] === 'black') {
                    $bgcolor = imagecolorallocate($dst, 0, 0, 0); // 검정
                } else {
                    $bgcolor = imagecolorallocate($dst, 255, 255, 255); // 흰색 (기본)
                }

                if($src_w > $src_h) {
                    $tmp_h = round(($dst_w * $src_h) / $src_w);
                    $dst_y = round(($dst_h - $tmp_h) / 2);
                    $dst_h = $tmp_h;
                } else {
                    $tmp_w = round(($dst_h * $src_w) / $src_h);
                    $dst_x = round(($dst_w - $tmp_w) / 2);
                    $dst_w = $tmp_w;
                }

                if($size[2] == 3) {
                    $bgcolor = imagecolorallocatealpha($dst, 0, 0, 0, 127);
                    imagefill($dst, 0, 0, $bgcolor);
                    imagealphablending($dst, false);
                    imagesavealpha($dst, true);
                } else if($size[2] == 1) {
                    $palletsize = imagecolorstotal($src);
                    if($src_transparency >= 0 && $src_transparency < $palletsize) {
                        $transparent_color   = imagecolorsforindex($src, $src_transparency);
                        $current_transparent = imagecolorallocate($dst, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
                        imagefill($dst, 0, 0, $current_transparent);
                        imagecolortransparent($dst, $current_transparent);
                    } else {
                        imagefill($dst, 0, 0, $bgcolor);
                    }
                } else {
                    imagefill($dst, 0, 0, $bgcolor);
                }
            }
        } else {
            $dst = imagecreatetruecolor($dst_w, $dst_h);
            // 투명 배경 처리 옵션 적용
            if ($this->config['thumbnail_transparent_bg'] === 'black') {
                $bgcolor = imagecolorallocate($dst, 0, 0, 0); // 검정
            } else {
                $bgcolor = imagecolorallocate($dst, 255, 255, 255); // 흰색 (기본)
            }

            if($src_w < $dst_w) {
                if($src_h >= $dst_h) {
                    if( $src_h > $src_w ){
                        $tmp_w = round(($dst_h * $src_w) / $src_h);
                        $dst_x = round(($dst_w - $tmp_w) / 2);
                        $dst_w = $tmp_w;
                    } else {
                        $dst_x = round(($dst_w - $src_w) / 2);
                        $src_h = $dst_h;
                        if( $dst_w > $src_w ){
                            $dst_w = $src_w;
                        }
                    }
                } else {
                    $dst_x = round(($dst_w - $src_w) / 2);
                    $dst_y = round(($dst_h - $src_h) / 2);
                    $dst_w = $src_w;
                    $dst_h = $src_h;
                }
            } else {
                if($src_h < $dst_h) {
                    if( $src_w > $dst_w ){
                        $tmp_h = round(($dst_w * $src_h) / $src_w);
                        $dst_y = round(($dst_h - $tmp_h) / 2);
                        $dst_h = $tmp_h;
                    } else {
                        $dst_y = round(($dst_h - $src_h) / 2);
                        $dst_h = $src_h;
                        $src_w = $dst_w;
                    }
                }
            }

            if($size[2] == 3) {
                $bgcolor = imagecolorallocatealpha($dst, 0, 0, 0, 127);
                imagefill($dst, 0, 0, $bgcolor);
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
            } else if($size[2] == 1) {
                $palletsize = imagecolorstotal($src);
                if($src_transparency >= 0 && $src_transparency < $palletsize) {
                    $transparent_color   = imagecolorsforindex($src, $src_transparency);
                    $current_transparent = imagecolorallocate($dst, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
                    imagefill($dst, 0, 0, $current_transparent);
                    imagecolortransparent($dst, $current_transparent);
                } else {
                    imagefill($dst, 0, 0, $bgcolor);
                }
            } else {
                imagefill($dst, 0, 0, $bgcolor);
            }
        }

        imagecopyresampled($dst, $src, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);

        if($is_sharpen && $is_large) {
            $val = explode('/', $um_value);
            $this->unsharpMask($dst, $val[0], $val[1], $val[2]);
        }

        $quality = (int)$this->config['thumb_quality'];

        if($size[2] == 1) {
            imagegif($dst, $thumb_file);
        } else if($size[2] == 3) {
            $png_compress = round((100 - $quality) / 10);
            imagepng($dst, $thumb_file, $png_compress);
        } else {
            imagejpeg($dst, $thumb_file, $quality);
        }

        @chmod($thumb_file, 0644);

        imagedestroy($src);
        imagedestroy($dst);

        return basename($thumb_file);
    }
    
    /**
     * 워터마크 적용 (제공해주신 코드 기반)
     */
    private function watermarkImage($target, $wtrmrk_file, $newcopy, $wh) {
        if (!file_exists($target) || !file_exists($wtrmrk_file)) {
            return false;
        }
        
        $watermark = imagecreatefrompng($wtrmrk_file);
        imagealphablending($watermark, false);
        imagesavealpha($watermark, true);
        $img = imagecreatefromjpeg($target);
        $img_w = imagesx($img);
        $img_h = imagesy($img);
        $wtrmrk_w = imagesx($watermark);
        $wtrmrk_h = imagesy($watermark);

        if($wh ==1) { //top left
           $margin_top=10;
           $margin_left=10;
           $dst_x = $margin_left; 
           $dst_y = $margin_top; 

        }elseif($wh ==2) { //top right
           $margin_top=10;
           $margin_right=10;
           $dst_x = $img_w  - $wtrmrk_w - $margin_right;
           $dst_y = $margin_top;

        }elseif($wh ==3) { //center
           $dst_x = ($img_w / 2) - ($wtrmrk_w / 2); 
           $dst_y = ($img_h / 2) - ($wtrmrk_h / 2);

        }elseif($wh ==4) { //left bottom
           $margin_left=10;
           $margin_bottom=10;
           $dst_x = $margin_left;
           $dst_y = $img_h - $wtrmrk_h - $margin_bottom;

        }elseif($wh ==5) { //right bottom
           $margin_right=10;
           $margin_bottom=10;
           $dst_x = $img_w  - $wtrmrk_w - $margin_right;
           $dst_y = $img_h - $wtrmrk_h - $margin_bottom;
        }

        imagecopy($img, $watermark, $dst_x, $dst_y, 0, 0, $wtrmrk_w, $wtrmrk_h);
        imagejpeg($img, $newcopy, 100);
        imagedestroy($img);
        imagedestroy($watermark);
        
        return true;
    }
    
    /**
     * Unsharp Mask (제공해주신 코드 기반)
     */
    private function unsharpMask($img, $amount, $radius, $threshold) {
        if ($amount > 500)    $amount = 500;
        $amount = $amount * 0.016;
        if ($radius > 50)    $radius = 50;
        $radius = $radius * 2;
        if ($threshold > 255)    $threshold = 255;

        $radius = abs(round($radius));
        if ($radius == 0) {
            return true;
        }
        $w = imagesx($img); $h = imagesy($img);
        $imgCanvas = imagecreatetruecolor($w, $h);
        $imgBlur = imagecreatetruecolor($w, $h);

        if (function_exists('imageconvolution')) {
            $matrix = array(
                array( 1, 2, 1 ),
                array( 2, 4, 2 ),
                array( 1, 2, 1 )
            );
            $divisor = array_sum(array_map('array_sum', $matrix));
            $offset = 0;

            imagecopy ($imgBlur, $img, 0, 0, 0, 0, $w, $h);
            imageconvolution($imgBlur, $matrix, $divisor, $offset);
        } else {
            for ($i = 0; $i < $radius; $i++)    {
                imagecopy ($imgBlur, $img, 0, 0, 1, 0, $w - 1, $h);
                imagecopymerge ($imgBlur, $img, 1, 0, 0, 0, $w, $h, 50);
                imagecopymerge ($imgBlur, $img, 0, 0, 0, 0, $w, $h, 50);
                imagecopy ($imgCanvas, $imgBlur, 0, 0, 0, 0, $w, $h);

                imagecopymerge ($imgBlur, $imgCanvas, 0, 0, 0, 1, $w, $h - 1, 33.33333 );
                imagecopymerge ($imgBlur, $imgCanvas, 0, 1, 0, 0, $w, $h, 25);
            }
        }

        if($threshold>0){
            for ($x = 0; $x < $w-1; $x++)    {
                for ($y = 0; $y < $h; $y++)    {
                    $rgbOrig = ImageColorAt($img, $x, $y);
                    $rOrig = (($rgbOrig >> 16) & 0xFF);
                    $gOrig = (($rgbOrig >> 8) & 0xFF);
                    $bOrig = ($rgbOrig & 0xFF);

                    $rgbBlur = ImageColorAt($imgBlur, $x, $y);

                    $rBlur = (($rgbBlur >> 16) & 0xFF);
                    $gBlur = (($rgbBlur >> 8) & 0xFF);
                    $bBlur = ($rgbBlur & 0xFF);

                    $rNew = (abs($rOrig - $rBlur) >= $threshold)
                        ? max(0, min(255, ($amount * ($rOrig - $rBlur)) + $rOrig))
                        : $rOrig;
                    $gNew = (abs($gOrig - $gBlur) >= $threshold)
                        ? max(0, min(255, ($amount * ($gOrig - $gBlur)) + $gOrig))
                        : $gOrig;
                    $bNew = (abs($bOrig - $bBlur) >= $threshold)
                        ? max(0, min(255, ($amount * ($bOrig - $bBlur)) + $bOrig))
                        : $bOrig;

                    if (($rOrig != $rNew) || ($gOrig != $gNew) || ($bOrig != $bNew)) {
                        $pixCol = ImageColorAllocate($img, $rNew, $gNew, $bNew);
                        ImageSetPixel($img, $x, $y, $pixCol);
                    }
                }
            }
        } else {
            for ($x = 0; $x < $w; $x++)    {
                for ($y = 0; $y < $h; $y++)    {
                    $rgbOrig = ImageColorAt($img, $x, $y);
                    $rOrig = (($rgbOrig >> 16) & 0xFF);
                    $gOrig = (($rgbOrig >> 8) & 0xFF);
                    $bOrig = ($rgbOrig & 0xFF);

                    $rgbBlur = ImageColorAt($imgBlur, $x, $y);

                    $rBlur = (($rgbBlur >> 16) & 0xFF);
                    $gBlur = (($rgbBlur >> 8) & 0xFF);
                    $bBlur = ($rgbBlur & 0xFF);

                    $rNew = ($amount * ($rOrig - $rBlur)) + $rOrig;
                    if($rNew>255){$rNew=255;}
                    elseif($rNew<0){$rNew=0;}
                    $gNew = ($amount * ($gOrig - $gBlur)) + $gOrig;
                    if($gNew>255){$gNew=255;}
                    elseif($gNew<0){$gNew=0;}
                    $bNew = ($amount * ($bOrig - $bBlur)) + $bOrig;
                    if($bNew>255){$bNew=255;}
                    elseif($bNew<0){$bNew=0;}
                    $rgbNew = ($rNew << 16) + ($gNew <<8) + $bNew;
                    ImageSetPixel($img, $x, $y, $rgbNew);
                }
            }
        }
        imagedestroy($imgCanvas);
        imagedestroy($imgBlur);

        return true;
    }
    
    /**
     * Animated GIF 체크
     */
    private function isAnimatedGif($filename) {
        if(!($fh = @fopen($filename, 'rb')))
            return false;
        $count = 0;
        
        while(!feof($fh) && $count < 2) {
            $chunk = fread($fh, 1024 * 100);
            $count += preg_match_all('#\x00\x21\xF9\x04.{4}\x00(\x2C|\x21)#s', $chunk, $matches);
        }

        fclose($fh);
        return $count > 1;
    }
}
