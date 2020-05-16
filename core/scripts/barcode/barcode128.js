/* call displayBarcode(div, val, checkdigit, h, w)

div - div wher eto put code
val - text for barcode
chedigit - unused just use 1
h - height of barcode
w - width of ONE full bar


*/
var imagePath = "/core/scripts/barcode/";

function code128B(toEncode)
 {
var charSet= new Array();
var encodedValue = '';
var startChar = '11010010000';
var stopChar = '1100011101011';
charSet[' '] = '11011001100';
charSet['!'] = '11001101100';
charSet['"'] = '11001100110';
charSet['#'] = '10010011000';
charSet['$'] = '10010001100';
charSet['%'] = '10001001100';
charSet['&'] = '10011001000';
charSet["'"] = '10011000100';
charSet['('] = '10001100100';
charSet[')'] = '11001001000';
charSet['*'] = '11001000100';
charSet['+'] = '11000100100';
charSet[','] = '10110011100';
charSet['-'] = '10011011100';
charSet['.'] = '10011001110';
charSet['/'] = '10111001100';
charSet['0'] = '10011101100';
charSet['1'] = '10011100110';
charSet['2'] = '11001110010';
charSet['3'] = '11001011100';
charSet['4'] = '11001001110';
charSet['5'] = '11011100100';
charSet['6'] = '11001110100';
charSet['7'] = '11101101110';
charSet['8'] = '11101001100';
charSet['9'] = '11100101100';
charSet[':'] = '11100100110';
charSet[';'] = '11101100100';
charSet['<'] = '11100110100';
charSet['='] = '11100110010';
charSet['>'] = '11011011000';
charSet['?'] = '11011000110';
charSet['@'] = '11000110110';
charSet['A'] = '10100011000';
charSet['B'] = '10001011000';
charSet['C'] = '10001000110';
charSet['D'] = '10110001000';
charSet['E'] = '10001101000';
charSet['F'] = '10001100010';
charSet['G'] = '11010001000';
charSet['H'] = '11000101000';
charSet['I'] = '11000100010';
charSet['J'] = '10110111000';
charSet['K'] = '10110001110';
charSet['L'] = '10001101110';
charSet['M'] = '10111011000';
charSet['N'] = '10111000110';
charSet['O'] = '10001110110';
charSet['P'] = '11101110110';
charSet['Q'] = '11010001110';
charSet['R'] = '11000101110';
charSet['S'] = '11011101000';
charSet['T'] = '11011100010';
charSet['U'] = '11011101110';
charSet['V'] = '11101011000';
charSet['W'] = '11101000110';
charSet['X'] = '11100010110';
charSet['Y'] = '11101101000';
charSet['Z'] = '11101100010';
charSet['['] = '11100011010';
charSet['\\'] = '11101111010';
charSet[']'] = '11001000010';
charSet['^'] = '11110001010';
charSet['_'] = '10100110000';
charSet['`'] = '11100001100';
charSet['a'] = '10010110000';
charSet['b'] = '10010000110';
charSet['c'] = '10000101100';
charSet['d'] = '10000100110';
charSet['e'] = '10110010000';
charSet['f'] = '10110000100';
charSet['g'] = '10011010000';
charSet['h'] = '10011000010';
charSet['i'] = '10000110100';
charSet['j'] = '10000110010';
charSet['k'] = '11000010010';
charSet['l'] = '11001010000';
charSet['m'] = '11110111010';
charSet['n'] = '11000010100';
charSet['o'] = '10001111010';
charSet['p'] = '10100111100';
charSet['q'] = '10010111100';
charSet['r'] = '10010011110';
charSet['s'] = '10111100100';
charSet['t'] = '10011110100';
charSet['u'] = '10011110010';
charSet['v'] = '11110100100';
charSet['w'] = '11110010100';
charSet['x'] = '11110010010';
charSet['y'] = '11011011110';
charSet['z'] = '11011110110';
charSet['{'] = '11110110110';
charSet['|'] = '10101111000';
charSet['}'] = '10100011110';
charSet['~'] = '10001011110';
charSet['95'] = '10111101000';
charSet['96'] = '10111100010';
charSet['97'] = '11110101000';
charSet['98'] = '11110100010';
charSet['99'] = '10111011110';
charSet['100']= '10111101110';
charSet['101']= '11101011110';
charSet['102']= '11110101110';
var checkDigit= 104;
for (var i = 0; i < toEncode.length; i++)
{
 var char = toEncode.substr(i, 1);
 var value = char.charCodeAt(0) - 32;
 if (value > 94){ return ''; } // invalid 128 B character
 checkDigit+= value * (i + 1);
 encodedValue += charSet[char];
}
checkDigit%= 103;
checkDigit= checkDigit > 94?
checkDigit:
String.fromCharCode(checkDigit + 32);
encodedValue = startChar + encodedValue + charSet[checkDigit] + stopChar;
return encodedValue;
 }


function displayBarcode(div, val, checkdigit, h, w) {
var val = code128B(val);
	var htm = '';
	var bit = 0;
	var gif = '';
	if (isNaN(h)){ h = 50; }
	if (isNaN(w)){ w = 1;}
	if (h < 50) { h = 50; }
	if (w < 1){ w = 1;}
	for (var i = 0; i < val.length; i++) {
	   bit= eval(val.substr(i, 1));
	   chars = 1;
 		if (i == val.length){ break; }
		 while (bit == eval(val.substr(i + 1, 1))) {
			chars++;
			i++;
			if (i == val.length){ break; }
 		}
 		gif = ((bit == 1) ? 'b.gif' : 'w.gif');
 		width= w * chars;
 		htm+= '<img src="' + imagePath + gif + '" style="height:' + h + 'px; width:' + width + 'px;">';
	}
	document.getElementById(div).innerHTML = htm;
}