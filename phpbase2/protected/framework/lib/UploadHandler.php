<?php
/*
 * jQuery File Upload Plugin PHP Class 8.3.1
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */
class UploadHandler {

    protected $options;

    private $_chunkFileSize = 15728640; //不能大于16M

    // PHP File Upload error message codes:
    // http://php.net/manual/en/features.file-upload.errors.php
    protected $error_messages = array(
        1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        3 => 'The uploaded file was only partially uploaded',
        4 => 'No file was uploaded',
        6 => 'Missing a temporary folder',
        7 => 'Failed to write file to disk',
        8 => 'A PHP extension stopped the file upload',
        'post_max_size' => 'The uploaded file exceeds the post_max_size directive in php.ini',
        'max_file_size' => 'File is too big',
        'min_file_size' => 'File is too small',
        'accept_file_types' => 'Filetype not allowed',
        'max_number_of_files' => 'Maximum number of files exceeded',
        'max_width' => '图片超过最大宽度',
        'min_width' => 'Image requires a minimum width',
        'max_height' => '图片超过最大高度',
        'min_height' => 'Image requires a minimum height',
        'abort' => 'File upload aborted',
        'image_resize' => 'Failed to resize image',
        'bs2upload_error' => 'bs2 error',
        'max_ratio' => '宽高或高宽比例超过%s',
    );

    protected $image_objects = array();

    function __construct($params) {
        $this->response = array();
        $this->options = array(
            'bucket' => $params['bucket'] ? $params['bucket'] : BS2_FILE_BUCKET,
            'script_url' => $this->get_full_url() . $params['server_script'],
            'upload_dir' => $params['upload_dir'] ? $params['upload_dir'] : dirname($this->get_server_var('SCRIPT_FILENAME')) . '/protected/data/',
            'user_dirs' => false,
            'mkdir_mode' => 0755,
            'param_name' => $params['param_name'] ? $params['param_name'] : 'files',
            // Set the following option to 'POST', if your server does not support
            // DELETE requests. This is a parameter sent to the client:
            'delete_type' => 'DELETE',
            'access_control_allow_origin' => '*',
            'access_control_allow_credentials' => false,
            'access_control_allow_methods' => array(
                'OPTIONS',
                'HEAD',
                'GET',
                'POST',
                'PUT',
                'PATCH',
                'DELETE'
            ),
            'access_control_allow_headers' => array(
                'Content-Type',
                'Content-Range',
                'Content-Disposition'
            ),
            // Enable to provide file downloads via GET requests to the PHP script:
            //     1. Set to 1 to download files via readfile method through PHP
            //     2. Set to 2 to send a X-Sendfile header for lighttpd/Apache
            //     3. Set to 3 to send a X-Accel-Redirect header for nginx
            // If set to 2 or 3, adjust the upload_url option to the base path of
            // the redirect parameter, e.g. '/files/'.
            'download_via_php' => false,
            // Read files in chunks to avoid memory limits when download_via_php
            // is enabled, set to 0 to disable chunked reading of files:
            'readfile_chunk_size' => 10 * 1024 * 1024, // 10 MiB
            // Defines which files can be displayed inline when downloaded:
            'inline_file_types' => $params['inline_file_types'] ? $params['inline_file_types'] : '/\.(gif|jpe?g|png)$/i',
            // Defines which files (based on their names) are accepted for upload:
            'accept_file_types' => $params['accept_file_types'] ? $params['accept_file_types'] : '/.+$/i',
            // The php.ini settings upload_max_filesize and post_max_size
            // take precedence over the following max_file_size setting:
            'max_file_size' => $params['max_file_size'] ?: null,
            'min_file_size' => 1,
            // The maximum number of files for the upload directory:
            'max_number_of_files' => null,
            // Defines which files are handled as image files:
            'image_file_types' => '/\.(gif|jpe?g|png)$/i',
            // Use exif_imagetype on all files to correct file extensions:
            'correct_image_extensions' => false,
            // Image resolution restrictions:
            'max_width' => $params['max_width'] ? $params['max_width'] : null,
            'max_height' => $params['max_height'] ? $params['max_height'] : null,
            'min_width' => 1,
            'min_height' => 1,
            // Set the following option to false to enable resumable uploads:
            'discard_aborted_uploads' => true,
            // Set to 0 to use the GD library to scale and orient images,
            // set to 1 to use imagick (if installed, falls back to GD),
            // set to 2 to use the ImageMagick convert binary directly:
            'image_library' => 1,
            // Uncomment the following to define an array of resource limits
            // for imagick:
            /*
            'imagick_resource_limits' => array(
                imagick::RESOURCETYPE_MAP => 32,
                imagick::RESOURCETYPE_MEMORY => 32
            ),
            */
            // Command or path for to the ImageMagick convert binary:
            'convert_bin' => 'convert',
            // Uncomment the following to add parameters in front of each
            // ImageMagick convert call (the limit constraints seem only
            // to have an effect if put in front):
            /*
            'convert_params' => '-limit memory 32MiB -limit map 32MiB',
            */
            // Command or path for to the ImageMagick identify binary:
            'identify_bin' => 'identify',
            'image_versions' => array(
                // The empty image version key defines options for the original image:
                '' => array(
                    // Automatically rotate images based on EXIF meta data:
                    'auto_orient' => true
                ),
                // Uncomment the following to create medium sized images:
                /*
                'medium' => array(
                    'max_width' => 800,
                    'max_height' => 600
                ),
                */
                'thumbnail' => array(
                    // Uncomment the following to use a defined directory for the thumbnails
                    // instead of a subdirectory based on the version identifier.
                    // Make sure that this directory doesn't allow execution of files if you
                    // don't pose any restrictions on the type of uploaded files, e.g. by
                    // copying the .htaccess file from the files directory for Apache:
                    //'upload_dir' => dirname($this->get_server_var('SCRIPT_FILENAME')).'/thumb/',
                    //'upload_url' => $this->get_full_url().'/thumb/',
                    // Uncomment the following to force the max
                    // dimensions and e.g. create square thumbnails:
                    //'crop' => true,
                    'max_width' => 80,
                    'max_height' => 80
                )
            ),
            'print_response' => isset($params['print_response']) ? $params['print_response'] : true,
            'max_ratio' => $params['max_ratio'] ? (int)$params['max_ratio'] : 0,
            'attrImg' => $params['attrImg'] ? (int)$params['attrImg'] : 0,
            'local' => $params['local'] ? (int)$params['local'] : 0, //是否保存到本地服务器
            'del_file_path' => $params['del_file_path'] ? $params['del_file_path'] : '', //删除文件路径
            'on_upload_done' => $params['on_upload_done'], // 上传成功后的回调
        );

        $this->post($this->options['print_response']);
    }

