#!/bin/sh

export ANTIWORDHOME=/usr/local/antiword

$ANTIWORDHOME/antiword -m 8859-1.txt 2>$1.err $1 >$1.txt
