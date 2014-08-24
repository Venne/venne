<?php

system('cd ' . __DIR__ . ' && bower install');
system('cd ' . __DIR__ . ' && npm install');
system('cd ' . __DIR__ . ' && grunt');
