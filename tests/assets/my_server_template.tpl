<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    http_response_code(201);
    header("Content-Type: text/plain");
    print "{{ body }}";
}
