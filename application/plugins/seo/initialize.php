<?php

// initialize seo
include("seo.php");

$seo = new SEO(array(
    "title" => "EarnBugs Affiliate Network",
    "keywords" => "arn money, facebook page monetization",
    "description" => "Monetize your platform through us, get paid with high cpm",
    "author" => "",
    "robots" => "INDEX,FOLLOW",
    "photo" => CDN . "img/logo.png"
));

Framework\Registry::set("seo", $seo);
