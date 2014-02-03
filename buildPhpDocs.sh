#!/bin/sh
set -ev

composer install

[ -d tmp ] && rm -fr tmp
git clone -b gh-pages git@github.com:${GITHUB_USER}/util-php tmp
./vendor/bin/phpdoc.php --directory src --target tmp/docs --template responsive-twig --title "DE PHP Utils"
cd tmp
git add .
git commit -m "Generate PHP docs"
git push origin gh-pages:gh-pages

cd ..
rm -fr tmp
