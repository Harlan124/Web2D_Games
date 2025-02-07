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
require 'common-guest.inc';
require 'common-64bit.inc';

function hex2float( $str )
{
	$str = preg_replace('|[^0-9a-fA-F]|', '', $str);
	$bit = strlen($str) * 4;

	$func = "float{$bit}";
	if ( ! function_exists($func) )
		return printf("NOT float %s\n", $str);

	$fl = $func( hexdec($str) );
	printf("%s( %s ) = %f\n", $func, $str, $fl);
	return;
}

echo "hex -> float(IEE 754)\n";
for ( $i=1; $i < $argc; $i++ )
	hex2float( $argv[$i] );

/*
1.0 = float32( 3f800000 )
2.0 = float32( 40000000 )
 */
