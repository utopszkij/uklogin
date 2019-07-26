#!/bin/bash
./sonar/bin/sonar-scanner \
  -Dsonar.projectKey=utopszkij-uklogin \
  -Dsonar.organization=utopszkij-github \
  -Dsonar.sources=./controllers,./models,./views,./js \
  -Dsonar.host.url=https://sonarcloud.io \
  -Dsonar.php.coverage.reportPaths=./tests/coverage-reports/phpunit-clover.xml \
  -Dsonar.javascript.lcov.reportPaths=./tests/coverage-reports/mocha.lcov \
  -Dsonar.login=7ea93a426ed7ecccfa4e7b1401d88cc5f6a9a027;
  

  