#!/usr/bin/perl

###################################################
### scan_privileges.pl							###
### Loop recursively through folders of php		###
### scripts and check for customer->can()		###
### references to find privilege definitions.	###
### A. Caravello 5/30/2022						###
###################################################

# Load Modules
use strict;
use FileHandle;

my ($path) = @ARGV;

scan_folders($path);

sub scan_folders {
	my $path = shift;

	#print "Scanning folder $path\n";

	my $dh;
	opendir($dh,$path) || die "Cannot open folder $path: $!\n";
	while (my $dir = readdir($dh)) {
		next if ($dir =~ /^\.{1,2}$/);
		next if ($dir =~ /^\.git/);

		if (-d "$path/$dir") {
			scan_folders("$path/$dir");
		}
		else {
			next unless ($dir =~ /\.php$/);

			#print "Scanning file $path/$dir\n";
			my $fh;
			open($fh,"$path/$dir") || die "Cannot open file $path/$dir: $!\n";
			my @records = <$fh>;
			close $fh;

			foreach my $record(@records) {
				if ($record =~ /can\([\'\"]([\w\-\_\s]+)[\'\"]\)/) {
					printf "%-30s\t%s\n",$1,"$path/$dir";
				}
				elsif ($record =~ /has\_role\([\'\"]([\w\-\.\_\s]+)[\'\"]\)/) {
					printf "--%-30s\t%s\n",$1,"$path/$dir";
				}
				elsif ($record =~ /role\([\'\"]([\w\-\.\_\s]+)[\'\"]\)/) {
					printf "--%-30s\t%s\n",$1,"$path/$dir";
				}
			}
		}
	}
}
