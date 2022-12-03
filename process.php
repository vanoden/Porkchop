<?php
$config = array(
    "inpath"    => "html.src",
    "outpath"   => "html"
);

if (! file_exists($config['inpath'])) {
	print "No input path found: ".getcwd()."/".$config['inpath']."\n";
	exit(1);
}

if (! file_exists($config['outpath'])) {
	mkdir($config['outpath'],0755);
}

$cache = array(
	"companyCode"		=> "spectros",
	"companyName"		=> "Spectros Instruments, Inc.",
    "siteTitle"			=> "Spectros Instruments Web Portal",
    "videoPath"			=> "https://www.spectrosinstruments.com/_storage/path/Videos",
    "docsPath"			=> "http://assets.spectrosinstruments.com/docs",
	"static_version"	=> date('YmdHi')
);

$files = scandir($config['inpath']."/pre");
foreach ($files as $file) {
	if (preg_match('/^([\w\-\_\.]+)\.html/',$file,$matches)) {
		$key = $matches[1];
		$contents = file_get_contents($config['inpath']."/pre/$file");
		$cache[$key] = $contents;
		print "Preloaded ".$config['inpath']."/pre/$file as $key\n";
	}
}

process_files($config['inpath'],$config['outpath']);

function process_files($inpath,$outpath) {
	$files = scandir($inpath);
	foreach ($files as $file) {
		if (preg_match('/^\./',$file)) continue;
		$path = $inpath."/".$file;
		if (is_dir($path)) {
			print "Copying folder $path\n";
			$folder = $outpath."/".$file;
			if (!file_exists($folder)) {
				print "Making $folder\n";
				mkdir($folder, 0755);
			}
			process_files("$inpath/$file","$outpath/$file");
		}
		elseif (preg_match('/\.(html|js|css)/',$file)) {
			print "Processing $path\n";
			$contents = file_get_contents($path);
			$fp = fopen($outpath."/".$file,'w');
			fwrite($fp, replace($contents));
			fclose($fp);
		}
		else {
			print "Copying $file\n";
			copy($path,$outpath."/".$file);
		}
	}
}

function replace($content) {
	global $cache;
	$module_pattern = "/<\%\=\s?([\w\-\.\_]+)\s?\%>/is";
	while ( preg_match ( $module_pattern, $content, $matched ) ) {
		$search = $matched[0];
		$key = $matched[1];
		if (!isset($cache[$key])) {
			$cache[$key] = "";
		}
		//print "\tReplace '".$search."' ($key) with '".$cache[$key]."\n";
		$content = str_replace($search, $cache[$key], $content);
	}

	// Return Messsage
	return $content;
}
