#!/usr/bin/perl
use strict;
use warnings;

sub calculate {
    my $sumOfSquare = 0;
    my $squareOfSum = 0;

    for (my $i=1; $i<=$_[0]; $i++) {
        $sumOfSquare += $i*$i;
        $squareOfSum += $i;
    }

    $squareOfSum *= $squareOfSum;

    return $squareOfSum - $sumOfSquare;
}

die "Please provide a number to calculate\n" if not defined $ARGV[0];

my $ans = calculate($ARGV[0]);

print "Ans: " . $ans . "\n";

