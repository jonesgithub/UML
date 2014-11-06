#!/usr/bin/env perl

use strict;
use warnings;
use utf8;
use 5.010;

use Getopt::Long;
use Storable qw(dclone);
use JSON;
use Data::Dumper;

sub temp_file_name {
	my $filename = shift;
	my @paths = split '/',$filename;
	my $filepath = $paths[$#paths];
	my @dots = split /\./,$filepath;
	if ($#dots > 0) {
		my $shift_name = "";
		for(my $i = 0; $i < $#paths; $i++){
			$shift_name .= $paths[$i];
			$shift_name .= "/";
		}
		for (my $i = 0; $i < $#dots; $i++) {
			$shift_name .= $dots[$i];
			$shift_name .= ".";
		}
		$shift_name .= "UMLtmp";
	} else {
		my $shift_name = "";
		for(my $i = 0; $i < $#paths - 1; $i++){
			$shift_name .= $paths[$i];
			$shift_name .= "/";
		}
		$shift_name .= "UMLtmp";
	}
}

my @all_file = ();
my $all_temp_file = "";
my $source_type;

GetOptions(
        'file|f=s{,}'=> \@all_file,
        's=s'        => \$source_type
);

die "Sorry, only support Java now!\n" if $source_type ne "java";

foreach (@all_file) {
	next if (! -f -e);
	
	open SOURCE_CODE , $_;
	my $temp_file_name = temp_file_name $_;
	open TEMP_FILE, ">$temp_file_name" or die "cannot open $temp_file_name\n";
	
	my %all_class;
	my %methods;
	my @private_vars;
	my @public_vars;
	my $class = "";
	
	while (<SOURCE_CODE>) {
		if (/(public[ +]|)class( +)(.*)( +)[\{]?/) {
			if (/[=!><'"\.]/) {
				next;
			}
			if ($class ne "") {
				my %h = ('methods'=>dclone(\%methods),'private_var'=>dclone(\@private_vars),'public_var'=>dclone(\@public_vars));
				$all_class{$class} = dclone(\%h);
			}
			$class = $3;
			%methods = ();
			@private_vars = ();
			@public_vars = ();
		} elsif (/(public( +)|private( +)){0,1}(static( +)){0,1}(\w+)( +)(\w+)( +)?\((.*)\)/) {
			if (/[=!><'"\.]/) {
				next;
			}
			my $type;
			if (!defined $1) {
				$type = "public ";
			}else {
				$type = $1;
			}
			$methods{$8} = "$type->$10";
		} elsif (/(public( +)|private( +)){1}(static( +)){0,1}(\w+)( +)(\w+)/) {
			push @private_vars, "$6 ->$8" if $1 eq "private ";
			push @public_vars, "$6 ->$8" if $1 eq "public ";
		} elsif (/(static( +)){1}(\w+)( +)(\w+)/) {
			push @public_vars, "$5 ->$3";
		}
	}
	my %h = ('methods'=>dclone(\%methods),'private_var'=>dclone(\@private_vars),'public_var'=>dclone(\@public_vars));
	$all_class{$class} = dclone(\%h);
	print TEMP_FILE to_json(\%all_class, {utf8 => 1, pretty => 1});
	close TEMP_FILE;
	close SOURCE_CODE;
	$all_temp_file .= $temp_file_name." ";
	
}
print `php ./UML.php $all_temp_file`;
system "rm $all_temp_file";
