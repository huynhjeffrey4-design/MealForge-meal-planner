#!/bin/bash

composer install

git config --local core.hooksPath .githooks/
