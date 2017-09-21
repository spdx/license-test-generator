September 2017

Licensegen 
version 1.0
-------------------------------

1.0 Overview

	This program can be used to generate test files for license scanners. It is designed to take the SPDX JSON formatted 
	license list fragments and then generate "fake" C test files based on those. It will look at the JSON fragment for a 
	license and if there is a standard license header (as recognized by SPDX) then it will write that to the file. If 
	there is not one, then the license text is used. Each test file also includes:

	 a. A version (which is specified on the command line). We use a version for the test files generated and include the 
	    License List version as well.
	 b. A link back to the license list for the license it represents. 
	 c. Optionally, an SPDX License Identifier. There is an option to generate one.
	 d. A standard header and some "fake" non compilable C code.
	 
	 All test files are named using the convention "SPDX Short Identifier".c.
 
 
2.0 Installation

	This program was written in PHP.  I know it so it was trivial to write it. Feel free to re-write it in python or any 
	language of choice. It was written in and tested with PHP 7. I don't believe it uses features specific to 7 but can't 
	guarantee it.

	To run this program download PHP from here: http://php.net/ 

	You will also need the JSON license fragments from this SPDX repo: https://github.com/spdx/license-list-data

	This program:  https://github.com/spdx/license-test-generator 


	Once you those pieces:

	1. Install PHP and make sure it is working, i.e. try php -i.
	2. Create a directory and place this program in it.
	3. Get the JSON files for the license list. Place them in the JSON directory. (you can override this location as a 
	   command line option)
	4. Go to section 3.0 of this readme.


3.0 Running the Program and Command Line Options

	To run the program you should specify at a minimum the version for the test file suite. You need only do this if 
	generating an official test suite to post. If a version is not specified the program will use the version 
	"unofficial version".

	To run type in a console window (no version):

	php licensegen.php 

	or with a version:

	php licensegen.php -v="Version 1.0"

	By default all the test files go into a local Output sub-directory.
	
	At this time the program only accepts short options:

	h      - Displays this help and usage and then exits.
	s      - Optional. Silent mode, no command line output.
	i      - Optional. Write the spdx license identifier to the test file.
	l      - Optional. Enables logging to licensegen.log. File is local.
	v=vers - Optional. Version to put in the generated test files. Defaults to Unofficial version.
	t=directory/ - Optional. Directory containing the templates used to create the test files.  Defaults to local 
	               template directory.
	j=directory/ - Optional. Directory containing the JSON license files  used to create license text for the test files. 
					Defaults to local json directory.
	o=directory/ - Optional. Directory to output the created test files. Defaults to local output directory. 


	NOTE: ALL directories and files must be local to the machine running this script.


4.0 Known Issues

	1. License text may need some curating. I noticed some mark up in some of the text.


5.0 Future work

Possible things to do in thee future:

	1. It would be nice to access the JSON fragments directly from their GIT.
	2. Add long options.
	3. Possibly generate just text files with nothing but the license text.
	4. Remove license list markup form the license text.








