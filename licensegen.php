<?php

/*
* Copyright(C) 2017, Jack Manbeck
* 
* Licensed under the Apache License, Version 2.0 (the "License");
* you may not use this file except in compliance with the License.
* You may obtain a copy of the License at
* 
*     http://www.apache.org/licenses/LICENSE-2.0
* 
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and  
* limitations under the License.
*/

/*
*	Program
*		Licensegen  
*
*	Version
*		1.0
*
*	Description
*		This program will auto-generate test files for the license detection test file suite.
*
*		See the readme for all the details.
*
*	Change Log
*
*	August 11, 2017	Jack Manbeck	Created
*
*/


/********************************************************************************************
* Constants																					*
********************************************************************************************/
/* EOL for the OS were running in -- Linux is \n and Windows is \r\n -- maybe set programatically ? */
define ("EOL" , "\r\n");

/* These values are used for debugging and logging */
define ("LOG_DIR" , "./");					/* Directory where file is kept */
define ("LOG_FILENAME" , "autogen.log");	/* Log file name				*/
define ("LOG_MAX_SIZE" , "50000000");		/* Max size of the log is 50Mb - this is not currently used	*/

/* Max length of a line in our test file we create so we can wrap around really long strings */
define ("MAXLINELNGTH" , 70);


/********************************************************************************************
* Globals																					*
********************************************************************************************/

/* template files directory path */
$sTemplateFilePath = "";

/* Full name of template files with path e.g. templates/header.txt */
$sHeaderTemplateFileName = "";
$sLicenseTemplateFileName = "";
$sCodeTemplateFileName = "";

/* test file version number */
$sTestFileVersion = "";

/* JSON file path */
$sJsonFilePath = "";

/* Output path for generated test files */
$sOutputFilePath = "";

/* Silent mode. True we don't write to the console, false we do */
$bSilentMode;

/* If true, write spdx license identifier to the outputted test file */
$bLicenseIdentifier;

/* If true we write to the log file */
$bLogData;


/****************************************************************************************
* Functions 																			*
****************************************************************************************/



/*  
* Function: 	writeLogFile(string $sLogData)
* Description:  If $bLogData is true, write logging data to LOG_FILENAME. An EOL is added by the function.
* Returns: 		nothing
*/

function writeLogFile ($sLogData) {
	global $bLogData;
	if ($bLogData) {
		file_put_contents(LOG_FILENAME,  date('h:i:s A') . ": " . $sLogData . EOL, FILE_APPEND | LOCK_EX);
	}
}

/* 
* Function: 	writeConsole(string $sStatement)
* Description:  Write the statement to the console. No EOL is added by the function.
* Returns: 		nothing
*/
function writeConsole ($sStatement) {
	global $bSilentMode;
	if (!$bSilentMode) {
		echo $sStatement;
	}
}

/*  
* Function: 	fatal(string $sErrorText)
* Description:  Logs a fatal error to the console and log file and exits with an error of 1.
* Returns: 		nothing
*/
function fatal($sErrorText) {
	writeLogFile ($sErrorText);
	writeConsole ($sErrorText);
	exit (1);
}
	
/* 
* Function: 	printUsage(void)
* Description:  Prints the usage statement for this program to the command line.
* Returns: 		nothing
*/
function printUsage () {
    echo "This program will create a set of test files for license detection using the JSON files which are part of " . EOL;
	echo "the SPDX license list and kept at: https://github.com/spdx/license-list-data " . EOL;
	echo "The JSON files must be local (readable) from the machine running this program. " . EOL;
	echo EOL;
	echo "usage:" . EOL;
	echo "h      - Displays this help and usage and then exits" . EOL;
	echo "s      - Optional. Silent mode, no command line output." . EOL;
	echo "i      - Optional. Write the spdx license identifier to the test file." . EOL;
	echo "l      - Optional. Enables logging to autogen.log. File is local." . EOL;
	echo "v=vers - Optional." . EOL . "        Version to put in the generated test files." . EOL . "        Defaults to Unofficial version." . EOL;  
	echo "t=directory/ - Optional." . EOL . "        Directory containing the templates used to create the test files. " . EOL . "        Defaults to local template directory." . EOL;
	echo "j=directory/ - Optional." . EOL . "        Directory containing the JSON license files  used to create license text for the test files."  . EOL . "        Defaults to local json directory." . EOL; 
	echo "o=directory/ - Optional." . EOL . "        Directory to output the created test files."  . EOL .  "        Defaults to local output directory." . EOL; 
	echo EOL;
	echo "e.g. php licensegen.php -v=1.0" . EOL;
}


