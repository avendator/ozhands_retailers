<?php

/**
 * Class zip_create
 * Generate Retailer plugin ZIP archive 
 */
class zip_create {
	/**
	 * @var string
	 */
	private $token;
	/**
	 * @var string
	 */
	private $name;
	/**
	 * @var array
	 */
	private $data;
	/**
	 * @var string
	 */
	private $directory;

	public function __construct() {
	    $token = random_bytes(10);
	    $token = bin2hex($token);
	    $this->token = base64_encode($token);
	    $name = random_bytes(5);
	    $name = bin2hex($name);
	    $this->name = base64_encode($name);
	    $this->directory = $_SERVER['DOCUMENT_ROOT'].'/wp-content/plugins/ozhands_retailers/zip_archives/'.$this->name;
	    $this->data['token'] = $token;
	    $this->data['name'] = $name;
	}

	/**
	 * creates a compressed zip file 
	 * @param array $files
	 * @param string $destination (path to file.zip)
	 * @param bool $overwrite
	 * @return bool
	 */
	private function create_zip($files = array(), $destination = '', $overwrite = false) {
	    //if the zip file already exists and overwrite is false, return false
	    if( file_exists($destination) && !$overwrite ) { 
	        return false; 
	    }
	    //vars
	    $valid_files = array();
	    if( is_array($files) ) {

	        foreach( $files as $file ) {
	            if( file_exists($file) ) {
	                $pos = stripos( $file, 'ozhands_connection' );
	                if ( $pos !== false ) {
	                    $file_name = substr($file, $pos + 19);
	                }
	                else {
	                    $file_name = $file;
	                }
	                $valid_files[] = ['file' => $file, 'name' => $file_name];
	            }
	        } 
	    }
	    // if we have good files...
	    if ( count($valid_files) ) {
	        //create the archive
	        $zip = new ZipArchive();
	        if( $zip->open( $destination, $overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true ) {
	            return false;
	        }
	        //add the files
	        foreach( $valid_files as $file ) {
	            $zip->addFile( $file['file'], $file['name'] );
	        }
	        $zip->close();
	        return file_exists($destination);
	    }
	    else {
	        return false;
	    }
	}

	/**
	 * creates a directory with a compressed zip file
	 * @param string $file_for_token
	 * @param array $files
	 * @return bool
	 */
	private function create_ozhand_connection_archive( $file_for_token, $files = array() ) {
		global $ozhands_name;
	    
	    $content = '<?php'."\n".'$token = '."'".$this->token."';"."\n".'$site = '."'".site_url()."';"."\n".'$name = '."'".$ozhands_name."';";

	    if ( is_writable($file_for_token) ) {

	        $handle = fopen($file_for_token, 'w');

	        if ( !$handle ) {
	        	return;
	        }

	        fwrite($handle, $content );

	        fclose($handle);
	    }
        if ( !is_dir( $this->directory ) ) {
	    	$result = mkdir( $this->directory );

	    	if ( !$result ) {
	    		return $result;
	    	}
    	}
    	$destination = $this->directory.'/ozhands_connection.zip';
    	$result = $this->create_zip( $files, $destination );
		return $result;
	}

	/**
	 * @param string $file_for_token
	 * @param array $files
	 * @return array $data or boll false
	 */
	public function get_zip_data($file_for_token, $files = array()) {

		if ( $this->create_ozhand_connection_archive( $file_for_token, $files ) === true ) {
			return $this->data;
		}		
	}
}