<?php
/**
 * @file
 * Provide LDAP-like filters to FileZ.
 *
 * Filters handled by this class are expressions given in a syntax close
 * to LDAP filters.
 *
 * As in LDAP, the criteria are expressed as "attribute=expression" put in
 * parenthesis. The general syntax is the same as in LDAP and the logical
 * operators ("&", "|", "!") work the same way. The main difference with LDAP is
 * that the expression is a non-achored PERL regular expressions instead
 * of a Unix shell-like wildcard.
 *
 * Filters are applied to associative arrays such as $_SERVER. The value of the
 * attribute in a filter is the value of the corresponding index in
 * the associative array.
 *
 * Examples :
 * (REMOTE_ADDR=^192\.168\.1\.$)
 *    If applied to $_SERVER, it evaluates to true if the connexion has
 *    been issued from the network 192.168.1.0/24
 *
 * (|(HTTP_HOST=domain1\.org$)(HTTP_HOST=domain2\.fr$))
 *    If applied to $_SERVER, it evaluates to true if the connexion has
 *    been issued from a host belonging to domains domain1.org or
 *    domain2.org or one of their subdomains.
 *
 * @author Roland.Dirlewanger@cnrs.fr, February 2013.
 * @package FileZ
 */

class Fz_Filter {
    /**
     * The private attributes
     */
    private $parsed = null;

    /**
     * The constructor
     */
    public function  __construct($filter) {
	// If the filter is empty, don't do anything
	if (empty($filter)) {
	    return;
	}

        // Break up the filter into lexical tokens
	$tokens = preg_split("/([()|&!])/", $filter , 0, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);

        // Parse the tokens
	$this->parsed = $this->parse($tokens, 0, count($tokens)-1);
    }


    /**
     * Apply the filter to an array.
     *
     * Returns true if the filter evaluates to true
     *
     * @param array $array        Associated array the filter is applied to
     * @return boolean
     */
    public function applyTo(&$array) {
	// If the filter is empty, return true
	if ($this->parsed === null) {
	    return true;
	}

	// evaluate the filter
	$result = $this->evaluate($this->parsed, $array);
	return $result;
    }

    /**
     * Parse a list of tokens.
     *
     * @return array The parse tree associated with the list of tokens
     */
    protected function parse(&$tok, $first, $last) {
	static $logical_operators = array("&", "|", "!");
	static $comparaison_operators = array("=");

	$result = array();
	$filter = join(" ", array_slice($tok, $first, $last - $first + 1));

	// Check if it's a single token
	if ($first == $last) {
	    $regexp = join("|", $comparaison_operators);
	    $regexp = "/^([-_a-zA-Z0_9]+)($regexp)(.*)$/";
	    if (! preg_match($regexp, $tok[$first], $match)) {
		throw new Exception("filter: illegal criterion, in $filter");
	    }
	    return array($match[2], $match[1], $match[3]);
	}

	// Otherwise, it should start with a "(" and end with the matching ")"
	if ($this->filterFindOpenPar($tok, $first, $last) != $first) {
	    throw new Exception("filter: \"(\" expected, in $filter");
	}
	$lastpar = $this->filterFindClosePar($tok, $first, $last);
	if ($lastpar == -1) {
	    throw new Exception("filter: missing \")\", in $filter");
	}
	if ($lastpar != $last) {
	    $failtok = $tok[$lastpar+1];
	    throw new Exception("filter: unexpected token \"$failtok\", in $filter");
	}

	// Look inside the (...)
	$ft = $tok[$first+1];

	// Is it one of the logical operators ?
	if (in_array($ft, $logical_operators)) {
	    $result[] = $ft;
	    for ($i = $first+2, $j = $last-1; $i <= $last-1;) {
		$filter = join(" ", array_slice($tok, $i, $last - $i));
		if ($this->filterFindOpenPar($tok, $i, $last) != $i) {
		    throw new Exception("filter: \"(\" expected, in $filter");
		}
		if (($j = $this->filterFindClosePar($tok, $i, $last)) == -1) {
		    throw new Exception("filter: unexpected data after last \")\", in $filter");
		}
		$result[] = $this->parse($tok, $i, $j);
		$i = $j + 1;
	    }

	    // check the arity of the operator
	    if ($result[0] == "!" and count($result) > 2) {
		throw new Exception("filter: operator \"!\" takes only one argument, in $filter");
	    }
	    return $result;
	}

	// Not a logical opération. Parse recursively
	return $this->parse($tok, $first+1, $last-1);
    }