    protected function get_download_url($file_name, $version = null, $direct = false) {
        if (!$direct && $this->options['download_via_php']) {
            $url = $this->options['script_url']
                .$this->get_query_separator($this->options['script_url'])
                .$this->get_singular_param_name()
                .'='.rawurlencode($file_name);
            if ($version) {
                $url .= '&version='.rawurlencode($version);
            }
            return $url.'&download=1';
        }
        if (empty($version)) {
            $version_path = '';
        } else {
            $version_url = @$this->options['image_versions'][$version]['upload_url'];
            if ($version_url) {
                return $version_url.$this->get_user_path().rawurlencode($file_name);
            }
            $version_path = rawurlencode($version).'/';
        }
        return $this->options['upload_url'].$this->get_user_path()
            .$version_path.rawurlencode($file_name);
    }

    protected function set_additional_file_properties($file) {
        $file->deleteUrl = $this->options['script_url']
        .$this->get_query_separator($this->options['script_url'])
        .$this->get_singular_param_name()
        .'='.rawurlencode($file->name);
        $file->deleteType = $this->options['delete_type'];
        $file->deleteUrl .= '&_method=DELETE';
        if ($this->options['access_control_allow_credentials']) {
            $file->deleteWithCredentials = true;
        }

    }

    protected function get_query_separator($url) {
        return strpos($url, '?') === false ? '?' : '&';
    }   

