<?php

Class Crontab {
  var $user = NULL;
  var $crontab = '';

  public function __construct($user = NULL) {
    $this->user = $user;
    return $this;
  }

  public function setUser($user) {
    $this->user = $user;
    return $this->user;
  }

  public function unsetUser() {
    $this->user = NULL;
    return $this->user;
  }

  private function load() {
    $cmd = 'crontab -l';
    if( $this->user != NULL ) {
      $cmd .= ' -u '.escapeshellcmd($this->user);
    }
    $cmd .= ' 2> /dev/null';

    $ph = popen($cmd, 'r');
    if( $ph === FALSE ) {
      error_log(__CLASS__."::".__FUNCTION__." Error popen");
      return FALSE;
    }

    $crontab = stream_get_contents($ph);
    if( $crontab === FALSE ) {
      error_log(__CLASS__."::".__FUNCTION__." Error stream_get_contents");
      return FALSE;
    }

    $this->crontab = $crontab;

    return $crontab;
  }

  private function save() {
    $tmp = tempnam('/tmp', 'crontab');
    if( $tmp === FALSE ) {
      error_log(__CLASS__."::".__FUNCTION__." Error creating temporary file");
      return FALSE;
    }

    $ret = file_put_contents($tmp, $this->crontab);
    if( $ret === FALSE ) {
      error_log(__CLASS__."::".__FUNCTION__." Error writing content to file '".$tmp."'");
      return FALSE;
    }

    $cmd = 'crontab';
    if( $this->user != NULL ) {
      $cmd .= ' -u '.escapeshellcmd($this->user);
    }
    $cmd .= ' '.escapeshellcmd($tmp);
    $cmd .= ' > /dev/null 2>&1';

    system($cmd, $ret);
    if( $ret != 0 ) {
      error_log(__CLASS__."::".__FUNCTION__." Error saving crontab '".$tmp."'");
      return FALSE;
    }

    return $this->crontab;
  }

  public function registerFile($file) {
    $crontab = file_get_contents($file);
    if( $crontab === FALSE ) {
      error_log(__CLASS__."::".__FUNCTION__." Error reading content from file '".$file."'");
      return FALSE;
    }

    $crontab = "# BEGIN:FREEDOM_CRONTAB:$file\n".$crontab."\n# END:FREEDOM_CRONTAB:$file\n";

    $activeCrontab = $this->load();
    if( $activeCrontab === FALSE ) {
      error_log(__CLASS__."::".__FUNCTION__." Error reading active crontab");
      return FALSE;
    }

    if( preg_match('/^#\s+BEGIN:FREEDOM_CRONTAB:\Q'.$file.'\E.*?#\s+END:FREEDOM_CRONTAB:\Q'.$file.'\E$/ms', $activeCrontab) === 1 ) {
      print "Removing existing crontab\n";
      $tmpCrontab = preg_replace('/^#\s+BEGIN:FREEDOM_CRONTAB:\Q'.$file.'\E.*?#\s+END:FREEDOM_CRONTAB:\Q'.$file.'\E$/ms', '', $activeCrontab);
      if( $tmpCrontab === NULL ) {
	error_log(__CLASS__."::".__FUNCTION__." Error removing existing registered crontab");
	return FALSE;
      }
      $activeCrontab = $tmpCrontab;
    }

    $activeCrontab .= "\n".$crontab;
    $this->crontab = $activeCrontab;

    $ret = $this->save();
    if( $ret === FALSE ) {
      error_log(__CLASS__."::".__FUNCTION__." Error saving crontab");
      return FALSE;
    }

    return $this->crontab;
  }

  public function unregisterFile($file) {
    $activeCrontab = $this->load();
    if( $activeCrontab === FALSE ) {
      error_log(__CLASS__."::".__FUNCTION__." Error reading active crontab");
      return FALSE;
    }

    $tmpCrontab = preg_replace('/^#\s+BEGIN:FREEDOM_CRONTAB:\Q'.$file.'\E.*?#\s+END:FREEDOM_CRONTAB:\Q'.$file.'\E$/ms', '', $activeCrontab);
    if( $tmpCrontab === NULL ) {
      error_log(__CLASS__."::".__FUNCTION__." Error unregistering crontab '".$file."' from active crontab");
      return FALSE;
    }

    $this->crontab = $tmpCrontab;

    $ret = $this->save();
    if( $ret === FALSE ) {
      error_log(__CLASS__."::".__FUNCTION__." Error saving crontab");
      return FALSE;
    }

    return $this->crontab;
  }

  public function listAll() {
    $crontabs = $this->getActiveCrontab();
    if( $crontabs === FALSE ) {
      error_log(__CLASS__."::".__FUNCTION__." Error retrieving active crontabs");
      return FALSE;
    }

    print "\n";
    print "Active crontabs\n";
    print "---------------\n";
    print "\n";
    foreach($crontabs as $crontab) {
      print "Crontab: ".$crontab['file']."\n";
      print "--8<--\n".$crontab['content']."\n-->8--\n\n";
    }

    return TRUE;
  }

  public function getActiveCrontab() {
    $activeCrontab = $this->load();
    if( $activeCrontab === FALSE ) {
      error_log(__CLASS__."::".__FUNCTION__." Error reading active crontab");
      return FALSE;
    }
    
    $ret = preg_match_all(
			  '/^#\s+BEGIN:FREEDOM_CRONTAB:(.*?)\n(.*?)\n#\s+END:FREEDOM_CRONTAB:\1/ms',
			  $activeCrontab,
			  $matches,
			  PREG_SET_ORDER
			  );
    if( $ret === FALSE ) {
      error_log(__CLASS__."::".__FUNCTION__." Error in preg_match_all");
      return FALSE;
    }

    $crontabs = array();
    foreach($matches as $match) {
      array_push($crontabs,
		 array(
		       'file' => $match[1],
		       'content' => $match[2]
		       )
		 );
    }

    return $crontabs;
  }

}

?>