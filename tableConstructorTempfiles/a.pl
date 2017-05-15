#!/usr/bin/perl

open(RH, "a");
open(WH, ">c");

while(<RH>)
{
	print WH;
	s/` float/ Count` int/g;
	print WH;
}
