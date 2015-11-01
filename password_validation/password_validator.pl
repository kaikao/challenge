#!/usr/bin/perl
use strict;
use warnings;

# =================== subs ========================
sub isValidPassword {
    die "No password provided\n" if not defined $_[0];

    # get password and length
    my $password = $_[0];
    my $length = length $password;

    # required at least 8 characters.
    die "Error: Password must be at least 8 characters\n" if $length < 8;

    # 8~11, required mixed case letters, numbers and symbols
    if ($length < 12) {
        return 1 if $password =~ /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^a-zA-Z0-9]).{8,11}$/;
        die "Error: Password requires mixed case letters, numbers and symbols if length 8~11\n";
    }

    # 12~15, required mixed case letters and numbers.
    if ($length < 16) {
        return 1 if $password =~ /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{12,15}$/;
        die "Error: Password requires mixed case letters and numbers if length 12~15\n";
    }

    # 16~19, required mixed case letters.
    if ($length < 20) {
        return 1 if $password =~ /^(?=.*[a-z])(?=.*[A-Z]).{16,19}$/;
        die "Error: Password requires mixed case letters if length 16~19\n";
    }

    # >= 20, any character is ok.
    return 1;
}

# =================== main ========================
# check is define first argument
if (not defined $ARGV[0]) {
    die "Error: Need a password string to be validated.\n";
}

# validate
die "\"" . $ARGV[0] . "\" Pass!\n" if isValidPassword($ARGV[0]);
