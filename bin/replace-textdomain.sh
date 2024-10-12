#!/bin/bash

# Check if running on macOS or Linux
if [ "$(uname)" = "Darwin" ]; then
  # macOS (BSD sed)
  find ./inc/libs/tasty-framework/inc ./inc/libs/tasty-framework/templates -type f -name '*.php' -exec sed -i '' -e "s/, 'tasty'/, 'tasty-recipes-lite'/g" {} +
else
  # Linux (GNU sed)
  find ./inc/libs/tasty-framework/inc ./inc/libs/tasty-framework/templates -type f -name '*.php' -exec sed -i -e "s/, 'tasty'/, 'tasty-recipes-lite'/g" {} +
fi
