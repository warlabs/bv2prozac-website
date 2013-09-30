<?php

/**
 * Babo Violent 2 banlist (or mutelist, or friendlist) restoring tool
 * Coded by keta 2011. MIT license.
 * Compiled version available at http://prozac.warlabs.ru/misc/banlistrestore.exe
 */

define('RECORD_LENGTH', 48);
define('NICK_LENGTH', 32);
define('IP_LENGTH', 16);

if (!isset($argv[1]))
{
	die('Usage: banlistrestore <listname> [-s]'.PHP_EOL.
		'       -s Strip colors from nicknames'.PHP_EOL.PHP_EOL.
		'       Coded by keta. MIT license.'.PHP_EOL.
		'       Source available at http://prozac.warlabs.ru/misc/banlistrestore.phps'.PHP_EOL);
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

if (!function_exists('file_put_contents')) // PHP 4
{
	function file_put_contents($filename, $data)
	{
		$f = @fopen($filename, 'w');

		if (!$f)
		{
			return false;
		}

		$bytes = @fwrite($f, $data);
		@fclose($f);
		return $bytes;
	}
}

if (!file_exists($argv[1]))
{
	exit("File not found\n");
}

// Read data
$data = file_get_contents($argv[1]);

echo 'Read '.strlen($data)." bytes\n";

$matches = array();
$count = preg_match_all('/(.{'.NICK_LENGTH.'})(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/', $data, $matches, PREG_SET_ORDER);

if ($count == 0)
{
	exit("No records found");
}

echo 'Found '.$count." records\n";

$strip = (isset($argv[2]) && ($argv[2] == '-s'));
$result = '';
foreach ($matches as $id => $record)
{
	$nick = trim(cstr_trim($record[1]));

	if ($strip)
	{
		$nick = preg_replace('/['.chr(1).'-'.chr(31).']+/', '', $nick);
	}

	if ((strlen($nick) == 0) || (strlen($nick) == NICK_LENGTH))
	{
		$nick = 'RESTORED-IP-BAN';
	}

	$result.= str_pad($nick, NICK_LENGTH, chr(0), STR_PAD_RIGHT).str_pad($record[2], IP_LENGTH, chr(0), STR_PAD_RIGHT);

	$ip   = $record[2];
}

file_put_contents($argv[1].'.restored', $result);
