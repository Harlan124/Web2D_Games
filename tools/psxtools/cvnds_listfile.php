<?php
require "common.inc";
require "common-guest.inc";

$gp_patch = array();

//////////////////////////////
function sclist( &$list, &$ram, $st, $pfx )
{
	while (1)
	{
		$b1 = str2int($ram, $st+0, 3);
		$b2 = str2int($ram, $st+4, 3);
			$st += 8;
		if ( $b2 == 0 || $b1 == BIT24 )
			return;
		if ( $b2 == 3 )
			continue;

		if ( isset( $list[$b1] ) )
		{
			printf("%4x , %s , %s\n", $b1, $pfx, $list[$b1]);
			unset( $list[$b1] );
		}
		else
			printf("%4x , ***\n", $b1);
	} // while (1)
	return;
}

function sclist1( &$list, &$ram, $st, $pfx )
{
	while (1)
	{
		$b1 = str2int($ram, $st, 3);
			$st += 4;
		if ( $b1 == 0 || $b1 == BIT24 )
			return;
		sclist( $list, $ram, $b1, $pfx );
	} // while (1)
	return;
}

function sclist2( &$list, &$ram, $st, $pfx )
{
	while (1)
	{
		$b1 = str2int($ram, $st, 3);
			$st += 4;
		if ( $b1 == 0 || $b1 == BIT24 )
			return;
		sclist1( $list, $ram, $b1, $pfx );
	} // while (1)
	return;
}
//////////////////////////////
function listfile( &$file, $dir, $st, $ed, $bk )
{
	$list = array();
	$id = 0;
	while ( $st < $ed )
	{
		//$b1 = str2int($file, $st+0, 4);
		//$b2 = str2int($file, $st+4, 2);
		$b3 = substr0($file, $st+6);
		//$txt .= sprintf("%4x , %8x , %4x , %s\n", $id, $b1, $b2, $b3);
		$list[] = $b3;
		$st += $bk;
	}
	return $list;
}

function nds_game( &$ram, $dir, $game )
{
	foreach ( $game as $g )
	{
		if ( strpos($g, 'ov-') === false )
			continue;
		nds_overlay( $ram, $dir, $g );
	}
	return;
}

function cvnds( $dir )
{
	if ( ! is_dir($dir) )
		return;

	global $gp_patch;
	$gp_patch = nds_patch($dir, 'cvnds');
	if ( empty($gp_patch) )
		return;
	$ram = nds_ram($dir);
	nds_game($ram, $dir, $gp_patch['ndsram']['game']);

	arrayhex( $gp_patch['ndsram']['files'] );
	arrayhex( $gp_patch['ndsram']['stg_bc'] );
	arrayhex( $gp_patch['ndsram']['mon_sc'] );
	arrayhex( $gp_patch['ndsram']['obj_sc'] );

	list($st,$ed,$bk) = $gp_patch['ndsram']['files'];
	$list = listfile($ram, $dir, $st, $ed, $bk);

	if ( ! empty($gp_patch['ndsram']['stg_bc']) )
	{
		$st = $gp_patch['ndsram']['stg_bc'][0];
		if ( $gp_patch['ndsram']['game'][0] == 'dos' )
			sclist1($list, $ram, $st, 'stg_bc');
		else
			sclist2($list, $ram, $st, 'stg_bc');
		echo "\n";
	}

	if ( ! empty($gp_patch['ndsram']['mon_sc']) )
	{
		$st = $gp_patch['ndsram']['mon_sc'][0];
		sclist1($list, $ram, $st, 'mon_sc');
		echo "\n";
	}

	if ( ! empty($gp_patch['ndsram']['obj_sc']) )
	{
		$st = $gp_patch['ndsram']['obj_sc'][0];
		sclist1($list, $ram, $st, 'obj_sc');
		echo "\n";
	}

	printf("\n=== UNUSED [%d] ===\n", count($list));
	foreach ( $list as $lk => $lv )
		printf("%4x , %s\n", $lk, $lv);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	cvnds( $argv[$i] );
