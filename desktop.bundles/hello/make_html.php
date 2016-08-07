<?
$bh = require('hello.bh.php');

$bemjson = file_get_contents('hello.bemjson.js');

$bemjson = str_replace(
	["module.exports = ",'{','}',':',"block","title","head","elem","url","scripts",
	"content","mods","theme","size","mix","placeholder",
	"type","text"], 
	["",'[',']','=>',"'block'","'title'","'head'","'elem'","'url'","'scripts'",
	"'content'","'mods'","'theme'","'size'","'mix'","'placeholder'",
	"'type'","'text'"], 
	$bemjson);

//echo $bemjson;

eval('$bj = ' . $bemjson.';');
//var_dump($bj);

echo $bh->apply($bj);