    protected function get_full_url() {
        $https = !empty($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'], 'on') === 0 ||
            !empty($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
                strcasecmp($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') === 0;
        return
            ($https ? 'https://' : 'http://').
            (!empty($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'].'@' : '').
            (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'].
            ($https && $_SERVER['SERVER_PORT'] === 443 ||
            $_SERVER['SERVER_PORT'] === 80 ? '' : ':'.$_SERVER['SERVER_PORT']))).
            substr($_SERVER['SCRIPT_NAME'],0, strrpos($_SERVER['SCRIPT_NAME'], '/'));
    }

    protected function get_user_id() {
        @session_start();
        return session_id();
    }

    protected function get_user_path() {
        if ($this->options['user_dirs']) {
            return $this->get_user_id().'/';
        }
        return '';
    }

    protected function get_upload_path($file_name = null, $version = null) {
        $file_name = $file_name ? $file_name : '';
        if (empty($version)) {
            $version_path = '';
        } else {
            $version_dir = @$this->options['image_versions'][$version]['upload_dir'];
            if ($version_dir) {
                return $version_dir.$this->get_user_path().$file_name;
            }
            $version_path = $version.'/';
        }
        return $this->options['upload_dir'].$this->get_user_path()
            .$version_path.$file_name;
    }

    protected function get_upload_path2($file_name = null, $file_type) {
        $file_name = $file_name ? $file_name : '';        
        $fileType = Bs2UploadHelper::fileTypeMap($file_type);

        return $this->options['upload_dir'] . '/' . $fileType . '/' .
                date('Ym') . '/' . $file_name;
    }

    // Fix for overflowing signed 32 bit integers,
    // works for sizes up to 2^32-1 bytes (4 GiB - 1):
    protected function fix_integer_overflow($size) {
        if ($size < 0) {
            $size += 2.0 * (PHP_INT_MAX + 1);
        }
        return $size;
    }

    protected function get_file_size($file_path, $clear_stat_cache = false) {
        if ($clear_stat_cache) {
            if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
                clearstatcache(true, $file_path);
            } else {
                clearstatcache();
            }
        }
        return $this->fix_integer_overflow(filesize($file_path));
    }

    protected function get_file_object($file_name) {
        if ($this->is_valid_file_object($file_name)) {
            $file = new \stdClass();
            $file->name = $file_name;
            $file->size = $this->get_file_size(
                $this->get_upload_path($file_name)
            );
            $file->url = $this->get_download_url($file->name);
            foreach($this->options['image_versions'] as $version => $options) {
                if (!empty($version)) {
                    if (is_file($this->get_upload_path($file_name, $version))) {
                        $file->{$version.'Url'} = $this->get_download_url(
                            $file->name,
                            $version
                        );
                    }
                }
            }
            $this->set_additional_file_properties($file);
            return $file;
        }
        return null;
    }

    protected function get_error_message($error, $msg = '') {
        $errorMsg = $error;
        if (isset($this->error_messages[$error])) {
            $errorMsg = $msg !== '' ? sprintf($this->error_messages[$error], $msg) : $this->error_messages[$error];
        } 
        return $errorMsg;
    }

    function get_config_bytes($val) {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        switch($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        return $this->fix_integer_overflow($val);
    }

    protected function upcount_name_callback($matches) {
        $index = isset($matches[1]) ? ((int)$matches[1]) + 1 : 1;
        $ext = isset($matches[2]) ? $matches[2] : '';
        return ' ('.$index.')'.$ext;
    }

    protected function upcount_name($name) {
        return preg_replace_callback(
            '/(?:(?: \(([\d]+)\))?(\.[^.]+))?$/',
            array($this, 'upcount_name_callback'),
            $name,
            1
        );
    }

    protected function get_unique_filename($file_path, $name, $file) {
        $md5 = md5_file($file_path);

        $info = pathinfo($name);
        $ext = $info['extension'];
        if ($file->width && $file->height) {
            $md5 = "{$md5}_size{$file->width}x{$file->height}";
        }

        return "{$md5}_len{$file->size}.{$ext}";
    }

    protected function fix_file_extension($file_path, $name, $size, $type, $error,
            $index, $content_range) {
        // Add missing file extension for known image types:
        if (strpos($name, '.') === false &&
                preg_match('/^image\/(gif|jpe?g|png)/', $type, $matches)) {
            $name .= '.'.$matches[1];
        }
        if ($this->options['correct_image_extensions'] &&
                function_exists('exif_imagetype')) {
            switch(@exif_imagetype($file_path)){
                case IMAGETYPE_JPEG:
                    $extensions = array('jpg', 'jpeg');
                    break;
                case IMAGETYPE_PNG:
                    $extensions = array('png');
                    break;
                case IMAGETYPE_GIF:
                    $extensions = array('gif');
                    break;
            }
            // Adjust incorrect image file extensions:
            if (!empty($extensions)) {
                $parts = explode('.', $name);
                $extIndex = count($parts) - 1;
                $ext = strtolower(@$parts[$extIndex]);
                if (!in_array($ext, $extensions)) {
                    $parts[$extIndex] = $extensions[0];
                    $name = implode('.', $parts);
                }
            }
        }
        return $name;
    }

    protected function trim_file_name($file_path, $name, $size, $type, $error,
            $index, $content_range) {
        // Remove path information and dots around the filename, to prevent uploading
        // into different directories or replacing hidden system files.
        // Also remove control characters and spaces (\x00..\x20) around the filename:
        $name = trim(basename(stripslashes($name)), ".\x00..\x20");
        // Use a timestamp for empty filenames:
        if (!$name) {
            $name = str_replace('.', '-', microtime(true));
        }
        return $name;
    }

    protected function get_file_name($file_path, $name, $size, $type, $error,
            $index, $content_range, $file) {
        $name = $this->trim_file_name($file_path, $name, $size, $type, $error,
            $index, $content_range);
        $name = $this->fix_file_extension($file_path, $name, $size, $type, $error, $index, $content_range);
        return $this->get_unique_filename($file_path, $name, $file);
    }

    protected function handle_image_file($file_path, $file) {
        $failed_versions = array();
        foreach($this->options['image_versions'] as $version => $options) {
            if ($this->create_scaled_image($file->name, $version, $options)) {
                if (!empty($version)) {
                    $file->{$version.'Url'} = $this->get_download_url(
                        $file->name,
                        $version
                    );
                } else {
                    $file->size = $this->get_file_size($file_path, true);
                }
            } else {
                $failed_versions[] = $version ? $version : 'original';
            }
        }
        if (count($failed_versions)) {
            $file->error = $this->get_error_message('image_resize')
                    .' ('.implode($failed_versions,', ').')';
        }
        // Free memory:
        $this->destroy_image_object($file_path);
    }

    protected function handle_file_upload($uploaded_file, $name, $size, $type, $error,
        $index = null, $content_range = null) {
        $file = new \stdClass();
        $file->saveName = $name;
        $size = $this->fix_integer_overflow((int) $size);
        $file->size = $size;
        $file->type = $type;
        $file->simpleType = Bs2UploadHelper::fileTypeMap($type);
        if (in_array($file->simpleType, ['video', 'audio'])) {
            $file->length = Bs2UploadHelper::getVideoLength($uploaded_file);
        } else if ($file->simpleType == 'image') {
            list($img_width, $img_height) = $this->get_image_size($uploaded_file);
            $file->width = $img_width;
            $file->height = $img_height;
        }

        $file->name = $this->get_file_name($uploaded_file, $name, $size, $type, $error,
            $index, $content_range, $file);

        if ($this->validate($uploaded_file, $file, $error, $index)) {
            $this->handle_form_data($file, $index);

            // bs2上传
            $data = array('filename'=>$file->name , 'filepath'=>$uploaded_file, 'filesize' => $file->size);
            if ($imgUrl = $this->bs2Upload($data)) {
                $file->url = $imgUrl;
                if ($this->_isImage($uploaded_file)) {
                    $file->thumbnailUrl = $imgUrl . '?imageview/5/8/w/80/h/80/blur/0.01';
                }
            } else {
                $file->error = $this->get_error_message('bs2upload_error');
            }

            if ($this->options['local']) {
                $upload_dir = $this->get_upload_path2('', $type);
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, $this->options['mkdir_mode'], true);
                }
                $file_path = $this->get_upload_path2($file->name, $type);
                $append_file = $content_range && is_file($file_path) &&
                    $file->size > $this->get_file_size($file_path);
                if ($uploaded_file && is_uploaded_file($uploaded_file)) {
                    // multipart/formdata uploads (POST method uploads)
                    if ($append_file) {
                        file_put_contents(
                            $file_path,
                            fopen($uploaded_file, 'r'),
                            FILE_APPEND
                        );
                    } else {
                        move_uploaded_file($uploaded_file, $file_path);
                    }
                } else {
                    // Non-multipart uploads (PUT method support)
                    file_put_contents(
                        $file_path,
                        fopen('php://input', 'r'),
                        $append_file ? FILE_APPEND : 0
                    );
                }
                $file_size = $this->get_file_size($file_path, $append_file);
                if ($file_size === $file->size) {
                    $file->path = $file_path;

                } else {
                    $file->size = $file_size;
                    if (!$content_range && $this->options['discard_aborted_uploads']) {
                        unlink($file_path);
                        $file->error = $this->get_error_message('abort');
                    }
                }
            }

            $this->set_additional_file_properties($file);

            // 上传成功后的回调
            $callback = $this->options['on_upload_done'];
            if ($callback) {
                $callback($uploaded_file, $file);
            }
        }

        return $file;
    }

    protected function readfile($file_path) {
        $file_size = $this->get_file_size($file_path);
        $chunk_size = $this->options['readfile_chunk_size'];
        if ($chunk_size && $file_size > $chunk_size) {
            $handle = fopen($file_path, 'rb');
            while (!feof($handle)) {
                echo fread($handle, $chunk_size);
                @ob_flush();
                @flush();
            }
            fclose($handle);
            return $file_size;
        }
        return readfile($file_path);
    }

    protected function body($str) {
        Response::exitMsg($str);
    }
    
    protected function header($str) {
        header($str);
    }

    protected function get_upload_data($id) {
        return @$_FILES[$id];
    }

    protected function get_query_param($id) {
        return @$_GET[$id];
    }

    protected function get_server_var($id) {
        return @$_SERVER[$id];
    }

    protected function handle_form_data($file, $index) {
        // Handle form data, e.g. $_POST['description'][$index]
    }

    protected function get_version_param() {
        return basename(stripslashes($this->get_query_param('version')));
    }

    protected function get_singular_param_name() {
        return substr($this->options['param_name'], 0, -1);
    }

    protected function get_file_name_param() {
        $name = $this->get_singular_param_name();
        return basename(stripslashes($this->get_query_param($name)));
    }

    protected function get_file_names_params() {
        $params = $this->get_query_param($this->options['param_name']);
        if (!$params) {
            return null;
        }
        foreach ($params as $key => $value) {
            $params[$key] = basename(stripslashes($value));
        }
        return $params;
    }

    protected function get_file_type($file_path) {
        switch (strtolower(pathinfo($file_path, PATHINFO_EXTENSION))) {
            case 'jpeg':
            case 'jpg':
                return 'image/jpeg';
            case 'png':
                return 'image/png';
            case 'gif':
                return 'image/gif';
            default:
                return '';
        }
    }

    protected function download() {

    }

    protected function validate($uploaded_file, $file, $error, $index) {
        if ($error) {
            $file->error = $this->get_error_message($error);
            return false;
        }
        $content_length = $this->fix_integer_overflow(
            (int)$this->get_server_var('CONTENT_LENGTH')
        );
        $post_max_size = $this->get_config_bytes(ini_get('post_max_size'));
        if ($post_max_size && ($content_length > $post_max_size)) {
            $file->error = $this->get_error_message('post_max_size');
            return false;
        }
        if (!preg_match($this->options['accept_file_types'], $file->name)) {
            $file->error = $this->get_error_message('accept_file_types');
            return false;
        }
        if ($uploaded_file && is_uploaded_file($uploaded_file)) {
            $file_size = $this->get_file_size($uploaded_file);
        } else {
            $file_size = $content_length;
        }
        if ($this->options['max_file_size'] && (
                $file_size > $this->options['max_file_size'] ||
                $file->size > $this->options['max_file_size'])
            ) {
            $file->error = $this->get_error_message('max_file_size');
            return false;
        }
        if ($this->options['min_file_size'] &&
            $file_size < $this->options['min_file_size']) {
            $file->error = $this->get_error_message('min_file_size');
            return false;
        }
        if (is_int($this->options['max_number_of_files']) &&
                ($this->count_file_objects() >= $this->options['max_number_of_files']) &&
                // Ignore additional chunks of existing files:
                !is_file($this->get_upload_path($file->name))) {
            $file->error = $this->get_error_message('max_number_of_files');
            return false;
        }
        $max_width = @$this->options['max_width'];
        $max_height = @$this->options['max_height'];
        $min_width = @$this->options['min_width'];
        $min_height = @$this->options['min_height'];
        if (($max_width || $max_height || $min_width || $min_height)
           && preg_match($this->options['image_file_types'], $file->name)) {
            list($img_width, $img_height) = $this->get_image_size($uploaded_file);

            // If we are auto rotating the image by default, do the checks on
            // the correct orientation
            if (
                @$this->options['image_versions']['']['auto_orient'] &&
                function_exists('exif_read_data') &&
                ($exif = @exif_read_data($uploaded_file)) &&
                (((int) @$exif['Orientation']) >= 5 )
            ) {
              $tmp = $img_width;
              $img_width = $img_height;
              $img_height = $tmp;
              unset($tmp);
            }

        }
        if (!empty($img_width)) {
            if ($max_width && $img_width > $max_width) {
                $file->error = $this->get_error_message('max_width');
                return false;
            }
            if ($max_height && $img_height > $max_height) {
                $file->error = $this->get_error_message('max_height');
                return false;
            }
            if ($min_width && $img_width < $min_width) {
                $file->error = $this->get_error_message('min_width');
                return false;
            }
            if ($min_height && $img_height < $min_height) {
                $file->error = $this->get_error_message('min_height');
                return false;
            }
        }

        //校验图片最大比例
        if ($this->options['max_ratio']) {
            $maxRatio = $this->options['max_ratio'];
            list($w, $h) = $this->get_image_size($uploaded_file);
            $min = min($w, $h);
            $max = $min === $w ? $h : $w;
            if (($max / $min) > $maxRatio) {
                $file->error = $this->get_error_message('max_ratio', "{$maxRatio}:1");
                return false;
            }
        }

        return true;
    }
    protected function get_image_size($file_path) {
        if ($this->options['image_library']) {
            if (extension_loaded('imagick')) {
                $image = new \Imagick();
                try {
                    if (@$image->pingImage($file_path)) {
                        $dimensions = array($image->getImageWidth(), $image->getImageHeight());
                        $image->destroy();
                        return $dimensions;
                    }
                    return false;
                } catch (Exception $e) {
                    error_log($e->getMessage());
                }
            }
            if ($this->options['image_library'] === 2) {
                $cmd = $this->options['identify_bin'];
                $cmd .= ' -ping '.escapeshellarg($file_path);
                exec($cmd, $output, $error);
                if (!$error && !empty($output)) {
                    // image.jpg JPEG 1920x1080 1920x1080+0+0 8-bit sRGB 465KB 0.000u 0:00.000
                    $infos = preg_split('/\s+/', $output[0]);
                    $dimensions = preg_split('/x/', $infos[2]);
                    return $dimensions;
                }
                return false;
            }
        }
        if (!function_exists('getimagesize')) {
            error_log('Function not found: getimagesize');
            return false;
        }
        return @getimagesize($file_path);
    }
    public function generate_response($content, $print_response = true) {
        $this->response = $content;
        if ($print_response) {
            $json = json_encode($content);
            $redirect = stripslashes($this->get_query_param('redirect'));
            if ($redirect) {
                $this->header('Location: '.sprintf($redirect, rawurlencode($json)));
                return;
            }
            $this->head();
            if ($this->get_server_var('HTTP_CONTENT_RANGE')) {
                $files = isset($content[$this->options['param_name']]) ?
                    $content[$this->options['param_name']] : null;
                if ($files && is_array($files) && is_object($files[0]) && $files[0]->size) {
                    $this->header('Range: 0-'.(
                        $this->fix_integer_overflow((int)$files[0]->size) - 1
                    ));
                }
            }
            $this->body($json);
        }
        return $content;
    }

    public function get_response () {
        return $this->response;
    }

    protected function send_content_type_header() {
        $this->header('Vary: Accept');
        if (strpos($this->get_server_var('HTTP_ACCEPT'), 'application/json') !== false) {
            $this->header('Content-type: application/json');
        } else {
            $this->header('Content-type: text/plain');
        }
    }

    protected function send_access_control_headers() {
        $this->header('Access-Control-Allow-Origin: '.$this->options['access_control_allow_origin']);
        $this->header('Access-Control-Allow-Credentials: '
            .($this->options['access_control_allow_credentials'] ? 'true' : 'false'));
        $this->header('Access-Control-Allow-Methods: '
            .implode(', ', $this->options['access_control_allow_methods']));
        $this->header('Access-Control-Allow-Headers: '
            .implode(', ', $this->options['access_control_allow_headers']));
    }

    public function head() {
        $this->header('Pragma: no-cache');
        $this->header('Cache-Control: no-store, no-cache, must-revalidate');
        //会导致微剧院上次失败 bad gateway
        $this->header('Content-Disposition: inline; filename="files.json"');
        // Prevent Internet Explorer from MIME-sniffing the content-type:
        $this->header('X-Content-Type-Options: nosniff');
        if ($this->options['access_control_allow_origin']) {
            $this->send_access_control_headers();
        }
        $this->send_content_type_header();
    }

    public function post($print_response = true) {
        if ($this->get_query_param('_method') === 'DELETE') {
            return $this->delete($print_response);
        }
        $upload = $this->get_upload_data($this->options['param_name']);

        // Parse the Content-Disposition header, if available:
        $content_disposition_header = $this->get_server_var('HTTP_CONTENT_DISPOSITION');
        $file_name = $content_disposition_header ?
            rawurldecode(preg_replace(
                '/(^[^"]+")|("$)/',
                '',
                $content_disposition_header
            )) : null;
        // Parse the Content-Range header, which has the following form:
        // Content-Range: bytes 0-524287/2000000
        $content_range_header = $this->get_server_var('HTTP_CONTENT_RANGE');
        $content_range = $content_range_header ?
            preg_split('/[^0-9]+/', $content_range_header) : null;
        $size =  $content_range ? $content_range[3] : null;
        $files = array();
        if ($upload) {
            if (is_array($upload['tmp_name'])) {
                // param_name is an array identifier like "files[]",
                // $upload is a multi-dimensional array:
                foreach ($upload['tmp_name'] as $index => $value) {
                    $files[] = $this->handle_file_upload(
                        $upload['tmp_name'][$index],
                        $file_name ? $file_name : $upload['name'][$index],
                        $size ? $size : $upload['size'][$index],
                        $upload['type'][$index],
                        $upload['error'][$index],
                        $index,
                        $content_range
                    );
                }
            } else {
                // param_name is a single object identifier like "file",
                // $upload is a one-dimensional array:
                $files[] = $this->handle_file_upload(
                    isset($upload['tmp_name']) ? $upload['tmp_name'] : null,
                    $file_name ? $file_name : (isset($upload['name']) ?
                            $upload['name'] : null),
                    $size ? $size : (isset($upload['size']) ?
                            $upload['size'] : $this->get_server_var('CONTENT_LENGTH')),
                    isset($upload['type']) ?
                            $upload['type'] : $this->get_server_var('CONTENT_TYPE'),
                    isset($upload['error']) ? $upload['error'] : null,
                    null,
                    $content_range
                );
            }
        } else {
            var_dump('can not find file. confirm php.ini:
            file_uploads = On
            post_max_size = 100M
            upload_max_filesize = 100M');
            exit;
        }

        $response = array($this->options['param_name'] => $files);
        return $this->generate_response($response, $print_response);
    }

    public function delete($print_response = true) {
        $file_names = $this->get_file_names_params();
        if (empty($file_names)) {
            $file_names = array($this->get_file_name_param());
        }
        $response = array();
        foreach($file_names as $file_name) {
            // bs2删除，bs2不做删除
//            $success = $this->bs2Del($file_name);
            $success = true;
            $response[$file_name] = $success;
        }

        //删除本地
        if (is_file($this->options['del_file_path'])) {
            unlink($this->options['del_file_path']);
        }

        return $this->generate_response($response, $print_response);
    }

    // IPS存储
    private function bs2Upload($data){

        $params = array();
        $params['accesskey'] = BS2_ACCESS_KEY;
        $params['access_key_secret'] = BS2_ACCESS_SECRET;
        $params['filename'] = $data['filename'];
        $params['localfile'] = $data['filepath'];
        $params['bucket'] = $this->options['bucket'];
        $params['bs2host'] = BS2_HOST;
        $params['bs2dlhost'] = BS2_DL_HOST;
        $params['large'] = false;
        $params['content-type'] = $this->_getMimeType($params['localfile']);


        if ($data['filesize'] > $this->_chunkFileSize) {
            $params['large'] = true;
            $bs2upload = new Bs2upload($params);
            $rt = $bs2upload->uploadsFile();
        } else {
            $bs2upload = new Bs2upload($params);
            $rt = $bs2upload->uploadFile();
        }

        if ($rt) {
            if ($this->_isImage($params['localfile'])) {
                return $bs2upload->getImgUrl();
            } else {
                return $bs2upload->getDownloadUrl();
            }
        } else {
            return false;
        }
    }

    /**
     * IPS删除图片
     */
    private function bs2Del($filename){
        $params = array();
        $params['accesskey'] = BS2_ACCESS_KEY;
        $params['access_key_secret'] = BS2_ACCESS_SECRET;
        $params['filename'] = $filename;
        $params['bucket'] = $this->options['bucket'];
        $params['bs2host'] = BS2_HOST;
        $params['bs2dlhost'] = BS2_DL_HOST;
        $params['bs2delhost'] = BS2_DEL_HOST;
        $params['large'] = false;
        
        $bs2upload = new Bs2upload($params);
        if ($bs2upload->delFile()) {
            return true;
        } else {
            return false;
        }

    }


    function _getMimeDetect() {
        if (class_exists('finfo')) {
            return 'finfo';
        } else if (function_exists('mime_content_type')) {
            return 'mime_content_type';
        } else if ( function_exists('exec')) {
            $result = exec('file -ib '.escapeshellarg(__FILE__));
            if ( 0 === strpos($result, 'text/x-php') || 0 === strpos($result, 'text/x-c++')) {
                return 'linux';
            }
            $result = exec('file -Ib '.escapeshellarg(__FILE__));
            if ( 0 === strpos($result, 'text/x-php') || 0 === strpos($result, 'text/x-c++')) {
                return 'bsd';
            }
        }
        return 'internal';
    }

    function _getMimeType($path) {
        $mime = array(
            //applications
            'ai'    => 'application/postscript',
            'eps'   => 'application/postscript',
            'exe'   => 'application/octet-stream',
            'doc'   => 'application/vnd.ms-word',
            'xls'   => 'application/vnd.ms-excel',
            'ppt'   => 'application/vnd.ms-powerpoint',
            'pps'   => 'application/vnd.ms-powerpoint',
            'pdf'   => 'application/pdf',
            'xml'   => 'application/xml',
            'odt'   => 'application/vnd.oasis.opendocument.text',
            'swf'   => 'application/x-shockwave-flash',
            // archives
            'gz'    => 'application/x-gzip',
            'tgz'   => 'application/x-gzip',
            'bz'    => 'application/x-bzip2',
            'bz2'   => 'application/x-bzip2',
            'tbz'   => 'application/x-bzip2',
            'zip'   => 'application/zip',
            'rar'   => 'application/x-rar',
            'tar'   => 'application/x-tar',
            '7z'    => 'application/x-7z-compressed',
            // texts
            'txt'   => 'text/plain',
            'php'   => 'text/x-php',
            'html'  => 'text/html',
            'htm'   => 'text/html',
            'js'    => 'text/javascript',
            'css'   => 'text/css',
            'rtf'   => 'text/rtf',
            'rtfd'  => 'text/rtfd',
            'py'    => 'text/x-python',
            'java'  => 'text/x-java-source',
            'rb'    => 'text/x-ruby',
            'sh'    => 'text/x-shellscript',
            'pl'    => 'text/x-perl',
            'sql'   => 'text/x-sql',
            // images
            'bmp'   => 'image/x-ms-bmp',
            'jpg'   => 'image/jpeg',
            'jpeg'  => 'image/jpeg',
            'gif'   => 'image/gif',
            'png'   => 'image/png',
            'tif'   => 'image/tiff',
            'tiff'  => 'image/tiff',
            'tga'   => 'image/x-targa',
            'psd'   => 'image/vnd.adobe.photoshop',
            //audio
            'mp3'   => 'audio/mpeg',
            'mid'   => 'audio/midi',
            'ogg'   => 'audio/ogg',
            'mp4a'  => 'audio/mp4',
            'wav'   => 'audio/wav',
            'wma'   => 'audio/x-ms-wma',
            // video
            'avi'   => 'video/x-msvideo',
            'dv'    => 'video/x-dv',
            'mp4'   => 'video/mp4',
            'mpeg'  => 'video/mpeg',
            'mpg'   => 'video/mpeg',
            'mov'   => 'video/quicktime',
            'wm'    => 'video/x-ms-wmv',
            'flv'   => 'video/x-flv',
            'mkv'   => 'video/x-matroska',
            // apk
            'apk'   => 'application/vnd.android.package-archive',
        );

        $fmime = $this->_getMimeDetect();
        switch($fmime) {
            case 'finfo':
            $finfo = finfo_open(FILEINFO_MIME);
            if ($finfo) 
                $type = @finfo_file($finfo, $path);
            break;
            case 'mime_content_type':
            $type = mime_content_type($path);
            break;
            case 'linux':
            $type = exec('file -ib '.escapeshellarg($path));
            break;
            case 'bsd':
            $type = exec('file -Ib '.escapeshellarg($path));
            break;
            default:
            $pinfo = pathinfo($path);
            $ext = isset($pinfo['extension']) ? strtolower($pinfo['extension']) : '';
            $type = isset($mime[$ext]) ? $mime[$ext] : 'unkown';
            break;
        }
        $type = explode(';', $type);

        if ($fmime != 'internal' && $type[0] == 'application/octet-stream') {
            $pinfo = pathinfo($path); 
            $ext = isset($pinfo['extension']) ? strtolower($pinfo['extension']) : '';
            if (!empty($ext) && !empty($mime[$ext])) {
                $type[0] = $mime[$ext];
            }
        }

        return $type[0];
    }

    private function _isImage($filepath) {
        $contentType = $this->_getMimeType($filepath);

        return FALSE !== strpos($contentType, 'image');
    }

}
