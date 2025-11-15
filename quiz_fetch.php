<?php
require 'db.php';
$id = intval($_GET['id']);
$res = $conn->query("SELECT * FROM quiz WHERE id=$id LIMIT 1");
if($res->num_rows){
    echo json_encode($res->fetch_assoc());
}else{
    echo json_encode(null);
}