/* 
* Function: 	parseOptions(associative array $options)
* Description:  Parses the associative array options for command line arguments.
* Returns: 		nothing
*/
function parseOptions($options) {
	global $sTemplateFilePath;
	global $sHeaderTemplateFileName;
	global $sLicenseTemplateFileName;
	global $sCodeTemplateFileName;
	global $sTestFileVersion;
	global $sJsonFilePath;
	global $sOutputFilePath;
	global $bSilentMode;
	global $bLicenseIdentifier;
	global $bLogData;
	
	/* Set default values */			
	$sHeaderTemplateFileName = "templates/" . "header.txt";
	$sLicenseTemplateFileName = "templates/" . "license.txt";
	$sCodeTemplateFileName = "templates/" . "code.txt";	
	$sJsonFilePath = "json/";
	$sOutputFilePath = "output/";
	$bSilentMode = false;	
	$bLicenseIdentifier = false;
	$bNoOutputDirSpecified = true;
	$sTestFileVersion = "Unofficial version";
	$bLogData = false;
	
	/* getopts is a little weird with optional no value entries. If the value is not specified, it doesn't exist in the array. 
	   if it is, then it exists but has a false value.  */
	 
	foreach (array_keys($options) as $opt) switch ($opt) {
		case 'h': /* print help and exit */
			printUsage();
			exit(0); 
			break;
		
		case 's': /* silent mode */
			$bSilentMode = true;
			writeLogFile ("Silent mode set.");
			break;

		case 'i': /* write license identifier to the test file */
			$bLicenseIdentifier = true;
			writeLogFile ("Write license identifiers to the test files.");
			break;

		case 'l': /* write to the log file */
			$bLogData = true;
			writeLogFile ("Logging was enabled.");
			break;
			
		case 'v':  /* get the test file version to use */
			$sTestFileVersion = $options["v"];
			writeLogFile ("version supplied via -v: " . $sTestFileVersion);
			break;
		
		case 't': /* template directory was specified */
			$sTemplateFilePath = $options["t"];
			if ($sTemplateFilePath) {
				writeLogFile ("template path supplied via -t: " . $sTemplateFilePath);
				if (is_dir($sTemplateFilePath)) {
					$sHeaderTemplateFileName = $sTemplateFilePath . "header.txt";
					$sLicenseTemplateFileName = $sTemplateFilePath . "license.txt";
					$sCodeTemplateFileName = $sTemplateFilePath . "code.txt";
				}
				else {
					fatal( $sTemplateFilePath . " is not a directory.");	
				}
			}
			else {
				fatal( "You must specify a directory with -t. See usage -h.");	
			}
			break;
	
		case 'j': /* json directory path specified */
			$sJsonFilePath = $options["j"];
			if ($sJsonFilePath) {
				if (!is_dir($sJsonFilePath)) {
					fatal( $sJsonFilePath . " is not a directory. ");	
				}
			}
			else {
				fatal( "You must specify a directory with -j. See usage -h.");	
			}
			break;
	
		case 'o': /* output directory specified */
			$sOutputFilePath = $options["o"];
			$bNoOutputDirSpecified = false;
			if ($sOutputFilePath) {
				writeLogFile ("output path supplied via -o: " . $sOutputFilePath);
				if (!is_dir($sOutputFilePath)) {
					/* create directory specified if it doesn't exist */
					if (!mkdir($sOutputFilePath, 0777)) {
						fatal("unable to create local output directory.");
					}
				}
			}
			else {
				fatal( "You must specify a directory with -o. See usage -h.");	
			}
			break;
			
		default:
			fatal( "Unknown argument " . $opt . ". See help -h.");	
		
	}
	
	/* Final error checks and set up */
	
	/* Check to be sure the template files are there */
	if (file_exists ($sHeaderTemplateFileName) && file_exists ($sCodeTemplateFileName) && file_exists ($sLicenseTemplateFileName)) {
		writeLogFile ("Using header template file: " . $sHeaderTemplateFileName);
		writeLogFile ("Using license template file: " . $sLicenseTemplateFileName);
		writeLogFile ("Using code template file: " . $sCodeTemplateFileName);
	}
	else {
		fatal("Unable to find the template files. Be sure they exist in the directory specified or in templates/.");
	}

	/* check for json files */
	if (glob($sJsonFilePath . "*.json")) {
		writeLogFile ("json files located at: " . $sJsonFilePath);
	}
	else {
		fatal ("No json license files *.json found in the specified directory.");
	}

	/* create a default output directory if one was not specified  */
	if ($bNoOutputDirSpecified) {
		if (!is_dir("output")) {
			if (!mkdir("output", 0777)) {
				fatal("unable to create local output directory.");
			}
		}
	}
	
	writeLogFile ("output directory for test files: " . $sOutputFilePath);
				
}

/****************************************************************************************
* Local variables																		*
****************************************************************************************/

/* Holds contents of the header template */
$sHeaderTemplate = "";

/* Holds contents of the license template */
$sLicenseTemplate = "";

/* Holds contents of the code template */
$sCodeTemplate = "";

/* Number of license files processed */
$iFilesProcessed = 0;

