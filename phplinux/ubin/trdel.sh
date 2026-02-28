#!/bin/bash
#
echo "file="$1
file=$1
cp $file $file.orig
tr -d "\r" < $file > $file.new
rm $file
mv $file.new $file
sudo chmod +x $file
echo "change file="$file
