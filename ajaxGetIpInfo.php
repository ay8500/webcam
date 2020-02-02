<?php
header('Content-Type: application/json');
$ret = json_decode(file_get_contents("http://ip-api.com/json/".$_GET["ip"]));
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

