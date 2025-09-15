<?php
    function getDBConnection($db) {
        static $conn;
        if ($conn === null) {
            $conn = new mysqli("localhost", "root", "", $db);
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