<?php
session_start();
$baza = mysqli_connect("localhost", "root", "", "szkola");

$blad = "";

if (isset($_POST['zaloguj'])) {
    $l = trim($_POST['login']);
    $h = trim($_POST['haslo']);

    $q = "SELECT * FROM klasa WHERE nazwa = '$l' AND wychowawca = '$h'";
    $res = mysqli_query($baza, $q);

    if (mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_assoc($res);
        $_SESSION['login'] = $l;
        $_SESSION['wychowawca'] = $h;
        $_SESSION['klasa_id'] = $row['id'];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $blad = "ZŁE DANE LOGOWANIA";
    }
}

if (isset($_GET['wyloguj'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

if (isset($_GET['usun']) && isset($_SESSION['klasa_id'])) {
    $id_u = intval($_GET['usun']);
    $sprawdz = mysqli_query($baza, "SELECT id_klasy FROM uczen WHERE id = $id_u");
    $dane_u = mysqli_fetch_assoc($sprawdz);
    if ($dane_u['id_klasy'] == $_SESSION['klasa_id']) {
        mysqli_query($baza, "DELETE FROM uczen WHERE id = $id_u");
    }
    header("Location: " . $_SERVER['PHP_SELF'] . "?klasa=" . $_SESSION['klasa_id']);
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dziennik szkolny</title>
</head>
<body>

    <?php if (!isset($_SESSION['login'])): ?>

        <h2>Logowanie do systemu</h2>
        <?php if ($blad != "") echo "<p style='color:red;'>$blad</p>"; ?>
        <form method="post">
            Login: <br>
            <input type="text" name="login" required><br><br>
            Hasło: <br>
            <input type="password" name="haslo" required><br><br>
            <input type="submit" name="zaloguj" value="ZALOGUJ">
        </form>

    <?php else: ?>

        <a href="?wyloguj=1"><button>WYLOGUJ</button></a>
        <hr>

        <form method="post">
            Podaj klasę:
            <select name="klasa" onchange="this.form.submit()">
                <option value="">-- wybierz --</option>
                <?php
                $sql = "SELECT * FROM klasa";
                $wynik = mysqli_query($baza, $sql);

                while ($wiersz = mysqli_fetch_assoc($wynik)) {
                    $sel = "";
                    if (isset($_POST["klasa"]) && $_POST["klasa"] == $wiersz["id"]) {
                        $sel = "selected";
                    } elseif (!isset($_POST["klasa"]) && isset($_GET['klasa']) && $_GET['klasa'] == $wiersz["id"]) {
                        $sel = "selected";
                    }
                    echo "<option value='".$wiersz['id']."' ".$sel.">".$wiersz['nazwa']." - ".$wiersz['wychowawca']."</option>";
                }
                ?>
            </select>
        </form>

        <?php
        $id_k = null;
        if (isset($_POST["klasa"]) && $_POST["klasa"] != "") {
            $id_k = $_POST["klasa"];
        } elseif (isset($_GET["klasa"]) && $_GET["klasa"] != "") {
            $id_k = $_GET["klasa"];
        }

        if ($id_k) {
            $sql = "SELECT id, Imie, Nazwisko, Srednia_ocen FROM uczen WHERE id_klasy='$id_k'";
            $wynik = mysqli_query($baza, $sql);

            echo "<table border='1'>";
            echo "<tr><th>Imię</th><th>Nazwisko</th><th>Średnia ocen</th>";
            if ($id_k == $_SESSION['klasa_id']) {
                echo "<th>Akcja</th>";
            }
            echo "</tr>";

            while ($wiersz = mysqli_fetch_assoc($wynik)) {
                echo "<tr>";
                echo "<td>" . $wiersz["Imie"] . "</td>";
                echo "<td>" . $wiersz["Nazwisko"] . "</td>";
                echo "<td>" . $wiersz["Srednia_ocen"] . "</td>";
                if ($id_k == $_SESSION['klasa_id']) {
                    echo "<td><a href='?klasa=$id_k&usun=" . $wiersz["id"] . "'>Usuń</a></td>";
                }
                echo "</tr>";
            }
            echo "</table>";

            $sql = "SELECT AVG(Srednia_ocen) AS srednia FROM uczen WHERE id_klasy='$id_k'";
            $wynik = mysqli_query($baza, $sql);
            $wiersz = mysqli_fetch_assoc($wynik);

            echo "<br>Średnia klasy: " . $wiersz["srednia"];
        }
        ?>

        <?php if (isset($_SESSION['klasa_id'])): ?>
            <hr>
            <h3>Dodawanie nowego ucznia</h3>
            <form action="dodaj_ucznia.php" method="post">
                <input type="text" name="imie" placeholder="imię" required><br><br>
                <input type="text" name="nazwisko" placeholder="nazwisko" required><br><br>
                <input type="number" step="0.1" name="srednia_ocen" placeholder="średnia" required><br><br>
                <input type="hidden" name="id_klasy" value="<?= $_SESSION['klasa_id'] ?>">
                <input type="submit" name="zapisz_nowego" value="Dodaj">
            </form>
        <?php endif; ?>

    <?php endif; ?>

</body>
</html>