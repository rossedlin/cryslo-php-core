#!/usr/bin/env bash

composer install

phpunit tests/MathTest.php
phpunit tests/ScrambleTest.php
phpunit tests/ViewTest.php