#!/bin/bash
./vendor/bin/phpdoc \
  -d ./controllers,./models,./views,./core \
  -t ./doc
  
