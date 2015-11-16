#!/usr/bin/perl
use strict;
use warnings;

my $bigNum = 20;
my $currentNumber = $bigNum;

while (1) {
    my $isOk = 1;
    for (my $i=2; $i<20; $i++) {
        if ($currentNumber % $i != 0) {
            $isOk = 0;
            last;
        }
    }

    if ($isOk) {
        last;
    }

    $currentNumber += 20;
}

print "Answer: " . $currentNumber . "\n";

