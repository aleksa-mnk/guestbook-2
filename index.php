<?php
    $host = 'localhost';
    $dbname = 'guestbook';
    $user = 'root';
    $pass = 'secretPass';

    $dsn = "mysql:host=$host;dbname=$dbname";

    // подключение
    try{
        $pdo = new PDO($dsn, $user, $pass);
    }catch(PDOExeption $e){
        $error_message = $e->getNessage();
        echo $error_message;
        exit();
    }

    // если act == del
    $act = isset($_GET['act']) ? $_GET['act'] : NULL;

    if($act == 'del'){
        // проверка, имеет ли пользователь доступ администратора
        $ipvx = substr($_SERVER['REMOTE_ADDR'], 0, 2);

        if($ipvx = '::'){ //IPv6
            $ip = substr($_SERVER['REMOTE_ADDR'], 2);
            if($ip >= 1 && $ip <= 128){
                $adm = true;
            }
        }else{ //IPv4
            $ip = ip2long($_SERVER['REMOTE_ADDR']);
            if((ip2long('127.0.0.0') <= $ip and $ip <= ip2long('127.255.255.255')) == bool(true)){
                $adm = true;
            }
        }

        if($adm){ // если пользователь является администратором
            $sqlDelete = "DELETE FROM messages WHERE id = ?";
            $delete = $pdo->prepare($sqlDelete)->execute([$_GET['id']]);
            echo "<script>alert('Сообщение было удалено!');</script>";
        }else{
            echo "<script>alert('Вы не администратор!');</script>";
        }
    }
        $rows = $pdo->query('SELECT * FROM messages ORDER BY 1');
        $rows = $rows->fetchAll();
    
        foreach($rows as $row){
            $id = $row[0];
            echo '<p>'.htmlspecialchars($row[1]).'</p>';
            echo '<a href="mailto:'. htmlspecialchars($row[2]) .'">' . htmlspecialchars($row[2]) . '</a>';
            echo '<p>'.htmlspecialchars($row[3]).'</p>';
            echo '<a href="index.php?act=del&id='. htmlspecialchars($id).'">' . htmlspecialchars("Delete message") . '</a>';
            echo '<hr>';
        }

    // если нажата button
    if ( isset( $_GET['button'] ) ) {
      // запись значений в переменные
      $email = $_GET['email'];
      $author = $_GET['author'];
      $message = $_GET['message'];

    // если поля заполнены верно
    if (trim($author) != "" && trim($message) != "" && filter_var($email, FILTER_VALIDATE_EMAIL)) {

        // добавление новых значений в гостевую книгу
        $sth = $pdo->prepare("INSERT INTO `messages` SET `author` = :author, `email` = :email, `message` = :message");
        $sth->execute(array('author' => $author, 'email' => $email, 'message' => $message));

        $rows = $pdo->query('SELECT * FROM messages ORDER BY 1');
        $numb_of_lines = ($rows->rowCount()) - 1;

        $rows = $rows->fetchAll();

        // отображение списка сообщений в гостевой книге
            if(isset($rows[$numb_of_lines])){
                $content = $rows[$numb_of_lines];
            }
            $id = $content[0];
            echo '<p>'.htmlspecialchars($content[1]).'</p>';
            echo '<a href="mailto:'. htmlspecialchars($content[2]) .'">' . htmlspecialchars($content[2]) . '</a>';
            echo '<p>'.htmlspecialchars($content[3]).'</p>';
            echo '<a href="index.php?act=del&id='. htmlspecialchars($id).'">' . htmlspecialchars("Delete message") . '</a>';
            echo '<hr>';

    }else{
        echo "<script>alert('Error while adding your message');</script>";
    }
    header('Location: http://localhost/dbex/index.php/');
    exit();
    }
?>

<form method="GET">
	<p>Your name: <input type="text" name="author"/></p>
	<p>Email: <input type="text" name="email"/></p>
	<p>Comment: <textarea name="message"></textarea></p>
	<p><input type='submit' name='button'/></p>
</form>