/* Command Line options, short and long forms */
$shortopts  = "";
$shortopts .= "v:"; // test file version 
$shortopts .= "t:"; // directory containing templates  
$shortopts .= "j:"; // directory containing json files to process 
$shortopts .= "o:"; // directory to output created test files to
$shortopts .= "hsil"; // No value. 
					 //  h - Prints help and exits.
					 //  s - Silent mode. Nothing written to console but we still write the log file.
					 //  i - Use license identifier. If specified we will write the spdx license identifier to the test file as well.
					 //  l - Enable logging to autogen.log.


/* longopts need work - not used rigth now */
$longopts  = array(
    "version:",     
    "templatedir:",    
    "jsondir:", 
	"outputdir:",
);


/****************************************************************************************
* Main																					*
****************************************************************************************/


/*
** Gather arguments
*/

/* Read in the command line options to an associative array so we can access */
$options = "";
$options = getopt($shortopts);
//var_dump($options, true); 
parseOptions($options);


/*
** Here are the steps to auto generate the file.
**
** 1. Read in the header template file and set the version.
** 2. Read in the license template file.
** 3. Read in the code template file.
** For each license file to create:
** 	1. Read in the JSON file into an associative array so we can access the data using the json field names 
**     in the JSON file.
** 	2. Get the short identifier.
** 	3. Create the test file: "short identifier-good".c.
** 	4. Write the header template
**	5. If the json file has a standard license header write that with the licensed template, else use the license text.
**  6. Write the code template.
*/

writeConsole ("Autogen starting processing ...." . EOL);

/* read the header template and set the version - only need to do this once, not per file */
$s_tempStr = "";
$s_tempStr = file_get_contents ( $sHeaderTemplateFileName, false);
$sHeaderTemplate = sprintf ($s_tempStr, $sTestFileVersion);

/* Read the code template - only need to do this once, not per file */
$sCodeTemplate = file_get_contents ( $sCodeTemplateFileName, false);

/* Start per file processing of the json license files */

foreach (glob($sJsonFilePath . "*.json") as $sJsonLicenseFileName) {
	
	/* 
	** Read in the contents of the JSON license file 
	*/
	writeLogFile ("Reading JSON license file: " . $sJsonLicenseFileName);
	writeConsole ("Autogen processing " . $sJsonLicenseFileName . " ");
	$sJsonFileContents = "";
	$sJsonFileContents = file_get_contents($sJsonLicenseFileName);
	$aJsonData = json_decode($sJsonFileContents, true); /* decode the JSON into an associative array */
	//var_dump($aJsonData, true); 

	/* 
	** Create test file and write the header 
	*/
	writeLogFile ("short identifier: " . $aJsonData["licenseId"]);
	$sTestFileName = $sOutputFilePath . $aJsonData["licenseId"] . ".c";
	
	/* should destroy the contents of the file since we are not appending if it already exists  */
	file_put_contents($sTestFileName, $sHeaderTemplate . EOL,  LOCK_EX);

	/* 
	** Write the license template followed by the short identifier (optional) and either the standard license header or the license text 
	*/
	
	/* license header with version */
	$s_tempStr = "";
	$s_tempStr = file_get_contents ( $sLicenseTemplateFileName, false);
	$sLicenseTemplate = sprintf ($s_tempStr, "https://spdx.org/licenses/" . $aJsonData["licenseId"] . ".html");
	file_put_contents($sTestFileName, $sLicenseTemplate . EOL, FILE_APPEND | LOCK_EX);

	/* license id */
	if ($bLicenseIdentifier) {
		file_put_contents($sTestFileName, "/* SPDX-License-Identifier: " . $aJsonData["licenseId"] . " */" . EOL, FILE_APPEND | LOCK_EX);
	}
	
	/* license text */
	if (empty ($aJsonData["standardLicenseHeader"])){
		writeLogFile ("No standard header found use license text. ");
		/* Using word wrap to wrap the license text text(because its a long ass line) is like using a cleaver but good enough for now... */
		$s_tempStr = wordwrap ( $aJsonData["licenseText"], MAXLINELNGTH, EOL, false);
		file_put_contents($sTestFileName, "/*" . EOL . $s_tempStr . EOL . "*/" . EOL, FILE_APPEND | LOCK_EX);
	}
	else {
		writeLogFile ("Standard header found. ");
		/* Using word wrap to wrap the license text text(because its a long ass line) is like using a cleaver but good enough for now... */
		$s_tempStr = wordwrap ( $aJsonData["standardLicenseHeader"], MAXLINELNGTH, EOL, false);
		file_put_contents($sTestFileName, "/*" . EOL . $s_tempStr . EOL . "*/" . EOL, FILE_APPEND | LOCK_EX);
	}

	/* 
	** Write the code template 
	*/
	file_put_contents($sTestFileName, $sCodeTemplate . EOL, FILE_APPEND | LOCK_EX);

	writeConsole ("done. Test file  " . $sTestFileName . " created." . EOL);
	
	/* Keep track of the number of files processed */
	$iFilesProcessed++;
	
}

/* were done */
writeConsole ("Autogen complete. " . sprintf("%d",$iFilesProcessed) . " files processed. " . EOL);
exit (0);
?>