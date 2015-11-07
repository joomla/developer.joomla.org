#!/bin/sh
rm -rf packaging/* && rm joomladata.zip
cp -r ../language/ packaging/language/
cp -r ../media/ packaging/media/
cp -r ../tmpl/ packaging/tmpl/
cp -r ../*.php packaging/
cp -r ../mod_joomladata.xml packaging/
cd packaging/
zip -r ../joomladata.zip language/ media/ tmpl/ *.php mod_joomladata.xml
