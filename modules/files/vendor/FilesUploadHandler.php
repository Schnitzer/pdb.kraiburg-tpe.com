<?php 

include_once NCW . DS . 'vendor' . DS . 'jquery-file-upload' . DS . 'UploadHandler.php'; 

class NcwUploadHandler extends UploadHandler {

    protected $_file_object = null;

    protected $_folder_id = 0;

    protected $_text_helper = null;

    protected $_thumbs_formats = array('jpg', 'jpeg', 'gif', 'png');

    protected $_thumbs_size = array(32, 96);

    function __construct($options = null, $file_object = null, $folder_id = 0, $text_helper = null) {
        $this->_file_object = $file_object;
        $this->_folder_id = $folder_id;        
        $this->_text_helper = $text_helper;
        parent::__construct($options, true, null);
    }

    protected function handle_file_upload($uploaded_file, $name, $size, $type, $error, $index = null, $content_range = null) {
        $name = explode('.', $name);
        $file_type = strtolower(array_pop($name));
        $file_name = $this->_text_helper->cleanForUrl(implode('.', $name));

        $this->_file_object->create();
        $this->_file_object->setType($file_type, false);
        $this->_file_object->setName($file_name, false);
        $this->_file_object->setSize($size, false);
        $this->_file_object->setFolderId($this->_folder_id, false);
        $this->_file_object->save();
        $file_id = $this->_file_object->getId();

        $name = $file_name . '_' . $file_id . '.' . $file_type;

        $file = parent::handle_file_upload(
            $uploaded_file, $name, $size, $type, $error, $index, $content_range
        );

       if (true === in_array($file_type, $this->_thumbs_formats)) {
            include_once MODULES . DS . 'files' . DS . 'vendor' . DS . 'wideimage' . DS . 'WideImage.php';
            foreach ($this->_thumbs_size as $thumb_size) {
                $target_file = $this->options['upload_dir'] . $name;
                $target_file_thumb = $this->options['upload_dir'] . '..' . DS . 'thumbnails' . DS . $file_name . '_' . $file_id . '_' . $thumb_size . '.' . $file_type;
                if ($size == 16) {
                    if ($file_type == 'png') {
                        WideImage::load($target_file)->resize($thumb_size, $thumb_size/4*3)->saveToFile($target_file_thumb, NULL, 6);
                    } else if ($file_type == 'gif') {
                        // do's not support compression level
                        WideImage::load($target_file)->resize($thumb_size, $thumb_size/4*3)->saveToFile($target_file_thumb, NULL);
                    } else {
                        WideImage::load($target_file)->resize($thumb_size, $thumb_size/4*3)->saveToFile($target_file_thumb, NULL, 35);
                    }
                } else {
                    if ($file_type == 'png') {
                        WideImage::load($target_file)->resize($thumb_size, $thumb_size/4*3)->saveToFile($target_file_thumb, NULL, 6);
                    } else if ($file_type == 'gif') {
                        // do's not support compression level
                        WideImage::load($target_file)->resize($thumb_size, $thumb_size/4*3)->saveToFile($target_file_thumb, NULL);
                    } else {
                        WideImage::load($target_file)->resize($thumb_size, $thumb_size/4*3)->saveToFile($target_file_thumb, NULL, 47);
                    }
                }
            }
        } 

        return $file;
    } 

}

?>