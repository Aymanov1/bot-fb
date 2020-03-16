#!/bin/sh
echo "Hello, "$USER".  This script will push the code to git repository using git flow"
git add .
git commit -m "changing behaviour of bot"
git push origin master 