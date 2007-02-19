<?php


function getIconMimeArray() {
  return array(
		"application/msword" => "mime-wordprocessing",
		"application/ogg" => "audio-x-generic",
		"application/pdf" => "mime-pdf",
		"application/postscript" => "mime-wordprocessing",
		"application/rtf" => "mime-wordprocessing",
		"application/vnd.lotus-1-2-3" => "mime-spreadsheet",
		"application/vnd.ms-excel" => "mime-spreadsheet",
		"application/vnd.ms-powerpoint" => "mime-presentation",
		"application/vnd.oasis.opendocument.presentation" => "mime-presentation",
		"application/vnd.oasis.opendocument.spreadsheet" => "mime-spreadsheet",
		"application/vnd.oasis.opendocument.text" => "mime-wordprocessing",
		"application/vnd.oasis.opendocument.graphics" => "mime-vectorgfx",
		"application/vnd.stardivision.calc" => "mime-spreadsheet",
		"application/vnd.stardivision.impress" => "mime-presentation",
		"application/vnd.stardivision.writer" => "mime-wordprocessing",
		"application/vnd.sun.xml.calc" => "mime-spreadsheet",
		"application/vnd.sun.xml.impress" => "mime-presentation",
		"application/vnd.sun.xml.writer" => "mime-wordprocessing",
		"application/vnd.sun.xml.writer.global" => "mime-wordprocessing",
		"application/vnd.sun.xml.draw" => "mime-vectorgfx",
		"application/wordperfect" => "mime-wordprocessing",
		"application/x-7z-compressed" => "mime-tgz",
		"application/x-abiword" => "mime-wordprocessing",
		"application/x-applix-spreadsheet" => "mime-spreadsheet",
		"application/x-applix-word" => "mime-wordprocessing",
		"application/x-archive" => "mime-tar",
		"application/x-arj" => "mime-tgz",
		"application/x-bzip-compressed-tar" => "mime-tgz",
		"application/x-bzip" => "mime-tgz",
		"application/x-compressed-tar" => "mime-tgz",
		"application/x-compress" => "mime-tgz",
		"application/x-cpio-compressed" => "mime-tgz",
		"application/x-cpio" => "mime-tgz",
		"application/x-deb" => "mime-tgz",
		"application/x-dvi" => "mime-wordprocessing",
		"application/x-executable" => "application/x-executable",
		"application/x-font-afm" => "font-x-generic",
		"application/x-font-bdf" => "font-x-generic",
		"application/x-font-linux-psf" => "font-x-generic",
		"application/x-font-pcf" => "font-x-generic",
		"application/x-font-sunos-news" => "font-x-generic",
		"application/x-font-ttf" => "font-x-generic",
		"application/x-gnumeric" => "mime-spreadsheet",
		"application/x-gzip" => "mime-tgz",
		"application/xhtml+xml" => "text-html",
		"application/x-killustrator" => "mime-vectorgfx",
		"application/x-kpresenter" => "mime-presentation",
		"application/x-kspread" => "mime-spreadsheet",
		"application/x-kword" => "mime-wordprocessing",
		"application/x-ms-dos-executable" => "application/x-executable",
		"application/x-perl" => "mime-shellscript",
		"application/x-php" => "text-html",
		"application/x-python-bytecode" => "mime-shellscript",
		"application/x-rar" => "mime-tgz",
		"application/x-rpm" => "mime-rpm",
		"application/x-shellscript" => "mime-shellscript",
		"application/x-shockwave-flash" => "video-x-generic",
		"application/x-tar" => "mime-tar",
		"application/x-tarz" => "mime-tgz",
		"application/x-tex" => "mime-tex",
		"application/x-zip" => "mime-zip",
		"application/zip" => "mime-zip",
		"application" => "mime-zip",
		"audio" => "mime-sound",
		"image" => "mime-image2",
		"video" => "mime-video",
		"image/jpeg" => "mime-image",
		"text/html" => "mime-html",
		"text/xml" => "mime-xml",
		"text/x-c++" => "mime-source_cpp",
		"text/x-c" => "mime-source_c",
		"text/x-c-header" => "mime-source_h",
		"text/x-php" => "mime-source_php",
		"text" => "mime-txt",
		"text/vnd.wap.wml" => "mime-html",
		"text/x-csh" => "mime-shellscript",
		"text/x-python" => "mime-shellscript",
		"text/x-sh" => "mime-shellscript",
		"text/x-vcalendar" => "mime-vcalendar",
		"text/x-vcard" => "mime-address-book",
		"text/x-zsh" => "mime-shellscript",
		"text/x-makefile" => "mime-make",
		"x-font-afm" => "font-x-generic");
}

