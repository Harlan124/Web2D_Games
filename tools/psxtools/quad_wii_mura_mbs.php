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
require 'common-quad.inc';
require 'quad.inc';

define('METAFILE', true);

function colorquad( &$mbs, $pos )
{
	$color = array();
	for ( $i=0; $i < $mbs['k']; $i += 4 )
	{
		$s = substr($mbs['d'], $pos+$i, 4);
		$rgba = '#' . bin2hex($s);

		if ( $rgba == '#ffffffff' )
			$color[] = '1';
		else
		if ( $rgba == '#00000000' )
			$color[] = '0';
		else
			$color[] = $rgba;
	} // for ( $i=0; $i < $mbs['k']; $i += 4 )

	$cqd = array($color[1] , $color[2] , $color[3] , $color[4]);
	if ( implode('',$cqd) == '1111' )
		$cqd = '';
	return $cqd;
}

function sectquad( &$mbs, $pos )
{
	$float = array();
	for ( $i=0; $i < $mbs['k']; $i += 4 )
	{
		$b = substrrev($mbs['d'], $pos+$i, 4);
		$float[] = float32($b);
	}

	//  0  1  center
	//  2  3  c1
	//  4  5  c2
	//  6  7  c3
	//  8  9  c4
	// 10 11  c1
	$bcde = array(
		$float[2] , $float[3] ,
		$float[4] , $float[5] ,
		$float[6] , $float[7] ,
		$float[8] , $float[9] ,
	);
	return $bcde;
}
//////////////////////////////
function sectspr( &$json, &$mbs, $pfx )
{
	// s6-s4-s0/s1/s2 [18-c-18/30/30]
	$len6 = strlen( $mbs[6]['d'] );
	for ( $i6=0; $i6 < $len6; $i6 += $mbs[6]['k'] )
	{
		// 0 4 8 c  10 11  12 13  14  15 16 17
		// - - - -  id     -  -   no  -  -  -
		$id6 = str2big($mbs[6]['d'], $i6+0x10, 2);
		$no6 = str2big($mbs[6]['d'], $i6+0x14, 1);
		// DO NOT skip numbering
		// JSON will become {object} instead [array]

		$k6 = $i6 / $mbs[6]['k'];
		$data = array();
		for ( $i4=0; $i4 < $no6; $i4++ )
		{
			$p4 = ($id6 + $i4) * $mbs[4]['k'];

			// 0 1 2 3  4 5  6 7  8 9  a b
			// sub      s1   - -  s0   s2
			$sub = substr ($mbs[4]['d'], $p4+ 0, 4);

			$s1  = str2big($mbs[4]['d'], $p4+ 4, 2); // sx,sy
			$s0  = str2big($mbs[4]['d'], $p4+ 8, 2);
			$s2  = str2big($mbs[4]['d'], $p4+10, 2); // dx,dy

			$sqd = sectquad ($mbs[1], $s1*$mbs[1]['k']);
			$cqd = colorquad($mbs[0], $s0*$mbs[0]['k']);
			$dqd = sectquad ($mbs[2], $s2*$mbs[2]['k']);

			$s1 = str2big($sub, 0, 2); // ?type?
			$s3 = ord( $sub[2] ); // mask
			$s4 = ord( $sub[3] ); // tid

			$data[$i4] = array();
			if ( $s1 & 2 )
				continue;

			$data[$i4]['DstQuad'] = $dqd;
			if ( ! empty($cqd) )
				$data[$i4]['ClrQuad']  = $cqd;

			//  1 layer normal
			//  2 layer top
			//  4 gradientFill
			//  8 attack box
			// 10
			// 20
			if ( ($s1 & 4) == 0 )
			{
				$data[$i4]['TexID']   = $s4;
				$data[$i4]['SrcQuad'] = $sqd;
			}
			quad_convexfix($data[$i4]);

	/*
			switch ( $s3 )
			{
				case 1:
					$data[$i4]['Blend'] = array('SUB', 1);
					break;
				case 2:
					$data[$i4]['Blend'] = array('ADD', 1);
					break;
				default: // 0
					//$data[$i4]['Blend'] = array('NORMAL', 1);
					break;
			} // switch ( $s3 )
	*/

		} // for ( $i4=0; $i4 < $no6; $i4++ )

		$json['Frame'][$k6] = $data;
	} // for ( $i6=0; $i6 < $len6; $i6 += $mbs[6]['k'] )
	return;
}
//////////////////////////////
function sectanim( &$json, &$mbs, $pfx )
{
	// s9-sa-s8 [30-10-20]
	$len9 = strlen( $mbs[9]['d'] );
	for ( $i9=0; $i9 < $len9; $i9 += $mbs[9]['k'] )
	{
		// 0 4 8 c  10    28 29  2a  2b 2c 2d 2e 2f
		// - - - -  name  id     no  -  -  -  -  -
		$name = substr0($mbs[9]['d'], $i9+0x10);
		$id9  = str2big($mbs[9]['d'], $i9+0x28, 2);
		$no9  = str2big($mbs[9]['d'], $i9+0x2a, 1);

		for ( $ia=0; $ia < $no9; $ia++ )
		{
			$pa = ($id9 + $ia) * $mbs[10]['k'];

			// 0 1  2 3  4 5 6 7  8 9 a b  c d e f
			// id   no   sum      -        -
			$ida = str2big($mbs[10]['d'], $pa+0, 2);
			$noa = str2big($mbs[10]['d'], $pa+2, 2);

			$ent = array(
				'FID' => array(),
				'POS' => array(),
				'FPS' => array(),
			);
			$is_mov = false;
			for ( $i8=0; $i8 < $noa; $i8++ )
			{
				$p8 = ($ida + $i8) * $mbs[8]['k'];

				// 0   2  4    6   8 c 10 14 18 1c
				// id  -  pos  no  - - -  -  -  -
				$id8 = str2big($mbs[8]['d'], $p8+0, 2);
				$id7 = str2big($mbs[8]['d'], $p8+4, 2);
				$no8 = str2big($mbs[8]['d'], $p8+6, 2);

				$p7 = $id7 * $mbs[7]['k'];
				$x7 = float32( substrrev($mbs[7]['d'], $p7+0, 4) );
				$y7 = float32( substrrev($mbs[7]['d'], $p7+4, 4) );

				$ent['FID'][] = $id8;
				$ent['FPS'][] = $no8;
				$ent['POS'][] = array($x7,$y7);

				if ( $x7 != 0 || $y7 != 0 )
					$is_mov = true;
			} // for ( $i8=0; $i8 < $noa; $i8++ )

			// skip all zero Pos
			if ( ! $is_mov )
				unset( $ent['POS'] );
			$json['Animation'][$name][$ia] = $ent;
		} // for ( $ia=0; $ia < $no9; $ia++ )

	} // for ( $i9=0; $i9 < $len9; $i9 += $mbs[9]['k'] )

	return;
}
//////////////////////////////
function sect_addoff( &$file, &$sect )
{
	foreach ( $sect as $k => $v )
	{
		if ( ! isset($v['p']) )
			continue;
		$off = str2big($file, $v['p'], 4);
		if ( $off !== 0 )
			$sect[$k]['o'] = $off;
	}
	return;
}

