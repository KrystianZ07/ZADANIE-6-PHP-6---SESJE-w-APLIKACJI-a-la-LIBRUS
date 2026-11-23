<?php

$conn = mysqli_connect("localhost", "root", "", "szkola");

if (isset($_POST["zapisz_nowego"])) {

    $imie = $_POST["imie"];
    $nazwisko = $_POST["nazwisko"];
    $srednia_ocen = $_POST["srednia_ocen"];
    $id_klasy = $_POST["id_klasy"];

    $sql = "INSERT INTO uczen VALUES (NULL,'$nazwisko','$imie','$srednia_ocen','$id_klasy')";
    mysqli_query($conn, $sql);

    header("Location:index.php");
}
