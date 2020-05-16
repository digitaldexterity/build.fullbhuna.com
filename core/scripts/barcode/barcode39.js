// Barcode 39 needs to have an area as follows:
// <div id="bcArea">&nbsp;</div>
// then call writeBC(value)
// the DIV will be populated


var character = new Object();
character['*'] = "bWbwBwBwb";
character['-'] = "bWbwbwBwB";
character['$'] = "bWbWbWbwb";
character['%'] = "bwbWbWbWb";
character[' '] = "bWBwbwBwb";
character['.'] = "BWbwbwBwb";
character['/'] = "bWbWbwbWb";
character['+'] = "bWbwbWbWb";
character['0'] = "bwbWBwBwb";
character['1'] = "BwbWbwbwB";
character['2'] = "bwBWbwbwB";
character['3'] = "BwBWbwbwb";
character['4'] = "bwbWBwbwB";
character['5'] = "BwbWBwbwb";
character['6'] = "bwBWBwbwb";
character['7'] = "bwbWbwBwB";
character['8'] = "BwbWbwBwb";
character['9'] = "bwBWbwBwb";
character['A'] = "BwbwbWbwB";
character['B'] = "bwBwbWbwB";
character['C'] = "BwBwbWbwb";
character['D'] = "bwbwBWbwB";
character['E'] = "BwbwBWbwb";
character['F'] = "bwBwBWbwb";
character['G'] = "bwbwbWBwB";
character['H'] = "BwbwbWBwb";
character['I'] = "bwBwbWBwb";
character['J'] = "bwbwBWBwb";
character['K'] = "BwbwbwbWB";
character['L'] = "bwBwbwbWB";
character['M'] = "BwBwbwbWb";
character['N'] = "bwbwBwbWB";
character['O'] = "BwbwBwbWb";
character['P'] = "bwBwBwbWb";
character['Q'] = "bwbwbwBWB";
character['R'] = "BwbwbwBWb";
character['S'] = "bwBwbwBWb";
character['T'] = "bwbwBwBWb";
character['U'] = "BWbwbwbwB";
character['V'] = "bWBwbwbwB";
character['W'] = "BWBwbwbwb";
character['X'] = "bWbwBwbwB";
character['Y'] = "BWbwBwbwb";
character['Z'] = "bWBwBwbwb";
function barcode39(s)
{
this._bcData = s;
}
barcode39.prototype.getBCSymbol = function(sym, nw, ww) {
var w = nw;
var clr = "black";
if (sym == "B") {
w = ww;
}
else if (sym == "b") {
// defaults good
}
else if (sym == "W") {
w = ww;
clr = "white";
}
else if (sym == "w") {
clr = "white";
}
return "<td style='width:"+w+"px;background-color:"+clr+"'></td>";
}
barcode39.prototype.getBC39 = function(c, nw, ww) {
var bcData = character[c]; 
if (bcData == null) { 
return "<td>Illegal Char ["+c+"]</td>";
}
var retSymbol = "";
for (var i = 0; i < bcData.length; ++i) {
var bar = bcData.substring(i, i+1); 
retSymbol += this.getBCSymbol(bar, nw, ww);
}
return retSymbol;
}
barcode39.prototype.getBarcode = function(h, nw, ww) {
var retBarcode = "";
if (h == null) { 
h = 40;
}
if (nw == null) { 
nw = 3;
}
if (ww == null) { 
ww = 6;
}
retBarcode += "<table cellpadding=0 cellspacing=0 style='height:"+h+"px'><tr style='height:"+h+"px'>";
retBarcode += this.getBC39("*", nw, ww);
retBarcode += this.getBCSymbol("w", nw, ww);
for (var i = 0; i < this._bcData.length; ++i) { 
retBarcode += this.getBC39(this._bcData.substring(i, i+1), nw, ww);
retBarcode += this.getBCSymbol("w", nw, ww);
}
retBarcode += this.getBC39("*", nw, ww);
retBarcode += "</tr></table>";
return retBarcode;
}
barcode39.prototype.write = function(h, nw, ww) {
document.write(this.getBarcode(h, nw, ww))
}

function writeBC(s)
{
var bc = new barcode39(s);
document.getElementById('bcArea').innerHTML = bc.getBarcode(50, 2, 8);
}
