<?php
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];   // blob.lukasokken.com
    $baseUrl = $protocol . $host . dirname($_SERVER['SCRIPT_NAME']);

    date_default_timezone_set("Europe/Oslo");
    function getDBConnection($db) {
        static $conn;
        if ($conn === null) {
            $conn = ($db == "blob_active" ?
                new mysqli("localhost", "root", "", "blob_active"):
                new mysqli("localhost", "root", "", "blob"));

                // Connections if on localhost
                // new mysqli("localhost", "root", "", "blob_active"):
                // new mysqli("localhost", "root", "", "blob"));
                
            if ($conn->connect_error) {
                die("Tilkoblingsfeil: " . $conn->connect_error);
            }
        }
        return $conn;
    }

    function timeAgo($dateString) {
        $diff = time() - strtotime($dateString);
        if ($diff < 0) return "In the future";

        $units = [
            31536000 => "y",
            2592000  => "m",
            86400    => "d",
            3600     => "h",
            60       => "m",
            1        => "s"
        ];

        foreach ($units as $seconds => $name) {
            if ($diff >= $seconds) {
                $value = floor($diff / $seconds);
                return $value . " {$name}";
            }
        }
    }