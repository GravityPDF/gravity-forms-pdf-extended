/**
 * A JS-port of PHP's version_compare()
 *
 * This function is taken from the Locutus project which has an MIT license.
 * The MIT license and copyright notice is included
 *
 * https://github.com/kvz/locutus
 * https://github.com/kvz/locutus/blob/master/src/php/info/version_compare.js
 *
 * @param string v1 First version number
 * @param string v2 Second version number
 * @param string operator optional If the third optional operator argument is specified, test for a particular relationship.
 *                                 The possible operators are: <, lt, <=, le, >, gt, >=, ge, ==, =, eq, !=, <>, ne respectively.
 * @returns -1 if the first version is lower than the second, 0 if they are equal, and 1 if the second is lower.
 *          When using the optional operator argument, the function will return TRUE if the relationship is the one specified by the operator, FALSE otherwise.
 */

/*
 Copyright (c) 2007-2016 Kevin van Zonneveld (http://kvz.io)
 and Contributors (http://locutus.io/authors)

 Permission is hereby granted, free of charge, to any person obtaining a copy of
 this software and associated documentation files (the "Software"), to deal in
 the Software without restriction, including without limitation the rights to
 use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 of the Software, and to permit persons to whom the Software is furnished to do
 so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in all
 copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 SOFTWARE.
 */
export default (v1, v2, operator) => {
	// discuss at: http://locutus.io/php/version_compare/
	// original by: Philippe Jausions (http://pear.php.net/user/jausions)
	// original by: Aidan Lister (http://aidanlister.com/)
	// reimplemented by: Kankrelune (http://www.webfaktory.info/)
	// improved by: Brett Zamir (http://brett-zamir.me)
	// improved by: Scott Baker
	// improved by: Theriault (https://github.com/Theriault)
	// example 1: version_compare('8.2.5rc', '8.2.5a')
	// returns 1: 1
	// example 2: version_compare('8.2.50', '8.2.52', '<')
	// returns 2: true
	// example 3: version_compare('5.3.0-dev', '5.3.0')
	// returns 3: -1
	// example 4: version_compare('4.1.0.52','4.01.0.51')
	// returns 4: 1
	// Important: compare must be initialized at 0.
	var i
	var x
	var compare = 0

	// vm maps textual PHP versions to negatives so they're less than 0.
	// PHP currently defines these as CASE-SENSITIVE. It is important to
	// leave these as negatives so that they can come before numerical versions
	// and as if no letters were there to begin with.
	// (1alpha is < 1 and < 1.1 but > 1dev1)
	// If a non-numerical value can't be mapped to this table, it receives
	// -7 as its value.
	var vm = {
		'dev': -6,
			'alpha': -5,
			'a': -5,
			'beta': -4,
			'b': -4,
			'RC': -3,
			'rc': -3,
			'#': -2,
			'p': 1,
			'pl': 1
	}

	// This function will be called to prepare each version argument.
	// It replaces every _, -, and + with a dot.
	// It surrounds any nonsequence of numbers/dots with dots.
	// It replaces sequences of dots with a single dot.
	// version_compare('4..0', '4.0') === 0
	// Important: A string of 0 length needs to be converted into a value
	// even less than an unexisting value in vm (-7), hence [-8].
	// It's also important to not strip spaces because of this.
	// version_compare('', ' ') === 1
	var _prepVersion = function _prepVersion (v) {
		v = ('' + v).replace( /[_\-+]/g, '.' )
		v = v.replace( /([^.\d]+)/g, '.$1.' ).replace( /\.{2,}/g, '.' )
		return ! v.length ? [-8] : v.split( '.' )
	}
	// This converts a version component to a number.
	// Empty component becomes 0.
	// Non-numerical component becomes a negative number.
	// Numerical component becomes itself as an integer.
	var _numVersion = function _numVersion (v) {
		return ! v ? 0 : isNaN( v ) ? vm[v] || -7 : parseInt( v, 10 )
	}

	v1 = _prepVersion( v1 )
	v2 = _prepVersion( v2 )
	x  = Math.max( v1.length, v2.length )
	for (i = 0; i < x; i++) {
		if (v1[i] === v2[i]) {
			continue
		}
		v1[i] = _numVersion( v1[i] )
		v2[i] = _numVersion( v2[i] )
		if (v1[i] < v2[i]) {
			compare = -1
			break
		} else if (v1[i] > v2[i]) {
			compare = 1
			break
		}
	}
	if ( ! operator) {
		return compare
	}

	// Important: operator is CASE-SENSITIVE.
	// "No operator" seems to be treated as "<."
	// Any other values seem to make the function return null.
	switch (operator) {
		case '>':
		case 'gt':
		return compare > 0
		case '>=':
		case 'ge':
		return compare >= 0
		case '<=':
		case 'le':
		return compare <= 0
		case '===':
		case '=':
		case 'eq':
		return compare === 0
		case '<>':
		case '!==':
		case 'ne':
		return compare !== 0
		case '':
		case '<':
		case 'lt':
		return compare < 0
		default:
		return null
	}
	}
