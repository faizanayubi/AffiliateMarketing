<?php

// initialize seo
include("seo.php");

$seo = new SEO(array(
    "title" => "EarnBugs Affiliate Network",
    "keywords" => "arn money, facebook page monetization" ,
    "description" => "Monetize your platform through us, get paid with high cpm",
    "author" => "https://plus.google.com/107837531266258418226",
    "robots" => "INDEX,FOLLOW",
    "photo" => CDN . "images/logo.png"
));

Framework\Registry::set("seo", $seo);