function mura( $fname )
{
	$mbs = load_file($fname);
	if ( empty($mbs) )  return;

	if ( substr($mbs,0,4) !== 'FMBS' )
		return;

	if ( str2int($mbs, 8, 4) != 0xa0 )
		return printf("DIFF not 0xa0  %s\n", $fname);

	// $siz = str2int($mbs, 4, 3);
	// $hdz = str2int($mbs, 8, 3);
	// $len = 0x10 + $hdz + $siz;
	$pfx = substr($fname, 0, strrpos($fname, '.'));

	//   0 1 2 |     1-0 2-1 3-2
	// 3 4 5 6 | 6-3 5-4 9-5 7-6
	// 7 8 9 a | 8-7 4-8 a-9 s-a
	// dummy_npc.mbs
	//        a0  b8  e8 |      1*18 1*30 1*30
	//   118 244 250 168 | 1*50 1*c  1*8  1*18
	//   180 1a4 258 348 | 1*24 5*20 5*30 5*10
	// momohime_battle_drm.mbs
	//            a0   6b8  2cc8 |        41*18 cb*30 7bd*30
	//   1a038 26484 31a7c 1a7b8 | 18*50 f2a*c  f2*8  127*18
	//   1c360 1c5c4 3220c 32e3c | 11*24 4f6*20 41*30  87*10
	// s9[+28] =  85+2 => sa
	// sa[+ 0] = 4ee+8 => s8
	// s8[+ 0] = 126   => s6
	// s6[+10] = f29+1 => s4
	// s4[+ 4] =  ca   => s1 , [+ 8] = 40 => s0 , [+ a] = 7bc => s2
	// s1
	// s0
	// s2
	$sect = array(
		array('p' => 0x54 , 'k' => 0x18), // 0
		array('p' => 0x58 , 'k' => 0x30), // 1
		array('p' => 0x5c , 'k' => 0x30), // 2 dummy_npc=0
		array('p' => 0x60 , 'k' => 0x50), // 3 bg=0
		array('p' => 0x64 , 'k' => 0xc ), // 4
		array('p' => 0x68 , 'k' => 0x8 ), // 5 bg=0
		array('p' => 0x6c , 'k' => 0x18), // 6
		array('p' => 0x70 , 'k' => 0x24), // 7
		array('p' => 0x74 , 'k' => 0x20), // 8
		array('p' => 0x78 , 'k' => 0x30), // 9
		array('p' => 0x7c , 'k' => 0x10), // 10
		array('o' => strrpos($mbs, "FEOC")),
	);
	sect_addoff($mbs, $sect);
	load_sect($mbs, $sect);
	save_sect($mbs, "$pfx/meta");

	$json = load_idtagfile('wii_mura');

	sectanim($json, $mbs, $pfx);
	sectspr ($json, $mbs, $pfx);

	save_quadfile($pfx, $json);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	mura( $argv[$i] );

/*
mbs 4-01 valids
	0 1 2 3 4 5 7 9 11 13 15 29 2d
mbs 4-2 valids
	0 1 2
 */
