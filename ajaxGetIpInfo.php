<?php
header('Content-Type: application/json');
$ret = json_decode(file_get_contents("http://ip-api.com/json/".$_GET["ip"]));
$ret->x = json_decode(file_get_contents("http://api.ipapi.com/".$_GET["ip"]."?access_key=68ae26e798c7aaef5446488d3ecd36ef&output=json&fields=country_name,city,zip,location.country_flag,security,connection"));

echo json_encode($ret);

/*
status: "success",
country: "United States",
countryCode: "US",
region: "CA",
regionName: "California",
city: "San Francisco",
zip: "94105",
lat: 37.7852,
lon: -122.3874,
timezone: "America/Los_Angeles",
isp: "Webpass Inc",
org: "",
as: "AS19165 Webpass Inc.",
query: "192.77.237.95"
}
*/

