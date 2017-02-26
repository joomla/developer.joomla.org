#!/bin/sh
rm -rf packaging && rm -f plg_content_nightlybuilds.zip && mkdir packaging
cp -r language/ packaging/language/
cp -r *.php packaging/
cp -r nightlybuilds.xml packaging/
cd packaging/
zip -r ../plg_content_nightlybuilds.zip *
