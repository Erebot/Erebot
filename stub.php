#!/usr/bin/env php
<?php

if (in_array('phar', stream_get_wrappers()) && class_exists('Phar', 0)) {
Phar::interceptFileFuncs();
var_dump(__FILE__);
set_include_path('phar://' . __FILE__ . PATH_SEPARATOR . get_include_path());
set_include_path('phar://' . __FILE__ .'/Erebot-0.3.2/php' . PATH_SEPARATOR . get_include_path());
include 'phar://' . __FILE__ . '/' . Extract_Phar::START;
return;
}

class Extract_Phar
{
static $temp;
static $origdir;
const GZ = 0x1000;
const BZ2 = 0x2000;
const MASK = 0x3000;
const START = 'Erebot-0.3.2/script/Erebot';
const LEN = 6685;

static function go($return = false)
{
$fp = fopen(__FILE__, 'rb');
fseek($fp, self::LEN);
$L = unpack('V', $a = (binary)fread($fp, 4));
$m = (binary)'';

do {
$read = 8192;
if ($L[1] - strlen($m) < 8192) {
$read = $L[1] - strlen($m);
}
$last = (binary)fread($fp, $read);
$m .= $last;
} while (strlen($last) && strlen($m) < $L[1]);

if (strlen($m) < $L[1]) {
die('ERROR: manifest length read was "' .
strlen($m) .'" should be "' .
$L[1] . '"');
}

$info = self::_unpack($m);
$f = $info['c'];

if ($f & self::GZ) {
if (!function_exists('gzinflate')) {
die('Error: zlib extension is not enabled -' .
' gzinflate() function needed for zlib-compressed .phars');
}
}

if ($f & self::BZ2) {
if (!function_exists('bzdecompress')) {
die('Error: bzip2 extension is not enabled -' .
' bzdecompress() function needed for bz2-compressed .phars');
}
}

$temp = self::tmpdir();

if (!$temp || !is_writable($temp)) {
$sessionpath = session_save_path();
if (strpos ($sessionpath, ";") !== false)
$sessionpath = substr ($sessionpath, strpos ($sessionpath, ";")+1);
if (!file_exists($sessionpath) || !is_dir($sessionpath)) {
die('Could not locate temporary directory to extract phar');
}
$temp = $sessionpath;
}

$temp .= '/pharextract/'.basename(__FILE__, '.phar');
self::$temp = $temp;
self::$origdir = getcwd();
@mkdir($temp, 0777, true);
$temp = realpath($temp);

if (!file_exists($temp . DIRECTORY_SEPARATOR . md5_file(__FILE__))) {
self::_removeTmpFiles($temp, getcwd());
@mkdir($temp, 0777, true);
@file_put_contents($temp . '/' . md5_file(__FILE__), '');

foreach ($info['m'] as $path => $file) {
$a = !file_exists(dirname($temp . '/' . $path));
@mkdir(dirname($temp . '/' . $path), 0777, true);
clearstatcache();

if ($path[strlen($path) - 1] == '/') {
@mkdir($temp . '/' . $path, 0777);
} else {
file_put_contents($temp . '/' . $path, self::extractFile($path, $file, $fp));
@chmod($temp . '/' . $path, 0666);
}
}
}

chdir($temp);

if (!$return) {
include self::START;
}
}

static function tmpdir()
{
if (strpos(PHP_OS, 'WIN') !== false) {
if ($var = getenv('TMP') ? getenv('TMP') : getenv('TEMP')) {
return $var;
}
if (is_dir('/temp') || mkdir('/temp')) {
return realpath('/temp');
}
return false;
}
if ($var = getenv('TMPDIR')) {
return $var;
}
return realpath('/tmp');
}

static function _unpack($m)
{
$info = unpack('V', substr($m, 0, 4));
 $l = unpack('V', substr($m, 10, 4));
$m = substr($m, 14 + $l[1]);
$s = unpack('V', substr($m, 0, 4));
$o = 0;
$start = 4 + $s[1];
$ret['c'] = 0;

for ($i = 0; $i < $info[1]; $i++) {
 $len = unpack('V', substr($m, $start, 4));
$start += 4;
 $savepath = substr($m, $start, $len[1]);
$start += $len[1];
   $ret['m'][$savepath] = array_values(unpack('Va/Vb/Vc/Vd/Ve/Vf', substr($m, $start, 24)));
$ret['m'][$savepath][3] = sprintf('%u', $ret['m'][$savepath][3]
& 0xffffffff);
$ret['m'][$savepath][7] = $o;
$o += $ret['m'][$savepath][2];
$start += 24 + $ret['m'][$savepath][5];
$ret['c'] |= $ret['m'][$savepath][4] & self::MASK;
}
return $ret;
}

static function extractFile($path, $entry, $fp)
{
$data = '';
$c = $entry[2];

while ($c) {
if ($c < 8192) {
$data .= @fread($fp, $c);
$c = 0;
} else {
$c -= 8192;
$data .= @fread($fp, 8192);
}
}

if ($entry[4] & self::GZ) {
$data = gzinflate($data);
} elseif ($entry[4] & self::BZ2) {
$data = bzdecompress($data);
}

if (strlen($data) != $entry[0]) {
die("Invalid internal .phar file (size error " . strlen($data) . " != " .
$stat[7] . ")");
}

if ($entry[3] != sprintf("%u", crc32((binary)$data) & 0xffffffff)) {
die("Invalid internal .phar file (checksum error)");
}

return $data;
}

static function _removeTmpFiles($temp, $origdir)
{
chdir($temp);

foreach (glob('*') as $f) {
if (file_exists($f)) {
is_dir($f) ? @rmdir($f) : @unlink($f);
if (file_exists($f) && is_dir($f)) {
self::_removeTmpFiles($f, getcwd());
}
}
}

@rmdir($temp);
clearstatcache();
chdir($origdir);
}
}

Extract_Phar::go();
__HALT_COMPILER(); ?>