function getIconMimeFile($sysmime) {
  $icon="";
  if ($sysmime) {    
    $tmime=explode(";",$sysmime);
    $mime=trim($tmime[0]);
    $mimeIcon=getIconMimeArray();
    if (isset($mimeIcon[$mime])) {
      $icon=$mimeIcon[$mime].".png";	  
    } else {
      $p=strpos($mime, '/');
      $mime=substr($mime,0,$p);
      if (isset($mimeIcon[$mime])) {
      $icon=$mimeIcon[$mime].".png";
      } 
    }
  }
  return $icon;
}

function getSysMimeFile($f,$fn="") {
  $sys = trim(`file -bi "$f"`);
  $txt=getTextMimeFile($f);
  error_log("MIME:.file -bi [$f] [$sys]");
  // correct errors of file function
  if (preg_match('/Makefile/',$fn)) return 'text/x-makefile';
  if (preg_match('/ASCII C\+\+/',$txt)) {
     if (preg_match('/\.h$/',$fn))    return 'text/x-c-header';
     return 'text/x-c++';
  } 
  if (preg_match('/PHP script/',$txt))  return 'text/x-php';
  
  // open office archive
  if (preg_match('/zip$/',$sys)) {
  
    if (preg_match('/OpenOffice/',$txt)) {
      if (preg_match('/\.sxw$/',$fn))    return 'application/vnd.sun.xml.writer';
      if (preg_match('/\.sxc$/',$fn))    return 'application/vnd.sun.xml.calc';
      if (preg_match('/\.sxi$/',$fn))    return 'application/vnd.sun.xml.impress';
      if (preg_match('/\.sxd$/',$fn))    return 'application/vnd.sun.xml.draw';
      if (preg_match('/\.sxg$/',$fn))    return 'application/vnd.sun.xml.writer.global';
      return 'application/vnd.sun.xml.writer';
      
    }
    if (preg_match('/OpenDocument/',$txt)) {
      if (preg_match('/\.odp$/',$fn))    return 'application/vnd.oasis.opendocument.presentation';
      if (preg_match('/\.odt$/',$fn))    return 'application/vnd.oasis.opendocument.text';
      if (preg_match('/\.ods$/',$fn))    return 'application/vnd.oasis.opendocument.spreadsheet';
      if (preg_match('/\.odg$/',$fn))    return 'application/vnd.oasis.opendocument.graphics';
      return 'application/vnd.oasis.opendocument.text';
    
    }

      if (preg_match('/\.sxw$/',$fn))    return 'application/vnd.sun.xml.writer';
      if (preg_match('/\.sxc$/',$fn))    return 'application/vnd.sun.xml.calc';
      if (preg_match('/\.sxi$/',$fn))    return 'application/vnd.sun.xml.impress';
      if (preg_match('/\.sxd$/',$fn))    return 'application/vnd.sun.xml.draw';
      if (preg_match('/\.sxg$/',$fn))    return 'application/vnd.sun.xml.writer.global';
      if (preg_match('/\.odp$/',$fn))    return 'application/vnd.oasis.opendocument.presentation';
      if (preg_match('/\.odt$/',$fn))    return 'application/vnd.oasis.opendocument.text';
      if (preg_match('/\.ods$/',$fn))    return 'application/vnd.oasis.opendocument.spreadsheet';
      if (preg_match('/\.odg$/',$fn))    return 'application/vnd.oasis.opendocument.graphics';
    
  }
  if (!$sys ) {
    // try with text only
    if ($txt) {
      if (preg_match('/^XML/',$txt))  return 'text/xml';
    }
    
    if (preg_match('/\.xls$/',$fn))    return 'application/vnd.ms-excel';
    if (preg_match('/\.doc$/',$fn))    return 'application/msword';
    if (preg_match('/\.ppt$/',$fn))    return 'application/vnd.ms-powerpoint';
  }
  if ($sys=='application/msword') { // for old sys mime info
    if (preg_match('/\.xls$/',$fn))    return 'application/vnd.ms-excel';
    if (preg_match('/\.ppt$/',$fn))    return 'application/vnd.ms-powerpoint';
  }

  return $sys;
}
function getTextMimeFile($f) {
  $txt = trim(`file -b "$f"`);

  if (! $txt) return " ";
  return $txt;
}
?>