    /**
     * Evaluate the parse tree.
     *
     * @return boolean
     */
    private function evaluate($tree, &$array) {
	switch($tree[0]) {
	case "&":
	    // AND : all subtrees must evaluate to true
	    for($i = 1; $i < count($tree); ++$i) {
		if (! $this->evaluate($tree[$i], $array)) {
		    return false;
		}
	    }
	    return true;

	case "|":
	    // OR: at least one subtree must evaluate to true
	    for($i = 1; $i < count($tree); ++$i) {
		if ($this->evaluate($tree[$i], $array)) {
		    return true;
		}
	    }
	    return false;

	case "!":
	    // NOT: the subtree must evaluate to false
	    return !$this->evaluate($tree[1], $array);

	default:
	    // it's a criterion
	    return $this->evaluateCriterion($tree[0], $tree[1], $tree[2], $array);
	}
    }

    /**
     * Index of the first open parenthesis.
     *
     * Searches the given list of lexical tokens from index $first to $last
     * included and returns the index of the first occurence of an open
     * parenthesis. If none could be found, returns -1
     *Importing Directory Data
     * @param array $tok     List of loxical tokens
     * @param int $first     Index of the token to start the search with
     * @param int $last      Index of the token to end the search with
     * @return int           Index of the first "(" or -1 if there is none
     */
    private function filterFindOpenPar(&$tok, $first, $last) {
	for ($i = $first; $i <= $last; ++$i) {
	    if ($tok[$i]== "(") {
		return $i;
	    }
	}
	return -1;
    }

    /**
     * Index of the last open parenthesis.
     *
     * Searches the given list of lexical tokens from index $first to $last
     * included and returns the index of the closing parenthesis witch
     * matches the first opening parenthesis. If none could be found,
     * returns -1.
     *
     * @param array $tok     List of loxical tokens
     * @param int $first     Index of the token to start the search with
     * @param int $last      Index of the token to end the search with
     * @return int           Index of the closing ")" or -1 if there is none
     */
    private function filterFindClosePar(&$tok, $first, $last) {
	$count = 0;
	for ($i = $first; $i <= $last; ++$i) {
	    if ($tok[$i] == "(") {
		++$count;
	    }
	    if ($tok[$i] == ")" and --$count == 0) {
		return $i;
	    }
	}
	return -1;
    }

    /**
     * Evaluate a criterion.
     *
     * @param string $operator    The operator (currently, only = is supported)
     * @param string $attribute   Name of the attribute to check
     * @param string $regexp      PERL regular expression
     *
     * @return boolean            Returns true if the value of $_SERVER['attribute']
     *                            matches the regular expression
     */
    private function evaluateCriterion($operator, $attribute, $regexp, &$array) {
	$criterion = "$attribute$operator$regexp";
	if (empty($attribute) || empty($regexp)) {
	    throw new Exception("filter: illegal criterion \"$criterion\"");
	}
	if (! array_key_exists($attribute, $array)) {
	    fz_log("filter: no value for attribute \"$attribute\" in \"$criterion\"");
	    return false;
	}
	$value = $array[$attribute];
	$regexp = str_replace("/", "\/", $regexp);
	$regexp = "/$regexp/i";
	$return = preg_match($regexp, $value);
	fz_log("filter: checking \"$criterion\" with value \"$value\" returns $return");
	return $return;
    }
  }
?>
