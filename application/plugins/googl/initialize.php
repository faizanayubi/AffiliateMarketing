<?php

// initialize Googl
include("googl.php");

$googl = new googl();

Framework\Registry::set("googl", $googl);
