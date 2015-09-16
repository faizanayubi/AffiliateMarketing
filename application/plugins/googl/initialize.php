<?php

// initialize Googl
include("Googl.php");

$googl = new Googl();

Framework\Registry::set("googl", $googl);
