<?php

/*	This is the picosite line parser which finds 'picocalls' which are expressions within a
 *	line, and passes them onto function hooks declared within library.php.inc or PHP files
 *	within the directory library ending in .php.inc with their string argument.
 *
 *	It was written to keep parsing simple and is not completely efficient. It however was
 *	easier than using a template system or a tokenizer. Since the syntax within picosite is
 *	simple I decided to write my own uncomplicated way of parsing them without dragging
 *	in bloated dependencies or breaking picosite's ability run with very basic builds of PHP.
 *
 *	You do not need to touch this file to add your own 'picocalls' to your site as this code
 *	looks for methods by the name of _picocall_method_[callname] where callname including the
 *	brackets is replaced with the name of your picocall method. This method is required to take
 *	a single string argument. Hook methods must return a string to this code, which replaces
 *	the picocall in the line where it occured.
 *
 *	The syntax for these calls is %%[METHOD]:[VALUE]%% where METHOD and VALUE, including the
 *	brackets are replacd with the name of the method in the library. The METHOD part of the
 *	call must be in upper case. The method name in the actual hook however is lower case. For
 *	example if you were calling price method within library.php it the appropriate picocall
 *	would be %%PRICE:itemname%% which would call the hook with the argument 'itemname'
 *
 *	This code is passed an array of the lines intended to be parsed which it then breaks up as
 *	an array with an element on each line. Individual lines are then parsed one at a time in a
 *	rudimentary way. A line is checked for having at least two instances of the sigils within
 *	the syntax, ignoring lines which do not, and parses the lines that do contain enough sigils. 
 *
 *	Lines are broken up by their sigils (%%) into an array of strings, with a string for each
 *	part of a line that exists between the sigils. Each string in that resulting array is then
 *	filtered, where only strings containing the seperator ':' are kept as they are required
 *	within any picocall, with other items in the array ignored. The remaining elements within
 *	the array of sigil seperated strings are processed one at a time in a loop.
 *
 *	Within that loop each string being parsed is broken up itself by the seperator ':' into an
 *	array, where the first element is assigned as $method and the second as $value, but this is
 *	rudimentary. If a line has enough sigils to have a picocall, but outside those calls as
 *	general writing uses the ':' character then things will end up in the $method and $value
 *	variables despite not being picocalls.  Therefore a final step is done which I call the
 *	reconstruct filter, which is required because the strings being parsed don't contain any
 *	sigils or the seperator.
 *
 *	The reconstruct filter is passed the line of raw text being parsed for picocalls and it is
 *	also passed the values $method and $value assumed in the loop. Those values are used to
 *	reconstruct the picocall and its sigils as they should appear in the raw text. Then the raw
 *	text is checked for occurances of the reconstructed picocall which was parsed from from
 *	that text. In the case of junk that isn't a picocall the construction will wrap it in
 *	sigils and it won't exist that way in the text. In the case it isn't junk the function
 *	parserMethods is called and passed the method name and arguments.
 *
 *	The parserMethods(...) function then gets the method name from the parsed picocall and then
 *	checks if the hook function has been declared for that method and calls it. The result of
 *	the hook function 
 *
 */

function listArray($array) {
	print_r($array);
}

function parserMethods($method, $value) {
	$output = "";
	$valid = true;

//	if (preg_match('/[^a-z0-9]/i', $value) && preg_match('/[^a-z0-9]/i', $method))
//	$valid = true;

	$method = strtolower($method);

	if ($valid) {
		$function_hook = "_picocall_$method";
		if (function_exists($function_hook)) {
//			$output = $function_hook($value);
			$output = call_user_func($function_hook, $value);
		} else {
			$output = "[PICOCALL METHOD $method DOES NOT EXIST]";
		}
	} else {
		$output = "[INVALID SYNTAX IN PICOCALL]";
	}

	if (! is_string($output)) $output = "[PICOCALL DOES NOT RETURN STRING]";
	if ($output == "") $output = "[USELESS PICOCALL NO OUTPUT]";

	return $output;
}

function filterSigils($input) {
	$output = [];
	foreach($input as $sigil) {
		if (substr_count($sigil, ":") == 1) array_push($output, $sigil);
	}

	return $output;
}

function parserLineIn($input) {
	if (substr_count($input, "%%") >= 2) {
		$sigils = explode("%%", $input);
		
		$sigils = filterSigils($sigils);

		foreach($sigils as $sigil) {
			$instances = explode(":", $sigil);


			$method = $instances[0];
			$value = $instances[1];
			$reconstruct = "%%$method:$value%%";

			if (substr_count($input,$reconstruct) >= 1) {
				$input = str_replace($reconstruct, parserMethods($method, $value), $input);
			}
		}
	}

	return $input;
}

function parse($input) {
	$return_array = true;
	$output_string = "";

	if (! is_array($input)) {
		$input = explode("\n", $input);
		$return_array = false;
	}

	$output = [];
	foreach($input as $line) array_push($output, parserLineIn($line));

	if (! $return_array) {
		foreach($output as $line) $output_string = $output_string . "$line\n";
		return $output_string;
	} else {
		return $output;
	}
}
