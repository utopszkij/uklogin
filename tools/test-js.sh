#!/bin/bash
#mocha js unittest by coverage report
node_modules/.bin/jscover js js-cov
mv js js-orig
mv js-cov js
node_modules/.bin/mocha -R mocha-lcov-reporter tests > tests/coverage-reports/mocha.lcov
rm -rf js
mv js-orig js
# a mocha.lcov -ban az filenév -ből hiányzik a js/
sed -i -e 's/SF:/SF:js\//g' ./tests/coverage-reports/mocha.lcov
node_modules/.bin/mocha tests











 