<?php
/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of Web2D Games.
    <https://github.com/rufaswan/Web2D_Games>

Web2D Games is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Web2D Games is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Web2D Games.  If not, see <http://www.gnu.org/licenses/>.
[/license]
 */
require 'common.inc';
require 'disc.inc';

function disc( $tag, $fname )
{
	if ( empty($tag) )
		return php_error('NO TAG');

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$sect = scnsect($file);

	$off = array();

	$ed6 = str2int($file, $sect[6]-4, 4);
	$st6 = $sect[6];
		$off[] = $ed6;
	switch ( $tag )
	{
		case 'dw1':
		case 'dw2':
			while ( $st6 < $ed6 )
			{
				$b0 = str2int($file, $st6+8, 4);
					$st6 += 0x10;

				$st19 = $b0 & 0xfffff;
				if ( array_search($st19, $off) === false )
					$off[] = $st19;
			} // while ( $st6 < $ed6 )
			break;
		case 'dwn':
			while ( $st6 < $ed6 )
			{
				$b0 = str2int($file, $st6+8, 4);
					$st6 += 0x14;

				$st19 = $b0 & 0xfffff;
				if ( array_search($st19, $off) === false )
					$off[] = $st19;
			} // while ( $st6 < $ed6 )
			break;
	} // switch ( $tag )

	echo "$fname\n";
	sort($off);
	$cnt = count($off) - 1;
	for ( $i=0; $i < $cnt; $i++ )
		printf("  %8x - %8x = %8x\n", $off[$i+0], $off[$i+1], $off[$i+1]-$off[$i+0]);
	return;
}

printf("%s  [-dw1/-dw2/-dwn]  FILE\n", $argv[0]);
$tag = '';
for ( $i=1; $i < $argc; $i++ )
{
	switch ( $argv[$i] )
	{
		case '-dw1':  $tag = 'dw1'; break;
		case '-dw2':  $tag = 'dw2'; break;
		case '-dw3':
		case '-dwn':  $tag = 'dwn'; break;
		default:
			disc( $tag, $argv[$i] );
			break;
	} // switch ( $argv[$i] )
}
