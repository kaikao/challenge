#!/usr/bin/perl
use strict;
use warnings;
use Cwd            qw( abs_path );
use File::Basename qw( dirname );

# =================== subs ========================
# A=1, B=2, to Z=26.
sub getPositionOfA2Z {
    die "No parameter provided.\n" if not defined $_[0];

    my $char = $_[0];
    die "Only allow one character from A to Z.\n" if length $char > 1 || $char !~ /[A-Z]/;

    # ASCII: A=65
    return ord($char) - 64;
}

# =================== main ========================
# open file and get the first line of file
# my $filename = $ARGV[0] or die "Need to get file on the command line\n";
my $filename = dirname(abs_path($0)) . "/names.txt";
open my $file, '<', $filename or die "Error: Could not open file " . $filename . "\n";
my $namesStr = <$file>;
close $file;

# filter double quotes
$namesStr =~ s/"//g;

# split and sort by alphabetical order
my @names = sort split(/,/, $namesStr);

# calculate total name score
my $totalScore = 0;
for my $i (0 .. $#names) {
    # get name score
    my $nameScore = 0;
    foreach my $char (split "", $names[$i]) {
        $nameScore += getPositionOfA2Z($char);
    }

    # sum total score
    $totalScore += $nameScore * ($i+1);

    # print $names[$i] . ": " . $nameScore . " * " . ($i+1) . "\n";
}

print "Total names score is [" . $totalScore . "]\n";
