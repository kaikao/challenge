#!/usr/bin/perl;
use strict;
use warnings;
use Math::Complex;
use POSIX;

die "Please provide a num which is greater than 0.\n" if not defined $ARGV[0] or $ARGV[0] < 1;

my $rank = $ARGV[0];
my $num = 2;
my $primeIndex = 0;

while (1) {
    # check is prime
    my $isPrime = 1;
    for (my $i=2; $i<=ceil(sqrt($num)); $i++) {
        if ($num == 2) {
            last;
        }

        if ($num % $i == 0) {
            $isPrime = 0;
            last;
        }
    }

    if ($isPrime) {
        $primeIndex++;
    }

    # break if already reach the number user given
    last if $primeIndex == $rank;

    # increase
    $num++;
}

print "#" . $rank . " prime number is " . $num . "\n";


