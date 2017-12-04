<?php

echo json_encode($_SERVER + $_GET + $_POST + array(
        'REQUEST_BODY' => file_get_contents('php://input')
    )
);
