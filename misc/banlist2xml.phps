<?php

/**
 * Babo Violent 2 banlist (or mutelist, or friendlist) to XML converter
 * Coded by keta 2011. MIT license.
 * Compiled version available at http://prozac.warlabs.ru/misc/banlist2xml.exe
 */

define('RECORD_LENGTH', 48);
define('NICK_LENGTH', 32);
define('IP_LENGTH', 16);

if (!isset($argv[1]))
{
	die('Usage: banlist2xml <listname> [-s|-e]'.PHP_EOL.
		'       -s Strip colors from nicknames'.PHP_EOL.
		'       -e Colors in nicknames will be encoded to XML entities'.PHP_EOL.
		'       If none of the options specified, colors will be converted to ^-codes'.PHP_EOL.PHP_EOL.
		'       Coded by keta. MIT license.'.PHP_EOL.
		'       Source available at http://prozac.warlabs.ru/misc/banlist2xml.phps'.PHP_EOL);
}

function cstr_trim($str)
{
	$nul_pos = strpos($str, chr(0));

	if ($nul_pos === false)
	{
		return $str;
	}

	return $nul_pos ? substr($str, 0, $nul_pos) : '';
}

if (!function_exists('str_split')) // PHP 4
{
	function str_split($string, $string_length = 1)
	{
		if ((strlen($string) > $string_length) || !$string_length)
		{
			$parts = array();
			do
			{
				$c = strlen($string);
				$parts[] = substr($string,0,$string_length);
				$string = substr($string,$string_length);
			}
			while ($string !== false);

			return $parts;
		}

		return array($string);
	}
}

echo '<?xml version="1.0" encoding="UTF-8"?>', PHP_EOL, '<players>', PHP_EOL;

if (!file_exists($argv[1]))
{
	die('<error>File not found</error>');
}

// Read data
$data = file_get_contents($argv[1]);

// Check file length (Babo sometimes brokes its lists)
if (strlen($data) % RECORD_LENGTH)
{
	die('<error>File size check failed</error>');
}

// Choose what to do with color codes
$todo = -1;
if (isset($argv[2]))
{
	if ($argv[2] == '-s')
	{
		$todo = 0;
	}
	else if ($argv[2] == '-e')
	{
		$todo = 1;
	}
}

// Generate color conversion tables
$colors_quake =
$colors_strip =
$colors_xml   = array();
for ($i = 1; $i <= 9; $i++)
{
	$color = chr($i);
	$colors_quake[$color] = '^'.$i;
	$colors_strip[$color] = '';
	$colors_xml[$color]   = '&#x0'.$i.';';
}

$data = str_split($data, RECORD_LENGTH);
foreach ($data as $item)
{
	if (strlen($item) != RECORD_LENGTH)
	{
		continue;
	}

	list($nick, $ip) = str_split($item, NICK_LENGTH);

	$nick = cstr_trim($nick);
	$ip   = cstr_trim($ip);

	switch ($todo)
	{
		case 0:  $nick = strtr($nick, $colors_strip); break;
		case 1:  $nick = strtr($nick, $colors_xml);   break;
		default: $nick = strtr($nick, $colors_quake);
	}

	echo "\t<player>", PHP_EOL,
		"\t\t<nick>", $nick, '</nick>', PHP_EOL,
		"\t\t<ip>", $ip, '</ip>', PHP_EOL,
		"\t</player>", PHP_EOL;
}

echo "</players>\n";

